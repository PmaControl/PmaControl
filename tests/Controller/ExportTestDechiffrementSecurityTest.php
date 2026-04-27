<?php

declare(strict_types=1);

use App\Controller\Export;
use App\Library\Chiffrement;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

if (!defined('CRYPT_KEY')) {
    define('CRYPT_KEY', 'pmacontrol-test-key');
}

final class ExportTestDechiffrementSecurityTest extends TestCase
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

    public function testRejectsMissingToken(): void
    {
        $session = [];

        $outcome = Export::evaluateTestDechiffrementPost(
            $this->validFiles('/tmp/export-test.pmactrl'),
            $this->validPost(),
            $this->sameSitePostServer(),
            $session,
            false
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
    }

    public function testRejectsTokenFromAnotherScope(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.import_conf');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Export::evaluateTestDechiffrementPost(
            $this->validFiles('/tmp/export-test.pmactrl'),
            $post,
            $this->sameSitePostServer(),
            $session,
            false
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
    }

    public function testRejectsExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.test_dechiffrement');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Export::evaluateTestDechiffrementPost(
            $this->validFiles('/tmp/export-test.pmactrl'),
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

    public function testRejectsMissingUploadAndInvalidPassword(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.test_dechiffrement');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $missingUpload = Export::evaluateTestDechiffrementPost([], $post, $this->sameSitePostServer(), $session, false);
        $invalidPasswordPost = $post;
        $invalidPasswordPost['export']['password'] = ['secret'];
        $invalidPassword = Export::evaluateTestDechiffrementPost(
            $this->validFiles('/tmp/export-test.pmactrl'),
            $invalidPasswordPost,
            $this->sameSitePostServer(),
            $session,
            false
        );

        $this->assertSame(422, $missingUpload['status']);
        $this->assertSame('Invalid export test payload', $missingUpload['body']);
        $this->assertSame(422, $invalidPassword['status']);
        $this->assertSame('Invalid export test payload', $invalidPassword['body']);
    }

    public function testRejectsUploadErrorAndOversizedFile(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.test_dechiffrement');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $uploadError = Export::evaluateTestDechiffrementPost(
            $this->validFiles('/tmp/export-test.pmactrl', UPLOAD_ERR_NO_FILE),
            $post,
            $this->sameSitePostServer(),
            $session,
            false
        );
        $oversized = Export::evaluateTestDechiffrementPost(
            $this->validFiles('/tmp/export-test.pmactrl', UPLOAD_ERR_OK, 5242881),
            $post,
            $this->sameSitePostServer(),
            $session,
            false
        );

        $this->assertSame(422, $uploadError['status']);
        $this->assertSame('Invalid export test payload', $uploadError['body']);
        $this->assertSame(413, $oversized['status']);
        $this->assertSame('Uploaded export file too large', $oversized['body']);
    }

    public function testAcceptsValidTokenAndPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'export.test_dechiffrement');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Export::evaluateTestDechiffrementPost(
            $this->validFiles('/tmp/export-test.pmactrl'),
            $post,
            $this->sameSitePostServer(),
            $session,
            false
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('/tmp/export-test.pmactrl', $outcome['file']);
        $this->assertSame('secret', $outcome['password']);
    }

    public function testDecryptsValidFixtureAndRejectsWrongPassword(): void
    {
        $json = '{"mysql":{"updated":["srv1"]}}';
        $file = $this->writeEncryptedFixture($json, 'secret');

        $this->assertSame($json, Export::decryptTestDechiffrementFile($file, 'secret'));
        $this->assertNull(Export::decryptTestDechiffrementFile($file, 'wrong-password'));
    }

    public function testControllerAndViewUseScopedCsrfToken(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Export.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Export/test_dechiffrement.view.php');

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString('EXPORT_TEST_DECHIFFREMENT_CSRF_SCOPE', $controller);
        $this->assertStringContainsString('evaluateTestDechiffrementPost($_FILES, $_POST, $_SERVER, $_SESSION)', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::EXPORT_TEST_DECHIFFREMENT_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('name="<?= $exportTestDechiffrementCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $exportTestDechiffrementCsrfToken ?>"', $view);
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
        file_put_contents($file, Chiffrement::encrypt(gzcompress($json), $password));

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
