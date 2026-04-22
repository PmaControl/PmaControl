#!/usr/bin/env php
<?php
/**
 * CLI runner for BinlogAnalyzer — invoked in background by the controller.
 * Usage: php run_binlog_analysis.php <analysis_id>
 */

if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}

if (!isset($argv[1]) || !is_numeric($argv[1])) {
    die("Usage: php run_binlog_analysis.php <analysis_id>\n");
}

$analysisId = (int) $argv[1];

// Bootstrap: pretend we're the index.php so ROOT resolves correctly
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__) . '/App/Webroot/index.php';
require_once dirname(__DIR__) . '/App/Webroot/index.php';

use App\Library\BinlogAnalyzer;
use App\Controller\Telegram;
use \Glial\Sgbd\Sgbd;

$analyzer = new BinlogAnalyzer($analysisId);
$ok = $analyzer->run();

// Send Telegram notification
$db = Sgbd::sql(DB_DEFAULT);
$res = $db->sql_query("SELECT ba.*, ms.display_name AS slave_name, ms.ip AS slave_ip,
                               mm.display_name AS master_name, mm.ip AS master_ip
                        FROM binlog_analysis ba
                        JOIN mysql_server ms ON ba.id_mysql_server = ms.id
                        JOIN mysql_server mm ON ba.id_mysql_server_master = mm.id
                        WHERE ba.id = $analysisId");
$a = $db->sql_fetch_array($res, MYSQLI_ASSOC);

if ($a) {
    $slaveName = $a['slave_name'] ?: $a['slave_ip'];
    $masterName = $a['master_name'] ?: $a['master_ip'];

    if ($a['status'] === 'done') {
        $totalRows = number_format($a['total_inserts'] + $a['total_updates'] + $a['total_deletes']);
        $sizeMb = round($a['total_size_bytes'] / 1048576, 1);

        $msg = "<b>Binlog Analysis Complete</b>\n"
             . "<b>Slave:</b> " . htmlspecialchars($slaveName) . "\n"
             . "<b>Master:</b> " . htmlspecialchars($masterName) . " (" . htmlspecialchars($a['mysql_version'] ?? '?') . ")\n"
             . "<b>Range:</b> " . $a['time_start'] . " → " . $a['time_end'] . "\n"
             . "━━━━━━━━━━━━━━━━━━━\n"
             . "<b>Size:</b> {$sizeMb} MB | <b>Txn:</b> " . number_format($a['total_transactions']) . " | <b>Duration:</b> {$a['duration_seconds']}s\n"
             . "<b>DML:</b> I:" . number_format($a['total_inserts']) . " U:" . number_format($a['total_updates']) . " D:" . number_format($a['total_deletes']) . " = {$totalRows} rows\n"
             . "<b>Peak:</b> {$a['peak_txn_per_sec']} txn/s | <b>Avg:</b> {$a['avg_txn_per_sec']} txn/s\n"
             . "<b>Sequential:</b> {$a['sequential_pct']}% | <b>Max parallel:</b> {$a['max_parallelism']}\n"
             . "<b>Large txn:</b> {$a['large_txn_100k']} >100K, {$a['large_txn_500k']} >500K (max " . round($a['max_txn_size_bytes'] / 1024) . " KB)\n"
             . "<b>Databases:</b> {$a['databases_count']}\n";

        $recs = json_decode($a['recommendations'] ?? '[]', true);
        if (!empty($recs)) {
            $msg .= "━━━━━━━━━━━━━━━━━━━\n";
            foreach (array_slice($recs, 0, 3) as $r) {
                $msg .= "• " . htmlspecialchars($r) . "\n";
            }
        }

        Telegram::broadcast($msg, 'HTML');
    } else {
        $msg = "<b>Binlog Analysis Failed</b>\n"
             . "<b>Slave:</b> " . htmlspecialchars($slaveName) . "\n"
             . "<b>Error:</b> " . htmlspecialchars(substr($a['error_message'] ?? 'Unknown', 0, 300));
        Telegram::broadcast($msg, 'HTML');
    }
}
