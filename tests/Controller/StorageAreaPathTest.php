<?php

declare(strict_types=1);

use App\Controller\StorageArea;
use PHPUnit\Framework\TestCase;

final class StorageAreaPathTest extends TestCase
{
    private StorageArea $storageArea;

    protected function setUp(): void
    {
        $this->storageArea = new StorageArea('Controller', 'View', []);
    }

    public function testParseStorageSpaceReturnsNumericValues(): void
    {
        $space = $this->invokePrivate('parseStorageSpace', [
            "1048576 524288 524288 50%\n",
            "4096\n",
        ]);

        $this->assertSame([
            'size' => 1048576,
            'used' => 524288,
            'available' => 524288,
            'percent' => 50,
            'backup' => 4096,
        ], $space);
    }

    public function testParseStorageSpaceRejectsInvalidPathOutput(): void
    {
        $space = $this->invokePrivate('parseStorageSpace', [
            "sh: cd: can't cd to /missing/path\n",
            "",
        ]);

        $this->assertFalse($space);
    }

    public function testStorageCommandsQuoteRemotePath(): void
    {
        $checkCommand = $this->invokePrivate('getStoragePathCheckCommand', ['/srv/backup mysql']);
        $dfCommand = $this->invokePrivate('getStorageDfCommand', ['/srv/backup mysql']);

        $this->assertSame("cd '/srv/backup mysql' && test -d . && test -r . && printf PMACTRL_STORAGE_OK", $checkCommand);
        $this->assertStringStartsWith("cd '/srv/backup mysql' && df -Pk .", $dfCommand);
        $this->assertStringContainsString("awk 'NR==2", $dfCommand);
    }

    public function testSshPortDefaultsToTwentyTwo(): void
    {
        $this->assertSame(22, $this->invokePrivate('getSshPort', [[]]));
        $this->assertSame(22, $this->invokePrivate('getSshPort', [['port' => 'invalid']]));
        $this->assertSame(2022, $this->invokePrivate('getSshPort', [['port' => '2022']]));
    }

    public function testRemotePathCheckAcceptsSuccessMarkerWithoutExitStatus(): void
    {
        $ssh = new class {
            public string $command = '';

            public function exec(string $command): string
            {
                $this->command = $command;

                return 'PMACTRL_STORAGE_OK';
            }

            public function getExitStatus(): ?int
            {
                return null;
            }
        };

        $this->assertTrue($this->invokePrivate('isRemoteStoragePathAvailable', [$ssh, '/srv/backup mysql']));
        $this->assertSame(
            "cd '/srv/backup mysql' && test -d . && test -r . && printf PMACTRL_STORAGE_OK",
            $ssh->command
        );
    }

    private function invokePrivate(string $methodName, array $arguments): mixed
    {
        $reflection = new ReflectionClass($this->storageArea);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($this->storageArea, $arguments);
    }
}
