<?php

declare(strict_types=1);

use App\Controller\Mysqlsys;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

if (!defined('IS_CLI')) {
    define('IS_CLI', true);
}

final class MysqlsysInstallSecurityTest extends TestCase
{
    public function testInstallRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlsys.install');

        $outcome = Mysqlsys::evaluateInstallRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertTrue($outcome['install']);
    }

    public function testInstallRequestRejectsNonPost(): void
    {
        $outcome = Mysqlsys::evaluateInstallRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertFalse($outcome['allowed']);
        $this->assertFalse($outcome['install']);
    }

    public function testInstallRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlsys.install');

        $outcome = Mysqlsys::evaluateInstallRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'install' => ['1'],
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
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertFalse($outcome['allowed']);
        $this->assertFalse($outcome['install']);
    }

    public function testInstallRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'mysqlsys.update_config');
        $server = $this->sameSitePostServer();

        $missingToken = Mysqlsys::evaluateInstallRequest($this->validPost(null), $server, $session);
        $foreignScope = Mysqlsys::evaluateInstallRequest($this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertFalse($missingToken['allowed']);
        $this->assertFalse($missingToken['install']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
        $this->assertFalse($foreignScope['allowed']);
        $this->assertFalse($foreignScope['install']);
    }

    public function testInstallRequestRejectsInvalidPayloadAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'mysqlsys.install');
        $server = $this->sameSitePostServer();

        foreach ($this->invalidPayloads($token) as $post) {
            $outcome = Mysqlsys::evaluateInstallRequest($post, $server, $session);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid MySQL-sys install payload', $outcome['body']);
            $this->assertFalse($outcome['allowed']);
            $this->assertFalse($outcome['install']);
        }
    }

    public function testInstallRequestAllowsCliWithoutCsrfToken(): void
    {
        $outcome = Mysqlsys::evaluateInstallRequest(
            $this->validPost(null),
            ['REQUEST_METHOD' => 'GET'],
            [],
            true
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertTrue($outcome['allowed']);
        $this->assertTrue($outcome['install']);
    }

    public function testInstallMysqlServerIdNormalizesRouteParameter(): void
    {
        $this->assertSame(12, Mysqlsys::normalizeInstallMysqlServerId(['mysql_server' => ['id' => '12']]));
        $this->assertSame(12, Mysqlsys::normalizeInstallMysqlServerId(['mysql_server' => ['id' => ' 12 ']]));
        $this->assertNull(Mysqlsys::normalizeInstallMysqlServerId([]));
        $this->assertNull(Mysqlsys::normalizeInstallMysqlServerId(['mysql_server' => ['id' => '0']]));
        $this->assertNull(Mysqlsys::normalizeInstallMysqlServerId(['mysql_server' => ['id' => '12 OR 1=1']]));
        $this->assertNull(Mysqlsys::normalizeInstallMysqlServerId(['mysql_server' => ['id' => ['12']]]));
    }

    public function testInstallShellCommandsEscapeArguments(): void
    {
        $generate = Mysqlsys::buildGenerateSqlFileCommand("app'; touch /tmp/pwned", '/srv/www/pmacontrol/');
        $expectedUser = "'app\\'; touch /tmp/pwned'@'localhost'";

        $this->assertSame(
            'cd ' . escapeshellarg('/srv/www/pmacontrol/vendor/esysteme/mysql-sys')
                . ' && ./generate_sql_file.sh -v 100 -u ' . escapeshellarg($expectedUser) . ' 2>&1',
            $generate
        );

        $install = Mysqlsys::buildMysqlSysInstallCommand(
            '10.0.0.1; reboot',
            "root'; id",
            3306,
            "pa'ss; cat /etc/passwd",
            '/tmp/mysql sys/install.sql'
        );

        $this->assertSame(
            'mysql -h ' . escapeshellarg('10.0.0.1; reboot')
                . ' -u ' . escapeshellarg("root'; id")
                . ' -P 3306'
                . ' -p' . escapeshellarg("pa'ss; cat /etc/passwd")
                . ' < ' . escapeshellarg('/tmp/mysql sys/install.sql') . ' 2>&1',
            $install
        );
    }

    public function testGeneratedSqlFileNameIsExtractedFromScriptOutput(): void
    {
        $this->assertSame(
            '/tmp/mysql-sys/gen/sys_100.sql',
            Mysqlsys::extractGeneratedSqlFileName("ignored\nWrote file: /tmp/mysql-sys/gen/sys_100.sql\n")
        );
        $this->assertSame('', Mysqlsys::extractGeneratedSqlFileName('no generated file'));
    }

    public function testInstallUsesSharedCsrfGuardBeforeSideEffectsAndViewCarriesToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Mysqlsys.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Mysqlsys/install.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $installPosition = strpos($controller, 'public function install');
        $evaluatePosition = strpos(
            $controller,
            '$outcome = self::evaluateInstallRequest($_POST, $_SERVER, $_SESSION, IS_CLI);',
            $installPosition
        );
        $generatePosition = strpos($controller, '$cmd = self::buildGenerateSqlFileCommand', $installPosition);
        $cryptPosition = strpos($controller, 'Crypt::$key = CRYPT_KEY;', $installPosition);

        $this->assertStringContainsString("private const MYSQLSYS_INSTALL_CSRF_SCOPE = 'mysqlsys.install'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::MYSQLSYS_INSTALL_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::MYSQLSYS_INSTALL_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('evaluateInstallRequest($_POST, $_SERVER, $_SESSION, IS_CLI)', $controller);
        $this->assertStringContainsString('$sql = "SELECT * FROM mysql_server where id=" . $idMysqlServer;', $controller);
        $this->assertStringContainsString('$idMysqlServer, __METHOD__', $controller);
        $this->assertIsInt($installPosition);
        $this->assertIsInt($evaluatePosition);
        $this->assertIsInt($generatePosition);
        $this->assertIsInt($cryptPosition);
        $this->assertLessThan($generatePosition, $evaluatePosition);
        $this->assertLessThan($cryptPosition, $evaluatePosition);

        $this->assertStringContainsString('$mysqlsysInstallCsrfField', $view);
        $this->assertStringContainsString('$mysqlsysInstallCsrfToken', $view);
        $this->assertStringContainsString('echo \'<input type="hidden" name="', $view);
        $this->assertStringContainsString('htmlspecialchars((string)($data[\'file_name\'] ?? \'\')', $view);
        $this->assertStringContainsString('nl2br(htmlspecialchars((string)base64_decode($_GET[\'error_msg\'])', $view);
    }

    private function validPost(?string $token): array
    {
        $post = ['install' => '1'];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function invalidPayloads(string $token): array
    {
        return [
            [Csrf::DEFAULT_FIELD => $token],
            [Csrf::DEFAULT_FIELD => $token, 'install' => '0'],
            [Csrf::DEFAULT_FIELD => $token, 'install' => 'yes'],
            [Csrf::DEFAULT_FIELD => $token, 'install' => ['1']],
        ];
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
