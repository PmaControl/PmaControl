<?php

declare(strict_types=1);

use App\Controller\Ldap;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class LdapGetGroupFromUserSecurityTest extends TestCase
{
    public function testGetGroupFromUserAcceptsValidPostShape(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'ldap.getGroupFromUser');

        $outcome = Ldap::evaluateGetGroupFromUserRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'ldap' => ['user' => ' j.smith-01@example.test '],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('j.smith-01@example.test', $outcome['user']);
    }

    public function testGetGroupFromUserRejectsNonPost(): void
    {
        $outcome = Ldap::evaluateGetGroupFromUserRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['user']);
    }

    public function testGetGroupFromUserRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'ldap.getGroupFromUser');

        $outcome = Ldap::evaluateGetGroupFromUserRequest(
            [Csrf::DEFAULT_FIELD => $token, 'ldap' => ['user' => 'jsmith']],
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
        $this->assertNull($outcome['user']);
    }

    public function testGetGroupFromUserRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'ldap.index');
        $server = $this->sameSitePostServer();

        $missingToken = Ldap::evaluateGetGroupFromUserRequest(
            ['ldap' => ['user' => 'jsmith']],
            $server,
            $session
        );
        $foreignScope = Ldap::evaluateGetGroupFromUserRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'ldap' => ['user' => 'jsmith']],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testGetGroupFromUserAcceptsSameSiteRefererWithoutOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'ldap.getGroupFromUser');

        $outcome = Ldap::evaluateGetGroupFromUserRequest(
            [Csrf::DEFAULT_FIELD => $token, 'ldap' => ['user' => 'jsmith']],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_REFERER' => 'https://pmacontrol.test/pmacontrol/fr/Ldap/index',
            ],
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('jsmith', $outcome['user']);
    }

    public function testGetGroupFromUserPayloadRejectsInvalidUsernames(): void
    {
        $this->assertNull(Ldap::normalizeGetGroupFromUserPayload([]));
        $this->assertNull(Ldap::normalizeGetGroupFromUserPayload(['ldap' => ['user' => ['nested']]]));
        $this->assertNull(Ldap::normalizeGetGroupFromUserPayload(['ldap' => ['user' => 'john*']]));
        $this->assertNull(Ldap::normalizeGetGroupFromUserPayload(['ldap' => ['user' => 'john)(samaccountname=*)']]));
        $this->assertNull(Ldap::normalizeGetGroupFromUserPayload(['ldap' => ['user' => '*)(memberof=*)']]));
        $this->assertNull(Ldap::normalizeGetGroupFromUserPayload(['ldap' => ['user' => str_repeat('a', 129)]]));
    }

    public function testGetGroupFromUserEscapesLdapFilterValue(): void
    {
        $this->assertSame(
            function_exists('ldap_escape')
                ? ldap_escape('john*()', '', LDAP_ESCAPE_FILTER)
                : addcslashes('john*()', "\\*()\0"),
            Ldap::escapeLdapFilterValue('john*()')
        );
    }

    public function testGetGroupFromUserUsesCsrfAndEscapesOutput(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Ldap.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Ldap/getGroupFromUser.view.php');

        $this->assertStringContainsString("private const LDAP_GET_GROUP_FROM_USER_CSRF_SCOPE = 'ldap.getGroupFromUser'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::LDAP_GET_GROUP_FROM_USER_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::LDAP_GET_GROUP_FROM_USER_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('self::evaluateGetGroupFromUserRequest($_POST, $_SERVER, $_SESSION)', $controller);
        $this->assertStringContainsString('self::escapeLdapFilterValue((string) $command)', $controller);
        $this->assertStringNotContainsString('requestLdap($_POST[\'ldap\'][\'user\'])', $controller);

        $this->assertStringContainsString('$ldapGetGroupFromUserCsrfField', $view);
        $this->assertStringContainsString('$ldapGetGroupFromUserCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('htmlspecialchars((string) $data[\'user\']', $view);
        $this->assertStringContainsString('htmlspecialchars((string) $list', $view);
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
