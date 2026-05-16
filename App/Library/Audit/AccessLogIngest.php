<?php

declare(strict_types=1);

namespace App\Library\Audit;

use Glial\Sgbd\Sgbd;

/**
 * Apache access log backfill (#1235, post-MVP step 1).
 *
 * Parses the combined-format access.log to populate `request_log` for
 * static assets the app-side RequestAuditCollector doesn't see (css,
 * images, fonts, favicon, etc.). Tracks last byte offset per log file
 * in `audit_apache_cursor` so subsequent runs resume from where they
 * stopped — log rotation (inode change) resets to byte 0.
 *
 * Heuristic to dedup against the app's own request_log rows: we IGNORE
 * any Apache row whose URI is already covered by an app-side row in
 * the same second (we'd get a duplicate). Apache rows are only kept
 * when they have NO matching request_uid — i.e. true static hits.
 *
 * Hot path safety: this class is invoked from `bin/audit_drain.php`,
 * never from a web request. Reading the access log requires sudo or
 * the operator running the drain as a user in the `adm` group.
 */
final class AccessLogIngest
{
    /** Combined log format: 1.2.3.4 - - [16/May/2026:00:00:43 +0200] "GET /uri HTTP/1.1" 200 3728 "ref" "ua" */
    private const COMBINED_RE =
        '/^(?P<ip>\S+)\s+\S+\s+\S+\s+\[(?P<date>[^\]]+)\]\s+"(?P<method>\S+)\s+(?P<uri>\S+)\s+\S+"\s+(?P<status>\d+)\s+(?P<bytes>\S+)(?:\s+"(?P<ref>[^"]*)"\s+"(?P<ua>[^"]*)")?/';

    /** @var \Glial\Sgbd\Sql\Sql */
    private $db;

    public function __construct()
    {
        $this->db = Sgbd::sql(DB_DEFAULT);
    }

    /**
     * Ingest one log file from its persisted cursor offset.
     *
     * @return array{ingested:int, skipped:int, last_byte:int}
     */
    public function ingestFile(string $logPath): array
    {
        if (!is_readable($logPath)) {
            return ['ingested' => 0, 'skipped' => 0, 'last_byte' => 0];
        }

        $stat = @stat($logPath);
        if ($stat === false) {
            return ['ingested' => 0, 'skipped' => 0, 'last_byte' => 0];
        }

        $cursor = $this->readCursor($logPath);
        $resumeOffset = (int) ($cursor['last_byte_offset'] ?? 0);
        $resumeInode  = (int) ($cursor['last_inode'] ?? 0);

        // If the inode rolled (logrotate), start from the beginning.
        if ($resumeInode !== 0 && $resumeInode !== $stat['ino']) {
            $resumeOffset = 0;
        }

        // If the file shrank (truncated externally), reset.
        if ($resumeOffset > $stat['size']) {
            $resumeOffset = 0;
        }

        $fh = @fopen($logPath, 'r');
        if ($fh === false) {
            return ['ingested' => 0, 'skipped' => 0, 'last_byte' => $resumeOffset];
        }
        if ($resumeOffset > 0) {
            @fseek($fh, $resumeOffset);
        }

        $ingested = 0;
        $skipped = 0;
        $buffer = [];
        while (($line = fgets($fh)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }
            $row = self::parseLine($line);
            if ($row === null) {
                $skipped++;
                continue;
            }
            // Only ingest static assets — anything the app's controller
            // would have handled is already in request_log via the spool.
            if (!self::looksLikeStaticAsset($row['uri'])) {
                $skipped++;
                continue;
            }
            $buffer[] = $row;
            if (count($buffer) >= 500) {
                $ingested += $this->insertBatch($buffer);
                $buffer = [];
            }
        }
        if ($buffer !== []) {
            $ingested += $this->insertBatch($buffer);
        }
        $newOffset = @ftell($fh);
        @fclose($fh);

        if ($newOffset === false) {
            $newOffset = $stat['size'];
        }

        $this->writeCursor($logPath, $stat['ino'], $newOffset, $stat['mtime'], $ingested, $skipped);
        return ['ingested' => $ingested, 'skipped' => $skipped, 'last_byte' => $newOffset];
    }

    /**
     * @return array{ip:string,date:string,method:string,uri:string,status:int,bytes:int,ref:?string,ua:?string}|null
     */
    public static function parseLine(string $line): ?array
    {
        if (preg_match(self::COMBINED_RE, $line, $m) !== 1) {
            return null;
        }
        // Apache date 16/May/2026:00:00:43 +0200 → DATETIME(3) with .000
        $dt = \DateTime::createFromFormat('d/M/Y:H:i:s O', $m['date']);
        if (!$dt) {
            return null;
        }
        return [
            'ip'     => $m['ip'],
            'date'   => $dt->format('Y-m-d H:i:s') . '.000',
            'method' => $m['method'],
            'uri'    => $m['uri'],
            'status' => (int) $m['status'],
            'bytes'  => $m['bytes'] === '-' ? 0 : (int) $m['bytes'],
            'ref'    => isset($m['ref']) && $m['ref'] !== '' ? $m['ref'] : null,
            'ua'     => isset($m['ua']) && $m['ua'] !== '' ? $m['ua'] : null,
        ];
    }

    /**
     * "Static asset" heuristic: matches .css/.js/.png/.jpg/.svg/.ico/.woff/.woff2/.ttf/.gif/.webp suffix,
     * or anything under /pmacontrol/{css,image,js,file}/. The app's request_log already
     * covers controller routes (/en/Foo/bar) — re-ingesting them would be a duplicate.
     */
    public static function looksLikeStaticAsset(string $uri): bool
    {
        // Strip query string.
        $path = $uri;
        if (($q = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $q);
        }
        if (preg_match('#\.(css|js|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|eot|webp|map|mp4|webm)$#i', $path) === 1) {
            return true;
        }
        if (preg_match('#/pmacontrol/(?:css|image|js|file|font)/#', $path) === 1) {
            return true;
        }
        return false;
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     */
    private function insertBatch(array $rows): int
    {
        if ($rows === []) {
            return 0;
        }
        $values = [];
        foreach ($rows as $r) {
            $uaHash = $r['ua'] !== null && $r['ua'] !== '' ? md5($r['ua']) : null;
            // Synthetic request_uid based on (ip, date, uri) so re-runs
            // of the ingester are idempotent (UNIQUE index on request_uid).
            $synthUid = md5('apache|' . $r['ip'] . '|' . $r['date'] . '|' . $r['uri'] . '|' . $r['method']);
            [$geoCountry, $geoCity] = GeoIpResolver::resolve((string) $r['ip']);
            $values[] = sprintf(
                "(%s,%s,NULL,%s,%s,%s,%s,%s,%d,%d,%s,%s,NULL,NULL,NULL,NULL,'apache_backfill')",
                $this->q($synthUid),
                $this->q($r['date']),
                $this->q($r['ip']),
                $this->q($geoCountry),
                $this->q($geoCity),
                $this->q($r['method']),
                $this->q(substr($r['uri'], 0, 2048)),
                $r['status'],
                $r['bytes'],
                $this->q($r['ref'] !== null ? substr($r['ref'], 0, 2048) : null),
                $this->q($uaHash)
            );
        }
        $sql = "INSERT IGNORE INTO request_log "
             . "(request_uid, date, id_user_main, ip, geo_country_iso, geo_city, method, uri, status, bytes_sent, referer, "
             . " user_agent_hash, duration_us, php_ms, controller, action, user_role_class) VALUES "
             . implode(',', $values);
        $this->db->sql_query($sql);
        return is_object($this->db) && method_exists($this->db, 'sql_affected_rows')
            ? (int) $this->db->sql_affected_rows()
            : count($rows);
    }

    /**
     * @return array{last_inode:int,last_byte_offset:int}|null
     */
    private function readCursor(string $path): ?array
    {
        $esc = $this->db->sql_real_escape_string($path);
        $res = $this->db->sql_query("SELECT last_inode, last_byte_offset FROM audit_apache_cursor WHERE log_path = '$esc'");
        if (!$res) return null;
        $row = $this->db->sql_fetch_array($res, MYSQLI_ASSOC);
        if (!$row) return null;
        return [
            'last_inode'        => (int) $row['last_inode'],
            'last_byte_offset'  => (int) $row['last_byte_offset'],
        ];
    }

    private function writeCursor(string $path, int $inode, int $offset, int $mtime, int $linesTotal, int $linesSkipped): void
    {
        $esc = $this->db->sql_real_escape_string($path);
        $mtimeStr = date('Y-m-d H:i:s', $mtime);
        $sql = "INSERT INTO audit_apache_cursor "
             . "(log_path, last_inode, last_byte_offset, last_mtime, last_run, lines_total, lines_skipped) VALUES "
             . "('$esc', $inode, $offset, '$mtimeStr', NOW(3), $linesTotal, $linesSkipped) "
             . "ON DUPLICATE KEY UPDATE "
             . " last_inode = VALUES(last_inode), "
             . " last_byte_offset = VALUES(last_byte_offset), "
             . " last_mtime = VALUES(last_mtime), "
             . " last_run = VALUES(last_run), "
             . " lines_total = lines_total + VALUES(lines_total), "
             . " lines_skipped = lines_skipped + VALUES(lines_skipped)";
        $this->db->sql_query($sql);
    }

    private function q($value): string
    {
        if ($value === null) return 'NULL';
        return "'" . $this->db->sql_real_escape_string((string) $value) . "'";
    }
}
