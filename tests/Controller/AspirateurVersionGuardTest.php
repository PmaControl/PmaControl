<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Tests for version-dependent query guards in Aspirateur.
 *
 * These tests validate the logic used to decide which SQL queries to run
 * based on the server version string, without requiring a live DB connection.
 */
final class AspirateurVersionGuardTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Helper: reproduce the version-detection logic used in Aspirateur
    // -------------------------------------------------------------------------

    private static function isMariaDB(string $version, string $comment = ''): bool
    {
        return (stripos($version, 'MariaDB') !== false) || (stripos($comment, 'MariaDB') !== false);
    }

    private static function numVersion(string $version): string
    {
        return preg_replace('/[^0-9.].*/', '', $version);
    }

    // -------------------------------------------------------------------------
    // Group Replication guard (fixes #1, #2, #3)
    // GR query should only run on MySQL >= 5.7.17 (not MariaDB)
    // MEMBER_ROLE column requires MySQL >= 8.0.2
    // -------------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\DataProvider('grQueryProvider')]
    public function testGroupReplicationQueryGuard(
        string $version,
        string $comment,
        bool $expectGrQuery,
        bool $expectMemberRole
    ): void {
        $isMariaDB = self::isMariaDB($version, $comment);
        $numVer = self::numVersion($version);

        $shouldRunGr = !$isMariaDB && version_compare($numVer, '5.7.17', '>=');
        $shouldUseMemberRole = version_compare($numVer, '8.0.2', '>=');

        $this->assertSame($expectGrQuery, $shouldRunGr, "GR query guard for $version");
        if ($expectGrQuery) {
            $this->assertSame($expectMemberRole, $shouldUseMemberRole, "MEMBER_ROLE for $version");
        }
    }

    public static function grQueryProvider(): array
    {
        return [
            'MariaDB 10.2'   => ['10.2.44-MariaDB-1:10.2.44+maria~bionic-log', '', false, false],
            'MariaDB 10.6'   => ['10.6.23-MariaDB-ubu2204-log', '', false, false],
            'MariaDB 10.11'  => ['10.11.16-MariaDB-deb12-log', '', false, false],
            'MariaDB 11.4'   => ['11.4.10-MariaDB-deb12-log', '', false, false],
            'MariaDB 11.8'   => ['11.8.6-MariaDB-deb13', '', false, false],
            'MariaDB 12.0'   => ['12.0.2-MariaDB-ubu2404-log', '', false, false],
            'MariaDB comment'=> ['10.6.23', 'MariaDB Server', false, false],
            'MySQL 5.0'      => ['5.0.96-log', 'MySQL Community Server', false, false],
            'MySQL 5.1'      => ['5.1.73-log', 'MySQL Community Server', false, false],
            'MySQL 5.5'      => ['5.5.62-log', 'MySQL Community Server', false, false],
            'MySQL 5.6'      => ['5.6.51-log', 'MySQL Community Server', false, false],
            'Percona 5.6'    => ['5.6.51-91.0', 'Percona Server', false, false],
            'MySQL 5.7'      => ['5.7.44-log', 'MySQL Community Server', true, false],
            'MySQL 8.0'      => ['8.0.44', 'MySQL Community Server', true, true],
            'MySQL 8.0.1'    => ['8.0.1', 'MySQL Community Server', true, false],
            'MySQL 8.0.2'    => ['8.0.2', 'MySQL Community Server', true, true],
            'MySQL 8.4'      => ['8.4.8', 'MySQL Community Server', true, true],
            'Percona 8.4'    => ['8.4.8-8', 'Percona Server', true, true],
            'Percona 8.0'    => ['8.0.36-28', 'Percona Server', true, true],
        ];
    }

    // -------------------------------------------------------------------------
    // INNODB_METRICS guard (fix #7)
    // Only available since MySQL 5.6 / MariaDB 10.0
    // -------------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\DataProvider('innodbMetricsProvider')]
    public function testInnodbMetricsGuard(string $version, bool $expected): void
    {
        $numVer = self::numVersion($version);
        $result = version_compare($numVer, '5.6.0', '>=');
        $this->assertSame($expected, $result, "INNODB_METRICS guard for $version");
    }

    public static function innodbMetricsProvider(): array
    {
        return [
            'MySQL 4.1'      => ['4.1.22-standard-log', false],
            'MySQL 5.0'      => ['5.0.96-log', false],
            'MySQL 5.1'      => ['5.1.73-log', false],
            'MySQL 5.5'      => ['5.5.62-log', false],
            'MySQL 5.6'      => ['5.6.51-log', true],
            'MySQL 5.7'      => ['5.7.44-log', true],
            'MySQL 8.0'      => ['8.0.44', true],
            'MySQL 8.4'      => ['8.4.8', true],
            'MariaDB 10.0'   => ['10.0.38-MariaDB-log', true],
            'MariaDB 10.2'   => ['10.2.44-MariaDB-1:10.2.44+maria~bionic-log', true],
            'MariaDB 10.11'  => ['10.11.16-MariaDB-deb12-log', true],
            'MariaDB 12.0'   => ['12.0.2-MariaDB-ubu2404-log', true],
        ];
    }

    // -------------------------------------------------------------------------
    // SCHEMA_COMMENT guard (fix #5)
    // Only available in MariaDB >= 10.5, not in MySQL
    // -------------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\DataProvider('schemaCommentProvider')]
    public function testSchemaCommentGuard(
        string $version,
        string $comment,
        bool $expected
    ): void {
        $isMariaDB = self::isMariaDB($version, $comment);
        $numVer = self::numVersion($version);
        $hasSchemaComment = $isMariaDB && version_compare($numVer, '10.5.0', '>=');

        $this->assertSame($expected, $hasSchemaComment, "SCHEMA_COMMENT for $version");
    }

    public static function schemaCommentProvider(): array
    {
        return [
            'MariaDB 10.2'   => ['10.2.44-MariaDB', '', false],
            'MariaDB 10.3'   => ['10.3.39-MariaDB-log', '', false],
            'MariaDB 10.4'   => ['10.4.34-MariaDB', '', false],
            'MariaDB 10.5'   => ['10.5.29-MariaDB-ubu2004-log', '', true],
            'MariaDB 10.6'   => ['10.6.23-MariaDB-deb11-log', '', true],
            'MariaDB 10.11'  => ['10.11.16-MariaDB-deb12-log', '', true],
            'MariaDB 11.4'   => ['11.4.10-MariaDB-deb12-log', '', true],
            'MariaDB 12.0'   => ['12.0.2-MariaDB-ubu2404-log', '', true],
            'MySQL 5.6'      => ['5.6.51-log', 'MySQL Community Server', false],
            'MySQL 5.7'      => ['5.7.44-log', 'MySQL Community Server', false],
            'MySQL 8.0'      => ['8.0.44', 'MySQL Community Server', false],
            'MySQL 8.4'      => ['8.4.8', 'MySQL Community Server', false],
            'Percona 5.6'    => ['5.6.51-91.0', 'Percona Server', false],
            'Percona 8.4'    => ['8.4.8-8', 'Percona Server', false],
        ];
    }

    // -------------------------------------------------------------------------
    // SHOW MASTER STATUS vs SHOW BINARY LOG STATUS (fix #8)
    // MySQL 8.4+ removed SHOW MASTER STATUS
    // -------------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\DataProvider('showMasterStatusProvider')]
    public function testShowMasterStatusGuard(
        string $version,
        string $comment,
        string $expectedCommand
    ): void {
        $isMariaDB = self::isMariaDB($version, $comment);
        $numVer = self::numVersion($version);

        // MariaDB always uses SHOW MASTER STATUS
        // MySQL >= 8.4 uses SHOW BINARY LOG STATUS
        if ($isMariaDB) {
            $cmd = 'SHOW MASTER STATUS';
        } elseif (version_compare($numVer, '8.4.0', '>=')) {
            $cmd = 'SHOW BINARY LOG STATUS';
        } else {
            $cmd = 'SHOW MASTER STATUS';
        }

        $this->assertSame($expectedCommand, $cmd, "Master status command for $version");
    }

    public static function showMasterStatusProvider(): array
    {
        return [
            'MySQL 5.6'      => ['5.6.51-log', 'MySQL Community Server', 'SHOW MASTER STATUS'],
            'MySQL 5.7'      => ['5.7.44-log', 'MySQL Community Server', 'SHOW MASTER STATUS'],
            'MySQL 8.0'      => ['8.0.44', 'MySQL Community Server', 'SHOW MASTER STATUS'],
            'MySQL 8.0.36'   => ['8.0.36-28', 'Percona Server', 'SHOW MASTER STATUS'],
            'MySQL 8.3'      => ['8.3.0', 'MySQL Community Server', 'SHOW MASTER STATUS'],
            'MySQL 8.4'      => ['8.4.8', 'MySQL Community Server', 'SHOW BINARY LOG STATUS'],
            'Percona 8.4'    => ['8.4.8-8', 'Percona Server', 'SHOW BINARY LOG STATUS'],
            'MySQL 9.0'      => ['9.0.0', 'MySQL Community Server', 'SHOW BINARY LOG STATUS'],
            'MariaDB 10.6'   => ['10.6.23-MariaDB', '', 'SHOW MASTER STATUS'],
            'MariaDB 10.11'  => ['10.11.16-MariaDB-deb12-log', '', 'SHOW MASTER STATUS'],
            'MariaDB 11.8'   => ['11.8.6-MariaDB-deb13', '', 'SHOW MASTER STATUS'],
        ];
    }

    // -------------------------------------------------------------------------
    // SHOW SLAVE STATUS display command (earlier fix in Slave view)
    // -------------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\DataProvider('showSlaveStatusProvider')]
    public function testShowSlaveStatusCommand(
        string $serverType,
        string $version,
        string $replicationName,
        string $expected
    ): void {
        $isMariaDB = (stripos($serverType, 'mariadb') !== false);
        $isMySQLNewSyntax = !$isMariaDB && version_compare($version, '8.0.22', '>=');

        if (empty($replicationName)) {
            $show = $isMySQLNewSyntax ? 'SHOW REPLICA STATUS;' : 'SHOW SLAVE STATUS;';
        } elseif ($isMariaDB) {
            $show = "SHOW SLAVE '" . $replicationName . "' STATUS;";
        } elseif ($isMySQLNewSyntax) {
            $show = "SHOW REPLICA STATUS FOR CHANNEL '" . $replicationName . "';";
        } else {
            $show = "SHOW SLAVE STATUS FOR CHANNEL '" . $replicationName . "';";
        }

        $this->assertSame($expected, $show, "SHOW SLAVE/REPLICA for $serverType $version");
    }

    public static function showSlaveStatusProvider(): array
    {
        return [
            'MariaDB named'       => ['MariaDB', '10.6.23', 'production_es', "SHOW SLAVE 'production_es' STATUS;"],
            'MariaDB empty'       => ['MariaDB', '10.6.23', '', 'SHOW SLAVE STATUS;'],
            'MySQL 5.7 named'     => ['MySQL', '5.7.44', 'channel1', "SHOW SLAVE STATUS FOR CHANNEL 'channel1';"],
            'MySQL 5.7 empty'     => ['MySQL', '5.7.44', '', 'SHOW SLAVE STATUS;'],
            'MySQL 8.0.22 named'  => ['MySQL', '8.0.22', 'channel1', "SHOW REPLICA STATUS FOR CHANNEL 'channel1';"],
            'MySQL 8.0.22 empty'  => ['MySQL', '8.0.22', '', 'SHOW REPLICA STATUS;'],
            'MySQL 8.4 named'     => ['MySQL', '8.4.8', 'group_replication_applier', "SHOW REPLICA STATUS FOR CHANNEL 'group_replication_applier';"],
            'Percona 8.4 named'   => ['Percona', '8.4.8', 'channel1', "SHOW REPLICA STATUS FOR CHANNEL 'channel1';"],
            'MySQL 8.0.21 named'  => ['MySQL', '8.0.21', 'ch', "SHOW SLAVE STATUS FOR CHANNEL 'ch';"],
        ];
    }

    // -------------------------------------------------------------------------
    // numVersion extraction helper
    // -------------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\DataProvider('numVersionProvider')]
    public function testNumVersionExtraction(string $fullVersion, string $expected): void
    {
        $this->assertSame($expected, self::numVersion($fullVersion));
    }

    public static function numVersionProvider(): array
    {
        return [
            'plain'          => ['8.0.44', '8.0.44'],
            'MariaDB suffix' => ['10.11.16-MariaDB-deb12-log', '10.11.16'],
            'Percona'        => ['8.4.8-8', '8.4.8'],
            'log suffix'     => ['5.5.62-log', '5.5.62'],
            'complex'        => ['10.2.44-MariaDB-1:10.2.44+maria~bionic-log', '10.2.44'],
            'enterprise'     => ['10.6.19-15-MariaDB-enterprise-log', '10.6.19'],
            'standard'       => ['4.1.22-standard-log', '4.1.22'],
        ];
    }
}
