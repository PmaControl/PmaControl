<?php

declare(strict_types=1);

use App\Controller\Ldap;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class LdapIndexSecurityTest extends TestCase
{
    public function testIndexAcceptsValidConfigPostShape(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'ldap.index');

        $outcome = Ldap::evaluateIndexPostRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'ldap' => [
                    'url' => 'ldap.internal.test',
                    'port' => '389',
                    'bind_dn' => 'cn=reader,dc=example,dc=org',
                    'root_dn' => 'dc=example,dc=org',
                    'root_dn_search' => 'ou=groups,dc=example,dc=org',
                    'bind_passwd' => 'secret',
                    'bind_passwd_confirm' => 'secret',
                ],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('ldap.internal.test', $outcome['post']['ldap']['url']);
        $this->assertSame('off', $outcome['post']['ldap']['check']);
    }

    public function testIndexAcceptsValidGroupPostShape(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'ldap.index');

        $outcome = Ldap::evaluateIndexPostRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'ldap_group' => [
                    ['id' => '2', 'name' => "CN=DBA's,OU=Groups,DC=example,DC=org"],
                    ['id' => '3', 'name' => ''],
                ],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            [
                ['id' => 2, 'name' => "CN=DBA's,OU=Groups,DC=example,DC=org"],
                ['id' => 3, 'name' => ''],
            ],
            $outcome['post']['ldap_group']
        );
    }

    public function testIndexRejectsNonPost(): void
    {
        $outcome = Ldap::evaluateIndexPostRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['post']);
    }

    public function testIndexRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'ldap.index');

        $outcome = Ldap::evaluateIndexPostRequest(
            [Csrf::DEFAULT_FIELD => $token, 'ldap' => ['url' => 'ldap.internal.test']],
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
        $this->assertNull($outcome['post']);
    }

    public function testIndexRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'ldap.getGroupFromUser');
        $server = $this->sameSitePostServer();

        $missingToken = Ldap::evaluateIndexPostRequest(
            ['ldap' => ['url' => 'ldap.internal.test']],
            $server,
            $session
        );
        $foreignScope = Ldap::evaluateIndexPostRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'ldap' => ['url' => 'ldap.internal.test']],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testIndexPayloadRejectsUnexpectedOrNonScalarFields(): void
    {
        $this->assertNull(Ldap::normalizeIndexPostPayload([]));
        $this->assertNull(Ldap::normalizeIndexPostPayload(['ldap' => 'invalid']));
        $this->assertNull(Ldap::normalizeIndexPostPayload(['ldap' => ['unexpected' => '1']]));
        $this->assertNull(Ldap::normalizeIndexPostPayload(['ldap' => ['url' => ['nested']]]));
        $this->assertNull(Ldap::normalizeIndexPostPayload(['ldap_group' => [['id' => '1 OR 1=1', 'name' => 'cn']]]));
        $this->assertNull(Ldap::normalizeIndexPostPayload(['ldap_group' => [['id' => '1', 'name' => ['nested']]]]));
    }

    public function testIndexConfigPayloadTrimsFieldsAndPreservesCheckedState(): void
    {
        $payload = Ldap::normalizeLdapConfigPayload([
            'url' => ' ldap.internal.test ',
            'port' => ' 389 ',
            'bind_dn' => ' cn=reader,dc=example,dc=org ',
            'root_dn' => ' dc=example,dc=org ',
            'root_dn_search' => ' ou=groups,dc=example,dc=org ',
            'bind_passwd' => ' secret ',
            'bind_passwd_confirm' => ' secret ',
            'check' => 'on',
        ]);

        $this->assertSame('ldap.internal.test', $payload['url']);
        $this->assertSame('389', $payload['port']);
        $this->assertSame('cn=reader,dc=example,dc=org', $payload['bind_dn']);
        $this->assertSame('on', $payload['check']);
    }

    public function testIndexErrorRedirectParametersDoNotLeakBindPasswords(): void
    {
        if (!defined('App\Controller\ROOT')) {
            define('App\Controller\ROOT', '/tmp');
        }

        $controller = (new ReflectionClass(Ldap::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(Ldap::class, 'postToGet');

        $url = $method->invoke(
            $controller,
            [
                'ldap' => [
                    'url' => 'ldap.internal.test',
                    'port' => '389',
                    'bind_passwd' => 'secret',
                    'bind_passwd_confirm' => 'secret',
                ],
            ],
            ['bind_passwd', 'bind_passwd_confirm']
        );

        $this->assertStringContainsString('ldap:url:ldap.internal.test', $url);
        $this->assertStringContainsString('ldap:port:389', $url);
        $this->assertStringNotContainsString('bind_passwd', $url);
        $this->assertStringNotContainsString('secret', $url);
    }

    public function testIndexLogPayloadRedactsBindPasswords(): void
    {
        $redacted = Ldap::redactLdapSecrets([
            'ldap' => [
                'url' => 'ldap.internal.test',
                'bind_passwd' => 'secret',
                'bind_passwd_confirm' => 'secret',
            ],
        ]);

        $this->assertSame('ldap.internal.test', $redacted['ldap']['url']);
        $this->assertSame('[redacted]', $redacted['ldap']['bind_passwd']);
        $this->assertSame('[redacted]', $redacted['ldap']['bind_passwd_confirm']);
    }

    public function testIndexUsesSharedCsrfLibraryAndViewSendsTokens(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Ldap.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Ldap/index.view.php');

        $this->assertStringContainsString("private const LDAP_INDEX_CSRF_SCOPE = 'ldap.index'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::LDAP_INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::LDAP_INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('self::evaluateIndexPostRequest($_POST, $_SERVER, $_SESSION)', $controller);
        $this->assertStringContainsString('$db->sql_real_escape_string($ldap_group[\'name\'])', $controller);
        $this->assertStringContainsString('json_encode(self::redactLdapSecrets($post))', $controller);
        $this->assertStringContainsString('DELETE FROM `ldap_group` WHERE id_group =".$ldap_group[\'id\']', $controller);
        $this->assertStringNotContainsString('UpdateConfigFile($_POST[\'ldap\'])', $controller);
        $this->assertStringNotContainsString('testLdap($_POST[\'ldap\']', $controller);

        $this->assertStringContainsString('$ldapIndexCsrfField', $view);
        $this->assertStringContainsString('$ldapIndexCsrfToken', $view);
        $this->assertSame(2, substr_count($view, 'type="hidden" name="<?= $ldapIndexCsrfField ?>" value="<?= $ldapIndexCsrfToken ?>">'));
        $this->assertStringContainsString('method="post"', $view);
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
}
