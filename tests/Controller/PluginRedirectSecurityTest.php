<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PluginRedirectSecurityTest extends TestCase
{
    private string $controller;

    protected function setUp(): void
    {
        $this->controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Plugin.php');
    }

    public function testPluginImportsSafeRedirect(): void
    {
        self::assertStringContainsString('use App\\Library\\Security\\SafeRedirect;', $this->controller);
    }

    public function testRemoveUsesSafeRedirectForRefererFallback(): void
    {
        $removeBody = $this->methodBody('public function remove($param)');

        self::assertStringContainsString(
            "\$this->redirectTo(SafeRedirect::refererOrFallback(\$_SERVER, LINK.'plugin/index'));",
            $removeBody
        );
        self::assertStringNotContainsString("redirectTo(\$_SERVER['HTTP_REFERER']", $removeBody);
        self::assertStringNotContainsString('redirectTo($_SERVER["HTTP_REFERER"]', $removeBody);
    }

    public function testPluginNeverRedirectsDirectlyToReferer(): void
    {
        self::assertStringNotContainsString("redirectTo(\$_SERVER['HTTP_REFERER']", $this->controller);
        self::assertStringNotContainsString('redirectTo($_SERVER["HTTP_REFERER"]', $this->controller);
    }

    public function testInstallRedirectKeepsExistingApplicationDestination(): void
    {
        self::assertStringContainsString(
            '$this->redirectTo(str_replace("{LINK}", LINK, $lastinstallmenu));',
            $this->controller
        );
    }

    private function methodBody(string $signature): string
    {
        $start = strpos($this->controller, $signature);
        self::assertNotFalse($start, 'Method not found: ' . $signature);

        $openBrace = strpos($this->controller, '{', (int) $start);
        self::assertNotFalse($openBrace, 'Method opening brace not found: ' . $signature);

        $depth = 0;
        $length = strlen($this->controller);
        for ($index = (int) $openBrace; $index < $length; $index++) {
            if ($this->controller[$index] === '{') {
                $depth++;
                continue;
            }

            if ($this->controller[$index] !== '}') {
                continue;
            }

            $depth--;
            if ($depth === 0) {
                return substr($this->controller, (int) $start, $index - (int) $start + 1);
            }
        }

        self::fail('Method closing brace not found: ' . $signature);
    }
}
