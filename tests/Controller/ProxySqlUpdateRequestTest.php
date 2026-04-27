<?php

declare(strict_types=1);

use App\Controller\ProxySQL;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ProxySqlUpdateRequestTest extends TestCase
{
    public function testUpdateAllowsOnlyPostForHttpRequests(): void
    {
        $this->assertFalse(ProxySQL::isUpdateRequestAllowed(false, 'GET'));
        $this->assertFalse(ProxySQL::isUpdateRequestAllowed(false, null));
        $this->assertTrue(ProxySQL::isUpdateRequestAllowed(false, 'POST'));
        $this->assertTrue(ProxySQL::isUpdateRequestAllowed(true, 'GET'));
    }

    public function testUpdateRequestRejectsNonPostHttpRequest(): void
    {
        $outcome = ProxySQL::evaluateUpdateRequest(
            ['5', 'SAVE', 'MYSQL_USERS', 'DISK'],
            [],
            ['REQUEST_METHOD' => 'GET'],
            []
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['command']);
    }

    public function testUpdateRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.update');

        $outcome = ProxySQL::evaluateUpdateRequest(
            ['5', 'SAVE', 'MYSQL_USERS', 'DISK'],
            [Csrf::DEFAULT_FIELD => $token],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame(
            [
                'id_proxysql_server' => 5,
                'from' => 'SAVE',
                'table' => 'MYSQL_USERS',
                'to' => 'DISK',
            ],
            $outcome['command']
        );
    }

    public function testUpdateRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.update');

        $outcome = ProxySQL::evaluateUpdateRequest(
            ['5', 'DROP', 'MYSQL_USERS', 'DISK'],
            [Csrf::DEFAULT_FIELD => $token],
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
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['command']);
    }

    public function testUpdateRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'proxysql.update_field');
        $server = $this->sameSitePostServer();

        $missingToken = ProxySQL::evaluateUpdateRequest(['5', 'SAVE', 'MYSQL_USERS', 'DISK'], [], $server, $session);
        $foreignScope = ProxySQL::evaluateUpdateRequest(
            ['5', 'SAVE', 'MYSQL_USERS', 'DISK'],
            [Csrf::DEFAULT_FIELD => $foreignToken],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertNull($missingToken['command']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertNull($foreignScope['command']);
    }

    public function testUpdateRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.update');
        $post = [Csrf::DEFAULT_FIELD => $token];
        $server = $this->sameSitePostServer();

        $this->assertNull(ProxySQL::normalizeUpdateCommandPayload([]));
        $this->assertNull(ProxySQL::normalizeUpdateCommandPayload(['0', 'SAVE', 'MYSQL_USERS', 'DISK']));
        $this->assertNull(ProxySQL::normalizeUpdateCommandPayload(['5 OR 1=1', 'SAVE', 'MYSQL_USERS', 'DISK']));
        $this->assertNull(ProxySQL::normalizeUpdateCommandPayload(['5', 'DROP', 'MYSQL_USERS', 'DISK']));
        $this->assertNull(ProxySQL::normalizeUpdateCommandPayload(['5', 'SAVE', 'mysql_users;DROP', 'DISK']));
        $this->assertNull(ProxySQL::normalizeUpdateCommandPayload(['5', 'SAVE', 'MYSQL_USERS', 'REMOTE']));

        $outcome = ProxySQL::evaluateUpdateRequest(['5', 'DROP', 'MYSQL_USERS', 'DISK'], $post, $server, $session);

        $this->assertSame(400, $outcome['status']);
        $this->assertSame('Invalid ProxySQL update command', $outcome['body']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['command']);
    }

    public function testUpdateRequestAllowsCliWithoutCsrfToken(): void
    {
        $outcome = ProxySQL::evaluateUpdateRequest(
            ['5', 'LOAD', 'MYSQL_QUERY_RULES', 'RUNTIME'],
            [],
            ['REQUEST_METHOD' => 'GET'],
            [],
            true
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame('LOAD MYSQL QUERY RULES TO RUNTIME;', ProxySQL::buildUpdateCommandSql($outcome['command']));
    }

    public function testUpdateRedirectUsesRefererWhenPresent(): void
    {
        $this->assertSame(
            '/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/',
            ProxySQL::getUpdateRedirectTarget('5', 'MYSQL_USERS', '/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/', '/base/')
        );
    }

    public function testUpdateRedirectIgnoresExternalReferer(): void
    {
        $this->assertSame(
            '/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/',
            ProxySQL::getUpdateRedirectTarget('5', 'MYSQL_USERS', 'https://example.invalid/phish', '/pmacontrol/fr/', 'pmacontrol.local')
        );
    }

    public function testUpdateRedirectAcceptsSameHostReferer(): void
    {
        $this->assertSame(
            'https://pmacontrol.local/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/',
            ProxySQL::getUpdateRedirectTarget(
                '5',
                'MYSQL_USERS',
                'https://pmacontrol.local/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/',
                '/base/',
                'pmacontrol.local'
            )
        );
    }

    public function testUpdateRedirectFallsBackToConfigPage(): void
    {
        $this->assertSame(
            '/pmacontrol/fr/ProxySQL/config/5/MYSQL_USERS/',
            ProxySQL::getUpdateRedirectTarget('5', 'MYSQL_USERS', null, '/pmacontrol/fr/')
        );
    }

    public function testConfigViewDoesNotExposeUpdateAsGetLink(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../App/view/ProxySQL/config.view.php');

        $this->assertStringNotContainsString('<a href="\'.LINK.\'ProxySQL/update/', $source);
        $this->assertStringContainsString('<form method="post"', $source);
        $this->assertStringContainsString('$proxySqlUpdateCsrfField', $source);
        $this->assertStringContainsString('$proxySqlUpdateCsrfToken', $source);
        $this->assertStringContainsString('<input type="hidden" name="', $source);
        $this->assertStringContainsString("LINK.'ProxySQL/update/'", $source);
    }

    public function testUpdateUsesSeeOtherForPostRedirectGet(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../App/Controller/ProxySQL.php');

        $this->assertStringContainsString("private const PROXYSQL_UPDATE_CSRF_SCOPE = 'proxysql.update'", $source);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::PROXYSQL_UPDATE_CSRF_SCOPE)', $source);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::PROXYSQL_UPDATE_CSRF_SCOPE)', $source);
        $this->assertStringContainsString('evaluateUpdateRequest($param, $_POST, $_SERVER, $_SESSION, IS_CLI)', $source);
        $this->assertStringContainsString(', true, 303);', $source);
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
