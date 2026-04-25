<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InstallerPasswordGenerationTest extends TestCase
{
    public function testDebian13InstallerDoesNotDerivePasswordsFromCurrentTime(): void
    {
        $script = (string) file_get_contents(__DIR__.'/../install/debian13.sh');

        $this->assertStringNotContainsString('date +%s | sha256sum', $script);
        $this->assertStringContainsString('generate_password()', $script);
        $this->assertStringContainsString('openssl rand -hex 16', $script);
        $this->assertStringContainsString('/dev/urandom', $script);
        $this->assertMatchesRegularExpression('/pwd_pmacontrol="\\$\\(generate_password\\)"/', $script);
        $this->assertMatchesRegularExpression('/pwd_admin="\\$\\(generate_password\\)"/', $script);
    }
}
