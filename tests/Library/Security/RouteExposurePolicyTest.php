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
    }

    public function testGuardedPasswordEditEndpointRemainsRoutable(): void
    {
        $this->assertFalse(
            RouteExposurePolicy::isDeniedWebRoute('Server', 'password'),
            'Server/password is the guarded POST+CSRF endpoint; only legacy Server/passwd is blocked.'
        );
    }

    public function testDeniedRouteReasonsStayDocumented(): void
    {
        foreach (RouteExposurePolicy::deniedWebRoutes() as $route => $reason) {
            $this->assertMatchesRegularExpression('/^[a-z]+\\/[a-z0-9_]+$/', $route);
            $this->assertGreaterThan(20, strlen($reason), $route.' must explain why it is blocked');
        }
    }
}
