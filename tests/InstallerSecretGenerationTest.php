<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InstallerSecretGenerationTest extends TestCase
{
    private const INSTALLERS = [
        'install/debian10.sh',
        'install/debian11.sh',
        'install/debian12.sh',
        'install/debian13.sh',
        'install/ubuntu22.04.sh',
        'install/centos9.sh',
        'install/rhel9.4.sh',
        'ci/remote-install-and-test.sh',
    ];

    private string $helper;
    private string $debian13Script;

    protected function setUp(): void
    {
        $this->helper = (string) file_get_contents(__DIR__ . '/../install/lib/install_secrets.sh');
        $this->debian13Script = (string) file_get_contents(__DIR__ . '/../install/debian13.sh');
    }

    public function testTrackedNonFixtureFilesDoNotEmbedPrivateKeyMaterial(): void
    {
        $root = dirname(__DIR__);
        $files = self::trackedFilesOrSkip($root);

        $privateKeyMarkerRegex = '/-----' . 'BEGIN (?:[A-Z0-9 ]+ )?PRIVATE KEY-----/';
        $legacyPrivateKeyPrefix = 'MIIJKQIBAAKCAgEA' . 'sLxsW';
        $legacyPublicKeyPrefix = 'AAAAB3NzaC1yc2EAAAADAQABAAACAQC' . 'wvGxb';
        $allowedMarkerFiles = [
            'App/Library/Ssh.php',
        ];

        foreach ($files as $path) {
            if (str_starts_with($path, 'tests/')) {
                continue;
            }

            if (in_array($path, $allowedMarkerFiles, true)) {
                continue;
            }

            $fullPath = $root . '/' . $path;
            if (!is_file($fullPath)) {
                continue;
            }

            $content = (string) file_get_contents($fullPath);
            $this->assertDoesNotMatchRegularExpression($privateKeyMarkerRegex, $content, $path);
            $this->assertStringNotContainsString($legacyPrivateKeyPrefix, $content, $path);
            $this->assertStringNotContainsString($legacyPublicKeyPrefix, $content, $path);
        }
    }

    /**
     * @return list<string>
     */
    private static function trackedFilesOrSkip(string $root): array
    {
        if (!is_dir($root . '/.git') && !is_file($root . '/.git')) {
            self::markTestSkipped('Git metadata is not available; tracked file secret scan requires git ls-files.');
        }

        $files = [];
        $output = [];
        $exitCode = 0;
        exec('git -C ' . escapeshellarg($root) . ' ls-files 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            self::markTestSkipped('Unable to list tracked files with git ls-files: ' . implode("\n", $output));
        }

        foreach ($output as $path) {
            if ($path !== '') {
                $files[] = $path;
            }
        }

        return $files;
    }

    public function testSharedInstallSecretHelperGeneratesPasswordsAtInstallTime(): void
    {
        $this->assertStringContainsString('pmactrl_install_generate_password()', $this->helper);
        $this->assertStringContainsString('openssl rand -base64 48', $this->helper);
        $this->assertStringContainsString("tr -dc 'A-Za-z0-9'", $this->helper);
        $this->assertStringContainsString('/dev/urandom', $this->helper);
        $this->assertStringContainsString('Unable to generate a secure password.', $this->helper);
        $this->assertStringNotContainsString('date +%s | sha256sum', $this->helper);
    }

    public function testDebian13InstallerUsesSharedSecretHelper(): void
    {
        $this->assertStringContainsString('install/lib/install_secrets.sh', $this->debian13Script);
        $this->assertStringContainsString('pwd_pmacontrol=$(generate_password)', $this->debian13Script);
        $this->assertStringContainsString('if [[ -z "${pwd_admin}" ]]; then', $this->debian13Script);
        $this->assertStringContainsString('pwd_webservice=$(generate_password)', $this->debian13Script);
        $this->assertStringContainsString('mysql_password_json=$(json_escape_string "${pwd_pmacontrol}")', $this->debian13Script);
        $this->assertStringContainsString('admin_password_json=$(json_escape_string "${pwd_admin}")', $this->debian13Script);
        $this->assertStringContainsString('"password": ${mysql_password_json}', $this->debian13Script);
        $this->assertStringContainsString('"password": ${admin_password_json}', $this->debian13Script);
        $this->assertStringContainsString('"password": ${webservice_password_json}', $this->debian13Script);
    }

    public function testSharedInstallSecretHelperGeneratesAndEscapesSshKeyPairAtInstallTime(): void
    {
        $this->assertStringContainsString('ssh-keygen -q -t rsa -b 4096 -m PEM', $this->helper);
        $this->assertStringContainsString('PMACTRL_INSTALL_SSH_KEY_DIR=$(mktemp -d /tmp/pmacontrol-install-ssh.XXXXXX)', $this->helper);
        $this->assertStringContainsString('PMACTRL_INSTALL_SSH_PRIVATE_KEY_FILE="${PMACTRL_INSTALL_SSH_KEY_DIR}/id_rsa"', $this->helper);
        $this->assertStringContainsString('chmod 600 "${PMACTRL_INSTALL_SSH_PRIVATE_KEY_FILE}"', $this->helper);
        $this->assertStringContainsString('jq -Rs .', $this->helper);
    }

    public function testInstallersGenerateSshKeysInsteadOfEmbeddingAStaticPair(): void
    {
        foreach (self::INSTALLERS as $path) {
            $script = (string) file_get_contents(__DIR__ . '/../' . $path);

            $this->assertStringContainsString('install_secrets.sh', $script, $path);
            $this->assertStringContainsString('generate_install_ssh_key', $script, $path);
            $this->assertStringContainsString('ssh_private_key_json=$(json_escape_file "${SSH_PRIVATE_KEY_FILE}")', $script, $path);
            $this->assertStringContainsString('ssh_public_key_json=$(json_escape_file "${SSH_PUBLIC_KEY_FILE}")', $script, $path);
            $this->assertStringContainsString('"private key": ${ssh_private_key_json}', $script, $path);
            $this->assertStringContainsString('"public key": ${ssh_public_key_json}', $script, $path);
        }
    }

    public function testInstallersCleanGeneratedTemporarySshKeyFiles(): void
    {
        $this->assertStringContainsString('cleanup_install_ssh_key', $this->helper);
        $this->assertStringContainsString('pmactrl_install_cleanup_ssh_key', $this->helper);
        $this->assertStringContainsString('rm -rf "${key_dir}"', $this->helper);
        $this->assertStringContainsString('trap cleanup_install_artifacts EXIT', $this->debian13Script);
        $this->assertStringContainsString("trap 'cleanup_install_artifacts; exit 129' HUP", $this->debian13Script);
        $this->assertStringContainsString("trap 'cleanup_install_artifacts; exit 130' INT", $this->debian13Script);
        $this->assertStringContainsString("trap 'cleanup_install_artifacts; exit 143' TERM", $this->debian13Script);

        foreach (array_diff(self::INSTALLERS, ['install/debian13.sh', 'ci/remote-install-and-test.sh']) as $path) {
            $script = (string) file_get_contents(__DIR__ . '/../' . $path);
            $this->assertStringContainsString('trap cleanup_install_ssh_key EXIT', $script, $path);
            $this->assertStringContainsString("trap 'cleanup_install_ssh_key; exit 129' HUP", $script, $path);
            $this->assertStringContainsString("trap 'cleanup_install_ssh_key; exit 130' INT", $script, $path);
            $this->assertStringContainsString("trap 'cleanup_install_ssh_key; exit 143' TERM", $script, $path);
        }

        $ciScript = (string) file_get_contents(__DIR__ . '/../ci/remote-install-and-test.sh');
        $this->assertStringContainsString("trap 'cleanup_install_ssh_key; exit 129' HUP", $ciScript);
        $this->assertStringContainsString("trap 'cleanup_install_ssh_key; exit 130' INT", $ciScript);
        $this->assertStringContainsString("trap 'cleanup_install_ssh_key; exit 143' TERM", $ciScript);
    }

    public function testInstallersInstallSshKeygenDependency(): void
    {
        $debianFamily = [
            'install/debian10.sh',
            'install/debian11.sh',
            'install/debian12.sh',
            'install/debian13.sh',
            'install/ubuntu22.04.sh',
            'ci/remote-install-and-test.sh',
        ];

        foreach ($debianFamily as $path) {
            $script = (string) file_get_contents(__DIR__ . '/../' . $path);
            $this->assertStringContainsString('openssh-client', $script, $path);
        }

        foreach (['install/centos9.sh', 'install/rhel9.4.sh'] as $path) {
            $script = (string) file_get_contents(__DIR__ . '/../' . $path);
            $this->assertStringContainsString('openssh-clients', $script, $path);
        }
    }

    public function testSshControllerDebugKeyRoutesAreRemoved(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../App/Controller/Ssh.php');

        $this->assertDoesNotMatchRegularExpression('/public function test_' . 'key\\s*\\(/', $controller);
        $this->assertDoesNotMatchRegularExpression('/public function test2_' . 'key\\s*\\(/', $controller);
    }
}
