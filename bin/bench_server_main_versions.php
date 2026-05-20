#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Bench the version-resolution block of Server::main(): Extraction2-only
 * path (master pre-#1321) vs Variable::last + Extraction2 (current).
 * Average of N runs (default 10).
 *
 * Both paths return the same shape ([id => [name => value]]) and operate
 * on the same monitored-server set, so timings are directly comparable.
 *
 * Usage:
 *     php bin/bench_server_main_versions.php [iterations]
 */

use App\Library\Extraction2;
use App\Library\Variable;
use Glial\Sgbd\Sgbd;
use Glial\Synapse\Config;

if (PHP_SAPI !== 'cli') {
    die("CLI only\n");
}

$iterations = isset($argv[1]) ? max(1, (int) $argv[1]) : 10;

// Bootstrap (same pattern as bin/audit_drain.php).
define('IS_CLI', true);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));
define('APP_DIR', ROOT . DS . 'App');
define('TMP', ROOT . DS . 'tmp' . DS);
define('DATA', ROOT . DS . 'data' . DS);
define('CONFIG', ROOT . DS . 'configuration' . DS);
define('LIBRARY', ROOT . DS . 'library' . DS);
define('CORE_PATH', ROOT . DS);
define('LIB', CORE_PATH . 'lib' . DS);
define('WEBROOT_DIR', 'Webroot' . DS);
define('GLIAL_INDEX', ROOT . DS . 'App' . DS . 'Webroot' . DS . 'index.php');

require_once ROOT . DS . 'vendor/autoload.php';
require_once ROOT . DS . 'App' . DS . 'Webroot' . DS . 'Basic.php';
require_once CONFIG . 'db.config.php';

// vendor/autoload.php points at master's checkout, so the new Variable
// class on this worktree branch must be required explicitly until the PR lands.
if (!class_exists(Variable::class)) {
    require_once ROOT . DS . 'App' . DS . 'Library' . DS . 'Variable.php';
}

$config = new Config();
$config->load(CONFIG);
Sgbd::setConfig($config->get('db'));

// Extraction2 (via App\Library\Filter::getFilter) expects a $_SESSION array.
// Empty session = no per-tag/client filter applied, which matches the
// version-resolution call site in Server::main().
$_SESSION = $_SESSION ?? [];
$_GET = $_GET ?? [];

$db = Sgbd::sql(DB_DEFAULT);

// Same filter as Server::main() in monitored mode.
$sql = "SELECT a.id
        FROM mysql_server PARTITION(pn) AS a
        INNER JOIN client c ON c.id = a.id_client
        INNER JOIN environment d ON d.id = a.id_environment
        WHERE a.is_deleted = 0
          AND (CASE WHEN c.is_monitored = 0 THEN 0 ELSE a.is_monitored END) = 1
        ORDER BY a.id;";
$res = $db->sql_query($sql);
$servers = [];
while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
    $servers[] = (int) $row['id'];
}

if ($servers === []) {
    fwrite(STDERR, "No monitored servers found — nothing to bench.\n");
    exit(1);
}

$globalVarKeys = [
    "version", "version_comment", "have_ssl", "general_log",
    "wsrep_on", "is_proxysql", "performance_schema", "read_only",
    "variables::hostname",
];
$computedKeys = [
    "mysql_ping", "time_server", "wsrep_cluster_status",
    "mysql_available", "mysql_server::mysql_error", "avg_latency",
    "delta_sum_timer_wait", "delta_sum_lock_time",
];
$allKeys = array_merge($globalVarKeys, $computedKeys);

printf("Servers in scope: %d (monitored).\n", count($servers));
printf("Iterations: %d.\n\n", $iterations);

// Warmup (one of each) so OS page cache / mysql query plan cache don't bias the first sample.
Extraction2::display($allKeys);
Variable::last($globalVarKeys, $servers);

// --- Path A: master — Extraction2::display([all 17 vars])
$timesA = [];
for ($i = 0; $i < $iterations; $i++) {
    $t0 = microtime(true);
    $extra = Extraction2::display($allKeys);
    $timesA[] = (microtime(true) - $t0) * 1000.0;
    unset($extra);
}

// --- Path B: new — Variable(9) + Extraction2(8)
$timesB = [];
for ($i = 0; $i < $iterations; $i++) {
    $t0 = microtime(true);

    $fromGlobal = Variable::last($globalVarKeys, $servers);
    $fromExtraction2 = Extraction2::display($computedKeys);

    $extra = [];
    $allIds = array_unique(array_merge(array_keys($fromExtraction2), array_keys($fromGlobal)));
    foreach ($allIds as $id) {
        $extra[$id] = array_merge($fromExtraction2[$id] ?? [], $fromGlobal[$id] ?? []);
    }

    $timesB[] = (microtime(true) - $t0) * 1000.0;
    unset($extra, $fromGlobal, $fromExtraction2);
}

function stats(array $samples): array
{
    sort($samples);
    $n = count($samples);
    return [
        'min'    => $samples[0],
        'max'    => $samples[$n - 1],
        'mean'   => array_sum($samples) / $n,
        'median' => $samples[(int) floor($n / 2)],
    ];
}

$a = stats($timesA);
$b = stats($timesB);

printf("Path A — Extraction2::display(all 17 vars)         [pre-#1321]\n");
printf("    min %.2f ms | mean %.2f ms | median %.2f ms | max %.2f ms\n",
    $a['min'], $a['mean'], $a['median'], $a['max']);

printf("\nPath B — Variable::last(9) + Extraction2(8)      [#1330]\n");
printf("    min %.2f ms | mean %.2f ms | median %.2f ms | max %.2f ms\n",
    $b['min'], $b['mean'], $b['median'], $b['max']);

$delta = $a['mean'] - $b['mean'];
$ratio = $a['mean'] > 0 ? ($b['mean'] / $a['mean']) : 0;
printf("\nMean speedup: %+.2f ms saved (B is %.2fx of A).\n", $delta, $ratio);

// Sanity check: values must agree on shared keys per server.
$extraA = Extraction2::display($allKeys);
$fromGlobal = Variable::last($globalVarKeys, $servers);
$fromExtraction2 = Extraction2::display($computedKeys);
$extraB = [];
foreach (array_unique(array_merge(array_keys($fromExtraction2), array_keys($fromGlobal))) as $id) {
    $extraB[$id] = array_merge($fromExtraction2[$id] ?? [], $fromGlobal[$id] ?? []);
}

$mismatches = 0;
foreach ($servers as $id) {
    foreach ($globalVarKeys as $key) {
        $parts = explode('::', $key, 2);
        $name = strtolower(trim(end($parts)));
        $va = $extraA[$id][$name] ?? null;
        $vb = $extraB[$id][$name] ?? null;
        if ($va !== null && $vb !== null && (string) $va !== (string) $vb) {
            $mismatches++;
            if ($mismatches <= 5) {
                fwrite(STDERR, sprintf("  mismatch id=%d %s: A=%s | B=%s\n",
                    $id, $name, var_export($va, true), var_export($vb, true)));
            }
        }
    }
}
printf("\nValue cross-check (shared keys): %d mismatch(es).\n", $mismatches);
