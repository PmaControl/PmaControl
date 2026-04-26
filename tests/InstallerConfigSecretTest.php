<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InstallerConfigSecretTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        $this->script = file_get_contents(__DIR__ . '/../install/debian13.sh');
    }

    public function testDebian13InstallerDoesNotUsePredictableTmpConfigPath(): void
    {
        $this->assertStringNotContainsString('/tmp/config.json', $this->script);
    }

    public function testDebian13InstallerCreatesPrivateTemporaryConfigFile(): void
    {
        $this->assertStringContainsString(
            'INSTALL_CONFIG_FILE=$(mktemp /tmp/pmacontrol-install-config.XXXXXX)',
            $this->script
        );
        $this->assertStringContainsString('chmod 600 "${INSTALL_CONFIG_FILE}"', $this->script);
    }

    public function testDebian13InstallerDeletesTemporaryConfigOnExit(): void
    {
        $this->assertStringContainsString('cleanup_install_config', $this->script);
        $this->assertStringContainsString('trap cleanup_install_artifacts EXIT', $this->script);
        $this->assertStringContainsString('rm -f "${INSTALL_CONFIG_FILE}"', $this->script);
    }

    public function testDebian13InstallerConsumesGeneratedTemporaryConfigPath(): void
    {
        $this->assertStringContainsString('./install.sh -c "${INSTALL_CONFIG_FILE}"', $this->script);
    }
}
