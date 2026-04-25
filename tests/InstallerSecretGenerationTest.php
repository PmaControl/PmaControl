<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InstallerSecretGenerationTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        $this->script = file_get_contents(__DIR__ . '/../install/debian13.sh');
    }

    public function testDebian13InstallerDoesNotEmbedStaticCredentialMaterial(): void
    {
        $this->assertStringNotContainsString('BEGIN RSA PRIVATE KEY', $this->script);
        $this->assertStringNotContainsString('ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCwvGxb', $this->script);
        $this->assertStringNotContainsString('QDRWSHGqdrtwhqetrHthTH', $this->script);
        $this->assertStringNotContainsString('secret_password', $this->script);
        $this->assertStringNotContainsString('pmacontrol.68koncept.com', $this->script);
        $this->assertStringNotContainsString('CN=pmacontrol-auth', $this->script);
        $this->assertStringNotContainsString('date +%s | sha256sum', $this->script);
    }

    public function testDebian13InstallerGeneratesPasswordsAtInstallTime(): void
    {
        $this->assertStringContainsString('generate_password()', $this->script);
        $this->assertStringContainsString('openssl rand -base64 48', $this->script);
        $this->assertStringContainsString("tr -dc 'A-Za-z0-9'", $this->script);
        $this->assertStringContainsString('Unable to generate a secure password.', $this->script);
        $this->assertStringNotContainsString('password=$(generate_password)', $this->script);
        $this->assertStringContainsString('pwd_pmacontrol=$(generate_password)', $this->script);
        $this->assertStringContainsString('if [[ -z "${pwd_admin}" ]]; then', $this->script);
        $this->assertStringContainsString('pwd_webservice=$(generate_password)', $this->script);
        $this->assertStringContainsString('mysql_password_json=$(json_escape_string "${pwd_pmacontrol}")', $this->script);
        $this->assertStringContainsString('admin_password_json=$(json_escape_string "${pwd_admin}")', $this->script);
        $this->assertStringContainsString('"password": ${mysql_password_json}', $this->script);
        $this->assertStringContainsString('"password": ${admin_password_json}', $this->script);
        $this->assertStringContainsString('"password": ${webservice_password_json}', $this->script);
    }

    public function testDebian13InstallerGeneratesAndEscapesSshKeyPairAtInstallTime(): void
    {
        $this->assertStringContainsString('ssh-keygen -q -t rsa -b 4096 -m PEM', $this->script);
        $this->assertStringContainsString('SSH_KEY_DIR=$(mktemp -d /tmp/pmacontrol-install-ssh.XXXXXX)', $this->script);
        $this->assertStringContainsString('ssh_private_key_json=$(json_escape_file "${SSH_PRIVATE_KEY_FILE}")', $this->script);
        $this->assertStringContainsString('ssh_public_key_json=$(json_escape_file "${SSH_PUBLIC_KEY_FILE}")', $this->script);
        $this->assertStringContainsString('"private key": ${ssh_private_key_json}', $this->script);
        $this->assertStringContainsString('"public key": ${ssh_public_key_json}', $this->script);
    }

    public function testDebian13InstallerCleansGeneratedTemporarySshKeyFiles(): void
    {
        $this->assertStringContainsString('trap cleanup_install_ssh_key EXIT', $this->script);
        $this->assertStringContainsString('rm -rf "${SSH_KEY_DIR}"', $this->script);
    }

    public function testDebian13InstallerInstallsSshKeygenDependency(): void
    {
        $this->assertStringContainsString('openssh-client', $this->script);
    }
}
