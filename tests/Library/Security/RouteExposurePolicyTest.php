<?php

declare(strict_types=1);

use App\Library\Security\RouteExposurePolicy;
use PHPUnit\Framework\TestCase;

final class RouteExposurePolicyTest extends TestCase
{
    public function testKnownDangerousLegacyRoutesAreDeniedOverHttp(): void
    {
        $routes = [
            ['Alter', 'dropsp'],
            ['Alter', 'slave'],
            ['Alter', 'dropRoot'],
            ['Alter', 'user'],
            ['Webservice', 'decrypt'],
            ['Mysql', 'passwd'],
            ['Server', 'passwd'],
        ];

        foreach ($routes as [$controller, $action]) {
            $this->assertTrue(
                RouteExposurePolicy::isDeniedWebRoute($controller, $action),
                $controller.'/'.$action.' must be blocked by the HTTP route exposure policy'
            );
            $this->assertNotSame('', RouteExposurePolicy::denialReason($controller, $action));
        }
    }

    public function testPolicyNormalizesControllerAndActionCase(): void
    {
        $this->assertTrue(RouteExposurePolicy::isDeniedWebRoute('alter', 'dropRoot'));
        $this->assertTrue(RouteExposurePolicy::isDeniedWebRoute('WEBSERVICE', 'DECRYPT'));
        $this->assertTrue(RouteExposurePolicy::isDeniedWebRoute('ASPIRATEUR', 'tryMysqlConnection'));
    }

    public function testGuardedPasswordEditEndpointRemainsRoutable(): void
    {
        $this->assertFalse(
            RouteExposurePolicy::isDeniedWebRoute('Server', 'password'),
            'Server/password is the guarded POST+CSRF endpoint; only legacy Server/passwd is blocked.'
        );
    }

    public function testCollectorControllersAreDeniedOverHttpByControllerPolicy(): void
    {
        foreach ([
            ['Aspirateur', 'tryMysqlConnection'],
            ['Ventilateur', 'pull'],
            ['Control', 'rebuildAll'],
            ['Integrate', 'integrateAll'],
            ['IntegrateLog', 'integrateAll'],
            ['AggregateMetric', 'aggregateRecentByServer'],
        ] as [$controller, $action]) {
            $this->assertTrue(
                RouteExposurePolicy::isDeniedWebRoute($controller, $action),
                $controller.'/'.$action.' must be blocked as a CLI-only controller route'
            );
        }
    }

    public function testListenerStatusRemainsAvailableWhileWorkerActionsAreDenied(): void
    {
        $this->assertFalse(
            RouteExposurePolicy::isDeniedWebRoute('Listener', 'status'),
            'Listener/status is kept available for the Daemon diagnostic view.'
        );
        $this->assertTrue(RouteExposurePolicy::isDeniedWebRoute('Listener', 'checkAll'));
        $this->assertTrue(RouteExposurePolicy::isDeniedWebRoute('Listener', 'resetAll'));
        $this->assertTrue(RouteExposurePolicy::isDeniedWebRoute('Listener', 'updateDatabase'));
    }

    public function testDeniedRouteReasonsStayDocumented(): void
    {
        foreach (RouteExposurePolicy::deniedWebRoutes() as $route => $reason) {
            $this->assertMatchesRegularExpression('/^[a-z]+\\/[a-z0-9_]+$/', $route);
            $this->assertGreaterThan(20, strlen($reason), $route.' must explain why it is blocked');
        }
    }

    public function testDeniedControllerReasonsStayDocumented(): void
    {
        foreach (RouteExposurePolicy::deniedWebControllers() as $controller => $reason) {
            $this->assertMatchesRegularExpression('/^[a-z][a-z0-9_]*$/', $controller);
            $this->assertGreaterThan(20, strlen($reason), $controller.' must explain why it is blocked');
        }
    }
}
