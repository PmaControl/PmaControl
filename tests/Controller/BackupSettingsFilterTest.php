<?php

declare(strict_types=1);

use App\Controller\Backup;
use PHPUnit\Framework\TestCase;

final class BackupSettingsFilterTest extends TestCase
{
    public function testSettingsAcceptsGetAndHead(): void
    {
        foreach (['GET', 'HEAD'] as $method) {
            $outcome = Backup::evaluateSettingsRequest(['REQUEST_METHOD' => $method]);

            $this->assertSame(200, $outcome['status']);
            $this->assertSame('', $outcome['body']);
            $this->assertSame([], $outcome['headers']);
        }
    }

    public function testSettingsRejectsResidualPostAndUnsafeMethods(): void
    {
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $outcome = Backup::evaluateSettingsRequest(['REQUEST_METHOD' => $method]);

            $this->assertSame(405, $outcome['status']);
            $this->assertSame('Method Not Allowed', $outcome['body']);
            $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
        }
    }

    public function testSettingsControllerAndViewDoNotExposePostSurface(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Backup.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Backup/settings.view.php');

        $settingsStart = strpos($controller, 'public function settings()');
        $evaluateStart = strpos($controller, 'public static function evaluateSettingsRequest');
        $settingsBody = substr($controller, $settingsStart, $evaluateStart - $settingsStart);

        $this->assertStringContainsString('self::evaluateSettingsRequest($_SERVER)', $settingsBody);
        $this->assertStringNotContainsString('$_POST', $settingsBody);
        $this->assertStringNotContainsString('REQUEST_METHOD\'] === "POST"', $settingsBody);
        $this->assertStringNotContainsString('REQUEST_METHOD\'] == "POST"', $settingsBody);
        $this->assertStringNotContainsString('Crontab::insert(', $settingsBody);

        $this->assertStringNotContainsString('method="post"', $view);
        $this->assertStringNotContainsString('method="POST"', $view);
        $this->assertStringNotContainsString('<form action=""', $view);
        $this->assertStringNotContainsString('</form>', $view);
    }
}
