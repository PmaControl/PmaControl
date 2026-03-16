<?php

declare(strict_types=1);

use App\Library\Extraction2;
use PHPUnit\Framework\TestCase;

final class Extraction2Test extends TestCase
{
    protected function setUp(): void
    {
        Extraction2::resetRuntimeState();
        Extraction2::$lastRuntimeCacheTouch = 0.0;
    }

    public function testShouldUseDirectPointLookupReturnsTrueForSingleServerSingleDateWithoutRange(): void
    {
        $this->assertTrue(Extraction2::shouldUseDirectPointLookup([213], ['2026-03-13 10:00:00'], false));
    }

    public function testShouldUseDirectPointLookupReturnsFalseForRangeQueries(): void
    {
        $this->assertFalse(Extraction2::shouldUseDirectPointLookup([213], ['2026-03-13 10:00:00'], true));
    }

    public function testShouldUseDirectPointLookupReturnsFalseForMultipleServers(): void
    {
        $this->assertFalse(Extraction2::shouldUseDirectPointLookup([213, 214], ['2026-03-13 10:00:00'], false));
    }

    public function testShouldUseTsMaxDateReturnsTrueForEmptyDate(): void
    {
        $this->assertTrue(Extraction2::shouldUseTsMaxDate(''));
    }

    public function testShouldUseTsMaxDateReturnsTrueForMaxDateKeyword(): void
    {
        $this->assertTrue(Extraction2::shouldUseTsMaxDate('MAX_DATE'));
    }

    public function testShouldUseTsMaxDateReturnsFalseForExplicitInterval(): void
    {
        $this->assertFalse(Extraction2::shouldUseTsMaxDate('1 HOUR'));
    }

    public function testBuildExplicitDateFilterReturnsNullWhenDateListIsEmpty(): void
    {
        $this->assertNull(Extraction2::buildExplicitDateFilter([]));
    }

    public function testBuildExplicitDateFilterBuildsExpectedInClause(): void
    {
        $filter = Extraction2::buildExplicitDateFilter([
            '2026-03-13 10:00:00',
            '2026-03-13 10:05:00',
        ]);

        $this->assertSame(
            " AND a.`date` IN ('2026-03-13 10:00:00','2026-03-13 10:05:00') ",
            $filter
        );
    }

    public function testResetRuntimeStateIfStaleClearsStaticCachesAfterOneSecond(): void
    {
        Extraction2::$variable = [1 => ['name' => 'hostname']];
        Extraction2::$server = [213];
        Extraction2::$ts_file = [3756];
        Extraction2::$partition = [1 => 'p740055'];

        Extraction2::resetRuntimeStateIfStale(100.0);
        $reset = Extraction2::resetRuntimeStateIfStale(101.1);

        $this->assertTrue($reset);
        $this->assertSame([], Extraction2::$variable);
        $this->assertSame([], Extraction2::$server);
        $this->assertSame([], Extraction2::$ts_file);
        $this->assertSame([], Extraction2::$partition);
    }

    public function testResetRuntimeStateIfStaleKeepsStaticCachesWithinOneSecond(): void
    {
        Extraction2::$variable = [1 => ['name' => 'hostname']];

        Extraction2::resetRuntimeStateIfStale(200.0);
        $reset = Extraction2::resetRuntimeStateIfStale(200.5);

        $this->assertFalse($reset);
        $this->assertSame([1 => ['name' => 'hostname']], Extraction2::$variable);
    }

    public function testGetExtraIdentifierFieldByRadicalReturnsExpectedFields(): void
    {
        $this->assertSame('connection_name', Extraction2::getExtraIdentifierFieldByRadical('slave'));
        $this->assertSame('id_ts_mysql_query', Extraction2::getExtraIdentifierFieldByRadical('digest'));
        $this->assertNull(Extraction2::getExtraIdentifierFieldByRadical('general'));
    }

    public function testBuildValueTableNameBuildsExpectedName(): void
    {
        $this->assertSame('ts_value_slave_text', Extraction2::buildValueTableName('slave', 'text'));
    }

    public function testBuildSelectFieldsForSlaveIncludesConnectionName(): void
    {
        $fields = Extraction2::buildSelectFields('slave', 'text');

        $this->assertStringContainsString('a.`connection_name`', $fields);
        $this->assertStringContainsString("a.`value`", $fields);
    }

    public function testBuildSelectFieldsForDigestIncludesDigestIdentifier(): void
    {
        $fields = Extraction2::buildSelectFields('digest', 'int');

        $this->assertStringContainsString('a.`id_ts_mysql_query`', $fields);
    }

    public function testBuildSelectFieldsForGeneralGraphIncludesLagWindowExpression(): void
    {
        $fields = Extraction2::buildSelectFields('general', 'double', true);

        $this->assertStringContainsString('LAG(a.`value`)', $fields);
        $this->assertStringContainsString('as value', $fields);
    }

    public function testBuildSelectFieldsForGeneralNonGraphUsesRawValue(): void
    {
        $fields = Extraction2::buildSelectFields('general', 'text', false);

        $this->assertStringContainsString("'N/A' as `connection_name`", $fields);
        $this->assertStringContainsString('a.`value` as value', $fields);
    }

    public function testNormalizeDisplayValueTrimsText(): void
    {
        $this->assertSame('hello', Extraction2::normalizeDisplayValue('text', ' hello '));
    }

    public function testNormalizeDisplayValueDecodesJson(): void
    {
        $this->assertSame(['a' => 1], Extraction2::normalizeDisplayValue('json', '{"a":1}'));
    }

    public function testAppendDisplayRowForGeneralLatestMode(): void
    {
        Extraction2::$variable[10] = [
            'radical' => 'general',
            'name' => 'hostname',
        ];

        $row = (object) [
            'id_mysql_server' => 213,
            'id_ts_variable' => 10,
            'date' => '2026-03-13 10:00:00',
            'type' => 'text',
            'value' => ' db01 ',
        ];

        $table = Extraction2::appendDisplayRow([], $row, false);

        $this->assertSame('db01', $table[213]['hostname']);
        $this->assertSame('2026-03-13 10:00:00', $table[213]['date']);
    }

    public function testAppendDisplayRowForGeneralRangeMode(): void
    {
        Extraction2::$variable[11] = [
            'radical' => 'general',
            'name' => 'mysql_available',
        ];

        $row = (object) [
            'id_mysql_server' => 213,
            'id_ts_variable' => 11,
            'date' => '2026-03-13 10:00:00',
            'type' => 'int',
            'value' => '1',
        ];

        $table = Extraction2::appendDisplayRow([], $row, true);

        $this->assertSame('1', $table[213]['2026-03-13 10:00:00']['mysql_available']);
    }

    public function testAppendDisplayRowForSlaveLatestModeUsesConnectionNameBucket(): void
    {
        Extraction2::$variable[2718] = [
            'radical' => 'slave',
            'name' => 'master_host',
        ];

        $row = (object) [
            'id_mysql_server' => 213,
            'id_ts_variable' => 2718,
            'date' => '2026-03-13 10:00:00',
            'type' => 'text',
            'value' => '10.105.1.11',
            'connection_name' => '',
        ];

        $table = Extraction2::appendDisplayRow([], $row, false);

        $this->assertSame('10.105.1.11', $table[213]['@slave']['']['master_host']);
    }

    public function testAppendDisplayRowForDigestLatestModeUsesDigestBucket(): void
    {
        Extraction2::$variable[5000] = [
            'radical' => 'digest',
            'name' => 'query_sample_text',
        ];

        $row = (object) [
            'id_mysql_server' => 213,
            'id_ts_variable' => 5000,
            'date' => '2026-03-13 10:00:00',
            'type' => 'text',
            'value' => 'select 1',
            'id_ts_mysql_query' => 77,
        ];

        $table = Extraction2::appendDisplayRow([], $row, false);

        $this->assertSame('select 1', $table[213]['@digest']['77']['query_sample_text']);
    }

    public function testAppendDisplayRowForDigestRangeModeUsesDateBucket(): void
    {
        Extraction2::$variable[5001] = [
            'radical' => 'digest',
            'name' => 'sum_rows_examined',
        ];

        $row = (object) [
            'id_mysql_server' => 213,
            'id_ts_variable' => 5001,
            'date' => '2026-03-13 10:00:00',
            'type' => 'int',
            'value' => '42',
            'id_ts_mysql_query' => 88,
        ];

        $table = Extraction2::appendDisplayRow([], $row, true);

        $this->assertSame('42', $table[213]['@digest']['88']['2026-03-13 10:00:00']['sum_rows_examined']);
    }

    public function testBuildDirectQuerySegmentsBuildsQueriesForEachTypedTable(): void
    {
        $segments = Extraction2::buildDirectQuerySegments([
            'general' => ['text' => [10]],
            'slave' => ['text' => [2718]],
            'digest' => ['int' => [5001]],
        ], 213, '2026-03-13 10:00:00', 'p740055');

        $this->assertCount(3, $segments);
        $this->assertStringContainsString('`ts_value_general_text` PARTITION(`p740055`)', $segments[0]);
        $this->assertStringContainsString('`ts_value_slave_text` PARTITION(`p740055`)', $segments[1]);
        $this->assertStringContainsString('`ts_value_digest_int` PARTITION(`p740055`)', $segments[2]);
        $this->assertStringContainsString('a.id_mysql_server = 213', $segments[0]);
        $this->assertStringContainsString("a.`date` = '2026-03-13 10:00:00'", $segments[0]);
    }
}
