<?php

declare(strict_types=1);

use App\Library\Cve\ServerCveImpactMatcher;
use PHPUnit\Framework\TestCase;

final class ServerCveImpactMatcherTest extends TestCase
{
    public function testProductCodeFromVersionUsesExistingFormatDetection(): void
    {
        $this->assertSame('mysql', ServerCveImpactMatcher::productCodeFromVersion('8.0.36', 'MySQL Community Server - GPL'));
        $this->assertSame('mariadb', ServerCveImpactMatcher::productCodeFromVersion('10.6.19-MariaDB-log', 'MariaDB Server'));
        $this->assertSame('percona', ServerCveImpactMatcher::productCodeFromVersion('8.0.36', 'Percona Server (GPL), Release 28'));
        $this->assertSame('proxysql', ServerCveImpactMatcher::productCodeFromVersion('2.5.5', 'ProxySQL Admin'));
        $this->assertSame('maxscale', ServerCveImpactMatcher::productCodeFromVersion('24.02.1', 'MaxScale'));
        $this->assertSame('singlestore', ServerCveImpactMatcher::productCodeFromVersion('8.5.0', 'SingleStore'));
        $this->assertSame('aurora_mysql', ServerCveImpactMatcher::productCodeFromVersion('8.0.mysql_aurora.3.08.0', 'Amazon Aurora MySQL'));
        $this->assertNull(ServerCveImpactMatcher::productCodeFromVersion('8.0.36', 'MySQL Router'));
    }

    public function testServerContextSkipsVipAndMapsProxyFlag(): void
    {
        $this->assertNull(ServerCveImpactMatcher::serverContext(['id' => 1, 'is_vip' => '1'], ['version' => '8.0.36']));

        $context = ServerCveImpactMatcher::serverContext(
            ['id' => 2, 'is_proxy' => '1', 'is_vip' => '0'],
            ['version' => '2.5.5', 'version_comment' => 'ProxySQL']
        );

        $this->assertSame('proxysql', $context['product_code'] ?? null);
        $this->assertSame('2.5.5', $context['version'] ?? null);
    }

    public function testAffectedRowMatchesStructuredRangesAndExactVersions(): void
    {
        $range = [
            'version_start_including' => '8.0.0',
            'version_end_excluding' => '8.0.37',
        ];
        $this->assertTrue(ServerCveImpactMatcher::affectedRowMatchesVersion($range, '8.0.36-log'));
        $this->assertFalse(ServerCveImpactMatcher::affectedRowMatchesVersion($range, '8.0.37'));

        $exact = ['version_text' => '5.7.44'];
        $this->assertTrue(ServerCveImpactMatcher::affectedRowMatchesVersion($exact, '5.7.44-log'));
        $this->assertFalse(ServerCveImpactMatcher::affectedRowMatchesVersion($exact, '5.7.43'));
    }

    public function testAffectedRowRejectsUnstructuredAdvisoryText(): void
    {
        $this->assertFalse(ServerCveImpactMatcher::affectedRowMatchesVersion(
            ['version_text' => 'Oracle CPU advisory'],
            '8.0.36'
        ));
    }

    public function testCacheMatchMethodReportsRangeOrExact(): void
    {
        $this->assertSame('version_range', ServerCveImpactMatcher::cacheMatchMethod([
            'version_start_including' => '8.0.0',
        ]));
        $this->assertSame('exact_version', ServerCveImpactMatcher::cacheMatchMethod([
            'version_text' => '8.0.36',
        ]));
    }
}
