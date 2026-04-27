<?php

declare(strict_types=1);

use App\Controller\Docker;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class DockerAddContainerSecurityTest extends TestCase
{
    public function testDockerAddContainerAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'docker.addContainer');

        $outcome = Docker::evaluateAddContainerRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'docker_container' => $this->validRows(),
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            [['count' => 2, 'id_software' => 3, 'major' => '11.4', 'id_image' => 9, 'label' => 'app-db']],
            $outcome['rows']
        );
    }

    public function testDockerAddContainerRejectsNonPost(): void
    {
        $outcome = Docker::evaluateAddContainerRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['rows']);
    }

    public function testDockerAddContainerRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'docker.addContainer');

        $outcome = Docker::evaluateAddContainerRequest(
            [Csrf::DEFAULT_FIELD => $token, 'docker_container' => $this->validRows()],
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
        $this->assertNull($outcome['rows']);
    }

    public function testDockerAddContainerRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'docker.add');
        $server = $this->sameSitePostServer();

        $missingToken = Docker::evaluateAddContainerRequest(
            ['docker_container' => $this->validRows()],
            $server,
            $session
        );
        $foreignScope = Docker::evaluateAddContainerRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'docker_container' => $this->validRows()],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testDockerAddContainerPayloadRejectsMalformedRows(): void
    {
        $this->assertNull(Docker::normalizeAddContainerPayload([]));
        $this->assertNull(Docker::normalizeAddContainerPayload(['docker_container' => 'invalid']));
        $this->assertNull(Docker::normalizeAddContainerPayload(['docker_container' => []]));
        $this->assertNull(Docker::normalizeAddContainerPayload(['docker_container' => $this->validRows(['label' => ['one', 'two']])]));
        $this->assertNull(Docker::normalizeAddContainerPayload(['docker_container' => $this->validRows(['count' => ['33']])]));
        $this->assertNull(Docker::normalizeAddContainerPayload(['docker_container' => $this->validRows(['id_software' => ['3 OR 1=1']])]));
        $this->assertNull(Docker::normalizeAddContainerPayload(['docker_container' => $this->validRows(['label' => [str_repeat('a', 65)]])]));

        $this->assertSame(
            [['count' => 1, 'id_software' => 0, 'major' => '', 'id_image' => 0, 'label' => '']],
            Docker::normalizeAddContainerPayload([
                'docker_container' => $this->validRows([
                    'count' => [''],
                    'id_software' => [''],
                    'major' => [''],
                    'id_image' => [''],
                    'label' => [''],
                ]),
            ])
        );
    }

    public function testExternalPostWouldHaveReachedLegacySessionMutationButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'docker.addContainer');
        $post = [Csrf::DEFAULT_FIELD => $token, 'docker_container' => $this->validRows()];

        $this->assertSame(3, Docker::normalizeAddContainerPayload($post)[0]['id_software']);

        $outcome = Docker::evaluateAddContainerRequest(
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
        $this->assertNull($outcome['rows']);
        $this->assertArrayNotHasKey('docker_containers_pending', $session);
    }

    public function testDockerAddContainerUsesSharedCsrfLibraryAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Docker.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Docker/addContainer.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('use App\\Library\\Security\\GroupedRowsRequest;', $controller);
        $this->assertStringContainsString("private const DOCKER_ADD_CONTAINER_CSRF_SCOPE = 'docker.addContainer'", $controller);
        $this->assertStringContainsString('GroupedRowsRequest::evaluate(', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::DOCKER_ADD_CONTAINER_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::isPost($_SERVER)', $controller);

        $this->assertStringContainsString('$dockerAddContainerCsrfField', $view);
        $this->assertStringContainsString('$dockerAddContainerCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('method="post"', $view);
    }

    private function validRows(array $override = []): array
    {
        return array_replace([
            'count' => ['2'],
            'id_software' => ['3'],
            'major' => ['11.4'],
            'id_image' => ['9'],
            'label' => ['app-db'],
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
