<?php

declare(strict_types=1);

use App\Controller\Slave;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Pin the single source of truth for SHOW {SLAVE|REPLICA} STATUS
 * gating across every fork+version combination we care about (#830).
 *
 * The bug that triggered this was Percona 5.6 receiving "SHOW REPLICA
 * STATUS" because the gate was duplicated and inconsistent across
 * controller / view / Glial. This test makes any future drift loud.
 */
final class SlaveBuildShowReplicaStatusSqlTest extends TestCase
{
    /** @return array<string, array{0:string,1:string,2:?string,3:string}> */
    public static function gatingCases(): array
    {
        // Tuple: server_type, server_version, channel (or null), expected SQL.
        return [
            // ---------- Percona ----------
            'percona 5.6 — default channel'      => ['Percona',        '5.6.51-91.0-log',         null,        'SHOW SLAVE STATUS'],
            'percona 5.6 — named channel'        => ['Percona',        '5.6.51-91.0-log',         'shard_a',   "SHOW SLAVE STATUS FOR CHANNEL 'shard_a'"],
            'percona 5.7 — default channel'      => ['Percona Server', '5.7.42-49',               null,        'SHOW SLAVE STATUS'],
            'percona 8.0.21 — still SLAVE'       => ['Percona',        '8.0.21-12',               null,        'SHOW SLAVE STATUS'],
            'percona 8.0.22 — exactly the cut'   => ['Percona',        '8.0.22-13',               null,        'SHOW REPLICA STATUS'],
            'percona 8.0.22 — named channel'     => ['Percona',        '8.0.22-13',               'shard_b',   "SHOW REPLICA STATUS FOR CHANNEL 'shard_b'"],
            'percona 8.4 — REPLICA'              => ['Percona',        '8.4.0-1',                 null,        'SHOW REPLICA STATUS'],

            // ---------- MySQL Oracle ----------
            'mysql 5.7'                          => ['MySQL',          '5.7.44',                  null,        'SHOW SLAVE STATUS'],
            'mysql 8.0.21 — still SLAVE'         => ['MySQL',          '8.0.21',                  null,        'SHOW SLAVE STATUS'],
            'mysql 8.0.22 — REPLICA'             => ['MySQL',          '8.0.22',                  null,        'SHOW REPLICA STATUS'],
            'mysql 8.4 — REPLICA'                => ['MySQL',          '8.4.0',                   null,        'SHOW REPLICA STATUS'],
            'mysql 9.7 — REPLICA'                => ['MySQL',          '9.7.0',                   null,        'SHOW REPLICA STATUS'],
            'mysql 8.0.22 — named channel'       => ['MySQL',          '8.0.22',                  'sourceA',   "SHOW REPLICA STATUS FOR CHANNEL 'sourceA'"],

            // ---------- MariaDB ----------
            // MariaDB never speaks REPLICA — even at 10.6+ the gate must
            // stay on SLAVE, with the per-connection variant for channels.
            'mariadb 10.5 — default channel'     => ['MariaDB',        '10.5.20-MariaDB',         null,        'SHOW SLAVE STATUS'],
            'mariadb 10.11 — default channel'    => ['MariaDB',        '10.11.5-MariaDB-log',     null,        'SHOW SLAVE STATUS'],
            'mariadb 11.4 — default channel'     => ['MariaDB',        '11.4.0-MariaDB',          null,        'SHOW SLAVE STATUS'],
            'mariadb 10.6 — named connection'    => ['MariaDB',        '10.6.19-MariaDB-log',     'shard_c',   "SHOW SLAVE 'shard_c' STATUS"],
        ];
    }

    #[DataProvider('gatingCases')]
    public function testBuildShowReplicaStatusSqlPicksTheRightKeyword(
        string $serverType,
        string $serverVersion,
        ?string $channel,
        string $expectedSql
    ): void {
        $this->assertSame(
            $expectedSql,
            Slave::buildShowReplicaStatusSql($serverType, $serverVersion, $channel)
        );
    }

    public function testSupportsReplicaStatusSyntaxFlagMatchesTheBuilder(): void
    {
        // Pure data-flow check — the boolean predicate must agree with
        // whether the builder emitted REPLICA. Prevents one being touched
        // without the other.
        $this->assertTrue(Slave::supportsReplicaStatusSyntax('MySQL', '8.0.22'));
        $this->assertTrue(Slave::supportsReplicaStatusSyntax('Percona', '8.0.22-13'));
        $this->assertTrue(Slave::supportsReplicaStatusSyntax('MySQL', '8.4.0'));

        $this->assertFalse(Slave::supportsReplicaStatusSyntax('MySQL', '8.0.21'));
        $this->assertFalse(Slave::supportsReplicaStatusSyntax('Percona', '5.6.51-91.0-log'));
        $this->assertFalse(Slave::supportsReplicaStatusSyntax('MariaDB', '11.4.0-MariaDB'));
    }

    public function testEmptyChannelStringBehavesAsNullChannel(): void
    {
        // Callers (the view) pass `''` when no replication_name is set;
        // both must produce the unscoped form, not "FOR CHANNEL ''".
        $sql = Slave::buildShowReplicaStatusSql('MySQL', '8.0.22', '');
        $this->assertSame('SHOW REPLICA STATUS', $sql);

        $sqlMaria = Slave::buildShowReplicaStatusSql('MariaDB', '10.11.5', '');
        $this->assertSame('SHOW SLAVE STATUS', $sqlMaria);
    }

    public function testChannelGetsSanitizedThroughTheCanonicalRule(): void
    {
        // Channel names must already be alphanumeric / underscore / hyphen / dot,
        // but the helper applies the same sanitizer the rest of the
        // controller uses so an injection attempt can't slip through.
        $sql = Slave::buildShowReplicaStatusSql('MySQL', '8.0.22', "shard_a'; DROP TABLE x;--");
        $this->assertSame("SHOW REPLICA STATUS FOR CHANNEL 'shard_aDROPTABLEx--'", $sql);
    }
}
