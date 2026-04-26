<?php

declare(strict_types=1);

use App\Controller\Home;
use PHPUnit\Framework\TestCase;

final class HomeVersionDistributionTest extends TestCase
{
    public function testVersionDistributionDetectsSingleStoreFromComment(): void
    {
        $key = Home::buildVersionDistributionKey('8.7.21', 'SingleStoreDB');

        $this->assertSame('SingleStore 8.7', $key);
    }

    public function testVersionDistributionDetectsSingleStoreFromVersionSuffix(): void
    {
        $key = Home::buildVersionDistributionKey('9.0.1-SingleStore', '');

        $this->assertSame('SingleStore 9.0', $key);
    }

    public function testVersionDistributionKeepsKnownForksFromCentralParser(): void
    {
        $this->assertSame('MariaDB 10.11', Home::buildVersionDistributionKey('10.11.16-MariaDB-deb12-log', ''));
        $this->assertSame('Percona 8.0', Home::buildVersionDistributionKey('8.0.36-28', 'Percona Server'));
        $this->assertSame('MySQL Router 8.0', Home::buildVersionDistributionKey('8.0.36', 'MySQL Router'));
    }

    public function testVersionDistributionFallsBackToMysqlForUnknownForks(): void
    {
        $key = Home::buildVersionDistributionKey('8.4.8-8', '');

        $this->assertSame('MySQL 8.4', $key);
    }

    public function testVersionDistributionSkipsEmptyVersion(): void
    {
        $this->assertNull(Home::buildVersionDistributionKey('', 'SingleStoreDB'));
    }
}
