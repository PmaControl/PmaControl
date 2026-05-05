<?php

declare(strict_types=1);

use App\Controller\Monitoring;
use PHPUnit\Framework\TestCase;

final class MonitoringQueryFilterTest extends TestCase
{
    public function testQueryRequestAllowsGet(): void
    {
        $outcome = Monitoring::evaluateQueryRequest(['REQUEST_METHOD' => 'GET']);

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(200, $outcome['status']);
    }

    public function testQueryRequestRejectsPost(): void
    {
        $outcome = Monitoring::evaluateQueryRequest(['REQUEST_METHOD' => 'POST']);

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(405, $outcome['status']);
        $this->assertSame('GET', $outcome['headers']['Allow']);
    }

    public function testQueryServerIdAcceptsRouteOrGetPositiveInteger(): void
    {
        $this->assertSame(12, Monitoring::normalizeQueryServerId([], ['12']));
        $this->assertSame(34, Monitoring::normalizeQueryServerId(['mysql_server' => ['id' => '34']], ['12']));
        $this->assertNull(Monitoring::normalizeQueryServerId(['mysql_server' => ['id' => '0']], []));
        $this->assertNull(Monitoring::normalizeQueryServerId(['mysql_server' => ['id' => '1 OR 1=1']], []));
        $this->assertNull(Monitoring::normalizeQueryServerId(['mysql_server' => ['id' => ['12']]], []));
    }

    public function testQueryFilterNormalizesFieldAndOrderByAgainstAllowedColumns(): void
    {
        $filter = Monitoring::normalizeQueryFilter(
            [
                'database' => [
                    'id' => " app' ",
                    'filter' => " SELECT 'x' ",
                ],
                'field' => ['id' => 'COUNT_STAR'],
                'orderby' => ['id' => 'desc'],
            ],
            ['COUNT_STAR', 'AVG_TIMER_WAIT']
        );

        $this->assertSame(
            [
                'database_id' => "app'",
                'database_filter' => "SELECT 'x'",
                'field_id' => 'COUNT_STAR',
                'orderby' => 'DESC',
            ],
            $filter
        );

        $invalid = Monitoring::normalizeQueryFilter(
            [
                'field' => ['id' => 'COUNT_STAR` DESC; DROP TABLE mysql_server; --'],
                'orderby' => ['id' => 'sideways'],
            ],
            ['COUNT_STAR']
        );

        $this->assertSame('', $invalid['field_id']);
        $this->assertSame('ASC', $invalid['orderby']);
    }

    public function testQueryWhereEscapesDatabaseAndFilterValues(): void
    {
        $sql = Monitoring::buildQueryWhereClause(
            [
                'database_id' => "app'",
                'database_filter' => "SELECT 'x'",
                'field_id' => '',
                'orderby' => 'ASC',
            ],
            static fn($value): string => addslashes((string)$value)
        );

        $this->assertSame(" AND a.SCHEMA_NAME ='app\\''  AND a.DIGEST_TEXT LIKE '%SELECT \\'x\\'%' ", $sql);
    }

    public function testQueryOrderClauseUsesWhitelistedFieldOnly(): void
    {
        $this->assertSame(
            ' ORDER BY a.`COUNT_STAR` DESC ',
            Monitoring::buildQueryOrderClause([
                'database_id' => '',
                'database_filter' => '',
                'field_id' => 'COUNT_STAR',
                'orderby' => 'DESC',
            ])
        );
        $this->assertSame(
            ' ',
            Monitoring::buildQueryOrderClause([
                'database_id' => '',
                'database_filter' => '',
                'field_id' => '',
                'orderby' => 'ASC',
            ])
        );
    }

    public function testQueryControllerDropsPostFlowAndViewUsesGet(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Monitoring.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Monitoring/query.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('evaluateQueryRequest($_SERVER)', $controller);
        $this->assertStringContainsString('normalizeQueryFilter($_GET, $allowedFields)', $controller);
        $this->assertStringContainsString('buildQueryWhereClause($filter, [$db, \'sql_real_escape_string\'])', $controller);
        $this->assertStringContainsString('buildQueryOrderClause($filter)', $controller);
        $this->assertStringNotContainsString('$_POST', $controller);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }
}
