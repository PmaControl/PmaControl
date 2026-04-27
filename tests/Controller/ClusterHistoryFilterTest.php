<?php

declare(strict_types=1);

use App\Controller\Cluster;
use PHPUnit\Framework\TestCase;

final class ClusterHistoryFilterTest extends TestCase
{
    public function testHistoryAcceptsCanonicalRouteDates(): void
    {
        $outcome = Cluster::evaluateHistoryRequest([], ['REQUEST_METHOD' => 'GET'], ['7', '2026-01-01', '2026-01-31']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(7, $outcome['id_mysql_server']);
        $this->assertSame('2026-01-01', $outcome['date_min']);
        $this->assertSame('2026-01-31', $outcome['date_max']);
        $this->assertNull($outcome['redirect_route']);
    }

    public function testHistoryRedirectsValidGetSelectionToCanonicalRoute(): void
    {
        $outcome = Cluster::evaluateHistoryRequest($this->validGet(), ['REQUEST_METHOD' => 'GET'], ['7']);

        $this->assertSame(302, $outcome['status']);
        $this->assertSame(7, $outcome['id_mysql_server']);
        $this->assertSame('2026-01-01', $outcome['date_min']);
        $this->assertSame('2026-01-31', $outcome['date_max']);
        $this->assertSame('Cluster/history/7/2026-01-01/2026-01-31', $outcome['redirect_route']);
    }

    public function testHistoryAllowsHeadAndSameDayRouteDates(): void
    {
        $outcome = Cluster::evaluateHistoryRequest([], ['REQUEST_METHOD' => 'HEAD'], ['7', '2026-01-01', '2026-01-01']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('2026-01-01', $outcome['date_min']);
        $this->assertSame('2026-01-01', $outcome['date_max']);
    }

    public function testHistoryGetSelectionTakesPrecedenceOverRouteDates(): void
    {
        $outcome = Cluster::evaluateHistoryRequest($this->validGet(), ['REQUEST_METHOD' => 'GET'], ['7', '2025-01-01', '2025-01-31']);

        $this->assertSame(302, $outcome['status']);
        $this->assertSame('2026-01-01', $outcome['date_min']);
        $this->assertSame('2026-01-31', $outcome['date_max']);
        $this->assertSame('Cluster/history/7/2026-01-01/2026-01-31', $outcome['redirect_route']);
    }

    public function testHistoryRejectsResidualPostAndOtherUnsafeMethods(): void
    {
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $outcome = Cluster::evaluateHistoryRequest($this->validGet(), ['REQUEST_METHOD' => $method], ['7']);

            $this->assertSame(405, $outcome['status']);
            $this->assertSame('Method Not Allowed', $outcome['body']);
            $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
            $this->assertNull($outcome['id_mysql_server']);
            $this->assertNull($outcome['redirect_route']);
        }
    }

    public function testHistoryRejectsInvalidRouteParameters(): void
    {
        foreach ($this->invalidRouteParams() as $param) {
            $outcome = Cluster::evaluateHistoryRequest([], ['REQUEST_METHOD' => 'GET'], $param);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid history selection', $outcome['body']);
        }
    }

    public function testHistoryRejectsInvalidGetSelections(): void
    {
        foreach ($this->invalidGets() as $get) {
            $outcome = Cluster::evaluateHistoryRequest($get, ['REQUEST_METHOD' => 'GET'], ['7']);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid history selection', $outcome['body']);
            $this->assertNull($outcome['redirect_route']);
        }
    }

    public function testHistoryControllerAndReplayViewUseGetOnlyFilter(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Cluster.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Cluster/replay.view.php');
        $historyStart = strpos($controller, 'public function history($param)');
        $evaluateStart = strpos($controller, 'public static function evaluateHistoryRequest');

        $this->assertNotFalse($historyStart);
        $this->assertNotFalse($evaluateStart);
        $historyBody = substr($controller, $historyStart, $evaluateStart - $historyStart);

        $this->assertStringContainsString('use App\\Library\\Security\\DateRangeSelection;', $controller);
        $this->assertStringContainsString('DateRangeSelection::normalizeSelection(', $controller);
        $this->assertStringContainsString('self::evaluateHistoryRequest($_GET, $_SERVER, $param)', $historyBody);
        $this->assertStringNotContainsString('$_POST', $historyBody);
        $this->assertStringNotContainsString('debug($_POST)', $historyBody);
        $this->assertStringNotContainsString('REQUEST_METHOD\'] == "POST"', $historyBody);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }

    private function validGet(): array
    {
        return [
            'dot3_cluster__mysql_server' => [
                'date_min' => '2026-01-01',
                'date_max' => '2026-01-31',
            ],
        ];
    }

    private function invalidRouteParams(): array
    {
        return [
            [],
            ['0', '2026-01-01', '2026-01-31'],
            ['server-7', '2026-01-01', '2026-01-31'],
            ['7'],
            ['7', '2026/01/01', '2026-01-31'],
            ['7', '2026-02-01', '2026-01-31'],
            ['7', '2026-13-01', '2026-01-31'],
            ['7', '2026-01-01', '2026-02-30'],
            ['7', ['2026-01-01'], '2026-01-31'],
        ];
    }

    private function invalidGets(): array
    {
        $base = $this->validGet();
        $nonArrayGroup = ['dot3_cluster__mysql_server' => 'date_min=2026-01-01'];
        $missingMin = ['dot3_cluster__mysql_server' => ['date_max' => '2026-01-31']];
        $unexpectedField = $base;
        $unexpectedField['dot3_cluster__mysql_server']['extra'] = '1';
        $badMin = $base;
        $badMin['dot3_cluster__mysql_server']['date_min'] = "2026-01-01' OR 1=1";
        $badMax = $base;
        $badMax['dot3_cluster__mysql_server']['date_max'] = '2026-02-30';
        $reversed = $base;
        $reversed['dot3_cluster__mysql_server']['date_min'] = '2026-02-01';

        return [
            $nonArrayGroup,
            $missingMin,
            $unexpectedField,
            $badMin,
            $badMax,
            $reversed,
        ];
    }
}
