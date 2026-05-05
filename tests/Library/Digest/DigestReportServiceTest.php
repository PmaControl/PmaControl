<?php

declare(strict_types=1);

use App\Library\Digest\DigestReportService;
use PHPUnit\Framework\TestCase;

final class DigestReportServiceTest extends TestCase
{
    public function testNormalizeSchedulePayloadValidatesAndCalculatesNextRun(): void
    {
        $now = new DateTimeImmutable('2026-04-30 09:30:00', new DateTimeZone('Europe/Paris'));

        $outcome = DigestReportService::normalizeSchedulePayload(
            [
                'id' => '12',
                'name' => " Weekly digest\n",
                'report_slug' => 'digest_summary',
                'id_group' => '3',
                'frequency' => 'weekly',
                'time_of_day' => '07:15',
                'day_of_week' => '1',
                'limit_rows' => '99',
                'is_active' => '1',
            ],
            $now
        );

        $this->assertTrue($outcome['valid']);
        $this->assertSame(12, $outcome['payload']['id']);
        $this->assertSame('Weekly digest', $outcome['payload']['name']);
        $this->assertSame('weekly', $outcome['payload']['frequency']);
        $this->assertSame('07:15:00', $outcome['payload']['time_of_day']);
        $this->assertSame(20, $outcome['payload']['limit_rows']);
        $this->assertSame(1, $outcome['payload']['is_active']);
        $this->assertSame('2026-05-04 07:15:00', $outcome['payload']['next_run_at']);
    }

    public function testNormalizeSchedulePayloadRejectsInvalidData(): void
    {
        $outcome = DigestReportService::normalizeSchedulePayload(
            [
                'name' => '',
                'report_slug' => 'unknown',
                'id_group' => '0',
                'frequency' => 'monthly',
                'time_of_day' => '24:00',
                'day_of_week' => '9',
            ]
        );

        $this->assertFalse($outcome['valid']);
        $this->assertContains('Name is required.', $outcome['errors']);
        $this->assertContains('Unknown digest report.', $outcome['errors']);
        $this->assertContains('Recipient group is required.', $outcome['errors']);
        $this->assertContains('Invalid frequency.', $outcome['errors']);
        $this->assertContains('Invalid time of day.', $outcome['errors']);
        $this->assertContains('Invalid day of week.', $outcome['errors']);
    }

    public function testBuildPayloadAndHtmlEscapeDigestText(): void
    {
        $payload = DigestReportService::buildPayloadFromRows(
            [[
                'min_date' => '2026-04-30 00:00:00',
                'max_date' => '2026-04-30 09:00:00',
                'server_count' => '2',
                'schema_count' => '4',
                'digest_count' => '8',
                'count_star' => '123',
                'errors' => '1',
                'warnings' => '2',
                'sum_timer_wait' => '3000000000000',
            ]],
            [[
                'display_name' => 'db1',
                'schema_name' => 'app',
                'digest_text' => '<script>alert(1)</script> SELECT * FROM t',
                'count_star' => '10',
                'sum_timer_wait' => '2000000000000',
                'max_timer_wait' => '500000000000',
                'rows_examined' => '5000',
                'last_seen' => '2026-04-30 08:00:00',
            ]],
            [[
                'display_name' => 'db2',
                'schema_name' => 'app',
                'digest_text' => 'SELECT * FROM u',
                'count_star' => '20',
                'no_index_used' => '9',
                'no_good_index_used' => '3',
                'last_seen' => '2026-04-30 08:30:00',
            ]],
            ['id' => 1, 'name' => 'Daily digest', 'frequency' => 'daily', 'limit_rows' => 5]
        );

        $html = DigestReportService::renderHtml($payload);
        $text = DigestReportService::renderText($payload);

        $this->assertSame(2, $payload['summary']['servers']);
        $this->assertSame(12, $payload['index_signals'][0]['score']);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('Daily digest', $text);
        $this->assertStringContainsString('2.00s total', $text);
    }

    public function testDigestReportQueriesAreWindowBounded(): void
    {
        $summarySql = DigestReportService::buildSummarySql(7);
        $slowSql = DigestReportService::buildSlowDigestSql(7, 5);
        $indexSql = DigestReportService::buildIndexSignalSql(7, 5);

        foreach ([$summarySql, $slowSql, $indexSql] as $sql) {
            $this->assertStringContainsString('stat.date >= DATE_SUB(NOW(), INTERVAL 7 DAY)', $sql);
            $this->assertStringNotContainsString('SELECT MAX(date) FROM ts_mysql_digest_stat', $sql);
        }

        $this->assertStringContainsString('LIMIT 5', $slowSql);
        $this->assertStringContainsString('LIMIT 5', $indexSql);
    }
}
