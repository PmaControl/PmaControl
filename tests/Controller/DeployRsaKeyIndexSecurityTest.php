<?php

declare(strict_types=1);

use App\Controller\DeployRsaKey;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class DeployRsaKeyIndexSecurityTest extends TestCase
{
    public function testIndexAcceptsValidPostShape(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'deploy_rsa_key.index');

        $outcome = DeployRsaKey::evaluateIndexRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(42, $outcome['payload']['ssh_key']['id']);
        $this->assertTrue($outcome['payload']['link__mysql_server__ssh_key'][0]['deploy']);
        $this->assertSame(7, $outcome['payload']['link__mysql_server__ssh_key'][0]['id_mysql_server']);
    }

    public function testIndexRejectsNonPost(): void
    {
        $outcome = DeployRsaKey::evaluateIndexRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['payload']);
    }

    public function testIndexRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'deploy_rsa_key.index');

        $outcome = DeployRsaKey::evaluateIndexRequest(
            $this->validPost($token),
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
        $this->assertNull($outcome['payload']);
    }

    public function testIndexRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'deploy_rsa_key.workerDeploy');
        $server = $this->sameSitePostServer();

        $missingToken = DeployRsaKey::evaluateIndexRequest($this->validPost(null), $server, $session);
        $foreignScope = DeployRsaKey::evaluateIndexRequest($this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testIndexPayloadRejectsMalformedRows(): void
    {
        $post = $this->validPost('token');

        $this->assertNull(DeployRsaKey::normalizeIndexPayload([]));
        $this->assertNull(DeployRsaKey::normalizeIndexPayload(array_replace($post, ['settings' => '0'])));
        $this->assertNull(DeployRsaKey::normalizeIndexPayload(array_replace($post, ['mysql_server' => ['login_ssh' => ['root']]])));
        $this->assertNull(DeployRsaKey::normalizeIndexPayload(array_replace($post, ['ssh_key' => ['id' => '42 OR 1=1']])));
        $this->assertNull(DeployRsaKey::normalizeIndexPayload(array_replace($post, [
            'link__mysql_server__ssh_key' => [['deploy' => 'on', 'id_mysql_server' => '0']],
        ])));
    }

    public function testExternalPostWouldHaveReachedLegacyDeploymentButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'deploy_rsa_key.index');
        $post = $this->validPost($token);

        $this->assertSame(7, DeployRsaKey::normalizeIndexPayload($post)['link__mysql_server__ssh_key'][0]['id_mysql_server']);

        $outcome = DeployRsaKey::evaluateIndexRequest(
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
        $this->assertNull($outcome['payload']);
    }

    public function testIndexUsesSharedCsrfLibraryBeforeDeploymentAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/DeployRsaKey.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/DeployRsaKey/index.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $indexStart = strpos($controller, 'public function index()');
        $indexEnd = strpos($controller, 'private function getFilter()');
        $this->assertIsInt($indexStart);
        $this->assertIsInt($indexEnd);
        $indexBody = substr($controller, $indexStart, $indexEnd - $indexStart);

        $guardPosition = strpos($indexBody, 'evaluateIndexRequest($_POST, $_SERVER, $_SESSION)');
        $deployPosition = strpos($indexBody, '$this->deploy(array($ob->ip, $public, $private));');
        $savePosition = strpos($indexBody, '$gg = $db->sql_save($tmp);');

        $this->assertIsInt($guardPosition);
        $this->assertIsInt($deployPosition);
        $this->assertIsInt($savePosition);
        $this->assertLessThan($deployPosition, $guardPosition);
        $this->assertLessThan($savePosition, $guardPosition);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('IndexedRowsRequest::normalize(', $controller);

        $this->assertStringContainsString('$deployRsaKeyIndexCsrfField', $view);
        $this->assertStringContainsString('$deployRsaKeyIndexCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('method="post"', $view);
    }

    private function validPost(?string $token): array
    {
        $post = [
            'settings' => '1',
            'mysql_server' => [
                'login_ssh' => 'root',
                'password_ssh' => 'secret',
                'key_ssh' => '',
            ],
            'ssh_key_pv' => ['id' => '24'],
            'ssh_key' => ['id' => '42'],
            'link__mysql_server__ssh_key' => [
                ['deploy' => 'on', 'id_mysql_server' => '7'],
                ['id_mysql_server' => '8'],
            ],
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
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
