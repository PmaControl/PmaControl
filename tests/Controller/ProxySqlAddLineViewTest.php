<?php

use PHPUnit\Framework\TestCase;

if (!function_exists('__')) {
    function __($value)
    {
        return $value;
    }
}

if (!defined('LINK')) {
    define('LINK', '/');
}

if (!defined('IMG')) {
    define('IMG', '/img/');
}

final class ProxySqlAddLineViewTest extends TestCase
{
    public function testAddLineViewHandlesMissingContext(): void
    {
        $output = $this->captureViewOutput(function (): void {
            include __DIR__ . '/../../App/view/ProxySQL/addLine.view.php';
        });

        $this->assertStringContainsString('ProxySQL context is missing', $output);
    }

    public function testMenuViewHandlesPartialProxySqlContext(): void
    {
        $output = $this->captureViewOutput(function (): void {
            $data = [
                'id_proxysql_server' => 'missing',
                'proxysql' => [
                    1 => ['id' => 1],
                    'invalid',
                ],
                'param' => ['menu_current' => 'config'],
                'menu' => [
                    'config' => [],
                    'statistic' => ['link' => '/ProxySQL/statistic/1', 'title' => 'Statistics'],
                    'invalid',
                ],
            ];

            include __DIR__ . '/../../App/view/ProxySQL/menu.view.php';
        });

        $this->assertStringContainsString('ProxySQL', $output);
        $this->assertStringContainsString('/ProxySQL/statistic/1', $output);
    }

    private function captureViewOutput(callable $callback): string
    {
        $buffer_level = ob_get_level();

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        ob_start();
        try {
            $callback();

            return (string) ob_get_clean();
        } catch (Throwable $exception) {
            while (ob_get_level() > $buffer_level) {
                ob_end_clean();
            }

            throw $exception;
        } finally {
            restore_error_handler();
        }
    }
}
