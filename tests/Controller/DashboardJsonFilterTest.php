<?php

declare(strict_types=1);

use App\Controller\Dashboard;
use PHPUnit\Framework\TestCase;

final class DashboardJsonFilterTest extends TestCase
{
    public function testJsonRequestAllowsGetWithDefaults(): void
    {
        $outcome = Dashboard::evaluateJsonRequest([], ['REQUEST_METHOD' => 'GET'], [], '2026-04-27 16:00:00');

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'id_mysql_server' => 1,
            'id_ts_variable' => 1323,
            'date' => '2026-04-27',
            'time' => '15:00:00',
            'limit' => 100,
        ], $outcome['filter']);
    }

    public function testJsonRequestAllowsHead(): void
    {
        $outcome = Dashboard::evaluateJsonRequest([], ['REQUEST_METHOD' => 'HEAD'], [], '2026-04-27 16:00:00');

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['body']);
        $this->assertSame([], $outcome['headers']);
    }

    public function testJsonRequestRejectsPost(): void
    {
        $outcome = Dashboard::evaluateJsonRequest([], ['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
        $this->assertNull($outcome['filter']);
    }

    public function testJsonFilterNormalizesGetSelection(): void
    {
        $filter = Dashboard::normalizeJsonFilter([
            'mysql_server' => ['id' => ' 7 '],
            'ts_variable' => ['id' => '55'],
            'date' => ['date' => '2026-04-26', 'time' => '08:09:10'],
            'limit' => '10',
        ], [], '2026-04-27 16:00:00');

        $this->assertSame([
            'id_mysql_server' => 7,
            'id_ts_variable' => 55,
            'date' => '2026-04-26',
            'time' => '08:09:10',
            'limit' => 10,
        ], $filter);
    }

    public function testJsonFilterKeepsRouteCompatibility(): void
    {
        $filter = Dashboard::normalizeJsonFilter([], [8, 56, '2026-04-25', '09:10:11', 25], '2026-04-27 16:00:00');

        $this->assertSame([
            'id_mysql_server' => 8,
            'id_ts_variable' => 56,
            'date' => '2026-04-25',
            'time' => '09:10:11',
            'limit' => 25,
        ], $filter);
    }

    public function testJsonFilterFallsBackFromHostileValues(): void
    {
        $filter = Dashboard::normalizeJsonFilter([
            'mysql_server' => ['id' => '7 OR 1=1'],
            'ts_variable' => ['id' => ['55']],
            'date' => ['date' => '2026-99-99', 'time' => '24:00:00'],
            'limit' => '5000',
        ], [], '2026-04-27 16:00:00');

        $this->assertSame([
            'id_mysql_server' => 1,
            'id_ts_variable' => 1323,
            'date' => '2026-04-27',
            'time' => '15:00:00',
            'limit' => 1000,
        ], $filter);
    }

    public function testJsonSqlUsesNormalizedFilter(): void
    {
        $sql = Dashboard::buildJsonRowsSql([
            'id_mysql_server' => 7,
            'id_ts_variable' => 55,
            'date' => '2026-04-26',
            'time' => '08:09:10',
            'limit' => 10,
        ]);

        $this->assertSame(
            "select * from ts_value_general_json where id_mysql_server =7 and id_ts_variable= 55 and date > '2026-04-26 08:09:10' limit 10",
            $sql
        );
    }

    public function testJsonControllerAndViewUseGetFlow(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Dashboard.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Dashboard/json.view.php');
        $jsonStart = strpos($controller, 'public function json($param)');
        $hitRatioStart = strpos($controller, 'public function hitRatio($param)');

        $this->assertNotFalse($jsonStart);
        $this->assertNotFalse($hitRatioStart);
        $jsonBody = substr($controller, $jsonStart, $hitRatioStart - $jsonStart);

        $this->assertStringContainsString('evaluateJsonRequest($_GET, $_SERVER, $param)', $jsonBody);
        $this->assertStringContainsString('buildJsonRowsSql($filter)', $jsonBody);
        $this->assertStringNotContainsString('$_POST', $jsonBody);
        $this->assertStringNotContainsString('header("location: ".LINK."Dashboard/json/', $jsonBody);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }
}
