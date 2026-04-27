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
