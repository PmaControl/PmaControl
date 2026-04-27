<?php

declare(strict_types=1);

use App\Controller\Database;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class DatabaseRenameSecurityTest extends TestCase
{
    public function testRenameRejectsMissingToken(): void
    {
        $session = [];

        $outcome = Database::evaluateRenameRequest($this->validPost(), $this->sameSitePostServer(), $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
        $this->assertNull($outcome['payload']);
    }

    public function testRenameRejectsExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.rename');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Database::evaluateRenameRequest($post, [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ], $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testRenameAcceptsValidTokenAndNormalizesPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.rename');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Database::evaluateRenameRequest($post, $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'id_mysql_server' => 7,
            'database' => 'old_db',
            'new_name' => 'new_db',
            'adjust_privileges' => '1',
        ], $outcome['payload']);
    }

    public function testRenameDefaultsUncheckedAdjustPrivileges(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.rename');
        $post = $this->validPost();
        unset($post['rename']['adjust_privileges']);
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = Database::evaluateRenameRequest($post, $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame('', $outcome['payload']['adjust_privileges']);
    }

    public function testRenameRejectsInvalidPayloads(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'database.rename');

        foreach ($this->invalidPosts($token) as $post) {
            $outcome = Database::evaluateRenameRequest($post, $this->sameSitePostServer(), $session);
            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid database rename payload', $outcome['body']);
            $this->assertNull($outcome['payload']);
        }
    }

    public function testRenameControllerAndViewUseCsrfGuard(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Database.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Database/rename.view.php');
        $renameStart = strpos($controller, 'public function rename($param)');
        $moveStart = strpos($controller, 'public function move($param)');

        $this->assertNotFalse($renameStart);
        $this->assertNotFalse($moveStart);
        $renameBody = substr($controller, $renameStart, $moveStart - $renameStart);

        $this->assertStringContainsString("private const DATABASE_RENAME_CSRF_SCOPE = 'database.rename'", $controller);
        $this->assertStringContainsString('GroupedFormRequest::evaluate(', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::DATABASE_RENAME_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('evaluateRenameRequest($_POST, $_SERVER, $_SESSION)', $renameBody);
        $this->assertStringContainsString('$renameRequest[\'payload\']', $renameBody);
        $this->assertStringNotContainsString('$_POST[\'rename\'][\'new_name\']', $renameBody);
        $this->assertStringNotContainsString('$_POST[\'rename\'][\'database\']', $renameBody);
        $this->assertStringNotContainsString('$_POST[\'rename\'][\'id_mysql_server\']', $renameBody);
        $this->assertStringContainsString('name="<?= $databaseRenameCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $databaseRenameCsrfToken ?>"', $view);
        $this->assertStringContainsString('method="POST"', $view);
    }

    private function validPost(): array
    {
        return [
            'database' => ['rename' => '1'],
            'rename' => [
                'id_mysql_server' => '7',
                'database' => ' old_db ',
                'new_name' => ' new_db ',
                'adjust_privileges' => 'on',
            ],
        ];
    }

    private function invalidPosts(string $token): array
    {
        $base = $this->validPost();
        $posts = [];

        $missingGroup = [Csrf::DEFAULT_FIELD => $token];
        $invalidServer = $base;
        $invalidServer['rename']['id_mysql_server'] = '0';
        $nonNumericServer = $base;
        $nonNumericServer['rename']['id_mysql_server'] = '7 OR 1=1';
        $emptyDatabase = $base;
        $emptyDatabase['rename']['database'] = ' ';
        $backtickDatabase = $base;
        $backtickDatabase['rename']['database'] = 'old`db';
        $quoteDatabase = $base;
        $quoteDatabase['rename']['database'] = "old'db";
        $backslashDatabase = $base;
        $backslashDatabase['rename']['database'] = 'old\\db';
        $dollarDatabase = $base;
        $dollarDatabase['rename']['database'] = 'old$db';
        $nulTarget = $base;
        $nulTarget['rename']['new_name'] = "new\0db";
        $longTarget = $base;
        $longTarget['rename']['new_name'] = str_repeat('a', 65);
        $arrayTarget = $base;
        $arrayTarget['rename']['new_name'] = ['new_db'];

        foreach (
            [
                $missingGroup,
                $invalidServer,
                $nonNumericServer,
                $emptyDatabase,
                $backtickDatabase,
                $quoteDatabase,
                $backslashDatabase,
                $dollarDatabase,
                $nulTarget,
                $longTarget,
                $arrayTarget,
            ] as $post
        ) {
            $post[Csrf::DEFAULT_FIELD] = $token;
            $posts[] = $post;
        }

        return $posts;
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
