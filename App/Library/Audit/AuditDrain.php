<?php

declare(strict_types=1);

namespace App\Library\Audit;

use Glial\Sgbd\Sgbd;

/**
 * Audit drain (#1235).
 *
 * Reads the NDJSON spool files under `tmp/audit/{requests,client_metrics,
 * auth_events}/<date>.ndjson`, INSERTs them into the corresponding tables
 * in batches, and rotates the spool file once fully consumed. Runs as a
 * CLI worker (cron once a minute is plenty for normal traffic).
 *
 * Design contract — keeps the hot path zero-DB:
 *   1. RequestAuditCollector / AuthEventCollector / ClientMetricsCollector
 *      append NDJSON lines from the shutdown handler.
 *   2. This drain reads, parses, dedups UA into `audit_user_agent`,
 *      INSERTs (batched), then renames the consumed spool file to a
 *      `.consumed.<ts>` suffix that a separate purge job removes.
 *
 * Idempotency:
 *   - request_log has UNIQUE(request_uid) so re-running the drain after
 *     a partial failure is safe (we use INSERT IGNORE).
 *   - audit_user_agent uses INSERT … ON DUPLICATE KEY UPDATE last_seen,
 *     hit_count = hit_count + 1.
 */
final class AuditDrain
{
    /** @var int How many rows we INSERT per multi-row VALUES statement. */
    private const BATCH = 200;

    /** @var \Glial\Sgbd\Sql\Sql */
    private $db;
    /** @var string */
    private $spoolRoot;

    public function __construct(?string $spoolRoot = null)
    {
        $this->db = Sgbd::sql(DB_DEFAULT);
        $this->spoolRoot = $spoolRoot
            ?? (defined('TMP') ? rtrim(\constant('TMP'), '/') . '/audit/' : '/srv/www/pmacontrol/tmp/audit/');
    }

    /**
     * Run all sub-drains in sequence. Returns a summary the CLI can echo.
     *
     * @return array<string,int>
     */
    public function run(): array
    {
        $summary = [];
        $summary['requests']        = $this->drainRequests();
        $summary['auth_events']     = $this->drainAuthEvents();
        $summary['client_metrics']  = $this->drainClientMetrics();
        $summary['subprocess']      = $this->drainSubprocess();
        return $summary;
    }

    private function drainRequests(): int
    {
        return $this->drainDirectory('requests', function (array $rows): int {
            return $this->insertRequests($rows);
        });
    }

    private function drainAuthEvents(): int
    {
        return $this->drainDirectory('auth_events', function (array $rows): int {
            return $this->insertAuthEvents($rows);
        });
    }

    private function drainClientMetrics(): int
    {
        return $this->drainDirectory('client_metrics', function (array $rows): int {
            return $this->insertClientMetrics($rows);
        });
    }

    private function drainSubprocess(): int
    {
        return $this->drainDirectory('subprocess', function (array $rows): int {
            return $this->insertSubprocess($rows);
        });
    }

    /**
     * Generic file walker — invokes the given inserter for every batch
     * of rows. Returns the total number of rows actually inserted.
     */
    private function drainDirectory(string $subdir, callable $inserter): int
    {
        $dir = $this->spoolRoot . $subdir . '/';
        if (!is_dir($dir)) {
            return 0;
        }
        $total = 0;
        foreach (glob($dir . '*.ndjson') ?: [] as $path) {
            // Don't touch a file modified less than a second ago — gives the
            // append() running on the hot path a chance to finish its line.
            if (filemtime($path) >= time() - 0) {
                clearstatcache(true, $path);
            }
            $fh = @fopen($path, 'r');
            if ($fh === false) {
                continue;
            }
            $buffer = [];
            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $row = json_decode($line, true);
                if (!is_array($row)) {
                    continue;
                }
                $buffer[] = $row;
                if (count($buffer) >= self::BATCH) {
                    $total += $inserter($buffer);
                    $buffer = [];
                }
            }
            if ($buffer !== []) {
                $total += $inserter($buffer);
            }
            @fclose($fh);
            // Rotate the consumed file so we don't re-read it next tick.
            $consumed = $path . '.consumed.' . date('Ymd-His');
            @rename($path, $consumed);
        }
        return $total;
    }

    /**
     * Insert a batch of request_log rows. Also upserts unique UAs into
     * audit_user_agent (parsed once, results cached forever).
     */
    private function insertRequests(array $rows): int
    {
        if ($rows === []) return 0;

        $this->upsertUserAgents($rows);

        $values = [];
        foreach ($rows as $r) {
            $values[] = sprintf(
                "(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                $this->q($r['request_uid'] ?? ''),
                $this->q($r['date'] ?? null),
                $r['id_user_main'] !== null ? (int) $r['id_user_main'] : 'NULL',
                $this->q($r['ip'] ?? ''),
                $this->q($r['method'] ?? 'GET'),
                $this->q($r['uri'] ?? ''),
                isset($r['status']) && $r['status'] !== null ? (int) $r['status'] : 'NULL',
                isset($r['bytes_sent']) && $r['bytes_sent'] !== null ? (int) $r['bytes_sent'] : 'NULL',
                $this->q($r['referer'] ?? null),
                $this->q($r['user_agent_hash'] ?? null),
                isset($r['duration_us']) && $r['duration_us'] !== null ? (int) $r['duration_us'] : 'NULL',
                isset($r['php_ms']) && $r['php_ms'] !== null ? (int) $r['php_ms'] : 'NULL',
                $this->q($r['controller'] ?? null),
                $this->q($r['action'] ?? null),
                $this->q($r['user_role_class'] ?? null)
            );
        }
        $sql = "INSERT IGNORE INTO request_log "
             . "(request_uid, date, id_user_main, ip, method, uri, status, bytes_sent, referer, "
             . " user_agent_hash, duration_us, php_ms, controller, action, user_role_class) VALUES "
             . implode(',', $values);
        $this->db->sql_query($sql);
        return is_object($this->db) && method_exists($this->db, 'sql_affected_rows')
            ? (int) $this->db->sql_affected_rows()
            : count($rows);
    }

    /**
     * Parse + upsert unique user_agent_raw values into audit_user_agent.
     * Each request_log row carries `user_agent_raw` in the spool but only
     * `user_agent_hash` in the DB — the raw is parsed once here.
     */
    private function upsertUserAgents(array $rows): void
    {
        $byHash = [];
        foreach ($rows as $r) {
            $raw  = $r['user_agent_raw']  ?? null;
            $hash = $r['user_agent_hash'] ?? null;
            $date = $r['date'] ?? null;
            if ($raw === null || $hash === null) continue;
            if (isset($byHash[$hash])) continue;
            $byHash[$hash] = ['raw' => $raw, 'date' => $date];
        }
        if ($byHash === []) return;

        $values = [];
        foreach ($byHash as $hash => $payload) {
            $parsed = UaParser::parse((string) $payload['raw']);
            $raw    = substr((string) $payload['raw'], 0, 500);
            $date   = $payload['date'];
            $values[] = sprintf(
                "(%s, %s, %s, %s, 1, %s, %s, %s, %s, %s, %d, %s)",
                $this->q($hash),
                $this->q($raw),
                $this->q($date),
                $this->q($date),
                $this->q($parsed['os_family']),
                $this->q($parsed['os_version']),
                $this->q($parsed['browser_family']),
                $this->q($parsed['browser_version']),
                $this->q($parsed['device_type']),
                $parsed['is_robot'] ? 1 : 0,
                $this->q($parsed['robot_family'])
            );
        }
        $sql = "INSERT INTO audit_user_agent "
             . "(ua_hash, ua_raw, first_seen, last_seen, hit_count, os_family, os_version, "
             . " browser_family, browser_version, device_type, is_robot, robot_family) VALUES "
             . implode(',', $values)
             . " ON DUPLICATE KEY UPDATE last_seen = VALUES(last_seen), hit_count = hit_count + 1";
        $this->db->sql_query($sql);
    }

    private function insertAuthEvents(array $rows): int
    {
        if ($rows === []) return 0;
        $values = [];
        $allowed = ['login_success','login_fail','logout','token_mismatch','fingerprint_mismatch',
                    'expired','unknown_selector','csrf_reject','rate_limit','password_change'];
        foreach ($rows as $r) {
            $event = (string) ($r['event_type'] ?? 'login_fail');
            if (!in_array($event, $allowed, true)) {
                continue;
            }
            $values[] = sprintf(
                "(%s, %s, %s, %s, %s, %s, %s, %s)",
                $this->q($r['request_uid'] ?? null),
                $this->q($r['date'] ?? null),
                $r['id_user_main'] !== null ? (int) $r['id_user_main'] : 'NULL',
                $this->q($r['ip'] ?? ''),
                $this->q($r['user_agent_hash'] ?? null),
                $this->q($event),
                $this->q($r['selector_prefix'] ?? null),
                $this->q($r['detail'] ?? null)
            );
        }
        if ($values === []) return 0;
        $sql = "INSERT INTO auth_event (request_uid, date, id_user_main, ip, user_agent_hash, "
             . " event_type, selector_prefix, detail) VALUES " . implode(',', $values);
        $this->db->sql_query($sql);
        return count($values);
    }

    private function insertClientMetrics(array $rows): int
    {
        if ($rows === []) return 0;
        $inserted = 0;
        foreach ($rows as $r) {
            // Normalize GPU/RAM/CPU here (Codex anti-fingerprint requirement).
            $gpuFamily = isset($r['gpu_renderer_raw']) ? UaParser::normaliseGpu((string) $r['gpu_renderer_raw']) : null;
            $ramBucket = UaParser::ramBucket(isset($r['device_memory']) ? (float) $r['device_memory'] : null);
            $cpuBucket = UaParser::cpuBucket(isset($r['hw_concurrency']) ? (int) $r['hw_concurrency'] : null);

            // Upsert (LCP/CLS/INP arrive after the initial POST).
            $sql = sprintf(
                "INSERT INTO audit_client_metrics (
                    request_uid, date, id_user_main, route_controller, route_action, user_role_class,
                    page_lifecycle, timezone_offset,
                    screen_width, screen_height, screen_avail_width, screen_avail_height,
                    color_depth, pixel_depth, device_pixel_ratio,
                    viewport_width, viewport_height, document_width, document_height,
                    orientation_type, orientation_angle,
                    gpu_family, ram_bucket, cpu_bucket, max_touch_points,
                    js_heap_used_mb, js_heap_total_mb, js_heap_limit_mb,
                    nav_dns_ms, nav_tcp_ms, nav_ttfb_ms, nav_download_ms,
                    nav_dom_ready_ms, nav_load_ms, nav_first_paint_ms, nav_fcp_ms,
                    cwv_lcp_ms, cwv_cls, cwv_inp_ms, cwv_lcp_rating, cwv_cls_rating, cwv_inp_rating,
                    net_effective_type, net_rtt_ms, net_downlink_mbps, net_save_data,
                    bat_level_pct, bat_charging, bat_charging_time_s, bat_discharging_time_s,
                    prefers_dark, prefers_reduced_motion,
                    nav_lang, nav_languages, nav_platform, nav_vendor, nav_cookie_enabled, nav_online,
                    visibility_state, ua_client_hints,
                    schema_version, collector_version, capture_ts_ms, payload_size_bytes,
                    degraded_capture, missing_fields_bm, collector_errors
                ) VALUES (
                    %s,%s,%s,%s,%s,%s,
                    %s,%s,
                    %s,%s,%s,%s,
                    %s,%s,%s,
                    %s,%s,%s,%s,
                    %s,%s,
                    %s,%s,%s,%s,
                    %s,%s,%s,
                    %s,%s,%s,%s,
                    %s,%s,%s,%s,
                    %s,%s,%s,%s,%s,%s,
                    %s,%s,%s,%s,
                    %s,%s,%s,%s,
                    %s,%s,
                    %s,%s,%s,%s,%s,%s,
                    %s,%s,
                    %s,%s,%s,%s,
                    %s,%s,%s
                ) ON DUPLICATE KEY UPDATE
                    cwv_lcp_ms = COALESCE(VALUES(cwv_lcp_ms), cwv_lcp_ms),
                    cwv_cls    = COALESCE(VALUES(cwv_cls),    cwv_cls),
                    cwv_inp_ms = COALESCE(VALUES(cwv_inp_ms), cwv_inp_ms),
                    cwv_lcp_rating = COALESCE(VALUES(cwv_lcp_rating), cwv_lcp_rating),
                    cwv_cls_rating = COALESCE(VALUES(cwv_cls_rating), cwv_cls_rating),
                    cwv_inp_rating = COALESCE(VALUES(cwv_inp_rating), cwv_inp_rating)",
                $this->q($r['request_uid'] ?? ''),
                $this->q($r['date'] ?? null),
                $r['id_user_main'] !== null ? (int) ($r['id_user_main'] ?? 0) : 'NULL',
                $this->q($r['route_controller'] ?? null),
                $this->q($r['route_action'] ?? null),
                $this->q($r['user_role_class'] ?? null),
                $this->q($r['page_lifecycle'] ?? null),
                isset($r['timezone_offset']) ? (int) $r['timezone_offset'] : 'NULL',
                $this->nullInt($r, 'screen_width'),  $this->nullInt($r, 'screen_height'),
                $this->nullInt($r, 'screen_avail_width'), $this->nullInt($r, 'screen_avail_height'),
                $this->nullInt($r, 'color_depth'), $this->nullInt($r, 'pixel_depth'),
                isset($r['device_pixel_ratio']) ? sprintf('%.2f', (float) $r['device_pixel_ratio']) : 'NULL',
                $this->nullInt($r, 'viewport_width'),  $this->nullInt($r, 'viewport_height'),
                $this->nullInt($r, 'document_width'),  $this->nullInt($r, 'document_height'),
                $this->q($r['orientation_type'] ?? null), $this->nullInt($r, 'orientation_angle'),
                $this->q($gpuFamily), $this->q($ramBucket), $this->q($cpuBucket), $this->nullInt($r, 'max_touch_points'),
                $this->nullInt($r, 'js_heap_used_mb'), $this->nullInt($r, 'js_heap_total_mb'), $this->nullInt($r, 'js_heap_limit_mb'),
                $this->nullInt($r, 'nav_dns_ms'), $this->nullInt($r, 'nav_tcp_ms'),
                $this->nullInt($r, 'nav_ttfb_ms'), $this->nullInt($r, 'nav_download_ms'),
                $this->nullInt($r, 'nav_dom_ready_ms'), $this->nullInt($r, 'nav_load_ms'),
                $this->nullInt($r, 'nav_first_paint_ms'), $this->nullInt($r, 'nav_fcp_ms'),
                $this->nullInt($r, 'cwv_lcp_ms'),
                isset($r['cwv_cls']) ? sprintf('%.3f', (float) $r['cwv_cls']) : 'NULL',
                $this->nullInt($r, 'cwv_inp_ms'),
                $this->q($r['cwv_lcp_rating'] ?? null),
                $this->q($r['cwv_cls_rating'] ?? null),
                $this->q($r['cwv_inp_rating'] ?? null),
                $this->q($r['net_effective_type'] ?? null), $this->nullInt($r, 'net_rtt_ms'),
                isset($r['net_downlink_mbps']) ? sprintf('%.2f', (float) $r['net_downlink_mbps']) : 'NULL',
                isset($r['net_save_data']) ? (int) (bool) $r['net_save_data'] : 'NULL',
                $this->nullInt($r, 'bat_level_pct'),
                isset($r['bat_charging']) ? (int) (bool) $r['bat_charging'] : 'NULL',
                $this->nullInt($r, 'bat_charging_time_s'), $this->nullInt($r, 'bat_discharging_time_s'),
                isset($r['prefers_dark']) ? (int) (bool) $r['prefers_dark'] : 'NULL',
                isset($r['prefers_reduced_motion']) ? (int) (bool) $r['prefers_reduced_motion'] : 'NULL',
                $this->q($r['nav_lang'] ?? null), $this->q($r['nav_languages'] ?? null),
                $this->q($r['nav_platform'] ?? null), $this->q($r['nav_vendor'] ?? null),
                isset($r['nav_cookie_enabled']) ? (int) (bool) $r['nav_cookie_enabled'] : 'NULL',
                isset($r['nav_online']) ? (int) (bool) $r['nav_online'] : 'NULL',
                $this->q($r['visibility_state'] ?? null),
                $this->q(isset($r['ua_client_hints']) ? json_encode($r['ua_client_hints']) : null),
                isset($r['schema_version']) ? (int) $r['schema_version'] : 1,
                $this->q($r['collector_version'] ?? '1.0'),
                isset($r['capture_ts_ms']) ? (int) $r['capture_ts_ms'] : 'NULL',
                $this->nullInt($r, 'payload_size_bytes'),
                isset($r['degraded_capture']) ? (int) (bool) $r['degraded_capture'] : 0,
                isset($r['missing_fields_bm']) ? (int) $r['missing_fields_bm'] : 0,
                $this->q($r['collector_errors'] ?? null)
            );
            $this->db->sql_query($sql);
            $inserted++;
        }
        return $inserted;
    }

    private function insertSubprocess(array $rows): int
    {
        if ($rows === []) return 0;
        $values = [];
        $allowedStatus = ['running','done','failed','timeout','killed'];
        foreach ($rows as $r) {
            $status = (string) ($r['status'] ?? 'running');
            if (!in_array($status, $allowedStatus, true)) $status = 'running';
            $values[] = sprintf(
                "(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                $this->q($r['request_uid'] ?? null),
                $this->q($r['date_start'] ?? null),
                $this->q($r['date_end'] ?? null),
                isset($r['duration_ms']) && $r['duration_ms'] !== null ? (int) $r['duration_ms'] : 'NULL',
                isset($r['pid']) && $r['pid'] !== null ? (int) $r['pid'] : 'NULL',
                isset($r['process_start_ticks']) && $r['process_start_ticks'] !== null ? (int) $r['process_start_ticks'] : 'NULL',
                $this->q($r['command'] ?? ''),
                $this->q($r['label'] ?? null),
                isset($r['exit_code']) && $r['exit_code'] !== null ? (int) $r['exit_code'] : 'NULL',
                $this->q($r['log_path'] ?? null),
                $this->q($status)
            );
        }
        if ($values === []) return 0;
        $sql = "INSERT INTO subprocess_log "
             . "(request_uid, date_start, date_end, duration_ms, pid, process_start_ticks, "
             . " command, label, exit_code, log_path, status) VALUES " . implode(',', $values);
        $this->db->sql_query($sql);
        return count($values);
    }

    /**
     * Quote a value for direct interpolation; NULL → SQL NULL.
     */
    private function q($value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        return "'" . $this->db->sql_real_escape_string((string) $value) . "'";
    }

    private function nullInt(array $row, string $key): string
    {
        return (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '')
            ? (string) (int) $row[$key]
            : 'NULL';
    }
}
