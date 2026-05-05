<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MysqlGetAliasSecurityTest extends TestCase
{
    public function testGetAliasUsesSharedResolverWithoutShell(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Mysql.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('use App\\Library\\Network\\HostResolver;', $controller);

        $getAliasStart = strpos($controller, 'public function getAlias($param)');
        $getColorStart = strpos($controller, 'public function getColor', (int) $getAliasStart);

        $this->assertNotFalse($getAliasStart);
        $this->assertNotFalse($getColorStart);

        $getAliasBody = substr($controller, (int) $getAliasStart, (int) $getColorStart - (int) $getAliasStart);

        $this->assertStringContainsString('HostResolver::resolveIpv4Addresses($masterHost)', $getAliasBody);
        $this->assertStringContainsString('$default->sql_save($data)', $getAliasBody);
        $this->assertStringContainsString('filter_var($masterHost, FILTER_VALIDATE_IP)', $getAliasBody);
        $this->assertStringNotContainsString('shell_exec', $getAliasBody);
        $this->assertStringNotContainsString('getent hosts', $getAliasBody);
        $this->assertStringNotContainsString("awk '{print $1}'", $getAliasBody);
        $this->assertStringNotContainsString('!filter_var($ip_destination, FILTER_VALIDATE_IP)', $getAliasBody);
    }
}
