<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1192 — pin the fast-path that bypasses live replica-status /
 * heavy page prep when the operator visits `/slave/show/<id>/__new__/`.
 * Without it the page does a SHOW REPLICA STATUS, several full
 * Extraction passes, and emits a 5-second `slave/getLag/<id>/__new__/`
 * poll loop. The fast-path may still read cached local metrics for the
 * source-tab strip.
 */
final class SlaveShowNewFastPathTest extends TestCase
{
    private string $code;

    protected function setUp(): void
    {
        $code = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        $this->assertNotFalse($code);
        $this->code = $code;
    }

    public function testShowEarlyReturnsOnNewReplicationName(): void
    {
        // The early return must live BEFORE getParallelReplicationSettingsFromTimeSeries
        // (the first heavy call), otherwise we still pay the time-series
        // round-trip on every __new__ visit.
        $earlyPos = strpos($this->code, "if (\$replication_name === '__new__') {");
        $heavyPos = strpos($this->code, 'getParallelReplicationSettingsFromTimeSeries((int) $id_mysql_server)');
        $this->assertNotFalse($earlyPos, 'early-return guard for __new__ must exist');
        $this->assertNotFalse($heavyPos, 'getParallelReplicationSettingsFromTimeSeries must exist');
        $this->assertLessThan($heavyPos, $earlyPos, 'early return must come BEFORE the heavy time-series prep');
    }

    public function testShowDelegatesToPrepareNewReplicationFormData(): void
    {
        $this->assertStringContainsString(
            'self::prepareNewReplicationFormData($db, $data, (int) $id_mysql_server, $_SESSION);',
            $this->code,
            'Slave::show must delegate to the dedicated helper'
        );
        $this->assertStringContainsString(
            'private static function prepareNewReplicationFormData(',
            $this->code,
            'helper must be declared'
        );
    }

    public function testHelperOnlyRunsLocalCachedLookups(): void
    {
        $start = strpos($this->code, 'function prepareNewReplicationFormData');
        $this->assertNotFalse($start);
        $end = strpos($this->code, "\n    }\n", $start + 50);
        $this->assertNotFalse($end);
        $body = substr($this->code, $start, $end - $start);

        // The helper may read local cached channel metrics and the local
        // mysql_server list; it must not connect to the target server.
        $this->assertStringContainsString(
            "\$data['all_connections']  = self::buildReplicationConnectionTabsFromCachedMetrics((int) \$idMysqlServer);",
            $body
        );
        $this->assertStringContainsString('SELECT a.id, a.display_name, a.ip, a.port, b.libelle AS environment', $body);

        // Must not call any of the heavy or live target-server code paths.
        foreach ([
            'getParallelReplicationSettingsFromTimeSeries',
            'buildDurabilityRows',
            'fetchReplicaStatusRows',
            'getReplicationLagVariables',
            'Mysql::getMaster',
            'Mysql::getDbLink',
            'Sgbd::sql($server',
        ] as $heavy) {
            $this->assertStringNotContainsString(
                $heavy,
                $body,
                "fast-path helper must not call $heavy"
            );
        }
    }

    public function testFastPathSetsTheDataDefaultsTheViewExpects(): void
    {
        $start = strpos($this->code, 'function prepareNewReplicationFormData');
        $end = strpos($this->code, "\n    }\n", $start + 50);
        $body = substr($this->code, $start, $end - $start);

        // The view's header/tab-strip + the optional CSRF banner read
        // these fields with `?? ''` / `?? []` guards. Pin them so a
        // future contributor cannot accidentally drop one and explode
        // the page on /slave/show/<id>/__new__/ without warning.
        foreach ([
            "\$data['slave']            = [];",
            "\$data['parallel_threads'] = 0;",
            "\$data['parallel_mode']    = null;",
            "\$data['durability_rows']  = [];",
            "\$data['all_connections']  = self::buildReplicationConnectionTabsFromCachedMetrics((int) \$idMysqlServer);",
            "\$data['server_type']      = '';",
            "\$data['server_version']   = '';",
            "\$data['available_servers'] = [];",
            "\$data['cpu_count']        = 0;",
            "\$data['binlog_gap']       = null;",
            "\$data['sparkline']        = '';",
        ] as $needle) {
            $this->assertStringContainsString($needle, $body, "missing default: $needle");
        }
    }

    public function testFastPathSkipsTheGetLagPollLoopJs(): void
    {
        // The 5-second setInterval lives inside `code_javascript(`
        // emitted later in show(). The early return prevents that
        // emit, but pin the chain explicitly: the JS must reference
        // `slave/getLag/` and the early return must come before the
        // call site.
        $earlyReturn = strpos($this->code, "if (\$replication_name === '__new__') {");
        $jsEmit      = strpos($this->code, "\$.getJSON(GLIAL_LINK + \"slave/getLag/");
        $this->assertNotFalse($earlyReturn);
        $this->assertNotFalse($jsEmit);
        $this->assertLessThan(
            $jsEmit,
            $earlyReturn,
            'early-return must precede the setInterval JS emit so __new__ pages stay quiet'
        );
    }
}
