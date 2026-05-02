<?php

declare(strict_types=1);

use App\Controller\Ssh;
use PHPUnit\Framework\TestCase;

final class SshTryAssociateSecurityTest extends TestCase
{
    public function testTryAssociateParamNormalizationRejectsInjectionPayloads(): void
    {
        self::assertSame(
            ['id_mysql_server' => 7, 'id_ssh_key' => 42],
            Ssh::normalizeTryAssociateParams(['07', '42'])
        );

        self::assertNull(Ssh::normalizeTryAssociateParams(['7 OR 1=1', '42']));
        self::assertNull(Ssh::normalizeTryAssociateParams(['7', '42 OR 1=1']));
        self::assertNull(Ssh::normalizeTryAssociateParams(['0', '42']));
        self::assertNull(Ssh::normalizeTryAssociateParams(['7', '0']));
        self::assertNull(Ssh::normalizeTryAssociateParams(['7']));
    }

    public function testTypedSqlBuildersDoNotAcceptRawRoutePayloads(): void
    {
        self::assertSame(
            'SELECT * FROM `mysql_server` WHERE `id`=7;',
            Ssh::buildMysqlServerByIdSql(7)
        );
        self::assertSame(
            'SELECT * FROM ssh_key WHERE id = 42',
            Ssh::buildSshKeyByIdSql(42)
        );
        self::assertSame('SELECT * FROM `ssh_key`', Ssh::buildSshKeysSql());
        self::assertSame('SELECT * FROM `ssh_key` WHERE id = 42', Ssh::buildSshKeysSql(42));
    }

    public function testLegacyTryAssociateSqlWasInjectableProof(): void
    {
        self::assertSame(
            'SELECT * FROM `mysql_server` WHERE `id`=7 OR 1=1;',
            $this->legacyMysqlServerSql('7 OR 1=1')
        );
        self::assertSame(
            'SELECT * FROM `ssh_key` WHERE `id`=42 OR 1=1;',
            $this->legacySshKeySql('42 OR 1=1')
        );
    }

    public function testGetSshKeysAndTryAssociateUseIntegerGuardsBeforeSql(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Ssh.php');
        $getSshKeys = self::extractMethodSource($controller, 'private function getSshKeys');
        $tryAssociate = self::extractMethodSource($controller, 'public function tryAssociate');

        self::assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $controller);
        self::assertStringContainsString('public static function normalizeSshKeyId', $controller);
        self::assertStringContainsString('public static function normalizeTryAssociateParams', $controller);
        self::assertStringContainsString('self::normalizeSshKeyId($id_ssh_key)', $getSshKeys);
        self::assertStringContainsString('self::buildSshKeysSql($normalizedSshKeyId)', $getSshKeys);
        self::assertStringContainsString('self::normalizeTryAssociateParams($param)', $tryAssociate);
        self::assertStringContainsString('self::buildMysqlServerByIdSql($id_mysql_server)', $tryAssociate);
        self::assertStringContainsString('self::buildSshKeyByIdSql($id_ssh_key)', $tryAssociate);
        self::assertStringContainsString("Debug::debug('Invalid SSH association identifiers'", $tryAssociate);
        self::assertStringContainsString("define('NET_SSH2_LOGGING', Debug::\$debug === true ? 2 : 0)", $tryAssociate);
        self::assertStringContainsString("if (Debug::\$debug === true) {\n            echo \$ssh->getLog();\n        }", $tryAssociate);

        self::assertStringNotContainsString('$id_mysql_server = $param[0]', $tryAssociate);
        self::assertStringNotContainsString('$id_ssh_key      = $param[1]', $tryAssociate);
        self::assertStringNotContainsString('WHERE `id`=".$id_mysql_server', $tryAssociate);
        self::assertStringNotContainsString('WHERE `id`=".$id_ssh_key', $tryAssociate);
        self::assertStringNotContainsString('$where = " WHERE id = ".$id_ssh_key;', $controller);
        self::assertStringNotContainsString("define('NET_SSH2_LOGGING', 2)", $tryAssociate);
        self::assertStringNotContainsString('Debug::debug(Chiffrement::decrypt($key[\'private_key\'])', $controller);
    }

    private function legacyMysqlServerSql(string $idMysqlServer): string
    {
        return 'SELECT * FROM `mysql_server` WHERE `id`='.$idMysqlServer.';';
    }

    private function legacySshKeySql(string $idSshKey): string
    {
        return 'SELECT * FROM `ssh_key` WHERE `id`='.$idSshKey.';';
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
