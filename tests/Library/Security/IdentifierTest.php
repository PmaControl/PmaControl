<?php

declare(strict_types=1);

use App\Library\Security\Identifier;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    public function testDatabaseNameAllowlist(): void
    {
        $this->assertTrue(Identifier::isDatabaseName('customer_db'));
        $this->assertTrue(Identifier::isDatabaseName('customer-db-01'));

        $this->assertFalse(Identifier::isDatabaseName(''));
        $this->assertFalse(Identifier::isDatabaseName(str_repeat('a', 65)));
        $this->assertFalse(Identifier::isDatabaseName('customer db'));
        $this->assertFalse(Identifier::isDatabaseName("customer'db"));
        $this->assertFalse(Identifier::isDatabaseName('customer`db'));
        $this->assertFalse(Identifier::isDatabaseName('customer$db'));
    }

    public function testNormalizeDatabaseNameList(): void
    {
        $this->assertSame(['app_db', 'log-db'], Identifier::normalizeDatabaseNameList(' app_db, log-db '));

        $this->assertNull(Identifier::normalizeDatabaseNameList(''));
        $this->assertNull(Identifier::normalizeDatabaseNameList('app_db,app_db'));
        $this->assertNull(Identifier::normalizeDatabaseNameList("app'db"));
        $this->assertNull(Identifier::normalizeDatabaseNameList(str_repeat('a', 65)));
    }

    public function testSqlAccountHostAndPrivilegeAllowlists(): void
    {
        $this->assertTrue(Identifier::isAccountName('app_user'));
        $this->assertTrue(Identifier::isAccountName(''));
        $this->assertFalse(Identifier::isAccountName("app'user"));

        $this->assertTrue(Identifier::isHostName('%'));
        $this->assertTrue(Identifier::isHostName('10.0.0.%'));
        $this->assertTrue(Identifier::isHostName('db-client.example.net'));
        $this->assertFalse(Identifier::isHostName("host'owned"));

        $this->assertTrue(Identifier::isPrivilegeName('SELECT'));
        $this->assertTrue(Identifier::isPrivilegeName('CREATE TEMPORARY TABLES'));
        $this->assertFalse(Identifier::isPrivilegeName('SELECT, DROP'));
    }

    public function testSafeAbsolutePathAllowlist(): void
    {
        $this->assertTrue(Identifier::isSafeAbsolutePath('/mysql/backup'));
        $this->assertTrue(Identifier::isSafeAbsolutePath('/srv/backups/mysql_01'));

        $this->assertFalse(Identifier::isSafeAbsolutePath('relative/path'));
        $this->assertFalse(Identifier::isSafeAbsolutePath('/mysql/../backup'));
        $this->assertFalse(Identifier::isSafeAbsolutePath('/mysql/backup;rm'));
        $this->assertFalse(Identifier::isSafeAbsolutePath('/mysql/backup with space'));
        $this->assertFalse(Identifier::isSafeAbsolutePath("/mysql/backup\0owned"));
    }
}
