<?php

declare(strict_types=1);

use App\Controller\Crontab;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class CrontabAdminSecurityTest extends TestCase
{
    public function testAdminCrontabAddAcceptsValidTokenAndNormalizesPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'crontab.admin_crontab.add');
        $post = $this->validAddPost($token);

        $outcome = Crontab::evaluateAdminCrontabAddRequest($post, $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'minute' => '*/5',
            'hour' => '*',
            'dayofmonth' => '*',
            'month' => '*',
            'dayofweek' => '*',
            'command' => '/usr/bin/php /srv/www/pmacontrol/index.php worker run',
            'line' => '*/5 * * * * /usr/bin/php /srv/www/pmacontrol/index.php worker run',
        ], $outcome['payload']);
    }

    public function testAdminCrontabDeleteAcceptsValidTokenAndNormalizesPayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'crontab.admin_crontab.delete');

        $outcome = Crontab::evaluateAdminCrontabDeleteRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'crontab' => ['delete' => '7'],
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['delete' => 7], $outcome['payload']);
    }

    public function testAdminCrontabAddRejectsNonPostMethod(): void
    {
        $outcome = Crontab::evaluateAdminCrontabAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['payload']);
    }

    public function testAdminCrontabRejectsExternalOriginBeforeTokenPayload(): void
    {
        $session = [];
        $addToken = Csrf::issueToken($session, 'crontab.admin_crontab.add');
        $deleteToken = Csrf::issueToken($session, 'crontab.admin_crontab.delete');
        $externalServer = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $addOutcome = Crontab::evaluateAdminCrontabAddRequest($this->validAddPost($addToken), $externalServer, $session);
        $deleteOutcome = Crontab::evaluateAdminCrontabDeleteRequest(
            [Csrf::DEFAULT_FIELD => $deleteToken, 'crontab' => ['delete' => '1']],
            $externalServer,
            $session
        );

        $this->assertSame(403, $addOutcome['status']);
        $this->assertSame('Invalid request origin', $addOutcome['body']);
        $this->assertSame(403, $deleteOutcome['status']);
        $this->assertSame('Invalid request origin', $deleteOutcome['body']);
    }

    public function testAdminCrontabRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $deleteToken = Csrf::issueToken($session, 'crontab.admin_crontab.delete');
        $addToken = Csrf::issueToken($session, 'crontab.admin_crontab.add');
        $server = $this->sameSitePostServer();

        $missingToken = Crontab::evaluateAdminCrontabAddRequest($this->validAddPost(null), $server, $session);
        $addWithDeleteToken = Crontab::evaluateAdminCrontabAddRequest($this->validAddPost($deleteToken), $server, $session);
        $deleteWithAddToken = Crontab::evaluateAdminCrontabDeleteRequest(
            [Csrf::DEFAULT_FIELD => $addToken, 'crontab' => ['delete' => '1']],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $addWithDeleteToken['status']);
        $this->assertSame('Invalid CSRF token', $addWithDeleteToken['body']);
        $this->assertSame(403, $deleteWithAddToken['status']);
        $this->assertSame('Invalid CSRF token', $deleteWithAddToken['body']);
    }

    public function testAdminCrontabRejectsDangerousAddPayloads(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'crontab.admin_crontab.add');

        foreach ($this->invalidAddPosts($token) as $post) {
            $outcome = Crontab::evaluateAdminCrontabAddRequest($post, $this->sameSitePostServer(), $session);

            $this->assertSame(400, $outcome['status']);
            $this->assertNull($outcome['payload']);
        }
    }

    public function testAdminCrontabRejectsDangerousDeletePayloads(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'crontab.admin_crontab.delete');

        foreach (
            [
                [Csrf::DEFAULT_FIELD => $token],
                [Csrf::DEFAULT_FIELD => $token, 'crontab' => 'delete=1'],
                [Csrf::DEFAULT_FIELD => $token, 'crontab' => ['delete' => '']],
                [Csrf::DEFAULT_FIELD => $token, 'crontab' => ['delete' => '0']],
                [Csrf::DEFAULT_FIELD => $token, 'crontab' => ['delete' => '1; rm -rf /']],
                [Csrf::DEFAULT_FIELD => $token, 'crontab' => ['delete' => ['1']]],
            ] as $post
        ) {
            $outcome = Crontab::evaluateAdminCrontabDeleteRequest($post, $this->sameSitePostServer(), $session);

            $this->assertSame(400, $outcome['status']);
            $this->assertNull($outcome['payload']);
        }
    }

    public function testAdminCrontabControllerAndViewUseCsrfGuard(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Crontab.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Crontab/admin_crontab.view.php');
        $adminStart = strpos($controller, 'function admin_crontab()');
        $viewStart = strpos($controller, 'private function view()');

        $this->assertNotFalse($adminStart);
        $this->assertNotFalse($viewStart);
        $adminBody = substr($controller, $adminStart, $viewStart - $adminStart);

        $this->assertStringContainsString("private const ADMIN_CRONTAB_ADD_CSRF_SCOPE = 'crontab.admin_crontab.add'", $controller);
        $this->assertStringContainsString("private const ADMIN_CRONTAB_DELETE_CSRF_SCOPE = 'crontab.admin_crontab.delete'", $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::ADMIN_CRONTAB_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::ADMIN_CRONTAB_DELETE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('evaluateAdminCrontabAddRequest($_POST, $_SERVER, $_SESSION)', $adminBody);
        $this->assertStringContainsString('evaluateAdminCrontabDeleteRequest($_POST, $_SERVER, $_SESSION)', $adminBody);
        $this->assertStringContainsString('CliCrontab::insert(', $adminBody);
        $this->assertStringContainsString('CliCrontab::delete(', $adminBody);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::ADMIN_CRONTAB_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::ADMIN_CRONTAB_DELETE_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('$add_csrf_input', $view);
        $this->assertStringContainsString('$delete_csrf_input', $view);
        $this->assertStringContainsString('method=\"post\"', $view);
    }

    private function validAddPost(?string $token): array
    {
        $post = [
            'crontab' => [
                'minute' => '*/5',
                'hour' => '*',
                'dayofmonth' => '*',
                'month' => '*',
                'dayofweek' => '*',
                'command' => '/usr/bin/php /srv/www/pmacontrol/index.php worker run',
            ],
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function invalidAddPosts(string $token): array
    {
        $base = $this->validAddPost($token);

        $missingGroup = [Csrf::DEFAULT_FIELD => $token];
        $nonArrayGroup = [Csrf::DEFAULT_FIELD => $token, 'crontab' => 'minute=*'];
        $missingMinute = $base;
        unset($missingMinute['crontab']['minute']);
        $arrayMinute = $base;
        $arrayMinute['crontab']['minute'] = ['*/5'];
        $spaceMinute = $base;
        $spaceMinute['crontab']['minute'] = '*/5 *';
        $newlineCommand = $base;
        $newlineCommand['crontab']['command'] = "/bin/true\n* * * * /bin/false";
        $emptyCommand = $base;
        $emptyCommand['crontab']['command'] = '   ';
        $arrayCommand = $base;
        $arrayCommand['crontab']['command'] = ['/bin/true'];

        return [
            $missingGroup,
            $nonArrayGroup,
            $missingMinute,
            $arrayMinute,
            $spaceMinute,
            $newlineCommand,
            $emptyCommand,
            $arrayCommand,
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
