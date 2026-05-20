<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MysqlServerRedirectSecurityTest extends TestCase
{
    private string $controller;

    protected function setUp(): void
    {
        $this->controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/MysqlServer.php');
    }

    public function testControllerUsesSafeRedirectHelper(): void
    {
        self::assertStringContainsString('use App\\Library\\Security\\SafeRedirect;', $this->controller);
        self::assertSame(3, substr_count($this->controller, 'SafeRedirect::refererOrFallback($_SERVER,'));
    }

    public function testRefreshActionsNoLongerReadRefererDirectly(): void
    {
        foreach (['refresh', 'refreshMetric', 'toggleMonitored'] as $method) {
            $slice = $this->methodBody($method);

            self::assertStringContainsString('SafeRedirect::refererOrFallback($_SERVER,', $slice);
            self::assertStringNotContainsString('$_SERVER[\'HTTP_REFERER\']', $slice);
            self::assertStringNotContainsString('$_SERVER["HTTP_REFERER"]', $slice);
        }
    }

    private function methodBody(string $method): string
    {
        $start = strpos($this->controller, 'public function ' . $method . '(');
        self::assertNotFalse($start, 'Method not found: ' . $method);

        $next = strpos($this->controller, "\n    public function ", (int) $start + 1);
        if ($next === false) {
            return substr($this->controller, (int) $start);
        }

        return substr($this->controller, (int) $start, $next - (int) $start);
    }
}
