<?php

namespace App\Controller;

use \Glial\Synapse\Controller;

/**
 * Parse tmp/log/error_php.log and group PHP warnings/notices/deprecations per page.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @license GPL-3.0
 */
class Error extends Controller
{
    const LOG_FILE = TMP."log/error_php.log";

    public function index()
    {
        $file = self::LOG_FILE;

        $data = [
            'log_file'   => $file,
            'log_exists' => is_file($file),
            'log_size'   => is_file($file) ? filesize($file) : 0,
            'pages'      => [],
            'orphans'    => [],
            'totals'     => ['pages' => 0, 'page_occurrences' => 0, 'orphan_occurrences' => 0],
        ];

        if ($data['log_exists']) {
            $report = $this->parseLog($file);
            $data['pages']   = $report['pages'];
            $data['orphans'] = $report['orphans'];
            $data['totals']  = $report['totals'];
        }

        $this->set('data', $data);
    }

    /**
     * Parse the PHP error log and group each stanza (header + stack trace) by
     * the Controller/action recovered from the FactoryController::rootNode frame.
     */
    private function parseLog(string $file): array
    {
        $pages   = [];
        $orphans = [];
        $current = null;

        $flush = function () use (&$current, &$pages, &$orphans) {
            if ($current === null) {
                return;
            }
            $sig = $current['level'].'|'.$current['msg'].'|'.$current['file'].':'.$current['line'];
            if ($current['page'] !== null) {
                if (!isset($pages[$current['page']][$sig])) {
                    $pages[$current['page']][$sig] = [
                        'level'     => $current['level'],
                        'msg'       => $current['msg'],
                        'file'      => $current['file'],
                        'line'      => $current['line'],
                        'count'     => 0,
                        'last_seen' => $current['ts'],
                    ];
                }
                $pages[$current['page']][$sig]['count']++;
                $pages[$current['page']][$sig]['last_seen'] = $current['ts'];
            } else {
                if (!isset($orphans[$sig])) {
                    $orphans[$sig] = [
                        'level'     => $current['level'],
                        'msg'       => $current['msg'],
                        'file'      => $current['file'],
                        'line'      => $current['line'],
                        'count'     => 0,
                        'last_seen' => $current['ts'],
                    ];
                }
                $orphans[$sig]['count']++;
                $orphans[$sig]['last_seen'] = $current['ts'];
            }
            $current = null;
        };

        $fh = fopen($file, 'r');
        if ($fh === false) {
            return ['pages' => [], 'orphans' => [], 'totals' => ['pages' => 0, 'page_occurrences' => 0, 'orphan_occurrences' => 0]];
        }

        $headerLevel = '/^\[([^\]]+)\]\s+PHP (Warning|Notice|Deprecated|Fatal error|Parse error):\s+(.*?) in (\/[^ ]+) on line (\d+)/';
        $headerFatal = '/^\[([^\]]+)\]\s+PHP (Fatal error):\s+Uncaught\s+(.*?) in (\/[^:]+):(\d+)/';
        $traceRoute  = "/rootNode\(\\\$controller\s*=\s*'([^']+)',\s*\\\$action\s*=\s*'([^']+)'/";

        while (($line = fgets($fh)) !== false) {
            if (preg_match($headerLevel, $line, $m)) {
                $flush();
                $current = [
                    'ts'    => $m[1],
                    'level' => $m[2],
                    'msg'   => $this->normalizeMessage($m[3]),
                    'file'  => $m[4],
                    'line'  => (int) $m[5],
                    'page'  => null,
                ];
                continue;
            }
            if (preg_match($headerFatal, $line, $m)) {
                $flush();
                $current = [
                    'ts'    => $m[1],
                    'level' => $m[2],
                    'msg'   => $this->normalizeMessage($m[3]),
                    'file'  => $m[4],
                    'line'  => (int) $m[5],
                    'page'  => null,
                ];
                continue;
            }
            if ($current !== null && preg_match($traceRoute, $line, $m)) {
                $current['page'] = $m[1].'::'.$m[2];
            }
        }
        $flush();
        fclose($fh);

        ksort($pages);
        foreach ($pages as $page => &$items) {
            uasort($items, fn($a, $b) => $b['count'] <=> $a['count']);
        }
        unset($items);
        uasort($orphans, fn($a, $b) => $b['count'] <=> $a['count']);

        $pageOccurrences   = 0;
        foreach ($pages as $items) {
            $pageOccurrences += array_sum(array_column($items, 'count'));
        }
        $orphanOccurrences = array_sum(array_column($orphans, 'count'));

        return [
            'pages'   => $pages,
            'orphans' => $orphans,
            'totals'  => [
                'pages'              => count($pages),
                'page_occurrences'   => $pageOccurrences,
                'orphan_occurrences' => $orphanOccurrences,
            ],
        ];
    }

    /**
     * Collapse volatile numeric values so equivalent messages share a signature.
     */
    private function normalizeMessage(string $msg): string
    {
        $msg = preg_replace('/Write of \d+ bytes/', 'Write of N bytes', $msg);
        $msg = preg_replace('/\b\d{4,}\b/', 'N', $msg);
        return trim($msg);
    }
}
