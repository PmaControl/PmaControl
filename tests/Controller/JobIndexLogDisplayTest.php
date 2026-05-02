<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #580 — /job/index log/error cells must be capped to ~10 lines
 * and 1000 px with a scrollbar, and must auto-scroll to the last line.
 * Source-level guard, same pattern as DatabaseRefreshLoadFlagTest /
 * HomeOrphanRefreshWarningTest.
 */
final class JobIndexLogDisplayTest extends TestCase
{
    private string $controller;
    private string $view;

    protected function setUp(): void
    {
        $this->controller = (string) file_get_contents(
            __DIR__ . '/../../App/Controller/Job.php'
        );
        $this->view = (string) file_get_contents(
            __DIR__ . '/../../App/view/Job/index.view.php'
        );
        $this->assertNotSame('', $this->controller, 'Job.php must be readable');
        $this->assertNotSame('', $this->view, 'Job/index.view.php must be readable');
    }

    public function testLogAndErrorCellsUseTheSharedJobLogClass(): void
    {
        $this->assertStringContainsString(
            "'<pre class=\"job-log\">'.\$job['log_msg'].'</pre>'",
            $this->view,
            'log cell must render <pre class="job-log"> (#580)'
        );
        $this->assertStringContainsString(
            "'<pre class=\"job-log\">'.\$job['error_msg'].'</pre>'",
            $this->view,
            'error cell must render <pre class="job-log"> (#580)'
        );
    }

    public function testDuplicateDataLogIdIsRemoved(): void
    {
        $this->assertStringNotContainsString(
            'id="data_log"',
            $this->view,
            'id="data_log" must be removed — it was duplicated across rows × columns and broke getElementById (#580)'
        );
    }

    public function testJobLogStyleBoundsHeightAndWidth(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.job-log\s*\{[^}]*max-height:\s*14em/s',
            $this->view,
            '.job-log must cap height to ~10 lines (14em) (#580)'
        );
        $this->assertMatchesRegularExpression(
            '/\.job-log\s*\{[^}]*max-width:\s*1000px/s',
            $this->view,
            '.job-log must cap width to 1000px (#580)'
        );
        $this->assertMatchesRegularExpression(
            '/\.job-log\s*\{[^}]*overflow:\s*auto/s',
            $this->view,
            '.job-log must enable scrollbars when content overflows (#580)'
        );
        $this->assertMatchesRegularExpression(
            '/\.job-log\s*\{[^}]*white-space:\s*pre/s',
            $this->view,
            '.job-log must keep raw whitespace (no wrap) — long mydumper lines stay on one line with a horizontal scrollbar (#580)'
        );
    }

    public function testJobLogPreservesMonospaceAndBlackBackground(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.job-log\s*\{[^}]*background-color:\s*#000/s',
            $this->view,
            '.job-log must preserve the black background of the original <pre>'
        );
        $this->assertMatchesRegularExpression(
            '/\.job-log\s*\{[^}]*font-family:\s*monospace/s',
            $this->view,
            '.job-log must preserve the monospace font'
        );
    }

    public function testAutoScrollPinsTheLastLine(): void
    {
        $this->assertStringContainsString(
            "document.querySelectorAll('.job-log')",
            $this->view,
            'auto-scroll JS must select all .job-log elements'
        );
        $this->assertStringContainsString(
            'el.scrollTop = el.scrollHeight',
            $this->view,
            'auto-scroll JS must pin scrollTop to the bottom (last line) — operator wants the current status, not the start of the log (#580)'
        );
    }

    public function testJobIndexRedactsRawLogsBeforeAnsiConversion(): void
    {
        $logRead = strpos($this->controller, '$log = file_get_contents($ob[\'log\']);');
        $logRedaction = strpos($this->controller, '$log = Mydumper::redactPasswords($log);');
        $logConversion = strpos($this->controller, '$log = Mydumper::ParseLog($converter->convert($log));');
        $errorRead = strpos($this->controller, '$error = file_get_contents($ob[\'error\']);');
        $errorRedaction = strpos($this->controller, '$error = Mydumper::redactPasswords($error);');
        $errorConversion = strpos($this->controller, '$error = Mydumper::ParseLog($converter->convert($error));');

        $this->assertNotFalse($logRead);
        $this->assertNotFalse($logRedaction);
        $this->assertNotFalse($logConversion);
        $this->assertNotFalse($errorRead);
        $this->assertNotFalse($errorRedaction);
        $this->assertNotFalse($errorConversion);
        $this->assertGreaterThan($logRead, $logRedaction);
        $this->assertLessThan($logConversion, $logRedaction);
        $this->assertGreaterThan($errorRead, $errorRedaction);
        $this->assertLessThan($errorConversion, $errorRedaction);
    }

    public function testParamColumnRedactsSensitiveJsonBeforeRendering(): void
    {
        $this->assertStringContainsString('use App\\Library\\Security\\SecretRedactor;', $this->view);
        $this->assertStringContainsString('SecretRedactor::jsonPayload((string) ($job[\'param\'] ?? \'\'))', $this->view);
        $this->assertStringContainsString('$displayParam', $this->view);
        $this->assertStringContainsString('htmlspecialchars(', $this->view);
        $this->assertStringNotContainsString("json_encode(json_decode(\$job['param'], true), JSON_PRETTY_PRINT)", $this->view);
    }
}
