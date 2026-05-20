<?php

declare(strict_types=1);

use App\Controller\Server;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ServerStateMutationSecurityTest extends TestCase
{
    public function testServerStateMutationRequestsAcceptValidTokens(): void
    {
        foreach ($this->actionScopes() as $method => $scope) {
            $session = [];
            $token = Csrf::issueToken($session, $scope);

            $outcome = Server::$method(
                [Csrf::DEFAULT_FIELD => $token, 'id_server' => '7'],
                $this->sameSitePostServer(),
                $session,
                ['7']
            );

            $this->assertSame(200, $outcome['status'], $method);
            $this->assertSame(7, $outcome['id_server'], $method);
        }
    }

    public function testServerStateMutationRequestsRejectNonPost(): void
    {
        foreach ($this->actionScopes() as $method => $_scope) {
            $outcome = Server::$method([], ['REQUEST_METHOD' => 'GET'], [], ['7']);

            $this->assertSame(405, $outcome['status'], $method);
            $this->assertSame('POST', $outcome['headers']['Allow'], $method);
            $this->assertNull($outcome['id_server'], $method);
        }
    }

    public function testServerStateMutationRequestsRejectExternalOriginBeforeSqlInjectionPayload(): void
    {
        foreach ($this->actionScopes() as $method => $scope) {
            $session = [];
            $token = Csrf::issueToken($session, $scope);

            $outcome = Server::$method(
                [Csrf::DEFAULT_FIELD => $token, 'id_server' => '7 OR 1=1'],
                [
                    'REQUEST_METHOD' => 'POST',
                    'HTTPS' => 'on',
                    'HTTP_HOST' => 'pmacontrol.test',
                    'HTTP_ORIGIN' => 'https://attacker.test',
                ],
                $session,
                ['7 OR 1=1']
            );

            $this->assertSame(403, $outcome['status'], $method);
            $this->assertSame('Invalid request origin', $outcome['body'], $method);
            $this->assertNull($outcome['id_server'], $method);
        }
    }

    public function testServerStateMutationRequestsRejectSqlInjectionIdsAfterValidCsrf(): void
    {
        foreach ($this->actionScopes() as $method => $scope) {
            $session = [];
            $token = Csrf::issueToken($session, $scope);

            $outcome = Server::$method(
                [Csrf::DEFAULT_FIELD => $token, 'id_server' => '7 OR 1=1'],
                $this->sameSitePostServer(),
                $session,
                ['7 OR 1=1']
            );

            $this->assertSame(400, $outcome['status'], $method);
            $this->assertSame('Invalid server id', $outcome['body'], $method);
            $this->assertNull($outcome['id_server'], $method);
        }
    }

    public function testServerStateMutationRequestsRejectRouteAndPostMismatch(): void
    {
        foreach ($this->actionScopes() as $method => $scope) {
            $session = [];
            $token = Csrf::issueToken($session, $scope);

            $outcome = Server::$method(
                [Csrf::DEFAULT_FIELD => $token, 'id_server' => '7'],
                $this->sameSitePostServer(),
                $session,
                ['42']
            );

            $this->assertSame(400, $outcome['status'], $method);
            $this->assertNull($outcome['id_server'], $method);
        }
    }

    public function testSqlBuildersUseNormalizedIntegerInputsOnly(): void
    {
        $this->assertSame(
            'UPDATE mysql_server SET is_acknowledged=9 WHERE id=7 LIMIT 1;',
            Server::buildAcknowledgeSql(7, 9)
        );
        $this->assertSame(
            'UPDATE mysql_server SET is_acknowledged=0 WHERE id=7 LIMIT 1;',
            Server::buildRetractSql(7)
        );
        $this->assertSame(
            'SELECT name FROM mysql_server WHERE id=7 LIMIT 1;',
            Server::buildRemoveSelectSql(7)
        );
        $this->assertSame(
            'UPDATE mysql_server SET is_deleted=1 WHERE id=7 LIMIT 1;',
            Server::buildRemoveSql(7)
        );
    }

    public function testControllerAndViewsUsePostCsrfForServerStateMutations(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Server.php');
        $mainView = (string) file_get_contents(__DIR__ . '/../../App/view/Server/main.view.php');
        $settingsView = (string) file_get_contents(__DIR__ . '/../../App/view/Server/settings.view.php');

        $this->assertStringContainsString("private const SERVER_ACKNOWLEDGE_CSRF_SCOPE = 'server.acknowledge'", $controller);
        $this->assertStringContainsString("private const SERVER_RETRACT_CSRF_SCOPE = 'server.retract'", $controller);
        $this->assertStringContainsString("private const SERVER_REMOVE_CSRF_SCOPE = 'server.remove'", $controller);
        $this->assertStringContainsString('RouteMutationRequest::evaluate', $controller);
        $this->assertStringContainsString('self::evaluateAcknowledgeRequest($_POST, $_SERVER, $_SESSION, is_array($param) ? $param : [])', $controller);
        $this->assertStringContainsString('self::evaluateRetractRequest($_POST, $_SERVER, $_SESSION, is_array($param) ? $param : [])', $controller);
        $this->assertStringContainsString('self::evaluateRemoveRequest($_POST, $_SERVER, $_SESSION, is_array($param) ? $param : [])', $controller);
        $this->assertStringNotContainsString('WHERE id=".$id_server', $controller);

        $this->assertStringContainsString('$serverAcknowledgeCsrfField', $mainView);
        $this->assertStringContainsString('$serverRetractCsrfField', $mainView);
        $this->assertStringContainsString('method="post" action="<?= LINK ?>server/acknowledge/', $mainView);
        $this->assertStringContainsString('method="post" action="<?= LINK ?>server/retract/', $mainView);
        $this->assertStringNotContainsString('href="<?= LINK ?>server/acknowledge/', $mainView);
        $this->assertStringNotContainsString('href="<?= LINK ?>server/retract/', $mainView);

        $this->assertStringContainsString('$serverRemoveCsrfField', $settingsView);
        $this->assertStringContainsString('method="post" action="\'.LINK.\'server/remove/', $settingsView);
        $this->assertStringContainsString("form=\"'.\$serverRemoveFormId.'\"", $settingsView);
        $this->assertStringNotContainsString('<a class="btn-xs btn btn-danger" href="\'.LINK.\'server/remove/', $settingsView);
    }

    private function actionScopes(): array
    {
        return [
            'evaluateAcknowledgeRequest' => 'server.acknowledge',
            'evaluateRetractRequest' => 'server.retract',
            'evaluateRemoveRequest' => 'server.remove',
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
