<?php

namespace Tests\Controller;

use App\Controller\Aspirateur;
use PHPUnit\Framework\TestCase;

class AspirateurGroupReplicationProbeTest extends TestCase
{
    private function invokePrivate(object $object, string $method, array $arguments = [])
    {
        $reflection = new \ReflectionClass($object);
        $instanceMethod = $reflection->getMethod($method);

        return $instanceMethod->invokeArgs($object, $arguments);
    }

    private function newAspirateur(): Aspirateur
    {
        $reflection = new \ReflectionClass(Aspirateur::class);

        return $reflection->newInstanceWithoutConstructor();
    }

    public function testGroupReplicationProbeSkipsMariaDbWithoutQueries(): void
    {
        $db = new class {
            public $queries = array();

            public function sql_query($sql, $table = "", $type = "")
            {
                $this->queries[] = $sql;

                return false;
            }
        };

        $status = $this->invokePrivate(
            $this->newAspirateur(),
            'getGroupReplicationStatusFromConnection',
            [$db, '10.11.16-MariaDB-deb12-log', '']
        );

        $this->assertSame(array(), $status);
        $this->assertSame(array(), $db->queries);
    }

    public function testGroupReplicationProbeSkipsWhenTableIsMissing(): void
    {
        $db = new GroupReplicationProbeFakeDb(false, true, true, 'server-uuid', array());

        $status = $this->invokePrivate(
            $this->newAspirateur(),
            'getGroupReplicationStatusFromConnection',
            [$db, '8.0.44', 'MySQL Community Server']
        );

        $this->assertSame(array(), $status);
        $this->assertCount(1, $db->queries);
        $this->assertStringContainsString('information_schema.tables', $db->queries[0]);
    }

    public function testGroupReplicationProbeSkipsWhenServerUuidIsMissing(): void
    {
        $db = new GroupReplicationProbeFakeDb(true, true, true, '', array());

        $status = $this->invokePrivate(
            $this->newAspirateur(),
            'getGroupReplicationStatusFromConnection',
            [$db, '8.0.44', 'MySQL Community Server']
        );

        $this->assertSame(array(), $status);
        $this->assertStringContainsString("SHOW VARIABLES LIKE 'server_uuid'", end($db->queries));
        $this->assertFalse($this->queriesContain($db->queries, '@@server_uuid'));
    }

    public function testGroupReplicationProbeUsesLiteralServerUuidAndMemberRoleWhenAvailable(): void
    {
        $db = new GroupReplicationProbeFakeDb(
            true,
            true,
            true,
            "server'uuid",
            array('MEMBER_ROLE' => 'PRIMARY', 'MEMBER_STATE' => 'ONLINE')
        );

        $status = $this->invokePrivate(
            $this->newAspirateur(),
            'getGroupReplicationStatusFromConnection',
            [$db, '8.0.44', 'MySQL Community Server']
        );

        $this->assertSame(array('gr_member_role' => 'PRIMARY', 'gr_member_state' => 'ONLINE'), $status);
        $finalQuery = end($db->queries);
        $this->assertStringContainsString('MEMBER_ROLE, MEMBER_STATE', $finalQuery);
        $this->assertStringContainsString("MEMBER_ID = 'server\\'uuid'", $finalQuery);
        $this->assertFalse($this->queriesContain($db->queries, '@@server_uuid'));
    }

    public function testGroupReplicationProbeFallsBackWhenMemberRoleColumnIsMissing(): void
    {
        $db = new GroupReplicationProbeFakeDb(
            true,
            false,
            true,
            'server-uuid',
            array('MEMBER_ROLE' => '', 'MEMBER_STATE' => 'ONLINE')
        );

        $status = $this->invokePrivate(
            $this->newAspirateur(),
            'getGroupReplicationStatusFromConnection',
            [$db, '5.7.44-log', 'MySQL Community Server']
        );

        $this->assertSame(array('gr_member_role' => '', 'gr_member_state' => 'ONLINE'), $status);
        $this->assertStringContainsString("'' AS MEMBER_ROLE", end($db->queries));
    }

    public function testGroupReplicationProbeCachesCapabilitiesBetweenCalls(): void
    {
        $aspirateur = $this->newAspirateur();
        $db = new GroupReplicationProbeFakeDb(
            true,
            true,
            true,
            'server-uuid',
            array('MEMBER_ROLE' => 'SECONDARY', 'MEMBER_STATE' => 'ONLINE')
        );

        $firstStatus = $this->invokePrivate(
            $aspirateur,
            'getGroupReplicationStatusFromConnection',
            [$db, '8.0.44', 'MySQL Community Server']
        );
        $queriesAfterFirstCall = count($db->queries);

        $secondStatus = $this->invokePrivate(
            $aspirateur,
            'getGroupReplicationStatusFromConnection',
            [$db, '8.0.44', 'MySQL Community Server']
        );
        $secondCallQueries = array_slice($db->queries, $queriesAfterFirstCall);

        $this->assertSame(array('gr_member_role' => 'SECONDARY', 'gr_member_state' => 'ONLINE'), $firstStatus);
        $this->assertSame($firstStatus, $secondStatus);
        $this->assertCount(1, $secondCallQueries);
        $this->assertStringContainsString('performance_schema.replication_group_members', $secondCallQueries[0]);
        $this->assertFalse($this->queriesContain($secondCallQueries, 'information_schema.'));
        $this->assertFalse($this->queriesContain($secondCallQueries, "SHOW VARIABLES LIKE 'server_uuid'"));
    }

    private function queriesContain(array $queries, string $needle): bool
    {
        foreach ($queries as $query) {
            if (strpos($query, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}

class GroupReplicationProbeFakeDb
{
    public $queries = array();
    private $tableExists;
    private $memberRoleExists;
    private $memberStateExists;
    private $serverUuid;
    private $statusRow;

    public function __construct(
        bool $tableExists,
        bool $memberRoleExists,
        bool $memberStateExists,
        string $serverUuid,
        array $statusRow
    ) {
        $this->tableExists = $tableExists;
        $this->memberRoleExists = $memberRoleExists;
        $this->memberStateExists = $memberStateExists;
        $this->serverUuid = $serverUuid;
        $this->statusRow = $statusRow;
    }

    public function sql_real_escape_string($value): string
    {
        return addslashes((string)$value);
    }

    public function sql_query($sql, $table = "", $type = "")
    {
        $this->queries[] = $sql;

        if (strpos($sql, 'information_schema.tables') !== false) {
            return 'table_exists';
        }

        if (strpos($sql, "column_name = 'MEMBER_ROLE'") !== false) {
            return 'member_role_exists';
        }

        if (strpos($sql, "column_name = 'MEMBER_STATE'") !== false) {
            return 'member_state_exists';
        }

        if (strpos($sql, "SHOW VARIABLES LIKE 'server_uuid'") !== false) {
            return 'server_uuid';
        }

        if (strpos($sql, 'performance_schema.replication_group_members') !== false) {
            return 'group_replication_status';
        }

        return false;
    }

    public function sql_fetch_array($res, $mode)
    {
        if ($res === 'table_exists') {
            return $this->tableExists ? array(1) : null;
        }

        if ($res === 'member_role_exists') {
            return $this->memberRoleExists ? array(1) : null;
        }

        if ($res === 'member_state_exists') {
            return $this->memberStateExists ? array(1) : null;
        }

        if ($res === 'server_uuid') {
            return $this->serverUuid !== '' ? array('Value' => $this->serverUuid) : null;
        }

        if ($res === 'group_replication_status') {
            return !empty($this->statusRow) ? $this->statusRow : null;
        }

        return null;
    }
}
