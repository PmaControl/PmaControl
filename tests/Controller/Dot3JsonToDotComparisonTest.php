<?php

declare(strict_types=1);

use App\Controller\Dot3;
use PHPUnit\Framework\TestCase;

if (!defined('LINK')) {
    define('LINK', '/');
}
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}
if (!function_exists('__')) {
    function __($value, $lang = '')
    {
        return $value;
    }
}

/**
 * Regression coverage for issue #732.
 *
 * Feeds a `dot3_information.information` JSON fixture into Dot3::buildServer +
 * Dot3::buildLink and asserts that the resulting DOT (as produced by
 * Dot3::writeDot) contains the expected nodes and replication edges.
 *
 * The test deliberately exercises the JSON → DOT mapping in isolation:
 *  - the JSON-with-monitoring-data case must produce a populated DOT
 *  - the JSON-stripped-of-monitoring-data case (post-#642 symptom) must produce
 *    an empty DOT, which is exactly what was hitting /architecture/index.
 */
final class Dot3JsonToDotComparisonTest extends TestCase
{
    private const DOT3_INFORMATION_ID = 990732;
    private const FIXTURE_PATH = __DIR__ . '/fixtures/dot3_information_master_slave.json';

    private array $originalConfig = [];
    private array $originalBuildServer = [];
    private array $originalBuildMs = [];
    private array $originalBuildGalera = [];
    private array $originalBuildInnoDB = [];
    private array $originalRankSame = [];
    private array $originalInformation = [];

    protected function setUp(): void
    {
        $this->originalConfig = Dot3::$config;
        $this->originalBuildServer = Dot3::$build_server;
        $this->originalBuildMs = Dot3::$build_ms;
        $this->originalBuildGalera = Dot3::$build_galera;
        $this->originalBuildInnoDB = Dot3::$build_innodb_cluster;
        $this->originalRankSame = Dot3::$rank_same;
        $this->originalInformation = Dot3::$information;

        Dot3::$config = array_merge(Dot3::$config, [
            'NODE_OK' => [
                'background' => '#FFFFFF',
                'color' => '#000000',
                'options' => [],
            ],
            'NODE_ERROR' => [
                'background' => '#FFCCCC',
                'color' => '#FF0000',
                'options' => [],
            ],
            'NODE_BUSY' => [
                'background' => '#FFFF99',
                'color' => '#999900',
                'options' => [],
            ],
            'REPLICATION_OK' => [
                'color' => '#008000',
                'style' => 'filled',
                'options' => [],
            ],
            'SERVER_CONFIG' => [
                'background' => '#FFFFFF',
                'color' => '#000000',
            ],
        ]);

        Dot3::$build_server = [];
        Dot3::$build_ms = [];
        Dot3::$build_galera = [];
        Dot3::$build_innodb_cluster = [];
        Dot3::$rank_same = [];
        Dot3::$id_dot3_information = self::DOT3_INFORMATION_ID;

        $payload = $this->loadFixture();
        Dot3::$information[self::DOT3_INFORMATION_ID] = ['information' => $payload];
    }

    protected function tearDown(): void
    {
        Dot3::$config = $this->originalConfig;
        Dot3::$build_server = $this->originalBuildServer;
        Dot3::$build_ms = $this->originalBuildMs;
        Dot3::$build_galera = $this->originalBuildGalera;
        Dot3::$build_innodb_cluster = $this->originalBuildInnoDB;
        Dot3::$rank_same = $this->originalRankSame;
        Dot3::$information = $this->originalInformation;
        Dot3::$id_dot3_information = null;
    }

    public function testJsonWithMonitoringDataProducesPopulatedBuildState(): void
    {
        $dot3 = $this->newDot3();
        $group = [1, 2, 3];

        $dot3->buildServer([self::DOT3_INFORMATION_ID, $group]);
        $dot3->buildLink([self::DOT3_INFORMATION_ID, $group]);

        $this->assertCount(3, Dot3::$build_server, 'All 3 servers from the JSON should map to nodes.');
        $this->assertSame([1, 2, 3], array_keys(Dot3::$build_server));
        $this->assertSame('db-master-1', Dot3::$build_server[1]['display_name']);
        $this->assertSame('1', Dot3::$build_server[2]['mysql_available']);

        $this->assertCount(2, Dot3::$build_ms, 'Both replicas should produce one replication edge each.');
        $arrows = array_column(Dot3::$build_ms, 'arrow');
        sort($arrows);
        $this->assertSame(
            ['1:target -> 2:target', '1:target -> 3:target'],
            $arrows,
            'Replication edges must come from the JSON @slave→master_host:master_port mapping.'
        );
    }

    public function testJsonWithMonitoringDataProducesDotContainingEachServer(): void
    {
        $dot3 = $this->newDot3();
        $group = [1, 2, 3];

        $dot3->buildServer([self::DOT3_INFORMATION_ID, $group]);
        $dot3->buildLink([self::DOT3_INFORMATION_ID, $group]);

        $dot = $dot3->writeDot();

        $this->assertStringStartsWith('digraph structs', $dot, 'Generated DOT should be a digraph structs document.');
        $this->assertStringContainsString('db-master-1', $dot);
        $this->assertStringContainsString('db-replica-2', $dot);
        $this->assertStringContainsString('db-replica-3', $dot);
        $this->assertStringContainsString('1:target -> 2:target', $dot);
        $this->assertStringContainsString('1:target -> 3:target', $dot);
    }

    /**
     * Symptom of #732: when ServerFilterWhere::build returned ' AND 0=1' for
     * any server list above 64 IDs, Extraction2::display returned [] and the
     * dot3 daemon stored a JSON without `version` / `mysql_available` for any
     * server. Dot3::buildServer then skipped every server, so no node block
     * was emitted in the DOT — which is what the architecture page rendered
     * as an 11×11 px empty cluster.
     */
    public function testJsonStrippedOfMonitoringFieldsProducesDotWithoutAnyServerNode(): void
    {
        $payload = $this->loadFixture();
        foreach ($payload['servers'] as $id => &$server) {
            unset($server['version'], $server['mysql_available'], $server['mysql_error'], $server['@slave']);
        }
        unset($server);
        Dot3::$information[self::DOT3_INFORMATION_ID]['information'] = $payload;

        $dot3 = $this->newDot3();
        $group = [1, 2, 3];

        $dot3->buildServer([self::DOT3_INFORMATION_ID, $group]);
        $dot3->buildLink([self::DOT3_INFORMATION_ID, $group]);

        $this->assertSame(
            [],
            Dot3::$build_server,
            'No server should be promoted to a DOT node when monitoring data is missing — '
            . 'this is the exact condition reproduced in production after issue #642 capped '
            . 'the server filter at 64 IDs and broke Dot3 extraction.'
        );
        $this->assertSame([], Dot3::$build_ms, 'No replication edge should be emitted when @slave is also missing.');

        $dot = $dot3->writeDot();

        $this->assertStringContainsString('digraph structs', $dot);
        $this->assertStringNotContainsString('db-master-1', $dot, 'Empty build_server must produce a DOT without server labels.');
        $this->assertStringNotContainsString('db-replica-2', $dot);
        $this->assertStringNotContainsString('db-replica-3', $dot);
        $this->assertStringNotContainsString('->', $dot, 'Empty build_ms must produce a DOT without edges.');
    }

    private function loadFixture(): array
    {
        $raw = (string) file_get_contents(self::FIXTURE_PATH);
        $decoded = json_decode($raw, true);

        $this->assertIsArray($decoded, 'Fixture JSON must decode to an array.');
        $this->assertArrayHasKey('servers', $decoded);
        $this->assertArrayHasKey('mapping', $decoded);

        return $decoded;
    }

    private function newDot3(): Dot3
    {
        return (new ReflectionClass(Dot3::class))->newInstanceWithoutConstructor();
    }
}
