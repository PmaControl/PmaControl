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
}
