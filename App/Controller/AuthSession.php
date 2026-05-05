<?php

namespace App\Controller;

use \Glial\Synapse\Controller;

/**
 * SuperAdmin diagnostics for the persistent auth session pipeline.
 *
 * Parses tmp/log/error_php.log and surfaces the events emitted by
 * App\Library\Security\PersistentAuthSession::logRevoke() and
 * ::logRotationLostRace() (issue #773), so an operator can see at a glance
 * whether the grace window absorbs concurrent rotations cleanly or whether
 * something heavier (slow request, > grace-window stalls, fingerprint
 * drift) is still kicking users out.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @license GPL-3.0
 */
class AuthSession extends Controller
{
    public function tokenMismatch()
    {
        // Resolved at call time so the class can load in test contexts where
        // the framework constant TMP has not been defined yet.
        $file = (defined('TMP') ? TMP : '/tmp/').'log/error_php.log';

        $data = [
            'log_file'   => $file,
            'log_exists' => is_file($file),
            'log_size'   => is_file($file) ? filesize($file) : 0,
            'events'     => [],
            'reasons'    => [],
            'totals'     => [
                'total'        => 0,
                'last_24h'     => 0,
                'last_7d'      => 0,
                'token_mismatch_total' => 0,
                'rotation_lost_race_total' => 0,
            ],
            'now'        => time(),
        ];

        if ($data['log_exists']) {
            $report = self::parseLog($file);
            $data['events']  = $report['events'];
            $data['reasons'] = $report['reasons'];
            $data['totals']  = $report['totals'];
        }

        $this->set('data', $data);
    }

    /**
     * Scan the PHP error log for PersistentAuthSession events. The line
     * format is whatever PHP error_log() writes:
     *
     *   [DD-MMM-YYYY HH:MM:SS UTC] PersistentAuthSession: revoking selector=AAAA… reason=R remote=IP ua=UA
     *   [DD-MMM-YYYY HH:MM:SS UTC] PersistentAuthSession: rotation_lost_race selector=AAAA… remote=IP — accepted via grace window (issue #773)
     *
     * Static so tests can exercise it without booting the framework
     * (Glial\Synapse\Controller's constructor requires 3 DI args).
     */
    public static function parseLog(string $file): array
    {
        $events  = [];
        $reasons = [];

        $now = time();
        $threshold24h = $now - 86400;
        $threshold7d  = $now - 7 * 86400;

        $patternRevoke = '/^\[([^\]]+)\]\s+PersistentAuthSession:\s+revoking\s+selector=([a-f0-9]+)\S*\s+reason=(\S+)\s+remote=(\S*)\s+ua=(.*)$/';
        $patternRace   = '/^\[([^\]]+)\]\s+PersistentAuthSession:\s+rotation_lost_race\s+selector=([a-f0-9]+)\S*\s+remote=(\S*)\b/';

        $totals = [
            'total'                    => 0,
            'last_24h'                 => 0,
            'last_7d'                  => 0,
            'token_mismatch_total'     => 0,
            'rotation_lost_race_total' => 0,
        ];

        if (!is_file($file)) {
            return ['events' => [], 'reasons' => [], 'totals' => $totals];
        }

        $fh = @fopen($file, 'r');
        if ($fh === false) {
            return ['events' => [], 'reasons' => [], 'totals' => $totals];
        }

        while (($line = fgets($fh)) !== false) {
            if (strpos($line, 'PersistentAuthSession') === false) {
                continue;
            }

            if (preg_match($patternRevoke, $line, $m)) {
                $ts        = self::parseTimestamp($m[1]);
                $selector  = $m[2];
                $reason    = $m[3];
                $remote    = $m[4];
                $userAgent = trim($m[5]);

                $events[] = [
                    'ts_text'  => $m[1],
                    'ts'       => $ts,
                    'kind'     => 'revoke',
                    'reason'   => $reason,
                    'selector' => $selector,
                    'remote'   => $remote,
                    'ua'       => $userAgent,
                ];

                if (!isset($reasons[$reason])) {
                    $reasons[$reason] = ['count' => 0, 'last_seen_ts' => 0, 'last_seen_text' => ''];
                }
                $reasons[$reason]['count']++;
                if ($ts !== null && $ts > $reasons[$reason]['last_seen_ts']) {
                    $reasons[$reason]['last_seen_ts']   = $ts;
                    $reasons[$reason]['last_seen_text'] = $m[1];
                }

                $totals['total']++;
                if ($reason === 'token_mismatch') {
                    $totals['token_mismatch_total']++;
                }
                if ($ts !== null) {
                    if ($ts >= $threshold24h) {
                        $totals['last_24h']++;
                    }
                    if ($ts >= $threshold7d) {
                        $totals['last_7d']++;
                    }
                }
                continue;
            }

            if (preg_match($patternRace, $line, $m)) {
                $ts       = self::parseTimestamp($m[1]);
                $selector = $m[2];
                $remote   = $m[3];

                $events[] = [
                    'ts_text'  => $m[1],
                    'ts'       => $ts,
                    'kind'     => 'race',
                    'reason'   => 'rotation_lost_race',
                    'selector' => $selector,
                    'remote'   => $remote,
                    'ua'       => '',
                ];

                if (!isset($reasons['rotation_lost_race'])) {
                    $reasons['rotation_lost_race'] = ['count' => 0, 'last_seen_ts' => 0, 'last_seen_text' => ''];
                }
                $reasons['rotation_lost_race']['count']++;
                if ($ts !== null && $ts > $reasons['rotation_lost_race']['last_seen_ts']) {
                    $reasons['rotation_lost_race']['last_seen_ts']   = $ts;
                    $reasons['rotation_lost_race']['last_seen_text'] = $m[1];
                }

                $totals['rotation_lost_race_total']++;
                $totals['total']++;
                if ($ts !== null) {
                    if ($ts >= $threshold24h) {
                        $totals['last_24h']++;
                    }
                    if ($ts >= $threshold7d) {
                        $totals['last_7d']++;
                    }
                }
            }
        }
        fclose($fh);

        // Sort events newest first; cap to the last 200 to keep the page
        // readable when the log is huge.
        usort($events, function ($a, $b) {
            return ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0);
        });
        if (count($events) > 200) {
            $events = array_slice($events, 0, 200);
        }

        uasort($reasons, fn($a, $b) => $b['count'] <=> $a['count']);

        return [
            'events'  => $events,
            'reasons' => $reasons,
            'totals'  => $totals,
        ];
    }

    /**
     * PHP error_log timestamps look like "06-May-2026 00:11:42 UTC".
     * strtotime understands them but returns false on noise, in which case
     * we surface null so the caller can keep the raw text without breaking
     * arithmetic.
     */
    private static function parseTimestamp(string $raw): ?int
    {
        $ts = strtotime($raw);
        return $ts === false ? null : $ts;
    }
}
