<?php

declare(strict_types=1);

use App\Controller\Docker;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class DockerAddSecurityTest extends TestCase
{
    public function testDockerAddAcceptsValidPostShape(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'docker.add');

        $outcome = Docker::evaluateAddRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'docker_server' => $this->validDockerServerPayload(),
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame($this->validDockerServerNormalized(), $outcome['docker_server']);
    }

    public function testDockerAddRejectsNonPost(): void
    {
        $outcome = Docker::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['docker_server']);
    }

    public function testDockerAddRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'docker.add');

        $outcome = Docker::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'docker_server' => $this->validDockerServerPayload()],
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
        $this->assertNull($outcome['docker_server']);
    }

    public function testDockerAddRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'docker.addContainer');
        $server = $this->sameSitePostServer();

        $missingToken = Docker::evaluateAddRequest(
            ['docker_server' => $this->validDockerServerPayload()],
            $server,
            $session
        );
        $foreignScope = Docker::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'docker_server' => $this->validDockerServerPayload()],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testDockerAddPayloadRejectsMalformedOrOutOfSchemaValues(): void
    {
        $this->assertNull(Docker::normalizeAddPayload([]));
        $this->assertNull(Docker::normalizeAddPayload(['docker_server' => 'invalid']));
        $this->assertNull(Docker::normalizeAddPayload(['docker_server' => []]));
        $this->assertNull(Docker::normalizeAddPayload(['docker_server' => $this->validDockerServerPayload(['unexpected' => '1'])]));
        $this->assertNull(Docker::normalizeAddPayload(['docker_server' => $this->validDockerServerPayload(['hostname' => ['docker.local']])]));
        $this->assertNull(Docker::normalizeAddPayload(['docker_server' => $this->validDockerServerPayload(['display_name' => str_repeat('a', 129)])]));
        $this->assertNull(Docker::normalizeAddPayload(['docker_server' => $this->validDockerServerPayload(['hostname' => str_repeat('a', 256)])]));
        $this->assertNull(Docker::normalizeAddPayload(['docker_server' => $this->validDockerServerPayload(['port' => '0'])]));
        $this->assertNull(Docker::normalizeAddPayload(['docker_server' => $this->validDockerServerPayload(['port' => '65536'])]));
        $this->assertNull(Docker::normalizeAddPayload(['docker_server' => $this->validDockerServerPayload(['id_ssh_key' => '7 OR 1=1'])]));
        $this->assertNull(Docker::normalizeAddPayload(['docker_server' => $this->validDockerServerPayload(['is_active' => 'yes'])]));

        $this->assertSame(
            $this->validDockerServerNormalized(['hostname' => '', 'port' => 22, 'id_ssh_key' => 0]),
            Docker::normalizeAddPayload(['docker_server' => $this->validDockerServerPayload(['hostname' => '', 'port' => '', 'id_ssh_key' => ''])])
        );
    }

    public function testExternalPostWouldHaveReachedLegacySshFlowButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'docker.add');
        $post = [Csrf::DEFAULT_FIELD => $token, 'docker_server' => $this->validDockerServerPayload()];

        $this->assertSame('docker.local', Docker::normalizeAddPayload($post)['hostname']);

        $outcome = Docker::evaluateAddRequest(
            $post,
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertNull($outcome['docker_server']);
    }

    public function testDockerAddUsesSharedCsrfLibraryBeforeSshAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Docker.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Docker/add.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('use App\\Library\\Security\\GroupedFormRequest;', $controller);
        $this->assertStringContainsString("private const DOCKER_ADD_CSRF_SCOPE = 'docker.add'", $controller);
        $this->assertStringContainsString('GroupedFormRequest::evaluate(', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::DOCKER_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::isPost($_SERVER)', $controller);

        $evaluatePosition = strpos($controller, '$outcome = self::evaluateAddRequest($_POST, $_SERVER, $_SESSION);');
        $decryptPosition = strpos($controller, 'Crypt::decrypt($ob->private_key, CRYPT_KEY);');
        $savePosition = strpos($controller, '$id = $db->sql_save($docker_server);');
        $this->assertIsInt($evaluatePosition);
        $this->assertIsInt($decryptPosition);
        $this->assertIsInt($savePosition);
        $this->assertLessThan($decryptPosition, $evaluatePosition);
        $this->assertLessThan($savePosition, $evaluatePosition);

        $this->assertStringContainsString('$dockerAddCsrfField', $view);
        $this->assertStringContainsString('$dockerAddCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('method="post"', $view);
        $this->assertStringContainsString('docker/add', $view);
    }

    private function validDockerServerPayload(array $override = []): array
    {
        return array_replace([
            'display_name' => 'Docker host',
            'hostname' => 'docker.local',
            'port' => '2222',
            'is_active' => '1',
            'id_ssh_key' => '7',
        ], $override);
    }

    private function validDockerServerNormalized(array $override = []): array
    {
        return array_replace([
            'display_name' => 'Docker host',
            'hostname' => 'docker.local',
            'port' => 2222,
            'is_active' => '1',
            'id_ssh_key' => 7,
        ], $override);
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
