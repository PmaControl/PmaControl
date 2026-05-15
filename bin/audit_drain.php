#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Audit drain CLI runner (#1235).
 *
 * Consumes the NDJSON spool under tmp/audit/{requests,client_metrics,
 * auth_events,subprocess}/ and INSERTs into the corresponding tables.
 * Run from cron once a minute, or manually for ad-hoc drains.
 *
 * Usage:
 *   sudo -u www-data php bin/audit_drain.php
 */

use App\Library\Audit\AuditDrain;
use Glial\Sgbd\Sgbd;
use Glial\Synapse\Config;

define('IS_CLI', PHP_SAPI === 'cli');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));
define('APP_DIR', ROOT.DS.'App');
define('TMP', ROOT.DS.'tmp'.DS);
define('DATA', ROOT.DS.'data'.DS);
define('CONFIG', ROOT.DS.'configuration'.DS);
define('LIBRARY', ROOT.DS.'library'.DS);
define('CORE_PATH', ROOT.DS);
define('LIB', CORE_PATH.'lib'.DS);
define('WEBROOT_DIR', 'Webroot'.DS);
define('GLIAL_INDEX', ROOT.DS.'App'.DS.'Webroot'.DS.'index.php');

require_once ROOT.DS.'vendor/autoload.php';
require_once ROOT.DS.'App'.DS.'Webroot'.DS.'Basic.php';
require_once CONFIG.'db.config.php';

$config = new Config();
$config->load(CONFIG);
Sgbd::setConfig($config->get('db'));

$start = microtime(true);
$drain = new AuditDrain();
$summary = $drain->run();
$elapsed = round((microtime(true) - $start) * 1000);

echo sprintf(
    "[audit_drain] %s — %d requests, %d auth_events, %d client_metrics, %d subprocess (%d ms)\n",
    date('c'),
    $summary['requests'],
    $summary['auth_events'],
    $summary['client_metrics'],
    $summary['subprocess'],
    $elapsed
);
