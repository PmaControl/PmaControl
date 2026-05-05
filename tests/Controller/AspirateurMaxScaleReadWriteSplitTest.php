<?php

namespace Tests\Controller;

use App\Controller\Aspirateur;
use PHPUnit\Framework\TestCase;

class AspirateurMaxScaleReadWriteSplitTest extends TestCase
{
    private function invokePrivate(object $object, string $method, array $arguments = [])
    {
        $reflection = new \ReflectionClass($object);
        $instanceMethod = $reflection->getMethod($method);

        return $instanceMethod->invokeArgs($object, $arguments);
    }

    private function newAspirateur(): Aspirateur
    {
        $reflection = new \ReflectionClass(Aspirateur::class);

        return $reflection->newInstanceWithoutConstructor();
    }

    public function testDetectsReadWriteSplitServiceForMatchingMaxScaleListener(): void
    {
        $data = $this->maxScaleData('readwritesplit');

        $result = $this->invokePrivate(
            $this->newAspirateur(),
            'isMaxScaleReadWriteSplitEndpointData',
            [$data, '10.68.68.111', 4006]
        );

        $this->assertTrue($result);
    }

    public function testDoesNotDetectReadConnRouteAsReadWriteSplit(): void
    {
        $data = $this->maxScaleData('readconnroute');

        $result = $this->invokePrivate(
            $this->newAspirateur(),
            'isMaxScaleReadWriteSplitEndpointData',
            [$data, '10.68.68.111', 4006]
        );

        $this->assertFalse($result);
    }

    public function testWildcardListenerAddressMatchesEndpointPort(): void
    {
        $data = $this->maxScaleData('readwritesplit', '0.0.0.0');

        $result = $this->invokePrivate(
            $this->newAspirateur(),
            'isMaxScaleReadWriteSplitEndpointData',
            [$data, '10.68.68.111', 4006]
        );

        $this->assertTrue($result);
    }

    public function testDetectsTransientProxySessionLoss(): void
    {
        $result = $this->invokePrivate(
            $this->newAspirateur(),
            'isTransientProxySessionLoss',
            ['ERROR: MySQL server has gone away']
        );

        $this->assertTrue($result);
    }

    public function testDoesNotTreatArbitrarySqlErrorAsTransientSessionLoss(): void
    {
        $result = $this->invokePrivate(
            $this->newAspirateur(),
            'isTransientProxySessionLoss',
            ['ERROR: Access denied for user pmacontrol']
        );

        $this->assertFalse($result);
    }

    private function maxScaleData(string $router, string $listenerAddress = '10.68.68.111'): array
    {
        return [
            'is_maxscale' => '1',
            'version_comment' => 'MaxScale',
            'maxscale_listeners' => [
                'data' => [[
                    'id' => 'Read-Write-Listener',
                    'attributes' => [
                        'parameters' => [
                            'address' => $listenerAddress,
                            'port' => 4006,
                        ],
                    ],
                    'relationships' => [
                        'services' => [
                            'data' => [['id' => 'Read-Write-Service', 'type' => 'services']],
                        ],
                    ],
                ]],
            ],
            'maxscale_services' => [
                'data' => [[
                    'id' => 'Read-Write-Service',
                    'attributes' => [
                        'router' => $router,
                    ],
                ]],
            ],
        ];
    }
}
