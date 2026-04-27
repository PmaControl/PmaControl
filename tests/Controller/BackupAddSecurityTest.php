<?php

declare(strict_types=1);

use App\Controller\Backup;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class BackupAddSecurityTest extends TestCase
{
    public function testBackupAddAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'backup.add');

        $outcome = Backup::evaluateAddRequest($this->backupPost($token), $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'backup_main' => [
                'name' => 'Nightly backup',
                'id_mysql_server' => 12,
                'database' => '7,8',
                'id_backup_storage_area' => 3,
                'id_backup_type' => 2,
            ],
            'crontab' => [
                'minute' => '*/15',
                'hour' => '2',
                'day_of_month' => '*',
                'month' => '*',
                'day_of_week' => '1-5',
            ],
        ], $outcome['backup']);
    }

    public function testBackupAddRejectsNonPost(): void
    {
        $outcome = Backup::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['backup']);
    }

    public function testBackupAddRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'backup.add');

        $outcome = Backup::evaluateAddRequest(
            $this->backupPost($token, ['crontab' => ['minute' => "*/15;rm -rf /"]]),
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
        $this->assertNull($outcome['backup']);
    }

    public function testBackupAddRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'backup.settings');
        $server = $this->sameSitePostServer();

        $missingToken = Backup::evaluateAddRequest($this->backupPost(null), $server, $session);
        $foreignScope = Backup::evaluateAddRequest($this->backupPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testBackupAddPayloadRejectsMalformedValues(): void
    {
        foreach ($this->invalidPosts() as $post) {
            $this->assertNull(Backup::normalizeAddPayload($post));
        }
    }

    public function testBackupAddTreatsEmptyDatabaseSelectionAsAllDatabases(): void
    {
        $post = $this->rawPost();
        $post['backup_main']['database'] = [];

        $payload = Backup::normalizeAddPayload($post);

        $this->assertIsArray($payload);
        $this->assertSame('0', $payload['backup_main']['database']);
    }

    public function testBackupAddBuildsRecordsFromNormalizedPayload(): void
    {
        $payload = Backup::normalizeAddPayload($this->rawPost());

        $this->assertIsArray($payload);
        $this->assertSame([
            'crontab' => [
                'minute' => '*/15',
                'hour' => '2',
                'day_of_month' => '*',
                'month' => '*',
                'day_of_week' => '1-5',
                'command' => '',
                'comment' => '',
            ],
        ], Backup::buildAddCrontabRecord($payload));

        $this->assertSame([
            'backup_main' => [
                'name' => 'Nightly backup',
                'id_mysql_server' => 12,
                'database' => '7,8',
                'id_backup_storage_area' => 3,
                'id_backup_type' => 2,
                'id_crontab' => 44,
                'is_active' => 1,
                'date_inserted' => '2026-04-27 12:00:00',
            ],
        ], Backup::buildAddBackupMainRecord($payload, 44, '2026-04-27 12:00:00'));
    }

    public function testExternalPostWouldHaveReachedLegacyTransactionButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'backup.add');
        $post = $this->backupPost($token, [
            'crontab' => ['minute' => "*/15;rm -rf /"],
            'backup_main' => ['id_mysql_server' => '12 OR 1=1'],
        ]);

        $this->assertSame("*/15;rm -rf /", $post['crontab']['minute']);
        $this->assertSame('12 OR 1=1', $post['backup_main']['id_mysql_server']);

        $outcome = Backup::evaluateAddRequest(
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
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['backup']);
    }

    public function testBackupAddUsesSharedSecurityLibraryAndViewSendsTokenBeforeEffects(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Backup.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Backup/add.view.php');
        $request = (string) file_get_contents(__DIR__ . '/../../App/Library/Security/BackupAddRequest.php');

        $addStart = strpos($controller, 'public function add()');
        $evaluateStart = strpos($controller, 'public static function evaluateAddRequest');
        $addBody = substr($controller, $addStart, $evaluateStart - $addStart);

        $this->assertStringContainsString('use App\\Library\\Security\\BackupAddRequest;', $controller);
        $this->assertStringContainsString("private const BACKUP_ADD_CSRF_SCOPE = 'backup.add'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::BACKUP_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('self::evaluateAddRequest($_POST, $_SERVER, $_SESSION)', $addBody);
        $this->assertLessThan(strpos($addBody, "sql_query('SET AUTOCOMMIT=0;')"), strpos($addBody, 'self::evaluateAddRequest('));
        $this->assertLessThan(strpos($addBody, 'Crontab::insert('), strpos($addBody, 'self::evaluateAddRequest('));
        $this->assertStringNotContainsString('$crontab[\'crontab\'] = $_POST[\'crontab\'];', $addBody);
        $this->assertStringNotContainsString('$backup_database[\'backup_main\']               = $_POST[\'backup_main\'];', $addBody);

        $this->assertStringContainsString('$backupAddCsrfField', $view);
        $this->assertStringContainsString('$backupAddCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('method="post"', $view);

        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, $scope)', $request);
        $this->assertStringContainsString('GroupedFormRequest::normalize($post, \'backup_main\', self::backupMainRules())', $request);
        $this->assertStringContainsString('GroupedFormRequest::normalize($post, \'crontab\', self::crontabRules())', $request);
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

    private function backupPost(?string $token, array $overrides = []): array
    {
        $post = $this->rawPost($overrides);
        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function rawPost(array $overrides = []): array
    {
        return array_replace_recursive([
            'backup_main' => [
                'name' => 'Nightly backup',
                'id_mysql_server' => '12',
                'database' => ['7', '8'],
                'id_backup_storage_area' => '3',
                'id_backup_type' => '2',
            ],
            'crontab' => [
                'minute' => '*/15',
                'hour' => '2',
                'day_of_month' => '*',
                'month' => '*',
                'day_of_week' => '1-5',
            ],
        ], $overrides);
    }

    private function invalidPosts(): array
    {
        return [
            [],
            $this->rawPost(['backup_main' => ['name' => 'bad;name']]),
            $this->rawPost(['backup_main' => ['name' => str_repeat('a', 129)]]),
            $this->rawPost(['backup_main' => ['id_mysql_server' => '12 OR 1=1']]),
            $this->rawPost(['backup_main' => ['id_mysql_server' => '0']]),
            $this->rawPost(['backup_main' => ['database' => ['7', '7']]]),
            $this->rawPost(['backup_main' => ['database' => ['7 OR 1=1']]]),
            $this->rawPost(['backup_main' => ['id_backup_storage_area' => 'storage']]),
            $this->rawPost(['backup_main' => ['id_backup_type' => ['2']]]),
            $this->rawPost(['crontab' => ['minute' => "*/15\n* * * *"]]),
            $this->rawPost(['crontab' => ['hour' => '2;touch /tmp/pwned']]),
            $this->rawPost(['crontab' => ['day_of_month' => '1 $HOME']]),
            $this->rawPost(['crontab' => ['month' => '*/very']]),
            $this->rawPost(['crontab' => ['day_of_week' => '1`id`']]),
            $this->rawPost(['crontab' => ['extra' => '*']]),
        ];
    }
}
