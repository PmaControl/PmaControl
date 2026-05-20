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

    public function testReferencesFromJsonExtractsDeduplicatedHttpLinks(): void
    {
        $refs = ServerCveImpactMatcher::referencesFromJson(json_encode([
            'referenceData' => [
                ['url' => 'https://example.test/advisory', 'source' => 'Vendor'],
                ['url' => 'https://example.test/advisory', 'source' => 'Duplicate'],
                ['url' => 'ftp://example.test/ignored', 'source' => 'Ignored'],
                ['url' => 'https://nvd.nist.gov/vuln/detail/CVE-2026-0001'],
            ],
        ]), 5);

        $this->assertCount(2, $refs);
        $this->assertSame('https://example.test/advisory', $refs[0]['url']);
        $this->assertSame('example.test', $refs[0]['label']);
        $this->assertSame('https://nvd.nist.gov/vuln/detail/CVE-2026-0001', $refs[1]['url']);
        $this->assertSame('nvd.nist.gov', $refs[1]['label']);
    }

    public function testReferencesFromJsonDisambiguatesRepeatedDomainsWithPathPrefixes(): void
    {
        $refs = ServerCveImpactMatcher::referencesFromJson(json_encode([
            'referenceData' => [
                ['url' => 'https://www.oracle.com/security-alerts/cpujul2022.html#AppendixMSQL', 'source' => 'ignored-1'],
                ['url' => 'https://www.oracle.com/security-alerts/cpuapr2022.html#AppendixMSQL', 'source' => 'ignored-2'],
                ['url' => 'https://nvd.nist.gov/vuln/detail/CVE-2022-0001'],
            ],
        ]), 5);

        $this->assertSame('oracle.com/security-alerts/cpujul2022.html', $refs[0]['label']);
        $this->assertSame('oracle.com/security-alerts/cpuapr2022.html', $refs[1]['label']);
        $this->assertSame('nvd.nist.gov', $refs[2]['label']);
    }

    public function testProductsFromGroupConcatParsesParkTags(): void
    {
        $products = ServerCveImpactMatcher::productsFromGroupConcat(
            "mysql\tMySQL Server\t#e97b00\nproxysql\tProxySQL\t#1f2937"
        );

        $this->assertSame([
            ['product_code' => 'mysql', 'product_name' => 'MySQL Server', 'color' => '#e97b00'],
            ['product_code' => 'proxysql', 'product_name' => 'ProxySQL', 'color' => '#1f2937'],
        ], $products);
    }
}
