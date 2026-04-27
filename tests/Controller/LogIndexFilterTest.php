<?php

declare(strict_types=1);

use App\Controller\Log;
use PHPUnit\Framework\TestCase;

final class LogIndexFilterTest extends TestCase
{
    public function testIndexRejectsPostRequests(): void
    {
        $outcome = Log::evaluateIndexRequest([], ['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('GET', $outcome['headers']['Allow']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertFalse($outcome['filters']['ready']);
    }

    public function testIndexNormalizesGetFiltersFromArrays(): void
    {
        $outcome = Log::evaluateIndexRequest(
            [
                'mysql_server' => ['id' => ['2', '1', '2', '0', '1 OR 1=1']],
                'ts_variable' => ['id' => ['Status::Threads_running', 'bad value', 'slave::last_sql_error']],
                'ts' => [
                    'date_start' => '2026-04-01 10:11:12',
                    'date_end' => '2026-04-01 11:12:13',
                ],
            ],
            ['REQUEST_METHOD' => 'GET']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([2, 1], $outcome['filters']['mysql_server_ids']);
        $this->assertSame(['status::threads_running', 'slave::last_sql_error'], $outcome['filters']['ts_variables']);
        $this->assertSame('2026-04-01 10:11:12', $outcome['filters']['date_start']);
        $this->assertSame('2026-04-01 11:12:13', $outcome['filters']['date_end']);
        $this->assertTrue($outcome['filters']['ready']);
    }

    public function testIndexNormalizesLegacyBracketRouteFilters(): void
    {
        $filters = Log::normalizeIndexFilters([
            'mysql_server' => ['id' => '[3,4,4,0,bad]'],
            'ts_variable' => ['id' => '[slave::exec_master_log_pos,status::wsrep_ready]'],
            'ts' => [
                'date_start' => '2026-04-02 10:00:00',
                'date_end' => '2026-04-02 11:00:00',
            ],
        ]);

        $this->assertSame([3, 4], $filters['mysql_server_ids']);
        $this->assertSame(['slave::exec_master_log_pos', 'status::wsrep_ready'], $filters['ts_variables']);
        $this->assertTrue($filters['ready']);
    }

    public function testIndexRejectsInvalidDatesBeforeExtraction(): void
    {
        $filters = Log::normalizeIndexFilters([
            'mysql_server' => ['id' => ['1']],
            'ts_variable' => ['id' => ['status::threads_running']],
            'ts' => [
                'date_start' => "2026-04-02 10:00:00' OR 1=1 --",
                'date_end' => '2026-04-02 11:00:00',
            ],
        ]);

        $this->assertSame('', $filters['date_start']);
        $this->assertFalse($filters['ready']);
    }

    public function testIndexRejectsInvalidCalendarDatesBeforeExtraction(): void
    {
        $filters = Log::normalizeIndexFilters([
            'mysql_server' => ['id' => ['1']],
            'ts_variable' => ['id' => ['status::threads_running']],
            'ts' => [
                'date_start' => '2026-02-30 10:00:00',
                'date_end' => '2026-04-02T11:00:00',
            ],
        ]);

        $this->assertSame('', $filters['date_start']);
        $this->assertSame('', $filters['date_end']);
        $this->assertFalse($filters['ready']);
    }

    public function testIndexCapsSelectionsAtTwoHundredItems(): void
    {
        $ids = range(1, 205);
        $variables = [];
        foreach (range(1, 205) as $i) {
            $variables[] = 'status::metric_' . $i;
        }

        $this->assertCount(200, Log::normalizePositiveIds($ids));
        $this->assertCount(200, Log::normalizeTsVariables($variables));
    }

    public function testIndexIgnoresNonScalarSelections(): void
    {
        $filters = Log::normalizeIndexFilters([
            'mysql_server' => ['id' => [new stdClass(), '5']],
            'ts_variable' => ['id' => [new stdClass(), 'status::threads_running']],
            'ts' => [
                'date_start' => '2026-04-02 10:00:00',
                'date_end' => '2026-04-02 11:00:00',
            ],
        ]);

        $this->assertSame([5], $filters['mysql_server_ids']);
        $this->assertSame(['status::threads_running'], $filters['ts_variables']);
        $this->assertTrue($filters['ready']);
    }

    public function testIndexViewUsesGetInsteadOfPost(): void
    {
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Log/index.view.php');

        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
        $this->assertStringNotContainsString('http://localhost/pmacontrol', $view);
    }

    public function testIndexControllerNoLongerReadsFromPost(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Log.php');

        $this->assertStringContainsString('evaluateIndexRequest($_GET, $_SERVER)', $controller);
        $this->assertStringContainsString("header('Cache-Control: private, no-store');", $controller);
        $this->assertStringNotContainsString('$_POST', $controller);
        $this->assertStringNotContainsString('Post::getToPost()', $controller);
    }
}
