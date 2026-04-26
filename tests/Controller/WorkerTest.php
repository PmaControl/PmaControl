<?php

declare(strict_types=1);

use App\Controller\Worker;
use PHPUnit\Framework\TestCase;

final class WorkerTest extends TestCase
{
    public function testSummarizeAvailabilityGroupsAvailableAndUnavailableServers(): void
    {
        $summary = Worker::summarizeAvailability([
            ['id_mysql_server' => 1, 'mysql_available' => 1, 'ssh_available' => 0],
            ['id_mysql_server' => 2, 'mysql_available' => 0, 'ssh_available' => 1],
            ['id_mysql_server' => 3, 'mysql_available' => 1, 'ssh_available' => 1],
        ]);

        $this->assertSame(2, $summary['mysql_available']['available_1_count']);
        $this->assertSame([1, 3], $summary['mysql_available']['available_1_ids']);
        $this->assertSame([2], $summary['mysql_available']['available_0_ids']);
        $this->assertSame([1], $summary['ssh_available']['available_0_ids']);
    }

    public function testGenerateWorkerUpdateQueriesAppliesBusinessRule(): void
    {
        $worker = new Worker('Controller', 'View', []);

        $queries = $worker->generateWorkerUpdateQueries([
            'mysql_available' => [
                'available_1_count' => 11,
                'available_0_count' => 2,
            ],
            'ssh_available' => [
                'available_1_count' => 4,
                'available_0_count' => 1,
            ],
            'unknown_available' => [
                'available_1_count' => 99,
                'available_0_count' => 99,
            ],
        ]);

        $this->assertContains("UPDATE worker_queue SET nb_worker = 4 WHERE `table` = 'mysql_server';", $queries);
        $this->assertContains("UPDATE worker_queue SET nb_worker = 1 WHERE `table` = 'ssh_server';", $queries);
        $this->assertCount(2, $queries);
    }

    public function testReadWorkerServerIdFromPidFileIgnoresMissingAndWaitingFiles(): void
    {
        $missing = sys_get_temp_dir() . '/pmacontrol-missing-worker-' . uniqid('', true) . '.pid';
        $this->assertNull(Worker::readWorkerServerIdFromPidFile($missing));

        $file = tempnam(sys_get_temp_dir(), 'pmacontrol-worker-pid-');
        $this->assertIsString($file);

        try {
            file_put_contents($file, " Waiting...\n");
            $this->assertNull(Worker::readWorkerServerIdFromPidFile($file));

            file_put_contents($file, " 42\n");
            $this->assertSame('42', Worker::readWorkerServerIdFromPidFile($file));
        } finally {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testCountWorkerServerIdsIgnoresNonScalars(): void
    {
        $this->assertSame([
            42 => 2,
            7 => 1,
        ], Worker::countWorkerServerIds(['42', 42, ['invalid'], false, null, '7']));
    }
}
