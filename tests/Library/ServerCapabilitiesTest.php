<?php

declare(strict_types=1);

use App\Library\ServerCapabilities;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServerCapabilitiesTest extends TestCase
{
    public function testUnknownCapabilityThrowsBeforeCallingDatabase(): void
    {
        $db = new ServerCapabilitiesFakeDb([]);

        try {
            ServerCapabilities::supports($db, 'typo_feature');
            $this->fail('Unknown capabilities must throw.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Unknown server capability: typo_feature', $exception->getMessage());
            $this->assertSame([], $db->calls);
        }
    }

    #[DataProvider('capabilityMatrixProvider')]
    public function testSupportsPassesCapabilityMatrixToCheckVersion(string $feature, array $expectedMatrix): void
    {
        $db = new ServerCapabilitiesFakeDb([]);

        $this->assertFalse(ServerCapabilities::supports($db, $feature));
        $this->assertSame([$expectedMatrix], $db->calls);
    }

    public function testPerconaAliasIsAcceptedForCapabilitiesThatTargetPercona(): void
    {
        $db = new ServerCapabilitiesFakeDb(['Percona' => '8.4.0']);

        $this->assertTrue(ServerCapabilities::supports($db, 'show_binary_log_status'));
        $this->assertTrue(ServerCapabilities::supports($db, 'performance_schema_processlist_modern'));
    }

    public function testPerconaLegacyProcesslistThresholdCanBeComposedAtCallSite(): void
    {
        $percona56 = new ServerCapabilitiesFakeDb(['Percona Server' => '5.6.51']);
        $this->assertTrue(ServerCapabilities::supports($percona56, 'percona_processlist_56'));
        $this->assertFalse(ServerCapabilities::supports($percona56, 'percona_processlist_57'));

        $percona57 = new ServerCapabilitiesFakeDb(['Percona Server' => '5.7.44']);
        $this->assertTrue(ServerCapabilities::supports($percona57, 'percona_processlist_56'));
        $this->assertTrue(ServerCapabilities::supports($percona57, 'percona_processlist_57'));
    }

    public static function capabilityMatrixProvider(): array
    {
        return [
            'information_schema_max_statement_time' => [
                'information_schema_max_statement_time',
                ['MariaDB' => '10.1.1'],
            ],
            'select_max_execution_time_hint' => [
                'select_max_execution_time_hint',
                ['MySQL' => '5.7', 'Percona' => '5.7', 'Percona Server' => '5.7'],
            ],
            'information_schema_tables_temporary_column' => [
                'information_schema_tables_temporary_column',
                ['MariaDB' => '10.3', 'MySQL' => '5.7', 'Percona' => '5.7', 'Percona Server' => '5.7'],
            ],
            'processlist_supported' => [
                'processlist_supported',
                ['MySQL' => '5.1', 'MariaDB' => '5.1', 'Percona' => '5.1', 'Percona Server' => '5.1'],
            ],
            'mysql8_processlist_trx_columns' => [
                'mysql8_processlist_trx_columns',
                ['MySQL' => '8.0'],
            ],
            'show_binary_log_status' => [
                'show_binary_log_status',
                ['MySQL' => '8.4', 'Percona' => '8.4', 'Percona Server' => '8.4'],
            ],
            'mysql_user_is_role_column' => [
                'mysql_user_is_role_column',
                ['MariaDB' => '10.0'],
            ],
            'performance_schema_processlist_modern' => [
                'performance_schema_processlist_modern',
                ['MySQL' => '8.0', 'Percona' => '8.0', 'Percona Server' => '8.0'],
            ],
            'percona_processlist_56' => [
                'percona_processlist_56',
                ['Percona' => '5.6', 'Percona Server' => '5.6'],
            ],
            'percona_processlist_57' => [
                'percona_processlist_57',
                ['Percona' => '5.7', 'Percona Server' => '5.7'],
            ],
            'mariadb_skip_replication_variable' => [
                'mariadb_skip_replication_variable',
                ['MariaDB' => '5.5.21'],
            ],
        ];
    }
}

final class ServerCapabilitiesFakeDb
{
    public array $calls = [];

    public function __construct(private readonly array $versionsByProvider)
    {
    }

    public function checkVersion(array $versions): bool
    {
        $this->calls[] = $versions;

        foreach ($versions as $provider => $minimumVersion) {
            $currentVersion = $this->versionsByProvider[$provider] ?? null;
            if ($currentVersion !== null && version_compare($currentVersion, (string) $minimumVersion, '>=')) {
                return true;
            }
        }

        return false;
    }
}
