<?php

declare(strict_types=1);

use App\Library\Security\RouteExposurePolicy;
use PHPUnit\Framework\TestCase;

final class CollectorWorkerNoHttpRouteTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';

    /**
     * @var list<string>
     */
    private const CLI_ONLY_CONTROLLERS = [
        'Aspirateur',
        'Ventilateur',
        'Control',
        'Integrate',
        'IntegrateLog',
        'AggregateMetric',
    ];

    /**
     * @var array<string,bool>
     */
    private const LISTENER_WEB_ACTIONS = [
        'status' => true,
    ];

    public function testEveryPublicActionOnCliOnlyControllersIsDeniedOverHttp(): void
    {
        foreach (self::CLI_ONLY_CONTROLLERS as $controller) {
            $methods = $this->publicMethodsForController($controller);
            $this->assertNotSame([], $methods, $controller.' must expose public methods for this test to be meaningful');

            foreach ($methods as $method => $line) {
                $this->assertTrue(
                    RouteExposurePolicy::isDeniedWebRoute($controller, $method),
                    $controller.'/'.$method.' declared at line '.$line.' must not be HTTP-routable'
                );
            }
        }
    }

    public function testListenerWorkerActionsAreDeniedButStatusDiagnosticRemainsAvailable(): void
    {
        $methods = $this->publicMethodsForController('Listener');
        $this->assertArrayHasKey('status', $methods);

        foreach ($methods as $method => $line) {
            if (isset(self::LISTENER_WEB_ACTIONS[$method])) {
                $this->assertFalse(
                    RouteExposurePolicy::isDeniedWebRoute('Listener', $method),
                    'Listener/'.$method.' declared at line '.$line.' is retained for the Daemon diagnostic view'
                );
                continue;
            }

            $this->assertTrue(
                RouteExposurePolicy::isDeniedWebRoute('Listener', $method),
                'Listener/'.$method.' declared at line '.$line.' must not be HTTP-routable'
            );
        }
    }

    public function testDaemonUiControllersAreNotBlockedByCollectorPolicy(): void
    {
        foreach ([
            ['Daemon', 'index'],
            ['Worker', 'index'],
            ['Worker', 'list'],
            ['Worker', 'file'],
            ['Agent', 'start'],
        ] as [$controller, $action]) {
            $this->assertFalse(
                RouteExposurePolicy::isDeniedWebRoute($controller, $action),
                $controller.'/'.$action.' remains part of the Daemon/Worker UI contract'
            );
        }
    }

    /**
     * @return array<string,int>
     */
    private function publicMethodsForController(string $controller): array
    {
        $path = self::ROOT.'/App/Controller/'.$controller.'.php';
        $source = (string) file_get_contents($path);

        preg_match_all(
            '/^\\s*public\\s+(?:static\\s+)?function\\s+([A-Za-z_][A-Za-z0-9_]*)\\s*\\(/m',
            $source,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $methods = [];
        foreach ($matches[1] as [$method, $offset]) {
            $methods[$method] = substr_count(substr($source, 0, $offset), "\n") + 1;
        }

        return $methods;
    }
}
