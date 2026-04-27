<?php

declare(strict_types=1);

use App\Controller\Binlog;
use App\Library\Security\ByteSize;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class BinlogAddSecurityTest extends TestCase
{
    public function testBinlogAddAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'binlog.add');

        $outcome = Binlog::evaluateAddRequest(
            $this->binlogPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'id_mysql_server' => 12,
            'size_max' => 10737418240,
            'file_binlog_size' => 1073741824,
            'number_file_max' => 10,
        ], $outcome['binlog']);
    }

    public function testBinlogAddRejectsNonPost(): void
    {
        $outcome = Binlog::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['binlog']);
    }

    public function testBinlogAddRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'binlog.add');

        $outcome = Binlog::evaluateAddRequest(
            $this->binlogPost($token, ['mysql_server' => ['id' => '12 OR 1=1']]),
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['binlog']);
    }

    public function testBinlogAddRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'environment.add');
        $server = $this->sameSitePostServer();

        $missingToken = Binlog::evaluateAddRequest($this->binlogPost(null), $server, $session);
        $foreignScope = Binlog::evaluateAddRequest($this->binlogPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testBinlogAddPayloadRejectsMalformedValues(): void
    {
        $this->assertNull(Binlog::normalizeAddPayload([]));
        $this->assertNull(Binlog::normalizeAddPayload(['mysql_server' => ['id' => '12']]));
        $this->assertNull(Binlog::normalizeAddPayload($this->rawPost(['mysql_server' => ['id' => '12 OR 1=1']])));
        $this->assertNull(Binlog::normalizeAddPayload($this->rawPost(['mysql_server' => ['id' => ['12']]])));
        $this->assertNull(Binlog::normalizeAddPayload($this->rawPost(['binlog_max' => ['size' => '10T']])));
        $this->assertNull(Binlog::normalizeAddPayload($this->rawPost(['binlog_max' => ['size' => '0G']])));
        $this->assertNull(Binlog::normalizeAddPayload($this->rawPost(['variables' => ['file_binlog_size' => '0']])));
        $this->assertNull(Binlog::normalizeAddPayload($this->rawPost(['variables' => ['file_binlog_size' => '1073741824 OR 1=1']])));
        $this->assertNull(Binlog::normalizeAddPayload($this->rawPost(['binlog_max' => ['size' => '512M']])));
    }

    public function testByteSizeParsesSupportedBinlogUnitsStrictly(): void
    {
        $this->assertSame(1024, ByteSize::parse('1K'));
        $this->assertSame(1048576, ByteSize::parse('1M'));
        $this->assertSame(1073741824, ByteSize::parse('1G'));
        $this->assertSame(1610612736, ByteSize::parse('1.5G'));
        $this->assertSame(4096, ByteSize::parse('4096'));

        $this->assertNull(ByteSize::parse('1.5'));
        $this->assertNull(ByteSize::parse('10T'));
        $this->assertNull(ByteSize::parse(''));
        $this->assertNull(ByteSize::parse(['10G']));
    }

    public function testBinlogAddBuildsSqlOnlyFromNormalizedIntegers(): void
    {
        $this->assertSame(
            "REPLACE INTO binlog_max (`id_mysql_server`, `size_max`, `number_file_max`) VALUES ('12', '10737418240', '10')",
            Binlog::buildBinlogAddSql([
                'id_mysql_server' => 12,
                'size_max' => 10737418240,
                'number_file_max' => 10,
            ])
        );
    }

    public function testExternalPostWouldHaveReachedLegacyMutationButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'binlog.add');
        $post = $this->binlogPost($token, ['mysql_server' => ['id' => '12 OR 1=1']]);

        $this->assertSame(
            "REPLACE INTO binlog_max (`id_mysql_server`, `size_max`, `number_file_max`) VALUES ('12 OR 1=1', '10737418240', '10')",
            $this->legacyBinlogAddSql($post)
        );

        $outcome = Binlog::evaluateAddRequest(
            $post,
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['binlog']);
    }

    public function testBinlogAddUsesSharedSecurityLibraryAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Binlog.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Binlog/add.view.php');
        $request = file_get_contents(__DIR__ . '/../../App/Library/Security/BinlogAddRequest.php');
        $byteSize = file_get_contents(__DIR__ . '/../../App/Library/Security/ByteSize.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertIsString($request);
        $this->assertIsString($byteSize);

        $this->assertStringContainsString('use App\\Library\\Security\\BinlogAddRequest;', $controller);
        $this->assertStringContainsString("private const BINLOG_ADD_CSRF_SCOPE = 'binlog.add'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::BINLOG_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('BinlogAddRequest::evaluate(', $controller);
        $this->assertStringContainsString('BinlogAddRequest::buildReplaceSql(', $controller);

        $this->assertStringContainsString('$binlogAddCsrfField', $view);
        $this->assertStringContainsString('$binlogAddCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('method="post"', $view);

        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, $scope)', $request);
        $this->assertStringContainsString('GroupedFormRequest::normalize(', $request);
        $this->assertStringContainsString('ByteSize::parse(', $request);
        $this->assertStringContainsString('final class ByteSize', $byteSize);
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

    private function binlogPost(?string $token, array $overrides = []): array
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
            'mysql_server' => ['id' => '12'],
            'binlog_max' => ['size' => '10G'],
            'variables' => [
                'max_binlog_size' => '1.0 Go',
                'file_binlog_size' => '1073741824',
            ],
        ], $overrides);
    }

    private function legacyBinlogAddSql(array $post): string
    {
        $number = 10737418240;
        $maxFileToKeep = (int) ceil($number / (int) $post['variables']['file_binlog_size']);

        return "REPLACE INTO binlog_max (`id_mysql_server`, `size_max`, `number_file_max`) VALUES ('"
            . $post['mysql_server']['id'] . "', '" . $number . "', '" . $maxFileToKeep . "')";
    }
}
