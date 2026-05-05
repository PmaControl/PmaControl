<?php

declare(strict_types=1);

use App\Controller\GaleraCluster;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class GaleraClusterSetPrimarySecurityTest extends TestCase
{
    public function testSetNodeAsPrimaryRequiresPostCsrfPositiveIdAndConfirmation(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, GaleraCluster::SET_PRIMARY_CSRF_SCOPE);
        $validPost = [
            Csrf::DEFAULT_FIELD => $token,
            'id_mysql_server' => '7',
            'confirm_set_primary' => GaleraCluster::SET_PRIMARY_CONFIRM_VALUE,
        ];

        $get = GaleraCluster::evaluateSetNodeAsPrimaryRequest(['7'], [], ['REQUEST_METHOD' => 'GET'], $session);
        $missingToken = GaleraCluster::evaluateSetNodeAsPrimaryRequest(['7'], [], $this->sameSitePostServer(), $session);
        $external = GaleraCluster::evaluateSetNodeAsPrimaryRequest(['7'], $validPost, $this->externalPostServer(), $session);
        $invalidId = GaleraCluster::evaluateSetNodeAsPrimaryRequest(
            ['7 OR 1=1'],
            $validPost,
            $this->sameSitePostServer(),
            $session
        );
        $missingConfirmation = GaleraCluster::evaluateSetNodeAsPrimaryRequest(
            ['7'],
            [Csrf::DEFAULT_FIELD => $token, 'id_mysql_server' => '7'],
            $this->sameSitePostServer(),
            $session
        );
        $valid = GaleraCluster::evaluateSetNodeAsPrimaryRequest(
            ['7'],
            $validPost,
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(405, $get['status']);
        $this->assertSame('POST', $get['headers']['Allow']);
        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $external['status']);
        $this->assertSame('Invalid request origin', $external['body']);
        $this->assertSame(400, $invalidId['status']);
        $this->assertNull($invalidId['id_mysql_server']);
        $this->assertSame(400, $missingConfirmation['status']);
        $this->assertSame('Invalid Galera primary confirmation', $missingConfirmation['body']);
        $this->assertSame(200, $valid['status']);
        $this->assertSame(7, $valid['id_mysql_server']);
    }

    public function testSetNodeAsPrimaryRejectsRouteAndPostMismatch(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, GaleraCluster::SET_PRIMARY_CSRF_SCOPE);

        $outcome = GaleraCluster::evaluateSetNodeAsPrimaryRequest(
            ['7'],
            [
                Csrf::DEFAULT_FIELD => $token,
                'id_mysql_server' => '8',
                'confirm_set_primary' => GaleraCluster::SET_PRIMARY_CONFIRM_VALUE,
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(400, $outcome['status']);
        $this->assertNull($outcome['id_mysql_server']);
    }

    public function testCliCallsBypassBrowserCsrfAfterIdValidation(): void
    {
        $valid = GaleraCluster::evaluateSetNodeAsPrimaryRequest(['7'], [], ['REQUEST_METHOD' => 'GET'], [], true);
        $invalid = GaleraCluster::evaluateSetNodeAsPrimaryRequest(['bad'], [], ['REQUEST_METHOD' => 'GET'], [], true);

        $this->assertSame(200, $valid['status']);
        $this->assertSame(7, $valid['id_mysql_server']);
        $this->assertSame(400, $invalid['status']);
        $this->assertNull($invalid['id_mysql_server']);
    }

    public function testControllerUsesSafeRedirectAndGuardsBeforeSideEffects(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/GaleraCluster.php');
        $methodStart = strpos($controller, 'public function setNodeAsPrimary');
        $guardPosition = strpos($controller, 'evaluateSetNodeAsPrimaryRequest', $methodStart);
        $debugPosition = strpos($controller, 'Debug::parseDebug($param);', $methodStart);
        $statePosition = strpos($controller, 'Extraction2::display([', $methodStart);

        $this->assertNotFalse($methodStart);
        $this->assertNotFalse($guardPosition);
        $this->assertNotFalse($debugPosition);
        $this->assertNotFalse($statePosition);
        $this->assertLessThan($debugPosition, $guardPosition);
        $this->assertLessThan($statePosition, $guardPosition);
        $this->assertStringContainsString('$this->view = false;', $controller);
        $this->assertStringContainsString('$this->layout_name = false;', $controller);
        $this->assertStringContainsString('RouteMutationRequest::evaluate', $controller);
        $this->assertStringContainsString('SafeRedirect::refererOrFallback', $controller);
        $this->assertStringNotContainsString('$_SERVER[\'HTTP_REFERER\']', $controller);
    }

    public function testViewsRenderSetPrimaryAsConfirmedPostForms(): void
    {
        $mysqlServerController = (string) file_get_contents(__DIR__ . '/../../App/Controller/MysqlServer.php');
        $mysqlServerView = (string) file_get_contents(__DIR__ . '/../../App/view/MysqlServer/main.view.php');
        $serverController = (string) file_get_contents(__DIR__ . '/../../App/Controller/Server.php');
        $serverView = (string) file_get_contents(__DIR__ . '/../../App/view/Server/main.view.php');

        $this->assertStringContainsString('Csrf::issueToken($_SESSION, GaleraCluster::SET_PRIMARY_CSRF_SCOPE)', $mysqlServerController);
        $this->assertStringContainsString("'csrf_scope' => 'galera_set_primary'", $mysqlServerController);
        $this->assertStringContainsString("'confirm_set_primary' => GaleraCluster::SET_PRIMARY_CONFIRM_VALUE", $mysqlServerController);
        $this->assertStringContainsString('use App\\Library\\Security\\CsrfRender;', $mysqlServerView);
        $this->assertStringContainsString("CsrfRender::hiddenInput(\$data, \$csrfScope)", $mysqlServerView);
        $this->assertStringContainsString('method="post"', $mysqlServerView);

        $this->assertStringContainsString('Csrf::issueToken($_SESSION, GaleraCluster::SET_PRIMARY_CSRF_SCOPE)', $serverController);
        $this->assertStringContainsString('$galeraSetPrimaryCsrfField = CsrfRender::field($data, \'galera_set_primary\');', $serverView);
        $this->assertStringContainsString('form method="post" action="<?= LINK ?>GaleraCluster/setNodeAsPrimary/', $serverView);
        $this->assertStringContainsString('name="confirm_set_primary" value="SET_PRIMARY"', $serverView);
        $this->assertStringNotContainsString('<a href="<?= LINK ?>GaleraCluster/setNodeAsPrimary/', $serverView);
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

    private function externalPostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];
    }
}
