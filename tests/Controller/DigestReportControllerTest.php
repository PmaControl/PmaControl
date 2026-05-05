<?php

declare(strict_types=1);

use App\Controller\DigestReport;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class DigestReportControllerTest extends TestCase
{
    public function testEvaluateSaveRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, DigestReport::DIGEST_REPORT_SAVE_CSRF_SCOPE);

        $outcome = DigestReport::evaluateSaveRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'digest_report' => [
                    'name' => 'Daily digest',
                    'report_slug' => 'digest_summary',
                    'id_group' => '1',
                    'frequency' => 'daily',
                    'time_of_day' => '07:00',
                    'day_of_week' => '1',
                    'limit_rows' => '5',
                    'is_active' => '1',
                ],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('Daily digest', $outcome['payload']['name']);
        $this->assertSame('digest_summary', $outcome['payload']['report_slug']);
        $this->assertSame(1, $outcome['payload']['id_group']);
    }

    public function testEvaluateSaveRequestRejectsExternalOriginAndMissingToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, DigestReport::DIGEST_REPORT_SAVE_CSRF_SCOPE);
        $post = [
            Csrf::DEFAULT_FIELD => $token,
            'digest_report' => [
                'name' => 'Daily digest',
                'report_slug' => 'digest_summary',
                'id_group' => '1',
                'frequency' => 'daily',
                'time_of_day' => '07:00',
                'day_of_week' => '1',
                'limit_rows' => '5',
            ],
        ];

        $external = DigestReport::evaluateSaveRequest(
            $post,
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );
        $missingToken = DigestReport::evaluateSaveRequest(
            ['digest_report' => $post['digest_report']],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(403, $external['status']);
        $this->assertSame('Invalid request origin', $external['body']);
        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
    }

    public function testEvaluateRunRequestIsCliOnlyAndParsesDryRun(): void
    {
        $http = DigestReport::evaluateRunRequest(['due'], 'fpm-fcgi');
        $due = DigestReport::evaluateRunRequest(['due', '--dry-run'], 'cli');
        $specific = DigestReport::evaluateRunRequest(['42'], 'cli');
        $invalid = DigestReport::evaluateRunRequest(['abc'], 'cli');

        $this->assertSame(403, $http['status']);
        $this->assertSame(200, $due['status']);
        $this->assertSame('due', $due['payload']['target']);
        $this->assertTrue($due['payload']['dry_run']);
        $this->assertSame(200, $specific['status']);
        $this->assertSame(42, $specific['payload']['schedule_id']);
        $this->assertSame(400, $invalid['status']);
    }

    public function testDigestReportControllerViewAclAndMigrationAreWired(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = (string)file_get_contents($root . '/App/Controller/DigestReport.php');
        $service = (string)file_get_contents($root . '/App/Library/Digest/DigestReportService.php');
        $view = (string)file_get_contents($root . '/App/view/DigestReport/index.view.php');
        $acl = (string)file_get_contents($root . '/config_sample/acl.config.ini');
        $migration = (string)file_get_contents($root . '/sql/incremental_v2/20260430_digest_report_schedule.sql');

        $this->assertStringContainsString('class DigestReport extends Controller', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::DIGEST_REPORT_SAVE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString("\$this->redirectTo(LINK . 'DigestReport/index')", $controller);
        $this->assertStringContainsString("header('Location: ' . \$url, true, 303);", $controller);
        $this->assertStringContainsString('PHP_SAPI', $controller);
        $this->assertStringContainsString('DigestReportService::runDueSchedules', $controller);

        $this->assertStringContainsString('namespace App\\Library\\Digest;', $service);
        $this->assertStringContainsString('stat.date >= DATE_SUB(NOW(), INTERVAL ', $service);
        $this->assertStringContainsString('DigestReportMailer::send', $service);

        $this->assertStringContainsString('method="post"', $view);
        $this->assertStringContainsString("CsrfRender::hiddenInput(\$data, 'digest_report_save')", $view);
        $this->assertStringContainsString('digest_report[report_slug]', $view);
        $this->assertStringContainsString('digest_report[is_active]', $view);

        $this->assertStringContainsString('Administrator[] = "DigestReport/index"', $acl);
        $this->assertStringContainsString('Administrator[] = "DigestReport/run"', $acl);
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS `digest_report_schedule`', $migration);
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS `digest_report_delivery`', $migration);
        $this->assertStringContainsString("'{LINK}DigestReport/index'", $migration);
        $this->assertStringContainsString("`title` = 'Settings'", $migration);
    }

    public function testDigestReportDefinesPostRedirectHelper(): void
    {
        $class = new ReflectionClass(DigestReport::class);

        $this->assertTrue($class->hasMethod('redirectTo'));
        $this->assertSame(DigestReport::class, $class->getMethod('redirectTo')->getDeclaringClass()->getName());
        $this->assertTrue($class->getMethod('redirectTo')->isPrivate());
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }
}
