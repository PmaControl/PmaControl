<?php

declare(strict_types=1);

use App\Controller\Cleaner;
use App\Library\Security\CsrfGuard;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class CleanerSettingsSecurityTest extends TestCase
{
    public function testSettingsRequestAcceptsValidPostToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.settings');

        $outcome = Cleaner::evaluateSettingsRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertArrayNotHasKey('allowed', $outcome);
        $this->assertSame(
            [
                'libelle' => 'Night cleaner',
                'id_mysql_server' => 7,
                'id_mysql_database' => 12,
                'main_table' => 'orders',
                'query' => 'created_at < NOW() - INTERVAL 14 DAY',
                'wait_time_in_sec' => 10,
                'cleaner_db' => 'cleaner_tmp',
                'prefix' => 'DEL_',
            ],
            $outcome['cleaner_main']
        );
        $this->assertSame(
            [
                [
                    'constraint_schema' => 'shop',
                    'constraint_table' => 'orders',
                    'constraint_column' => 'customer_id',
                    'referenced_schema' => 'shop',
                    'referenced_table' => 'customers',
                    'referenced_column' => 'id',
                ],
            ],
            $outcome['cleaner_foreign_key']
        );
    }

    public function testSettingsRequestRejectsNonPost(): void
    {
        $outcome = Cleaner::evaluateSettingsRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertArrayNotHasKey('allowed', $outcome);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['cleaner_main']);
        $this->assertNull($outcome['cleaner_foreign_key']);
    }

    public function testSettingsRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.settings');

        $outcome = Cleaner::evaluateSettingsRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'cleaner_main' => ['id_mysql_server' => ['invalid']],
            ],
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
        $this->assertNull($outcome['cleaner_foreign_key']);
    }

    public function testSettingsRequestRejectsMissingOriginAndReferer(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.settings');

        $outcome = Cleaner::evaluateSettingsRequest(
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
        $this->assertNull($outcome['cleaner_foreign_key']);
    }

    public function testSettingsRequestRejectsMissingForeignOrCrossSessionToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'cleaner.add');
        $validToken = Csrf::issueToken($session, 'cleaner.settings');
        $server = $this->sameSitePostServer();

        $missingToken = Cleaner::evaluateSettingsRequest($this->validPost(null), $server, $session);
        $foreignScope = Cleaner::evaluateSettingsRequest($this->validPost($foreignToken), $server, $session);
        $crossSession = Cleaner::evaluateSettingsRequest($this->validPost($validToken), $server, []);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertSame(403, $crossSession['status']);
        $this->assertSame('Invalid CSRF token', $crossSession['body']);
    }

    public function testSettingsRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.settings');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidPayloads($token) as $post) {
            $outcome = Cleaner::evaluateSettingsRequest($post, $server, $session);

            $this->assertSame(400, $outcome['status']);
            $this->assertArrayNotHasKey('allowed', $outcome);
            $this->assertSame('Invalid cleaner settings payload', $outcome['body']);
            $this->assertNull($outcome['cleaner_main']);
            $this->assertNull($outcome['cleaner_foreign_key']);
        }
    }

    public function testExternalPostWouldPassLegacyPostGateButIsRejectedBeforeSqlSave(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cleaner.settings');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(CsrfGuard::isPost($server));

        $outcome = Cleaner::evaluateSettingsRequest($this->validPost($token), $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['cleaner_main']);
        $this->assertNull($outcome['cleaner_foreign_key']);
    }

    public function testSettingsUsesSharedCsrfGuardBeforeSideEffectsAndViewSendsToken(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Cleaner.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Cleaner/settings.view.php');

        $settingsPosition = strpos($controller, 'public function settings($param)');
        $evaluatePosition = strpos($controller, '$outcome = self::evaluateSettingsRequest($_POST, $_SERVER, $_SESSION);', $settingsPosition);
        $saveMainPosition = strpos($controller, 'sql_save($cleaner_main)', $settingsPosition);
        $saveForeignKeyPosition = strpos($controller, 'sql_save($ob_foreign_key)', $settingsPosition);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\GroupedFormRequest;', $controller);
        $this->assertStringContainsString('use App\\Library\\Security\\IndexedRowsRequest;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString("private const CLEANER_SETTINGS_CSRF_SCOPE = 'cleaner.settings'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::CLEANER_SETTINGS_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::CLEANER_SETTINGS_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('GroupedFormRequest::normalize(', $controller);
        $this->assertStringContainsString('IndexedRowsRequest::normalize(', $controller);
        $this->assertIsInt($settingsPosition);
        $this->assertIsInt($evaluatePosition);
        $this->assertIsInt($saveMainPosition);
        $this->assertIsInt($saveForeignKeyPosition);
        $this->assertLessThan($saveMainPosition, $evaluatePosition);
        $this->assertLessThan($saveForeignKeyPosition, $evaluatePosition);

        $this->assertStringContainsString('$cleanerSettingsCsrfField', $view);
        $this->assertStringContainsString('$cleanerSettingsCsrfToken', $view);
        $this->assertStringContainsString('name="<?= $cleanerSettingsCsrfField ?>"', $view);
        $this->assertStringContainsString('value="<?= $cleanerSettingsCsrfToken ?>"', $view);
        $this->assertStringContainsString('method="post"', $view);
    }

    private function validPost(?string $token): array
    {
        $post = [
            'cleaner_main' => [
                'libelle' => ' Night cleaner ',
                'id_mysql_server' => '7',
                'id_mysql_database' => '12',
                'main_table' => ' orders ',
                'query' => ' created_at < NOW() - INTERVAL 14 DAY ',
                'wait_time_in_sec' => '10',
                'cleaner_db' => ' cleaner_tmp ',
                'prefix' => ' DEL_ ',
            ],
            'cleaner_foreign_key' => [
                1 => [
                    'constraint_schema' => ' shop ',
                    'constraint_table' => ' orders ',
                    'constraint_column' => ' customer_id ',
                    'referenced_schema' => ' shop ',
                    'referenced_table' => ' customers ',
                    'referenced_column' => ' id ',
                ],
            ],
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function invalidPayloads(string $token): array
    {
        $base = $this->validPost($token);

        $withoutDatabase = $base;
        unset($withoutDatabase['cleaner_main']['id_mysql_database'], $withoutDatabase['cleaner_main']['database']);

        return [
            [Csrf::DEFAULT_FIELD => $token],
            [Csrf::DEFAULT_FIELD => $token, 'cleaner_main' => 'invalid'],
            $this->withMainField($base, 'libelle', ''),
            $this->withMainField($base, 'id_mysql_server', '0'),
            $this->withMainField($base, 'id_mysql_server', 'db'),
            $this->withMainField($base, 'id_mysql_database', '0'),
            $this->withMainField($base, 'main_table', ''),
            $this->withMainField($base, 'query', ['invalid']),
            $this->withMainField($base, 'wait_time_in_sec', '101'),
            $this->withMainField($base, 'cleaner_db', ''),
            $this->withMainField($base, 'prefix', str_repeat('a', 51)),
            $withoutDatabase,
            [Csrf::DEFAULT_FIELD => $token, 'cleaner_main' => $base['cleaner_main'], 'cleaner_foreign_key' => []],
            $this->withForeignKeyField($base, 1, 'constraint_schema', ''),
            $this->withForeignKeyField($base, 1, 'referenced_column', ['id']),
        ];
    }

    private function withMainField(array $post, string $field, $value): array
    {
        $post['cleaner_main'][$field] = $value;
        return $post;
    }

    private function withForeignKeyField(array $post, int $index, string $field, $value): array
    {
        $post['cleaner_foreign_key'][$index][$field] = $value;
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
