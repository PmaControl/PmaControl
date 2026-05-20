<?php

declare(strict_types=1);

use App\Controller\Pmm;
use PHPUnit\Framework\TestCase;

final class PmmExportTest extends TestCase
{
    public function testParseExportOptionsAcceptsScopedServerIds(): void
    {
        $options = Pmm::parseExportOptions(['12', '--servers=14,15', '--id=16']);

        $this->assertSame([12, 14, 15, 16], $options['server_ids']);
        $this->assertFalse($options['include_passwords']);
    }

    public function testParseExportOptionsRequiresConfirmationForPasswordExport(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password export requires --confirm-secret-export');

        Pmm::parseExportOptions(['12', '--include-passwords']);
    }

    public function testParseExportOptionsRequiresSingleServerForPasswordExport(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password export requires exactly one selected server');

        Pmm::parseExportOptions(['12', '13', '--include-passwords', '--confirm-secret-export']);
    }

    public function testParseExportOptionsAcceptsConfirmedSingleServerPasswordExport(): void
    {
        $options = Pmm::parseExportOptions(['12', '--include-passwords', '--confirm-secret-export']);

        $this->assertSame([12], $options['server_ids']);
        $this->assertTrue($options['include_passwords']);
    }

    public function testFormatPmmAdminMysqlCommandRedactsPasswordByDefault(): void
    {
        $command = Pmm::formatPmmAdminMysqlCommand([
            'login' => 'monitor',
            'ip' => '10.0.0.12',
            'port' => '3306',
            'display_name' => 'prod mysql',
        ]);

        $this->assertStringContainsString("--password='<redacted>'", $command);
        $this->assertStringNotContainsString('secret', $command);
    }

    public function testFormatPmmAdminMysqlCommandEscapesShellValues(): void
    {
        $command = Pmm::formatPmmAdminMysqlCommand([
            'login' => "ops'user",
            'ip' => '10.0.0.12',
            'port' => '3306',
            'display_name' => 'prod mysql',
        ], 'secret');

        $this->assertStringContainsString("--username='ops'\\''user'", $command);
        $this->assertStringContainsString("--password='secret'", $command);
        $this->assertStringContainsString("--service-name='prod mysql'", $command);
    }
}
