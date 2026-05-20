<?php

declare(strict_types=1);

use App\Controller\ProxySQL;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

if (!function_exists('__')) {
    function __($value)
    {
        return $value;
    }
}

if (!defined('LINK')) {
    define('LINK', '/');
}

if (!defined('LOG_FILE')) {
    define('LOG_FILE', sys_get_temp_dir() . '/pmacontrol-test.log');
}

final class ProxySqlAddLineSecurityTest extends TestCase
{
    public function testAddLinePostRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.add_line');

        $outcome = ProxySQL::evaluateAddLinePostRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'proxysql_addline' => ['hostname' => '10.0.0.10', 'port' => '3306'],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame(['hostname' => '10.0.0.10', 'port' => '3306'], $outcome['values']);
    }

    public function testAddLinePostRequestRejectsNonPost(): void
    {
        $outcome = ProxySQL::evaluateAddLinePostRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['values']);
    }

    public function testAddLinePostRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.add_line');

        $outcome = ProxySQL::evaluateAddLinePostRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'proxysql_addline' => 'invalid',
            ],
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
        $this->assertNull($outcome['values']);
    }

    public function testAddLinePostRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'proxysql.update');
        $server = $this->sameSitePostServer();

        $missingToken = ProxySQL::evaluateAddLinePostRequest(
            ['proxysql_addline' => ['hostname' => '10.0.0.10']],
            $server,
            $session
        );
        $foreignScope = ProxySQL::evaluateAddLinePostRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'proxysql_addline' => ['hostname' => '10.0.0.10']],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertNull($missingToken['values']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertNull($foreignScope['values']);
    }

    public function testAddLinePostRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'proxysql.add_line');

        $invalidContainer = ProxySQL::evaluateAddLinePostRequest(
            [Csrf::DEFAULT_FIELD => $token, 'proxysql_addline' => 'invalid'],
            $this->sameSitePostServer(),
            $session
        );
        $invalidValue = ProxySQL::evaluateAddLinePostRequest(
            [Csrf::DEFAULT_FIELD => $token, 'proxysql_addline' => ['hostname' => ['10.0.0.10']]],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(400, $invalidContainer['status']);
        $this->assertSame('Invalid ProxySQL addLine payload', $invalidContainer['body']);
        $this->assertFalse($invalidContainer['allowed']);
        $this->assertNull($invalidContainer['values']);
        $this->assertSame(400, $invalidValue['status']);
        $this->assertSame('Invalid ProxySQL addLine payload', $invalidValue['body']);
        $this->assertFalse($invalidValue['allowed']);
        $this->assertNull($invalidValue['values']);
    }

    public function testAddLinePostRequestAllowsCliWithoutCsrfToken(): void
    {
        $outcome = ProxySQL::evaluateAddLinePostRequest(
            ['proxysql_addline' => ['hostname' => '10.0.0.10']],
            ['REQUEST_METHOD' => 'GET'],
            [],
            true
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertSame(['hostname' => '10.0.0.10'], $outcome['values']);
    }

    public function testAddLineViewCarriesCsrfToken(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/ProxySQL/addLine.view.php');

        $this->assertIsString($view);
        $this->assertStringContainsString('$proxySqlAddLineCsrfField', $view);
        $this->assertStringContainsString('$proxySqlAddLineCsrfToken', $view);
        $this->assertStringContainsString('<form action="" method="post">', $view);
        $this->assertStringContainsString('<input type="hidden" name="', $view);
        $this->assertStringContainsString('value="', $view);
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
