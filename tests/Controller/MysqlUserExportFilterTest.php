<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MysqlUserExportFilterTest extends TestCase
{
    public function testExportUsesIsRoleFilterForMariaDb10AndAbove(): void
    {
        $db = new FakeVersionedMysqlDb('MariaDB');

        $sql = \App\Controller\MysqlUser::getExportAccountsSql($db);

        $this->assertSame(
            "SELECT User as `user`,`Host` as `host` FROM mysql.user WHERE BINARY is_role = BINARY 'N' ORDER by user,host",
            $sql
        );
    }

    public function testExportDoesNotUseIsRoleFilterForMysqlAndPercona(): void
    {
        $mysql = new FakeVersionedMysqlDb('MySQL');
        $percona = new FakeVersionedMysqlDb('Percona Server');

        $expected = "SELECT User as `user`,`Host` as `host` FROM mysql.user ORDER by user,host";

        $this->assertSame($expected, \App\Controller\MysqlUser::getExportAccountsSql($mysql));
        $this->assertSame($expected, \App\Controller\MysqlUser::getExportAccountsSql($percona));
    }
}

final class FakeVersionedMysqlDb
{
    public function __construct(private readonly string $family)
    {
    }

    public function checkVersion(array $versions): bool
    {
        return array_key_exists($this->family, $versions);
    }
}
