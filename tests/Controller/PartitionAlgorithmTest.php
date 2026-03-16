<?php

declare(strict_types=1);

use App\Controller\Partition;
use PHPUnit\Framework\TestCase;

final class PartitionAlgorithmTest extends TestCase
{
    private Partition $partition;

    protected function setUp(): void
    {
        $this->partition = $this->getMockBuilder(Partition::class)
            ->setConstructorArgs(['Controller', 'View', []])
            ->onlyMethods(['saveSQLToFile'])
            ->getMock();
    }

    public function testExtractParametersNormalizesInvalidBounds(): void
    {
        $result = $this->invokePrivate('extractParameters', [[1, 'db', 'table', '', 1, 5]]);

        $this->assertSame(2, $result['nb_partitions']);
        $this->assertSame(1.0, $result['sample_ratio']);
        $this->assertNull($result['field']);
    }

    public function testCalculatePartitionLimitsIncrementsIntegerCuts(): void
    {
        $limits = $this->invokePrivate('calculatePartitionLimits', [[
            ['val' => 10, 'cnt' => 5],
            ['val' => 20, 'cnt' => 5],
            ['val' => 30, 'cnt' => 5],
        ], 3, true]);

        $this->assertSame([21, 31], $limits);
    }

    public function testCalculatePartitionLimitsCompletesMissingStringCuts(): void
    {
        $limits = $this->invokePrivate('calculatePartitionLimits', [[
            ['val' => 'a', 'cnt' => 10],
        ], 3, false]);

        $this->assertSame(['a', 'a_max'], $limits);
    }

    public function testGeneratePartitionSqlQuotesStringBoundaries(): void
    {
        $sql = $this->invokePrivate('generatePartitionSQL', ['orders', 'code', ['m', 'z'], false]);

        $this->assertStringContainsString("PARTITION p0 VALUES LESS THAN ('m')", $sql);
        $this->assertStringContainsString("PARTITION p1 VALUES LESS THAN ('z')", $sql);
        $this->assertStringContainsString('PARTITION p2 VALUES LESS THAN (MAXVALUE)', $sql);
    }

    private function invokePrivate(string $methodName, array $arguments): mixed
    {
        $reflection = new ReflectionClass($this->partition);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->partition, $arguments);
    }
}
