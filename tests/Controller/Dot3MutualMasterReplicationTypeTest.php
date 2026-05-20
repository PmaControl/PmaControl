<?php

declare(strict_types=1);

use App\Controller\Dot3;
use PHPUnit\Framework\TestCase;

/**
 * Locks down the contract that Dot3::isMutualMasterReplication() accepts both
 * the numeric snapshot id and the virtual "import:<md5>" id used by the
 * Cluster::viewDot preview_key flow (App/Controller/Dot3.php:1453).
 *
 * Regression: a strict `int $idDot3Information` typehint blew up
 * /pmacontrol/fr/Cluster/viewDot/<id>/?preview_key=… with
 *   "Argument #4 ($idDot3Information) must be of type int, string given,
 *    called in App/Controller/Dot3.php on line 2070".
 */
final class Dot3MutualMasterReplicationTypeTest extends TestCase
{
    protected function tearDown(): void
    {
        Dot3::$information = [];
    }

    public function testParameterAcceptsStringVirtualSnapshotId(): void
    {
        $reflection = new ReflectionMethod(Dot3::class, 'isMutualMasterReplication');
        $params = $reflection->getParameters();
        $idDot3Information = $params[3];

        $this->assertSame('idDot3Information', $idDot3Information->getName());
        $this->assertFalse(
            $idDot3Information->hasType() && $idDot3Information->getType() instanceof ReflectionNamedType
                && $idDot3Information->getType()->getName() === 'int',
            'Strict int typehint rejects "import:<md5>" virtual ids forwarded by '
            . 'renderImportedGraphGroup() and breaks the preview_key flow.'
        );
    }

    public function testInvocableWithImportVirtualIdWithoutTypeError(): void
    {
        $virtualId = 'import:' . str_repeat('a', 32);

        Dot3::$information[$virtualId] = [
            'id' => $virtualId,
            'information' => [
                'mapping' => [
                    '10.0.0.5:3306' => 5,
                ],
            ],
        ];

        $servers = [
            1 => [
                '@slave' => [
                    [
                        'master_host' => '10.0.0.5',
                        'master_port' => '3306',
                    ],
                ],
            ],
        ];

        $reflection = new ReflectionMethod(Dot3::class, 'isMutualMasterReplication');
        $instance = $this->newDot3Without__construct();

        $result = $reflection->invoke($instance, $servers, 1, 5, $virtualId);

        $this->assertTrue(
            $result,
            'Mutual replication should be detected: 1 -> 5 (master) and reverse mapping '
            . 'returns 5 for "10.0.0.5:3306" in the import-virtual snapshot.'
        );
    }

    public function testInvocableWithIntegerSnapshotIdWithoutTypeError(): void
    {
        $idDot3Information = 12345;

        Dot3::$information[$idDot3Information] = [
            'id' => $idDot3Information,
            'information' => [
                'mapping' => [
                    '10.0.0.5:3306' => 5,
                ],
            ],
        ];

        $servers = [
            1 => [
                '@slave' => [
                    [
                        'master_host' => '10.0.0.5',
                        'master_port' => '3306',
                    ],
                ],
            ],
        ];

        $reflection = new ReflectionMethod(Dot3::class, 'isMutualMasterReplication');
        $instance = $this->newDot3Without__construct();

        $result = $reflection->invoke($instance, $servers, 1, 5, $idDot3Information);

        $this->assertTrue($result);
    }

    public function testReturnsFalseWhenSlaveListEmptyRegardlessOfSnapshotIdType(): void
    {
        $virtualId = 'import:' . str_repeat('b', 32);
        Dot3::$information[$virtualId] = [
            'id' => $virtualId,
            'information' => ['mapping' => []],
        ];

        $reflection = new ReflectionMethod(Dot3::class, 'isMutualMasterReplication');
        $instance = $this->newDot3Without__construct();

        $this->assertFalse($reflection->invoke($instance, [1 => []], 1, 5, $virtualId));
    }

    private function newDot3Without__construct(): Dot3
    {
        return (new ReflectionClass(Dot3::class))->newInstanceWithoutConstructor();
    }
}
