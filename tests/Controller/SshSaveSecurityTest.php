<?php

declare(strict_types=1);

use App\Controller\Ssh;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class SshSaveSecurityTest extends TestCase
{
    public function testSshSaveRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'ssh.save');

        $outcome = Ssh::evaluateSaveRequest(
            $this->sshKeyPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame($this->validSshKeyPayload(), $outcome['ssh_key']);
    }

    public function testSshSaveRequestAcceptsValidEditId(): void
    {
        $this->assertSame(
            array_merge($this->validSshKeyPayload(), ['id' => 7]),
            Ssh::normalizeSavePayload(['ssh_key' => $this->validSshKeyPayload(['id' => '7'])])
        );
    }

    public function testSshSaveRequestRejectsNonPost(): void
    {
        $outcome = Ssh::evaluateSaveRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testSshSaveRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'ssh.save');

        $outcome = Ssh::evaluateSaveRequest(
            [Csrf::DEFAULT_FIELD => $token, 'ssh_key' => 'invalid'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['ssh_key']);
    }

    public function testSshSaveRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'storage_area.add');
        $server = $this->sameSitePostServer();

        $missingToken = Ssh::evaluateSaveRequest(
            ['ssh_key' => $this->validSshKeyPayload()],
            $server,
            $session
        );
        $foreignScope = Ssh::evaluateSaveRequest(
            $this->sshKeyPost($foreignToken),
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testSshSavePayloadRejectsMalformedValues(): void
    {
        $this->assertNull(Ssh::normalizeSavePayload([]));
        $this->assertNull(Ssh::normalizeSavePayload(['ssh_key' => 'invalid']));
        $this->assertNull(Ssh::normalizeSavePayload(['ssh_key' => $this->validSshKeyPayload(['name' => str_repeat('a', 65)])]));
        $this->assertNull(Ssh::normalizeSavePayload(['ssh_key' => $this->validSshKeyPayload(['user' => str_repeat('a', 65)])]));
        $this->assertNull(Ssh::normalizeSavePayload(['ssh_key' => $this->validSshKeyPayload(['public_key' => ''])]));
        $this->assertNull(Ssh::normalizeSavePayload(['ssh_key' => $this->validSshKeyPayload(['private_key' => ['nested']])]));
        $this->assertNull(Ssh::normalizeSavePayload(['ssh_key' => $this->validSshKeyPayload(['id' => '0'])]));
        $this->assertNull(Ssh::normalizeSavePayload(['ssh_key' => $this->validSshKeyPayload(['id' => '7 OR 1=1'])]));
    }

    public function testSshKeyLookupSqlEscapesFingerprintAndUser(): void
    {
        $sql = Ssh::buildSshKeyLookupSql(
            "aa'bb",
            "root' OR '1'='1",
            static fn (string $value): string => str_replace("'", "\\'", $value)
        );

        $this->assertSame("SELECT id from ssh_key WHERE fingerprint='aa\\'bb' and user = 'root\\' OR \\'1\\'=\\'1'", $sql);
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeLookup(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'ssh.save');
        $post = $this->sshKeyPost($token, ['user' => "root' OR '1'='1"]);
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');
        $this->assertSame(
            "SELECT id from ssh_key WHERE fingerprint='fingerprint' and user = 'root' OR '1'='1'",
            $this->legacySshKeyLookupSql($post, 'fingerprint')
        );

        $outcome = Ssh::evaluateSaveRequest($post, $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['ssh_key']);
    }

    public function testSshSaveUsesSharedCsrfGuardAndFormCarriesToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Ssh.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Ssh/add.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString("private const SSH_SAVE_CSRF_SCOPE = 'ssh.save'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::SSH_SAVE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::SSH_SAVE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('buildSshKeyLookupSql($fingerprint, $keys[', $controller);
        $this->assertStringNotContainsString('$_POST[\'ssh_key\'][\'user\']', $controller);

        $this->assertStringContainsString('$sshSaveCsrfInput', $view);
        $this->assertStringContainsString('<input type="hidden"', $view);
        $this->assertStringContainsString('$sshSaveCsrfField', $view);
        $this->assertStringContainsString('$sshSaveCsrfToken', $view);
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }

    private function sshKeyPost(string $token, array $overrides = []): array
    {
        return [
            Csrf::DEFAULT_FIELD => $token,
            'ssh_key' => $this->validSshKeyPayload($overrides),
        ];
    }

    private function validSshKeyPayload(array $overrides = []): array
    {
        return array_replace([
            'name' => 'prod-key',
            'user' => 'deploy',
            'public_key' => 'ssh-rsa AAAATESTKEY comment',
            'private_key' => "-----BEGIN OPENSSH PRIVATE KEY-----\nkey\n-----END OPENSSH PRIVATE KEY-----",
        ], $overrides);
    }

    private function legacySshKeyLookupSql(array $post, string $fingerprint): string
    {
        return "SELECT id from ssh_key WHERE fingerprint='".$fingerprint."' and user = '".$post['ssh_key']['user']."'";
    }
}
