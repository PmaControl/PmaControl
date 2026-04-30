<?php

declare(strict_types=1);

use App\Controller\ForeignKey;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ForeignKeyAddSecurityTest extends TestCase
{
    public function testAddPostRejectsMissingToken(): void
    {
        $session = [];

        $outcome = ForeignKey::evaluateAddPost($this->validPost(), $this->sameSitePostServer(), $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
        $this->assertSame([], $outcome['payload']);
    }

    public function testAddPostAcceptsValidTokenAndNormalizesPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'foreign_key.add');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = ForeignKey::evaluateAddPost($post, $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'id_mysql_server' => 7,
            'database_name' => 'customer_db',
            'prefix' => 'old_',
        ], $outcome['payload']);
    }

    public function testAddPostRejectsTokenFromAnotherScope(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'format.index');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = ForeignKey::evaluateAddPost($post, $this->sameSitePostServer(), $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid CSRF token', $outcome['body']);
    }

    public function testAddPostRejectsExternalOrigin(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'foreign_key.add');
        $post = $this->validPost();
        $post[Csrf::DEFAULT_FIELD] = $token;

        $outcome = ForeignKey::evaluateAddPost($post, [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ], $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testAddPostRejectsInvalidPayloads(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'foreign_key.add');

        foreach ($this->invalidPosts($token) as $post) {
            $outcome = ForeignKey::evaluateAddPost($post, $this->sameSitePostServer(), $session);
            $this->assertSame(422, $outcome['status']);
            $this->assertSame('Invalid foreign-key prefix payload', $outcome['body']);
        }
    }

    public function testNormalizeAddPayloadRejectsExtraTopLevelFields(): void
    {
        $post = $this->validPost();
        $post['_csrf_token'] = 'token';
        $post['unexpected'] = ['value' => 'ignored'];

        $this->assertSame([
            'id_mysql_server' => 7,
            'database_name' => 'customer_db',
            'prefix' => 'old_',
        ], ForeignKey::normalizeAddPayload($post));
    }

    public function testAddUsesSharedCsrfGuardAndViewCarriesToken(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/ForeignKey.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/ForeignKey/add.view.php');

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::FOREIGN_KEY_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::FOREIGN_KEY_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString("\$db->sql_save(['foreign_key_remove_prefix' => \$addPost['payload']]);", $controller);
        $this->assertStringNotContainsString('$db->sql_save($_POST)', $controller);
        $this->assertStringContainsString('name="<?= $foreignKeyAddCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $foreignKeyAddCsrfToken ?>"', $view);
    }

    private function validPost(): array
    {
        return [
            'foreign_key_remove_prefix' => [
                'id_mysql_server' => '7',
                'database_name' => ' customer_db ',
                'prefix' => ' old_ ',
            ],
        ];
    }

    private function invalidPosts(string $token): array
    {
        $base = $this->validPost();
        $posts = [];

        $missingTable = [Csrf::DEFAULT_FIELD => $token];
        $invalidServer = $base;
        $invalidServer['foreign_key_remove_prefix']['id_mysql_server'] = '0';
        $emptyDatabase = $base;
        $emptyDatabase['foreign_key_remove_prefix']['database_name'] = ' ';
        $emptyPrefix = $base;
        $emptyPrefix['foreign_key_remove_prefix']['prefix'] = '';
        $longDatabase = $base;
        $longDatabase['foreign_key_remove_prefix']['database_name'] = str_repeat('a', 65);
        $arrayPrefix = $base;
        $arrayPrefix['foreign_key_remove_prefix']['prefix'] = ['old_'];

        foreach ([$missingTable, $invalidServer, $emptyDatabase, $emptyPrefix, $longDatabase, $arrayPrefix] as $post) {
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
