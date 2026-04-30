<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InstallerPasswordGenerationTest extends TestCase
{
    public function testDebian13InstallerDoesNotDerivePasswordsFromCurrentTime(): void
    {
        $script = (string) file_get_contents(__DIR__.'/../install/debian13.sh');
        $helper = (string) file_get_contents(__DIR__.'/../install/lib/install_secrets.sh');

        $this->assertStringNotContainsString('date +%s | sha256sum', $script);
        $this->assertStringNotContainsString('date +%s | sha256sum', $helper);
        $this->assertStringContainsString('generate_password()', $helper);
        $this->assertStringContainsString('openssl rand -base64 48', $helper);
        $this->assertStringContainsString("tr -dc 'A-Za-z0-9'", $helper);
        $this->assertStringContainsString('/dev/urandom', $helper);
        $this->assertStringContainsString('pwd_pmacontrol=$(generate_password)', $script);
        $this->assertStringContainsString('if [[ -z "${pwd_admin}" ]]; then', $script);
        $this->assertStringContainsString('pwd_admin=$(generate_password)', $script);
        $this->assertStringContainsString('pwd_webservice=$(generate_password)', $script);
    }
}
