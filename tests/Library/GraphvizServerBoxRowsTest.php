<?php

declare(strict_types=1);

use App\Controller\Dot3;
use App\Library\Graphviz;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}

if (!defined('LINK')) {
    define('LINK', '/');
}

if (!function_exists('__')) {
    function __($value)
    {
        return $value;
    }
}

final class GraphvizServerBoxRowsTest extends TestCase
{
    private const DOT3_INFORMATION_ID = 990001;

    protected function setUp(): void
    {
        $fixture = json_decode((string) file_get_contents(ROOT . '/tests/fixtures/legacy_dot3_box_rows.json'), true, 512, JSON_THROW_ON_ERROR);

        Dot3::$id_dot3_information = self::DOT3_INFORMATION_ID;
        Dot3::$information[self::DOT3_INFORMATION_ID] = $fixture;
    }

    protected function tearDown(): void
    {
        unset(Dot3::$information[self::DOT3_INFORMATION_ID]);
        Dot3::$id_dot3_information = null;
    }

    #[DataProvider('firstTwoBoxLinesProvider')]
    public function testExtractedFirstTwoBoxLinesForAllVariants(
        string $serverId,
        string $expectedTitle,
        string $expectedLine1,
        string $expectedLine2
    ): void
    {
        $server = Dot3::$information[self::DOT3_INFORMATION_ID]['information']['servers'][$serverId];

        $dot = Graphviz::generateServer($server);
        $lines = $this->extractFirstBoxLines($dot);

        $this->assertSame($expectedTitle, $lines['title']);
        $this->assertSame($expectedLine1, $lines['line_1']);
        $this->assertSame($expectedLine2, $lines['line_2']);
    }

    public function testGenerateServerRendersMysqlBoxHeaderRowsFromFixture(): void
    {
        $server = Dot3::$information[self::DOT3_INFORMATION_ID]['information']['servers']['101'];

        $dot = Graphviz::generateServer($server);

        $this->assertStringContainsString('<b>mariadb-11-1</b>', $dot);
        $this->assertStringContainsString('>MariaDB 11.1<', $dot);
        $this->assertStringContainsString('> 10.68.68.105:61101<', $dot);
    }

    public function testGenerateServerRendersTunnelAddressOnSecondDataRow(): void
    {
        $server = Dot3::$information[self::DOT3_INFORMATION_ID]['information']['servers']['112'];

        $dot = Graphviz::generateServer($server);

        $this->assertStringContainsString('<b>FRDC1-DR-DTA01L</b>', $dot);
        $this->assertStringContainsString('>MariaDB DR<', $dot);
        $this->assertStringContainsString('> 🔀192.168.114.104:3306<', $dot);
    }

    public function testGenerateServerRendersProxySqlBoxWithReachableIp(): void
    {
        $server = Dot3::$information[self::DOT3_INFORMATION_ID]['information']['servers']['144'];

        $dot = Graphviz::generateServer($server);

        $this->assertStringContainsString('<b>dc-prd-proxysql-01</b>', $dot);
        $this->assertStringContainsString('>ProxySQL 2.7<', $dot);
        $this->assertStringContainsString('> 10.68.68.111:6033<', $dot);
        $this->assertStringNotContainsString('proxysql-admin-1:6033', $dot);
    }

    public static function firstTwoBoxLinesProvider(): array
    {
        return [
            'mysql' => ['1', 'pmacontrol', 'MariaDB : 10.11.16', '127.0.0.1'],
            'mariadb' => ['101', 'mariadb-11-1', 'MariaDB 11.1', '10.68.68.105'],
            'percona' => ['145', 'percona-1', 'Percona : 8.0.35', '10.50.60.70'],
            'proxy' => ['144', 'dc-prd-proxysql-01', 'ProxySQL 2.7', '10.68.68.111'],
            'mysql routeur' => ['148', 'prod-router-rw', 'MySQL Router', '10.20.30.40'],
            'singlestore' => ['149', 'single-store-1', 'SingleStore : 9.0.1', '10.30.40.50'],
            'group replication sans tunnel' => ['146', 'gr-node-1', 'MariaDB : 10.6.23', '10.60.70.80'],
            'group replication avec tunnel' => ['147', 'gr-node-2', 'MariaDB : 10.6.23', '10.60.70.81'],
        ];
    }

    private function extractFirstBoxLines(string $dot): array
    {
        preg_match_all('/<td\b[^>]*>(.*?)<\/td>/si', $dot, $matches);

        $lines = [];
        foreach ($matches[1] as $cell) {
            $text = trim(html_entity_decode(strip_tags($cell), ENT_QUOTES | ENT_HTML5));
            if ($text === '') {
                continue;
            }

            $text = preg_replace('/\s+/', ' ', $text);
            $lines[] = trim((string) $text);
        }

        $versionLine = $lines[1] ?? '';
        $addressLine = $lines[2] ?? '';
        $addressLine = preg_replace('/^🔀\s*/u', '', $addressLine);
        $host = $addressLine;
        if (preg_match('/^\[([^\]]+)\]:(\d+)$/', $addressLine, $ipv6Match)) {
            $host = $ipv6Match[1];
        } elseif (preg_match('/^(.+):(\d+)$/', $addressLine, $match)) {
            $host = $match[1];
        }

        return [
            'title' => $lines[0] ?? '',
            'line_1' => $versionLine,
            'line_2' => trim((string) $host),
            'raw_lines' => $lines,
        ];
    }
}
