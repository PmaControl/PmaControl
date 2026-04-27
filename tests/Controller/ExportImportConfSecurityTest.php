<?php

declare(strict_types=1);

use App\Controller\Export;
use App\Library\Chiffrement;
use App\Library\Security\EncryptedExportRequest;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

if (!defined('CRYPT_KEY')) {
    define('CRYPT_KEY', 'pmacontrol-test-key');
}

final class ExportImportConfSecurityTest extends TestCase
{
    private array $tmpFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tmpFiles as $file) {
            if (is_string($file) && file_exists($file)) {
                unlink($file);
            }
        }

        $this->tmpFiles = [];
    }

    public function testImportPostRejectsNonPost(): void
    {
        $outcome = Export::evaluateImportConfPost([], [], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
    }

    public function testImportPostRejectsMissingToken(): void
    {
        $session = [];

        $outcome = Export::evaluateImportConfPost(
            $this->validFiles('/tmp/export-import.pmactrl'),
            $this->validPost(),
            $this->sameSitePostServer(),
            $session,
            false
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
    }

    public function testImportPostRejectsTokenFromAnotherScope(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.test_dechiffrement');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Export::evaluateImportConfPost(
            $this->validFiles('/tmp/export-import.pmactrl'),
            $post,
            $this->sameSitePostServer(),
            $session,
            false
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
    }

    public function testImportPostRejectsExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.import_conf');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Export::evaluateImportConfPost(
            $this->validFiles('/tmp/export-import.pmactrl'),
            $post,
            [
                'REQUEST_METHOD' => 'POST',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTPS' => 'on',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            false
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testImportPostRejectsMissingUploadAndInvalidPassword(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.import_conf');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $missingUpload = Export::evaluateImportConfPost([], $post, $this->sameSitePostServer(), $session, false);
        $invalidPasswordPost = $post;
        $invalidPasswordPost['export']['password'] = ['secret'];
        $invalidPassword = Export::evaluateImportConfPost(
            $this->validFiles('/tmp/export-import.pmactrl'),
            $invalidPasswordPost,
            $this->sameSitePostServer(),
            $session,
            false
        );

        $this->assertSame(422, $missingUpload['status']);
        $this->assertSame('Invalid export import payload', $missingUpload['body']);
        $this->assertSame(422, $invalidPassword['status']);
        $this->assertSame('Invalid export import payload', $invalidPassword['body']);
    }

    public function testImportPostRejectsUploadErrorAndOversizedFile(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.import_conf');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $uploadError = Export::evaluateImportConfPost(
            $this->validFiles('/tmp/export-import.pmactrl', UPLOAD_ERR_NO_FILE),
            $post,
            $this->sameSitePostServer(),
            $session,
            false
        );
        $oversized = Export::evaluateImportConfPost(
            $this->validFiles('/tmp/export-import.pmactrl', UPLOAD_ERR_OK, 5242881),
            $post,
            $this->sameSitePostServer(),
            $session,
            false
        );

        $this->assertSame(422, $uploadError['status']);
        $this->assertSame('Invalid export import payload', $uploadError['body']);
        $this->assertSame(413, $oversized['status']);
        $this->assertSame('Uploaded export file too large', $oversized['body']);
    }

    public function testImportPostAcceptsValidTokenAndPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.import_conf');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Export::evaluateImportConfPost(
            $this->validFiles('/tmp/export-import.pmactrl'),
            $post,
            $this->sameSitePostServer(),
            $session,
            false
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('/tmp/export-import.pmactrl', $outcome['file']);
        $this->assertSame('secret', $outcome['password']);
    }

    public function testDecryptsRawExportFixtureAndRejectsWrongPassword(): void
    {
        $json = '{"mysql":[{"name":"srv1"}]}';
        $file = $this->writeEncryptedFixture($json, 'secret');

        $this->assertSame($json, EncryptedExportRequest::decryptFile($file, 'secret'));
        $this->assertNull(EncryptedExportRequest::decryptFile($file, 'wrong-password'));
    }

    public function testControllerAndViewUseScopedCsrfTokenAndSingleImport(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Export.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Export/index.view.php');
        $start = strpos($controller, 'public function import_conf($param)');
        $end = strpos($controller, 'public static function evaluateImportConfPost', $start);
        $importConfBody = substr($controller, $start, $end - $start);

        $this->assertStringContainsString('use App\\Library\\Security\\EncryptedExportRequest;', $controller);
        $this->assertStringContainsString('EXPORT_IMPORT_CONF_CSRF_SCOPE', $controller);
        $this->assertStringContainsString('evaluateImportConfPost($_FILES, $_POST, $_SERVER, $_SESSION)', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::EXPORT_IMPORT_CONF_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('EncryptedExportRequest::evaluateUploadPasswordPost(', $controller);
        $this->assertStringContainsString('EncryptedExportRequest::decryptFile($file, $password)', $controller);
        $this->assertSame(1, substr_count($importConfBody, '$this->import(array($json))'));
        $this->assertStringContainsString('name="<?= $exportImportConfCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $exportImportConfCsrfToken ?>"', $view);
    }

    private function validPost(): array
    {
        return [
            'export' => [
                'password' => ' secret ',
            ],
        ];
    }

    private function validFiles(
        string $file,
        int $error = UPLOAD_ERR_OK,
        int $size = 128
    ): array {
        return [
            'export' => [
                'tmp_name' => [
                    'file' => $file,
                ],
                'error' => [
                    'file' => $error,
                ],
                'size' => [
                    'file' => $size,
                ],
            ],
        ];
    }

    private function writeEncryptedFixture(string $json, string $password): string
    {
        $file = tempnam(sys_get_temp_dir(), 'pmactrl_export_');
        $this->tmpFiles[] = $file;
        file_put_contents($file, Chiffrement::encrypt($json, $password));

        return $file;
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
