<?php

declare(strict_types=1);

use App\Controller\Server;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ServerSettingsSecurityTest extends TestCase
{
    public function testSettingsRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'server.settings');

        $outcome = Server::evaluateSettingsRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            [
                0 => [
                    'id' => 7,
                    'display_name' => 'Primary DB',
                    'id_client' => 2,
                    'id_environment' => 3,
                    'is_monitored' => 1,
                    'is_proxy' => 0,
                    'is_vip' => 1,
                ],
            ],
            $outcome['settings']['servers']
        );
        $this->assertSame([7], $outcome['settings']['server_ids']);
        $this->assertTrue($outcome['settings']['tags_submitted']);
        $this->assertSame([4, 5], $outcome['settings']['tags'][0]);
    }

    public function testSettingsRequestRejectsNonPost(): void
    {
        $outcome = Server::evaluateSettingsRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['settings']);
    }

    public function testSettingsRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'server.settings');

        $outcome = Server::evaluateSettingsRequest(
            [Csrf::DEFAULT_FIELD => $token, 'id' => ['7 OR 1=1']],
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
        $this->assertNull($outcome['settings']);
    }

    public function testSettingsRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'worker.update');
        $server = $this->sameSitePostServer();

        $missingToken = Server::evaluateSettingsRequest($this->validPost(null), $server, $session);
        $foreignScope = Server::evaluateSettingsRequest($this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testSettingsPayloadRejectsInvalidValues(): void
    {
        $post = $this->validPost('token');

        $this->assertNull(Server::normalizeSettingsPayload([]));
        $this->assertNull(Server::normalizeSettingsPayload(array_replace($post, ['id' => ['0']])));
        $this->assertNull(Server::normalizeSettingsPayload(array_replace($post, ['id' => ['7 OR 1=1']])));
        $this->assertNull(Server::normalizeSettingsPayload(array_replace($post, ['mysql_server' => []])));
        $this->assertNull(Server::normalizeSettingsPayload($this->withServerField($post, 'display_name', ['Primary DB'])));
        $this->assertNull(Server::normalizeSettingsPayload($this->withServerField($post, 'display_name', str_repeat('a', 256))));
        $this->assertNull(Server::normalizeSettingsPayload($this->withServerField($post, 'id_client', '0')));
        $this->assertNull(Server::normalizeSettingsPayload($this->withServerField($post, 'id_environment', 'env')));
        $this->assertNull(Server::normalizeSettingsPayload($this->withServerField($post, 'is_proxy', ['1'])));
        $this->assertNull(Server::normalizeSettingsPayload(array_replace($post, ['link__mysql_server_tag' => [1 => ['tag' => ['4']]]])));
        $this->assertNull(Server::normalizeSettingsPayload(array_replace($post, ['link__mysql_server_tag' => [0 => ['tag' => ['bad']]]])));
    }

    public function testSettingsPayloadAllowsNoTagSubmissionAndDefaultsFlags(): void
    {
        $post = $this->validPost('token');
        unset($post['link__mysql_server_tag'], $post['mysql_server'][0]['is_monitored'], $post['mysql_server'][0]['is_proxy'], $post['mysql_server'][0]['is_vip']);

        $settings = Server::normalizeSettingsPayload($post);

        $this->assertSame(0, $settings['servers'][0]['is_monitored']);
        $this->assertSame(0, $settings['servers'][0]['is_proxy']);
        $this->assertSame(0, $settings['servers'][0]['is_vip']);
        $this->assertFalse($settings['tags_submitted']);
        $this->assertSame([], $settings['tags']);
    }

    public function testExternalPostWouldPassLegacyMethodGateButIsRejectedBeforeSql(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'server.settings');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');

        $outcome = Server::evaluateSettingsRequest($this->validPost($token), $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['settings']);
    }

    public function testSettingsUsesSharedCsrfGuardAndFormCarriesToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Server.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Server/settings.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString("private const SERVER_SETTINGS_CSRF_SCOPE = 'server.settings'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::SERVER_SETTINGS_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::SERVER_SETTINGS_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('$serverSettingsCsrfField', $view);
        $this->assertStringContainsString('$serverSettingsCsrfToken', $view);
        $this->assertStringContainsString('<input type="hidden"', $view);
        $this->assertStringContainsString('name="settings" value="1"', $view);
    }

    public function testClientEnvironmentFilterUsesGetInsteadOfPost(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Common.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Common/displayClientEnvironment.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringContainsString('$_GET[\'client_environment\']', $controller);
        $this->assertStringNotContainsString('$_POST[\'client_environment\']', $controller);
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

    private function validPost(?string $token): array
    {
        $post = [
            'settings' => '1',
            'id' => [0 => '7'],
            'mysql_server' => [
                0 => [
                    'display_name' => ' Primary DB ',
                    'id_client' => '2',
                    'id_environment' => '3',
                    'is_monitored' => '1',
                    'is_vip' => '1',
                ],
            ],
            'link__mysql_server_tag' => [
                0 => [
                    'tag' => ['4', '4', '5'],
                ],
            ],
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function withServerField(array $post, string $field, $value): array
    {
        $post['mysql_server'][0][$field] = $value;
        return $post;
    }
}
