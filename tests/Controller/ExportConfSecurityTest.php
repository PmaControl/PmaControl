<?php

declare(strict_types=1);

use App\Controller\Export;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ExportConfSecurityTest extends TestCase
{
    public function testExportPostRejectsNonPost(): void
    {
        $outcome = Export::evaluateExportConfPost([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
    }

    public function testExportPostRejectsMissingToken(): void
    {
        $session = [];

        $outcome = Export::evaluateExportConfPost($this->validPost(), $this->sameSitePostServer(), $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
        $this->assertSame('', $outcome['password']);
    }

    public function testExportPostRejectsTokenFromAnotherScope(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.import_conf');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Export::evaluateExportConfPost($post, $this->sameSitePostServer(), $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
    }

    public function testExportPostRejectsExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.export_conf');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Export::evaluateExportConfPost($post, [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ], $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testExportPostRejectsInvalidPasswordPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.export_conf');

        foreach ($this->invalidPasswordPosts($token) as $post) {
            $outcome = Export::evaluateExportConfPost($post, $this->sameSitePostServer(), $session);

            $this->assertSame(422, $outcome['status']);
            $this->assertSame('Invalid export password payload', $outcome['body']);
            $this->assertSame('', $outcome['password']);
        }
    }

    public function testExportPostRejectsPasswordMismatch(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.export_conf');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;
        $post['export']['password2'] = 'different';

        $outcome = Export::evaluateExportConfPost($post, $this->sameSitePostServer(), $session);

        $this->assertSame(422, $outcome['status']);
        $this->assertSame('Export passwords do not match', $outcome['body']);
    }

    public function testExportPostAcceptsValidPasswordPair(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.export_conf');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Export::evaluateExportConfPost($post, $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('secret', $outcome['password']);
    }

    public function testControllerAndViewUseScopedCsrfBeforeExportAndUniqueTempFile(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Export.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Export/index.view.php');
        $start = strpos($controller, 'public function export_conf()');
        $end = strpos($controller, 'public static function evaluateExportConfPost', $start);
        $exportBody = substr($controller, $start, $end - $start);

        $evaluatePosition = strpos($exportBody, 'self::evaluateExportConfPost($_POST, $_SERVER, $_SESSION)');
        $exportPosition = strpos($exportBody, '$backup = $this->_export();');

        $this->assertStringContainsString('EXPORT_CONF_CSRF_SCOPE', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::EXPORT_CONF_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('EncryptedExportRequest::evaluatePasswordPairPost(', $controller);
        $this->assertStringContainsString("\$crypted = Chiffrement::encrypt(\$json, \$exportPost['password']);", $controller);
        $this->assertIsInt($evaluatePosition);
        $this->assertIsInt($exportPosition);
        $this->assertLessThan($exportPosition, $evaluatePosition);
        $this->assertStringContainsString("tempnam(sys_get_temp_dir(), 'pmactrl_export_')", $exportBody);
        $this->assertStringNotContainsString('file_put_contents("/tmp/export"', $exportBody);
        $this->assertStringContainsString('name="<?= $exportConfCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $exportConfCsrfToken ?>"', $view);
    }

    private function validPost(): array
    {
        return [
            'export' => [
                'password' => ' secret ',
                'password2' => ' secret ',
            ],
        ];
    }

    private function invalidPasswordPosts(string $token): array
    {
        return [
            [Csrf::DEFAULT_FIELD => $token],
            [Csrf::DEFAULT_FIELD => $token, 'export' => ['password' => ['secret'], 'password2' => 'secret']],
            [Csrf::DEFAULT_FIELD => $token, 'export' => ['password' => 'secret', 'password2' => ['secret']]],
            [Csrf::DEFAULT_FIELD => $token, 'export' => ['password' => '', 'password2' => 'secret']],
            [Csrf::DEFAULT_FIELD => $token, 'export' => ['password' => 'secret', 'password2' => '']],
            [
                Csrf::DEFAULT_FIELD => $token,
                'export' => [
                    'password' => str_repeat('a', 4097),
                    'password2' => str_repeat('a', 4097),
                ],
            ],
        ];
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }
}
