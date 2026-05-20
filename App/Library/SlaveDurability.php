<?php

declare(strict_types=1);

namespace App\Library;

/**
 * Issue #1185 — surface the MySQL/MariaDB durability + crash-safety
 * variables on `/slave/show/<id>/<conn>/`.
 *
 * The helper turns each variable's current value into a 4-tuple the
 * view can render directly:
 *
 *   [
 *     'name'    => string  // canonical variable name
 *     'value'   => string  // value as reported by SHOW VARIABLES
 *     'level'   => string  // one of 'ok', 'warn', 'risk', 'unknown'
 *     'label'   => string  // short human label for the value
 *     'tooltip' => string  // long English tooltip explaining what the
 *                          // value does and the recommended setting
 *   ]
 *
 * Keeping the rendering rules out of the view means PHPUnit can pin
 * each value → level mapping deterministically (no DOM scraping).
 */
final class SlaveDurability
{
    public const LEVEL_OK      = 'ok';
    public const LEVEL_WARN    = 'warn';
    public const LEVEL_RISK    = 'risk';
    public const LEVEL_UNKNOWN = 'unknown';

    /**
     * Variables we surface (canonical name → first-class display name).
     * Both legacy and 8.0+ aliases are accepted on input; the canonical
     * name in the output is the modern one when both exist.
     *
     * @return list<string>
     */
    public static function variableKeys(): array
    {
        return [
            'sync_binlog',
            'innodb_flush_log_at_trx_commit',
            'binlog_format',
            'binlog_row_image',
            'replica_preserve_commit_order', // alias: slave_preserve_commit_order
            'source_info_repository',        // alias: master_info_repository
            'relay_log_info_repository',
            'relay_log_recovery',
            'super_read_only',
        ];
    }

    /**
     * Build the per-variable rendering rows from a flat
     * `[variable_name => value]` map (typically what
     * `Extraction::display(['variables::*'])` returns).
     *
     * @param array<string,scalar|null> $values raw `[name => value]`
     * @param array<string,scalar|null> $context extra signals used for
     *        cross-variable rules; today only `parallel_threads` matters
     *        (preserve_commit_order risk is conditional on parallel > 1)
     * @return list<array{name:string,value:string,level:string,label:string,tooltip:string}>
     */
    public static function rows(array $values, array $context = []): array
    {
        $get = static function (array $names) use ($values): ?string {
            foreach ($names as $n) {
                if (array_key_exists($n, $values) && $values[$n] !== null && $values[$n] !== '') {
                    return (string) $values[$n];
                }
            }
            return null;
        };

        $rows = [];

        // --- sync_binlog -----------------------------------------------
        $v = $get(['sync_binlog']);
        $rows[] = self::syncBinlogRow($v);

        // --- innodb_flush_log_at_trx_commit ----------------------------
        $v = $get(['innodb_flush_log_at_trx_commit']);
        $rows[] = self::trxCommitRow($v);

        // --- binlog_format ---------------------------------------------
        $v = $get(['binlog_format']);
        $rows[] = self::binlogFormatRow($v);

        // --- binlog_row_image ------------------------------------------
        $v = $get(['binlog_row_image']);
        $rows[] = self::binlogRowImageRow($v);

        // --- replica/slave_preserve_commit_order -----------------------
        $v = $get(['replica_preserve_commit_order', 'slave_preserve_commit_order']);
        $rows[] = self::preserveCommitOrderRow($v, $context);

        // --- source_info_repository / master_info_repository -----------
        $v = $get(['source_info_repository', 'master_info_repository']);
        $rows[] = self::infoRepositoryRow('source_info_repository', $v);

        // --- relay_log_info_repository ---------------------------------
        $v = $get(['relay_log_info_repository']);
        $rows[] = self::infoRepositoryRow('relay_log_info_repository', $v);

        // --- relay_log_recovery ----------------------------------------
        $v = $get(['relay_log_recovery']);
        $rows[] = self::relayLogRecoveryRow($v);

        // --- super_read_only -------------------------------------------
        $v = $get(['super_read_only']);
        $rows[] = self::superReadOnlyRow($v);

        return $rows;
    }

    private static function unknown(string $name, string $explainer): array
    {
        return [
            'name'    => $name,
            'value'   => 'n/a',
            'level'   => self::LEVEL_UNKNOWN,
            'label'   => 'n/a',
            'tooltip' => 'Variable not reported by the metrics pipeline yet. ' . $explainer,
        ];
    }

    private static function syncBinlogRow(?string $v): array
    {
        if ($v === null) {
            return self::unknown('sync_binlog',
                'Controls how often the binlog is fsync()ed to disk. '
                . 'Recommended: 1 (fsync at every commit, ACID-safe).');
        }
        $iv = (int) $v;
        $tooltip = 'sync_binlog controls how often MySQL fsync()s the binary log. '
                 . '0 = never (the OS decides; transactions can be lost on crash). '
                 . '1 = at every commit (fully ACID, slowest). '
                 . 'N>1 = group commits, fsync every N transactions (compromise). '
                 . 'Recommended for production replicas / sources: 1.';
        if ($iv === 0)  return self::row('sync_binlog', $v, self::LEVEL_RISK, '0 — never fsync', $tooltip);
        if ($iv === 1)  return self::row('sync_binlog', $v, self::LEVEL_OK,   '1 — ACID',        $tooltip);
        return self::row('sync_binlog', $v, self::LEVEL_WARN, $v . ' — group commit', $tooltip);
    }

    private static function trxCommitRow(?string $v): array
    {
        if ($v === null) {
            return self::unknown('innodb_flush_log_at_trx_commit',
                'Controls how often the InnoDB redo log is fsync()ed. '
                . 'Recommended: 1 (ACID).');
        }
        $iv = (int) $v;
        $tooltip = 'innodb_flush_log_at_trx_commit controls when the InnoDB redo log is written and fsync()ed. '
                 . '0 = log buffer written + fsync()ed once per second; up to 1 s of committed transactions can be lost on crash. '
                 . '1 = written + fsync()ed at every commit (ACID, default, recommended). '
                 . '2 = written at every commit but fsync()ed once per second; survives mysqld crash but not OS crash.';
        if ($iv === 0) return self::row('innodb_flush_log_at_trx_commit', $v, self::LEVEL_RISK, '0 — flush every 1 s', $tooltip);
        if ($iv === 1) return self::row('innodb_flush_log_at_trx_commit', $v, self::LEVEL_OK,   '1 — ACID',           $tooltip);
        if ($iv === 2) return self::row('innodb_flush_log_at_trx_commit', $v, self::LEVEL_WARN, '2 — fsync every 1 s', $tooltip);
        return self::row('innodb_flush_log_at_trx_commit', $v, self::LEVEL_WARN, $v . ' — non-standard', $tooltip);
    }

    private static function binlogFormatRow(?string $v): array
    {
        if ($v === null) {
            return self::unknown('binlog_format', 'Recommended: ROW (deterministic replication).');
        }
        $u = strtoupper($v);
        $tooltip = 'binlog_format selects the binary-log event format used by the source. '
                 . 'ROW = full before/after row images, fully deterministic, recommended in production. '
                 . 'STATEMENT = original SQL replayed on the replica; non-deterministic functions (UUID(), NOW(), …) cause silent divergence. '
                 . 'MIXED = STATEMENT with auto-fallback to ROW for unsafe statements.';
        if ($u === 'ROW')       return self::row('binlog_format', $u, self::LEVEL_OK,   'ROW',       $tooltip);
        if ($u === 'MIXED')     return self::row('binlog_format', $u, self::LEVEL_WARN, 'MIXED',     $tooltip);
        if ($u === 'STATEMENT') return self::row('binlog_format', $u, self::LEVEL_RISK, 'STATEMENT', $tooltip);
        return self::row('binlog_format', $u, self::LEVEL_WARN, $u, $tooltip);
    }

    private static function binlogRowImageRow(?string $v): array
    {
        if ($v === null) {
            return self::unknown('binlog_row_image',
                'Recommended: FULL (or MINIMAL only if binlog volume is the proven bottleneck).');
        }
        $u = strtoupper($v);
        $tooltip = 'binlog_row_image controls how much of each row is logged when binlog_format=ROW. '
                 . 'FULL = every column logged (safe baseline). '
                 . 'MINIMAL = only PK + changed columns; smaller binlogs but breaks tools that need before-images (pt-table-checksum, some CDC consumers). '
                 . 'NOBLOB = like FULL but skips BLOB/TEXT unless modified.';
        if ($u === 'FULL')    return self::row('binlog_row_image', $u, self::LEVEL_OK,   'FULL',    $tooltip);
        if ($u === 'MINIMAL') return self::row('binlog_row_image', $u, self::LEVEL_WARN, 'MINIMAL', $tooltip);
        if ($u === 'NOBLOB')  return self::row('binlog_row_image', $u, self::LEVEL_WARN, 'NOBLOB',  $tooltip);
        return self::row('binlog_row_image', $u, self::LEVEL_WARN, $u, $tooltip);
    }

    private static function preserveCommitOrderRow(?string $v, array $context): array
    {
        if ($v === null) {
            return self::unknown('replica_preserve_commit_order',
                'Recommended: ON when parallel workers > 1.');
        }
        $on = self::isOn($v);
        $threads = (int) ($context['parallel_threads'] ?? 0);
        $tooltip = 'replica_preserve_commit_order (alias: slave_preserve_commit_order) preserves the source commit order '
                 . 'across parallel applier workers. ON is required for sound point-in-time recovery and to avoid '
                 . 'replica-side gaps in GTID order. The flag is harmless when parallel workers = 0/1; '
                 . 'when parallel > 1 it should always be ON.';
        if ($on && $threads > 1)  return self::row('replica_preserve_commit_order', 'ON',  self::LEVEL_OK,   'ON',  $tooltip);
        if ($on)                  return self::row('replica_preserve_commit_order', 'ON',  self::LEVEL_OK,   'ON',  $tooltip);
        if ($threads > 1)         return self::row('replica_preserve_commit_order', 'OFF', self::LEVEL_RISK, 'OFF (parallel > 1)', $tooltip);
        return self::row('replica_preserve_commit_order', 'OFF', self::LEVEL_WARN, 'OFF', $tooltip);
    }

    private static function infoRepositoryRow(string $name, ?string $v): array
    {
        if ($v === null) {
            return self::unknown($name, 'Recommended: TABLE (crash-safe).');
        }
        $u = strtoupper($v);
        $tooltip = $name . ' selects how MySQL persists replication metadata between restarts. '
                 . 'TABLE = stored in mysql.* tables, crash-safe, transactional with the data — required for crash-safe replicas. '
                 . 'FILE = stored in flat files (relay-log.info, master.info), not crash-safe.';
        if ($u === 'TABLE') return self::row($name, $u, self::LEVEL_OK,   'TABLE', $tooltip);
        if ($u === 'FILE')  return self::row($name, $u, self::LEVEL_WARN, 'FILE',  $tooltip);
        return self::row($name, $u, self::LEVEL_WARN, $u, $tooltip);
    }

    private static function relayLogRecoveryRow(?string $v): array
    {
        if ($v === null) {
            return self::unknown('relay_log_recovery',
                'Recommended: ON on a replica (crash-safe relay-log handling).');
        }
        $on = self::isOn($v);
        $tooltip = 'relay_log_recovery rebuilds the relay log from the source after a crash so the SQL thread '
                 . 'restarts on a known-good position. Required for crash-safe replicas. '
                 . 'OFF means a crashed replica may apply duplicate events or skip events on restart.';
        return $on
            ? self::row('relay_log_recovery', 'ON',  self::LEVEL_OK,   'ON',  $tooltip)
            : self::row('relay_log_recovery', 'OFF', self::LEVEL_RISK, 'OFF', $tooltip);
    }

    private static function superReadOnlyRow(?string $v): array
    {
        if ($v === null) {
            return self::unknown('super_read_only',
                'Recommended: ON on every replica that should never accept writes (even from SUPER users).');
        }
        $on = self::isOn($v);
        $tooltip = 'super_read_only extends read_only to SUPER-privileged users. '
                 . 'ON guarantees no write can land on the replica (apart from the SQL applier). '
                 . 'OFF on a replica leaves a window for accidental writes via root or DBAs.';
        return $on
            ? self::row('super_read_only', 'ON',  self::LEVEL_OK,   'ON',  $tooltip)
            : self::row('super_read_only', 'OFF', self::LEVEL_WARN, 'OFF', $tooltip);
    }

    private static function isOn(string $v): bool
    {
        $u = strtoupper(trim($v));
        return in_array($u, ['1', 'ON', 'TRUE', 'YES'], true);
    }

    /**
     * @return array{name:string,value:string,level:string,label:string,tooltip:string}
     */
    private static function row(string $name, string $value, string $level, string $label, string $tooltip): array
    {
        return [
            'name'    => $name,
            'value'   => $value,
            'level'   => $level,
            'label'   => $label,
            'tooltip' => $tooltip,
        ];
    }
}
