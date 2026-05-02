<?php

declare(strict_types=1);

use App\Controller\Monitoring;
use PHPUnit\Framework\TestCase;

final class MonitoringExplainSecurityTest extends TestCase
{
    public function testExplainRequestNormalizesServerIdAndDigest(): void
    {
        $outcome = Monitoring::evaluateExplainRequest(
            [
                'mysql_server' => ['id' => '007'],
                'digest' => '0123456789abcdef0123456789abcdef',
            ],
            ['REQUEST_METHOD' => 'GET']
        );

        self::assertTrue($outcome['allowed']);
        self::assertSame(7, $outcome['id_mysql_server']);
        self::assertSame('0123456789ABCDEF0123456789ABCDEF', $outcome['digest']);
    }

    public function testExplainRequestRejectsInvalidMethodServerIdAndDigest(): void
    {
        $validGet = [
            'mysql_server' => ['id' => '7'],
            'digest' => '0123456789abcdef0123456789abcdef',
        ];

        $post = Monitoring::evaluateExplainRequest($validGet, ['REQUEST_METHOD' => 'POST']);
        self::assertFalse($post['allowed']);
        self::assertSame(405, $post['status']);
        self::assertSame('GET', $post['headers']['Allow']);

        $badServer = Monitoring::evaluateExplainRequest(
            ['mysql_server' => ['id' => '7 OR 1=1'], 'digest' => $validGet['digest']],
            ['REQUEST_METHOD' => 'GET']
        );
        self::assertFalse($badServer['allowed']);
        self::assertSame(400, $badServer['status']);

        $badDigest = Monitoring::evaluateExplainRequest(
            ['mysql_server' => ['id' => '7'], 'digest' => "0123456789abcdef' OR '1'='1"],
            ['REQUEST_METHOD' => 'GET']
        );
        self::assertFalse($badDigest['allowed']);
        self::assertSame(400, $badDigest['status']);
    }

    public function testExplainSqlBuildersUseTypedInputs(): void
    {
        self::assertSame(
            'SELECT * FROM mysql_server where id= 7',
            Monitoring::buildExplainServerSql(7)
        );
        self::assertSame(
            "select * from performance_schema.events_statements_history_long where DIGEST='0123456789ABCDEF'",
            Monitoring::buildExplainDigestSql('0123456789ABCDEF')
        );
    }

    public function testLegacyExplainSqlWasInjectableProof(): void
    {
        self::assertSame(
            'SELECT * FROM mysql_server where id= 7 OR 1=1',
            $this->legacyServerSql('7 OR 1=1')
        );
        self::assertSame(
            "select * from performance_schema.events_statements_history_long where DIGEST='abc' OR '1'='1'",
            $this->legacyDigestSql("abc' OR '1'='1")
        );
    }

    public function testExplainControllerUsesValidatedRequestBeforeSql(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Monitoring.php');
        $explain = self::extractMethodSource($controller, 'public function explain');

        self::assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $controller);
        self::assertStringContainsString('evaluateExplainRequest($_GET, $_SERVER)', $explain);
        self::assertStringContainsString('buildExplainServerSql($idMysqlServer)', $explain);
        self::assertStringContainsString('buildExplainDigestSql($digest)', $explain);
        self::assertStringContainsString('sendExplainError($outcome)', $explain);
        self::assertStringNotContainsString('$_GET[\'mysql_server\'][\'id\']', $explain);
        self::assertStringNotContainsString('$_GET[\'digest\']', $explain);
        self::assertStringNotContainsString('where DIGEST=\'".$_GET', $controller);
        self::assertStringNotContainsString('where id= ".$_GET', $controller);
    }

    private function legacyServerSql(string $idMysqlServer): string
    {
        return 'SELECT * FROM mysql_server where id= '.$idMysqlServer;
    }

    private function legacyDigestSql(string $digest): string
    {
        return "select * from performance_schema.events_statements_history_long where DIGEST='".$digest."'";
    }

    private static function extractMethodSource(string $source, string $signature): string
    {
        $start = strpos($source, $signature);
        self::assertIsInt($start);

        $openBrace = strpos($source, '{', $start);
        self::assertIsInt($openBrace);

        $depth = 0;
        $length = strlen($source);
        for ($i = $openBrace; $i < $length; $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $start, $i - $start + 1);
                }
            }
        }

        self::fail('Unable to extract method source for ' . $signature);
    }
}
