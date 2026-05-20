<?php

declare(strict_types=1);

if (!defined('DATA')) {
    define('DATA', sys_get_temp_dir().'/pmacontrol-data/');
}

use App\Controller\Cleaner;
use PHPUnit\Framework\TestCase;

final class CleanerDuplicatePathTest extends TestCase
{
    public function testMissingCleanerRowsFilterExcludesAlreadyPresentPrimaryKeys(): void
    {
        $cleaner = new Cleaner('Controller', 'View', []);

        $filter = $this->invokePrivate($cleaner, 'getMissingCleanerRowsFilter', [
            'child_table',
            ['id', 'tenant_id'],
        ]);

        $this->assertSame(
            'LEFT JOIN `CLEANER`.`DELETE_child_table` c ON c.`id` = a.`id` AND c.`tenant_id` = a.`tenant_id`',
            $filter['join']
        );
        $this->assertSame(
            'c.`id` IS NULL AND c.`tenant_id` IS NULL',
            $filter['where']
        );
    }

    private function invokePrivate(object $object, string $methodName, array $arguments): mixed
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $arguments);
    }
}
