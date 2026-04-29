<?php

declare(strict_types=1);

use App\Controller\Cleaner;
use App\Library\Security\CsrfGuard;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class CleanerAddSecurityTest extends TestCase
{
    public function testAddRequestAcceptsValidPostToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.add');

        $outcome = Cleaner::evaluateAddRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertArrayNotHasKey('allowed', $outcome);
        $this->assertSame(
            [
                'id' => 0,
                'libelle' => 'Night cleaner',
                'id_mysql_server' => 7,
                'database' => 'shop',
                'main_table' => 'orders',
                'query' => 'created_at < NOW() - INTERVAL 14 DAY',
                'limit' => 1000,
                'wait_time_in_sec' => 10,
                'cleaner_db' => 'cleaner_tmp',
                'prefix' => 'DEL_',
                'id_backup_storage_area' => 3,
                'is_crypted' => 'on',
            ],
            $outcome['cleaner_main']
        );
    }

    public function testAddRequestAcceptsUpdateIdAndUncheckedArchive(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.add');
        $post = $this->validPost($token, ['id' => '9']);
        unset($post['cleaner_main']['is_crypted']);

        $outcome = Cleaner::evaluateAddRequest($post, $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(9, $outcome['cleaner_main']['id']);
        $this->assertSame('0', $outcome['cleaner_main']['is_crypted']);
    }

    public function testAddRequestRejectsNonPost(): void
    {
        $outcome = Cleaner::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertArrayNotHasKey('allowed', $outcome);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['cleaner_main']);
    }

    public function testAddRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.add');

        $outcome = Cleaner::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'cleaner_main' => ['id_mysql_server' => ['invalid']]],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertArrayNotHasKey('allowed', $outcome);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['cleaner_main']);
    }

    public function testAddRequestRejectsMissingOriginAndReferer(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.add');

        $outcome = Cleaner::evaluateAddRequest(
            $this->validPost($token),
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['cleaner_main']);
    }

    public function testAddRequestRejectsMissingForeignOrCrossSessionToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'cleaner.settings');
        $validToken = Csrf::issueToken($session, 'cleaner.add');
        $server = $this->sameSitePostServer();

        $missingToken = Cleaner::evaluateAddRequest($this->validPost(null), $server, $session);
        $foreignScope = Cleaner::evaluateAddRequest($this->validPost($foreignToken), $server, $session);
        $crossSession = Cleaner::evaluateAddRequest($this->validPost($validToken), $server, []);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertSame(403, $crossSession['status']);
        $this->assertSame('Invalid CSRF token', $crossSession['body']);
    }

    public function testAddRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.add');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidPayloads($token) as $post) {
            $outcome = Cleaner::evaluateAddRequest($post, $server, $session);

            $this->assertSame(400, $outcome['status']);
            $this->assertArrayNotHasKey('allowed', $outcome);
            $this->assertSame('Invalid cleaner add payload', $outcome['body']);
            $this->assertNull($outcome['cleaner_main']);
        }
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSqlSave(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.add');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(CsrfGuard::isPost($server));
        $this->assertSame('Night cleaner', Cleaner::normalizeAddPayload($this->validPost($token))['libelle']);

        $outcome = Cleaner::evaluateAddRequest($this->validPost($token), $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['cleaner_main']);
    }

    public function testAddUsesSharedCsrfGuardBeforeSideEffectsAndViewSendsToken(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Cleaner.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Cleaner/add.view.php');

        $addPosition = strpos($controller, 'public function add($param)');
        $evaluatePosition = strpos($controller, '$outcome = self::evaluateAddRequest($_POST, $_SERVER, $_SESSION);', $addPosition);
        $savePosition = strpos($controller, '$id_cleaner_main = $db->sql_save($cleaner_main);', $addPosition);
        $restartPosition = strpos($controller, '$this->restart(array($cleaner_main[', $addPosition);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\GroupedFormRequest;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString("private const CLEANER_ADD_CSRF_SCOPE = 'cleaner.add'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::CLEANER_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('GroupedFormRequest::evaluate(', $controller);
        $this->assertStringContainsString('CsrfGuard::isPost($_SERVER)', $controller);
        $this->assertIsInt($addPosition);
        $this->assertIsInt($evaluatePosition);
        $this->assertIsInt($savePosition);
        $this->assertIsInt($restartPosition);
        $this->assertLessThan($savePosition, $evaluatePosition);
        $this->assertLessThan($restartPosition, $evaluatePosition);

        $this->assertStringContainsString('$cleanerAddCsrfField', $view);
        $this->assertStringContainsString('$cleanerAddCsrfToken', $view);
        $this->assertStringContainsString('name="<?= $cleanerAddCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $cleanerAddCsrfToken ?>"', $view);
        $this->assertStringContainsString('method="post"', $view);
    }

    private function validPost(?string $token, array $overrides = []): array
    {
        $post = [
            'cleaner_main' => array_replace([
                'id' => '',
                'libelle' => ' Night cleaner ',
                'id_mysql_server' => '7',
                'database' => ' shop ',
                'main_table' => ' orders ',
                'query' => ' created_at < NOW() - INTERVAL 14 DAY ',
                'limit' => '1000',
                'wait_time_in_sec' => '10',
                'cleaner_db' => ' cleaner_tmp ',
                'prefix' => ' DEL_ ',
                'id_backup_storage_area' => '3',
                'is_crypted' => 'on',
            ], $overrides),
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function invalidPayloads(string $token): array
    {
        $base = $this->validPost($token);

        return [
            [Csrf::DEFAULT_FIELD => $token],
            [Csrf::DEFAULT_FIELD => $token, 'cleaner_main' => 'invalid'],
            $this->withMainField($base, 'unexpected', '1'),
            $this->withMainField($base, 'id', 'edit'),
            $this->withMainField($base, 'libelle', ''),
            $this->withMainField($base, 'id_mysql_server', '0'),
            $this->withMainField($base, 'database', ''),
            $this->withMainField($base, 'main_table', str_repeat('a', 65)),
            $this->withMainField($base, 'query', ['invalid']),
            $this->withMainField($base, 'limit', '0'),
            $this->withMainField($base, 'wait_time_in_sec', '101'),
            $this->withMainField($base, 'cleaner_db', ''),
            $this->withMainField($base, 'prefix', str_repeat('a', 51)),
            $this->withMainField($base, 'id_backup_storage_area', 'storage'),
            $this->withMainField($base, 'is_crypted', 'yes'),
        ];
    }

    private function withMainField(array $post, string $field, $value): array
    {
        $post['cleaner_main'][$field] = $value;
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
