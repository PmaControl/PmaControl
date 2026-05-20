<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BinlogAnalysisVolumeStorageMigrationTest extends TestCase
{
    private string $migration;
    private string $baseSchema;

    protected function setUp(): void
    {
        $migrationPath = __DIR__ . '/../../sql/incremental_v2/20260518_binlog_analysis_volume_mediumtext.sql';
        $baseSchemaPath = __DIR__ . '/../../sql/incremental_v2/patch_commercial_2026-04-13.sql';

        $this->assertFileExists($migrationPath);
        $this->assertFileExists($baseSchemaPath);

        $this->migration = (string) file_get_contents($migrationPath);
        $this->baseSchema = (string) file_get_contents($baseSchemaPath);
    }

    public function testMigrationWidensBinlogGraphPayloadColumns(): void
    {
        self::assertStringContainsString('table_name = \'binlog_analysis\'', $this->migration);
        self::assertStringContainsString("data_type NOT IN ('mediumtext', 'longtext')", $this->migration);
        self::assertMatchesRegularExpression(
            '/MODIFY `parallelism_per_second` MEDIUMTEXT DEFAULT NULL COMMENT \'\'JSON \\[\\{ts, txn_count\\}\\] for parallelism graph\'\'/s',
            $this->migration
        );
        self::assertMatchesRegularExpression(
            '/MODIFY `volume_per_second` MEDIUMTEXT DEFAULT NULL COMMENT \'\'JSON \\[\\{ts, bytes, txn\\}\\]\'\'/s',
            $this->migration
        );
    }

    public function testBaseCommercialSchemaUsesMediumTextForNewInstalls(): void
    {
        self::assertMatchesRegularExpression(
            '/`parallelism_per_second` mediumtext DEFAULT NULL COMMENT \'JSON \\[\\{ts, txn_count\\}\\] for parallelism graph\'/',
            $this->baseSchema
        );
        self::assertMatchesRegularExpression(
            '/`volume_per_second` mediumtext DEFAULT NULL COMMENT \'JSON \\[\\{ts, bytes, txn\\}\\]\'/',
            $this->baseSchema
        );
    }
}
