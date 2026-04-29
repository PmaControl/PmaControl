<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #597 — /job/index param column must be capped to ~10 lines
 * × 600 px with a scrollbar, mirroring the .job-log contract from
 * #580. The JSON pretty-print must be HTML-escaped on its way out.
 * Source-level guard, same pattern as JobIndexLogDisplayTest.
 */
final class JobIndexParamDisplayTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $this->view = (string) file_get_contents(
            __DIR__ . '/../../App/view/Job/index.view.php'
        );
        $this->assertNotSame('', $this->view, 'Job/index.view.php must be readable');
    }

    public function testParamCellUsesJobParamPreClass(): void
    {
        $this->assertMatchesRegularExpression(
            '/<td><pre class="job-param">/',
            $this->view,
            'param cell must render <pre class="job-param"> (#597)'
        );
    }

    public function testLegacyMaxWidth200StyleIsRemoved(): void
    {
        $this->assertStringNotContainsString(
            'max-width:200px; overflow-wrap: break-word',
            $this->view,
            'legacy max-width:200px + overflow-wrap:break-word style must be replaced by the .job-param class (#597)'
        );
    }

    public function testParamJsonIsHtmlEscaped(): void
    {
        $this->assertMatchesRegularExpression(
            '/htmlspecialchars\(\s*\(string\)\s*\(?\s*json_encode\(json_decode\(\$job\[\'param\'\]/s',
            $this->view,
            "param JSON pretty-print must go through htmlspecialchars before reaching the <pre> (#597)"
        );
        $this->assertStringContainsString(
            "ENT_QUOTES | ENT_SUBSTITUTE",
            $this->view,
            'param escaping must use ENT_QUOTES | ENT_SUBSTITUTE for consistency with the rest of the codebase'
        );
    }

    public function testJobParamStyleBoundsHeightAndWidth(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.job-param\s*\{[^}]*max-height:\s*14em/s',
            $this->view,
            '.job-param must cap height to ~10 lines (14em) (#597)'
        );
        $this->assertMatchesRegularExpression(
            '/\.job-param\s*\{[^}]*max-width:\s*350px/s',
            $this->view,
            '.job-param must cap width to 350px (param is structured data — narrower than .job-log)'
        );
        $this->assertMatchesRegularExpression(
            '/\.job-param\s*\{[^}]*overflow:\s*auto/s',
            $this->view,
            '.job-param must enable scrollbars when content overflows (#597)'
        );
        $this->assertMatchesRegularExpression(
            '/\.job-param\s*\{[^}]*white-space:\s*pre/s',
            $this->view,
            '.job-param must keep JSON_PRETTY_PRINT indentation — no character-level word-break (#597)'
        );
        $this->assertMatchesRegularExpression(
            '/\.job-param\s*\{[^}]*font-family:\s*monospace/s',
            $this->view,
            '.job-param must use a monospace font'
        );
    }
}
