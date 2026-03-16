<?php

declare(strict_types=1);

use App\Library\Extraction;
use PHPUnit\Framework\TestCase;

final class ExtractionTest extends TestCase
{
    protected function setUp(): void
    {
        Extraction::$variable = [];
        Extraction::$server = [];
        Extraction::$groupbyday = false;
    }

    public function testBuildSelectFieldsForSlaveIncludesConnectionName(): void
    {
        $fields = Extraction::buildSelectFields('slave');

        $this->assertStringContainsString('a.`connection_name`', $fields);
        $this->assertStringContainsString('a.`value`', $fields);
    }

    public function testBuildSelectFieldsForGeneralNonGraphUsesRawValue(): void
    {
        $fields = Extraction::buildSelectFields('general', false);

        $this->assertStringContainsString("'' as connection_name", $fields);
        $this->assertStringContainsString('a.`value` as value', $fields);
    }

    public function testBuildSelectFieldsForGeneralGraphUsesLagExpression(): void
    {
        $fields = Extraction::buildSelectFields('general', true);

        $this->assertStringContainsString('LAG(a.`value`)', $fields);
        $this->assertStringContainsString('as value', $fields);
    }

    public function testNormalizeDisplayValueReturnsEmptyStringForNull(): void
    {
        $this->assertSame('', Extraction::normalizeDisplayValue(null));
    }

    public function testNormalizeDisplayValueTrimsString(): void
    {
        $this->assertSame('hello', Extraction::normalizeDisplayValue(' hello '));
    }

    public function testAppendDisplayRowLatestModeStoresMetricUnderConnectionName(): void
    {
        Extraction::$variable[2718] = [
            'name' => 'master_host',
        ];

        $row = (object) [
            'id_mysql_server' => 213,
            'id_ts_variable' => 2718,
            'connection_name' => '',
            'date' => '2026-03-13 10:00:00',
            'value' => '10.105.1.11',
        ];

        $table = Extraction::appendDisplayRow([], $row, false);

        $this->assertSame('10.105.1.11', $table[213]['']['master_host']);
        $this->assertSame('2026-03-13 10:00:00', $table[213]['']['date']);
    }

    public function testAppendDisplayRowRangeModeStoresMetricUnderDateBucket(): void
    {
        Extraction::$variable[2727] = [
            'name' => 'slave_io_running',
        ];

        $row = (object) [
            'id_mysql_server' => 213,
            'id_ts_variable' => 2727,
            'connection_name' => '',
            'date' => '2026-03-13 10:00:00',
            'value' => 'Yes',
        ];

        $table = Extraction::appendDisplayRow([], $row, true);

        $this->assertSame('Yes', $table[213]['']['2026-03-13 10:00:00']['slave_io_running']);
    }

    public function testCountRecursiveCountsNestedLeafValues(): void
    {
        $count = Extraction::count_recursive([
            'a' => 1,
            'b' => [2, 3],
            'c' => ['d' => [4]],
        ]);

        $this->assertSame(4, $count);
    }
}
