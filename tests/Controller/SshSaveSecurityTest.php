<?php

declare(strict_types=1);

use App\Controller\Ssh;
use App\Library\Html;
use App\Library\Security\PositiveIntegerSelection;
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

    public function testSshKeyByIdSqlUsesTypedIdAndWhitelistedColumns(): void
    {
        $this->assertSame('SELECT public_key FROM ssh_key WHERE id = 42', Ssh::buildSshKeyByIdSql(42, 'public_key'));
        $this->assertSame('SELECT * FROM ssh_key WHERE id = 7', Ssh::buildSshKeyByIdSql(7));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SSH key select columns');
        Ssh::buildSshKeyByIdSql(1, 'public_key FROM ssh_key WHERE id = 1 OR 1=1');
    }

    public function testSshRouteIdNormalizationRejectsInjectionPayloads(): void
    {
        $this->assertSame(42, PositiveIntegerSelection::normalizeSingle('42'));
        $this->assertSame(42, PositiveIntegerSelection::normalizeSingle('042'));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle('0'));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle('-1'));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle('1 OR 1=1'));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle('1; DROP TABLE ssh_key'));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle(['1']));
    }

    public function testLegacySshKeyRouteSqlWasInjectableProof(): void
    {
        $this->assertSame(
            'select public_key from ssh_key where id =1 OR 1=1',
            $this->legacyDisplayPublicSql('1 OR 1=1')
        );
        $this->assertSame(
            'SELECT * FROM ssh_key WHERE id = 1 OR 1=1',
            $this->legacyEditSql('1 OR 1=1')
        );
    }

    public function testSshTextareaValuesAreHtmlEscaped(): void
    {
        $escaped = Html::escape('</textarea><script>alert(1)</script>');

        $this->assertStringContainsString('&lt;/textarea&gt;&lt;script&gt;', $escaped);
        $this->assertStringNotContainsString('</textarea>', $escaped);
        $this->assertStringNotContainsString('<script>', $escaped);
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
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::SSH_SAVE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('buildSshKeyLookupSql($fingerprint, $keys[', $controller);
        $this->assertStringNotContainsString('$_POST[\'ssh_key\'][\'user\']', $controller);

        $this->assertStringContainsString('$sshSaveCsrfInput', $view);
        $this->assertStringContainsString('<input type="hidden"', $view);
        $this->assertStringContainsString('$sshSaveCsrfField', $view);
        $this->assertStringContainsString('$sshSaveCsrfToken', $view);
    }

    public function testSshDisplayAndEditRoutesUseSharedIntegerGuardBeforeSql(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Ssh.php');
        $this->assertIsString($controller);

        $displayPublic = self::extractMethodSource($controller, 'public function display_public');
        $edit = self::extractMethodSource($controller, 'public function edit');

        $this->assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $controller);
        $this->assertStringContainsString('PositiveIntegerSelection::normalizeSingle($param[0] ?? null)', $displayPublic);
        $this->assertStringContainsString('self::buildSshKeyByIdSql($id_ssh_key, \'public_key\')', $displayPublic);
        $this->assertStringNotContainsString('select public_key from ssh_key where id =".$id_ssh_key', $displayPublic);

        $this->assertStringContainsString('PositiveIntegerSelection::normalizeSingle($param[0] ?? null)', $edit);
        $this->assertStringContainsString('$_GET[\'ssh_key\'][\'id\'] = $id_ssh_key;', $edit);
        $this->assertStringContainsString('self::buildSshKeyByIdSql($id_ssh_key)', $edit);
        $this->assertStringNotContainsString('$_GET[\'ssh_key\'][\'id\'] = $param[0]', $edit);
        $this->assertStringNotContainsString('"SELECT * FROM ssh_key WHERE id = ".$id_ssh_key', $edit);
        $this->assertStringNotContainsString('$_SESSION[\'ssh_key\'][\'private_key\'] = Chiffrement::decrypt($ob->private_key)', $edit);
    }

    public function testSshAddViewEscapesSessionAndDataKeys(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/Ssh/add.view.php');
        $this->assertIsString($view);

        $this->assertStringContainsString('use App\\Library\\Html;', $view);
        $this->assertStringContainsString("Html::escape(\$dataSshKey['public_key'] ?? \$sessionSshKey['public_key'] ?? '')", $view);
        $this->assertStringContainsString("Html::escape(\$dataSshKey['private_key'] ?? \$sessionSshKey['private_key'] ?? '')", $view);
        $this->assertStringContainsString('<?= $sshPublicKeyValue ?>', $view);
        $this->assertStringContainsString('<?= $sshPrivateKeyValue ?>', $view);
        $this->assertStringNotContainsString("<?= \$_SESSION['ssh_key']['public_key']", $view);
        $this->assertStringNotContainsString("<?= \$_SESSION['ssh_key']['private_key']", $view);
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

    private function legacyDisplayPublicSql(string $id): string
    {
        return 'select public_key from ssh_key where id =' . $id;
    }

    private function legacyEditSql(string $id): string
    {
        return 'SELECT * FROM ssh_key WHERE id = ' . $id;
    }

    private static function extractMethodSource(string $source, string $signature): string
    {
        $start = strpos($source, $signature);
        self::assertIsInt($start);

        $openBrace = strpos($source, '{', $start);
        self::assertIsInt($openBrace);

        $depth = 0;
        $length = strlen($source);
        for ($i = $openBrace; $i < $length; $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $start, $i - $start + 1);
                }
            }
        }

        self::fail('Unable to extract method source for ' . $signature);
    }
}
