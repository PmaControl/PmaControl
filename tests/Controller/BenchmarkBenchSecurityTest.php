<?php

declare(strict_types=1);

use App\Controller\Benchmark;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class BenchmarkBenchSecurityTest extends TestCase
{
    private const ALLOWED_MODES = ['oltp_read_write.lua', 'oltp_read_only.lua'];

    public function testBenchmarkBenchAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'benchmark.bench');

        $outcome = Benchmark::evaluateBenchRequest(
            $this->benchmarkPost($token),
            $this->sameSitePostServer(),
            $session,
            self::ALLOWED_MODES
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'server_ids' => [11, 12],
            'threads' => [1, 16],
            'threads_csv' => '1,16',
            'tables_count' => 4,
            'table_size' => 4,
            'modes' => ['oltp_read_write.lua'],
            'max_time' => 60,
        ], $outcome['benchmark']);
    }

    public function testBenchmarkBenchRejectsNonPost(): void
    {
        $outcome = Benchmark::evaluateBenchRequest([], ['REQUEST_METHOD' => 'GET'], [], self::ALLOWED_MODES);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['benchmark']);
    }

    public function testBenchmarkBenchRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'benchmark.bench');

        $outcome = Benchmark::evaluateBenchRequest(
            $this->benchmarkPost($token, ['mysql_server' => ['id' => ['11 OR 1=1']]]),
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            self::ALLOWED_MODES
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['benchmark']);
    }

    public function testBenchmarkBenchRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'benchmark.graph');
        $server = $this->sameSitePostServer();

        $missingToken = Benchmark::evaluateBenchRequest($this->benchmarkPost(null), $server, $session, self::ALLOWED_MODES);
        $foreignScope = Benchmark::evaluateBenchRequest($this->benchmarkPost($foreignToken), $server, $session, self::ALLOWED_MODES);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testBenchmarkBenchPayloadRejectsMalformedValues(): void
    {
        foreach ($this->invalidPosts() as $post) {
            $this->assertNull(Benchmark::normalizeBenchPayload($post, self::ALLOWED_MODES));
        }
    }

    public function testBenchmarkBenchRejectsEmptyModeWhitelist(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'benchmark.bench');

        $this->assertNull(Benchmark::normalizeBenchPayload($this->rawPost(), []));

        $outcome = Benchmark::evaluateBenchRequest(
            $this->benchmarkPost($token),
            $this->sameSitePostServer(),
            $session,
            []
        );

        $this->assertSame(400, $outcome['status']);
        $this->assertSame('Invalid benchmark bench payload', $outcome['body']);
        $this->assertNull($outcome['benchmark']);
    }

    public function testBenchmarkBenchBuildsInsertSqlFromNormalizedPayload(): void
    {
        $payload = Benchmark::normalizeBenchPayload($this->rawPost(), self::ALLOWED_MODES);

        $this->assertIsArray($payload);
        $this->assertSame(
            "INSERT INTO benchmark_main\n"
            . "                            SET id_mysql_server = '11',\n"
            . "                            id_user_main = '5',\n"
            . "                            date = '2026-04-27 12:00:00',\n"
            . "                            sysbench_version = '1.0.20',\n"
            . "                            threads = '1,16',\n"
            . "                            tables_count = '4',\n"
            . "                            table_size = '4',\n"
            . "                            mode = 'oltp_read_write.lua',\n"
            . "                            max_time = '60',\n"
            . "                            status = 'NOT STARTED',\n"
            . "                            date_start='0000-00-00 00:00:00',\n"
            . "                            date_end='0000-00-00 00:00:00',\n"
            . "                            progression=0\n"
            . "                            ",
            Benchmark::buildBenchInsertSql($payload, 11, 'oltp_read_write.lua', 5, '1.0.20', '2026-04-27 12:00:00')
        );
    }

    public function testExternalPostWouldHaveReachedLegacyInsertButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'benchmark.bench');
        $post = $this->benchmarkPost($token, [
            'mysql_server' => ['id' => ['11 OR 1=1']],
            'benchmark_main' => ['mode' => ["oltp_read_write.lua', status='RUNNING"]],
        ]);

        $this->assertStringContainsString("11 OR 1=1", $this->legacyBenchmarkInsertSql($post));
        $this->assertStringContainsString("status='RUNNING", $this->legacyBenchmarkInsertSql($post));

        $outcome = Benchmark::evaluateBenchRequest(
            $post,
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            self::ALLOWED_MODES
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['benchmark']);
    }

    public function testBenchmarkBenchUsesSharedSecurityLibraryAndViewSendsToken(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Benchmark.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Benchmark/bench.view.php');
        $request = (string) file_get_contents(__DIR__ . '/../../App/Library/Security/BenchmarkBenchRequest.php');

        $benchStart = strpos($controller, 'public function bench($param)');
        $evaluateStart = strpos($controller, 'public static function evaluateBenchRequest');
        $benchBody = substr($controller, $benchStart, $evaluateStart - $benchStart);

        $this->assertStringContainsString('use App\\Library\\Security\\BenchmarkBenchRequest;', $controller);
        $this->assertStringContainsString("private const BENCHMARK_BENCH_CSRF_SCOPE = 'benchmark.bench'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::BENCHMARK_BENCH_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('self::evaluateBenchRequest($_POST, $_SERVER, $_SESSION, $this->getLua())', $benchBody);
        $this->assertStringContainsString('self::buildBenchInsertSql(', $benchBody);
        $this->assertStringNotContainsString('id_mysql_server = \'" . $id_mysql_server', $benchBody);
        $this->assertStringNotContainsString('threads = \'" . implode(\',\', $_POST', $benchBody);

        $this->assertStringContainsString('$benchmarkBenchCsrfField', $view);
        $this->assertStringContainsString('$benchmarkBenchCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('method="post"', $view);

        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, $scope)', $request);
        $this->assertStringContainsString('PositiveIntegerSelection::normalizeList($post[\'mysql_server\'][\'id\'], 64)', $request);
        $this->assertStringContainsString('GroupedFormRequest::normalize($post, \'benchmark_main\', self::benchmarkMainRules())', $request);
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }

    private function benchmarkPost(?string $token, array $overrides = []): array
    {
        $post = $this->rawPost($overrides);
        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function rawPost(array $overrides = []): array
    {
        return array_replace_recursive([
            'benchmark' => '1',
            'mysql_server' => ['id' => ['11', '12']],
            'benchmark_main' => [
                'threads' => ['1', '16'],
                'tables_count' => '4',
                'mode' => ['oltp_read_write.lua'],
                'max_time' => '60',
            ],
        ], $overrides);
    }

    private function invalidPosts(): array
    {
        return [
            [],
            $this->rawPost(['benchmark' => '0']),
            $this->rawPost(['mysql_server' => ['id' => ['11 OR 1=1']]]),
            $this->rawPost(['mysql_server' => ['id' => ['0']]]),
            $this->rawPost(['mysql_server' => ['id' => [['11']]]]),
            $this->rawPost(['benchmark_main' => ['threads' => ['0']]]),
            $this->rawPost(['benchmark_main' => ['threads' => ['1025']]]),
            $this->rawPost(['benchmark_main' => ['threads' => ['1 OR 1=1']]]),
            $this->rawPost(['benchmark_main' => ['tables_count' => '0']]),
            $this->rawPost(['benchmark_main' => ['tables_count' => '101']]),
            $this->rawPost(['benchmark_main' => ['max_time' => '-1']]),
            $this->rawPost(['benchmark_main' => ['max_time' => '300']]),
            $this->rawPost(['benchmark_main' => ['mode' => ['../owned.lua']]]),
            $this->rawPost(['benchmark_main' => ['mode' => ["oltp_read_write.lua'"]]]),
            $this->rawPost(['benchmark_main' => ['mode' => ['not-allowed.lua']]]),
            $this->rawPost(['benchmark_main' => ['mode' => ['oltp_read_write.lua'], 'extra' => '1']]),
        ];
    }

    private function legacyBenchmarkInsertSql(array $post): string
    {
        $mode = $post['benchmark_main']['mode'][0];
        $idMysqlServer = $post['mysql_server']['id'][0];

        return "INSERT INTO benchmark_main SET id_mysql_server = '" . $idMysqlServer
            . "', threads = '" . implode(',', $post['benchmark_main']['threads'])
            . "', tables_count = '" . $post['benchmark_main']['tables_count']
            . "', table_size = '" . $post['benchmark_main']['tables_count']
            . "', mode = '" . $mode
            . "', max_time = '" . $post['benchmark_main']['max_time'] . "'";
    }
}
