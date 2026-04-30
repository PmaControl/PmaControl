<?php

declare(strict_types=1);

use App\Library\Security\Identifier;
use PHPUnit\Framework\TestCase;

final class IdentifierMysqlVariableNameTest extends TestCase
{
    public function testMysqlVariableNameAllowlistAcceptsKnownForms(): void
    {
        $this->assertTrue(Identifier::isMysqlVariableName('query_cache_size'));
        $this->assertTrue(Identifier::isMysqlVariableName('mysql-default_charset'));
        $this->assertTrue(Identifier::isMysqlVariableName('Innodb_status'));
        $this->assertTrue(Identifier::isMysqlVariableName(str_repeat('a', 128)));
    }

    public function testMysqlVariableNameAllowlistRejectsUnsafePayloads(): void
    {
        foreach ($this->invalidVariableNames() as $name) {
            $this->assertFalse(Identifier::isMysqlVariableName($name));
        }
    }

    public function testNormalizeMysqlVariableNameTrimsScalarsAndRejectsInvalidRawValues(): void
    {
        $this->assertSame('query_cache_size', Identifier::normalizeMysqlVariableName(' query_cache_size '));
        $this->assertSame('123', Identifier::normalizeMysqlVariableName(123));

        $this->assertNull(Identifier::normalizeMysqlVariableName(''));
        $this->assertNull(Identifier::normalizeMysqlVariableName(['query_cache_size']));
        $this->assertNull(Identifier::normalizeMysqlVariableName(new stdClass()));
        $this->assertNull(Identifier::normalizeMysqlVariableName('<script>alert(1)</script>'));
        $this->assertNull(Identifier::normalizeMysqlVariableName('" OR 1=1 -- '));
    }

    private function invalidVariableNames(): array
    {
        return [
            '',
            str_repeat('a', 129),
            'query cache size',
            'query.cache.size',
            '<script>alert(1)</script>',
            '" OR 1=1 -- ',
            "query_cache_size\0owned",
            'variablesé',
        ];
    }
}
