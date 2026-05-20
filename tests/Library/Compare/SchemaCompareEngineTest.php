<?php

declare(strict_types=1);

use App\Library\Compare\SchemaCompareEngine;
use PHPUnit\Framework\TestCase;

final class SchemaCompareEngineTest extends TestCase
{
    public function testEngineCarriesSystemVersionedTableSupport(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../../App/Library/Compare/SchemaCompareEngine.php');

        $this->assertStringContainsString("TABLE_TYPE='SYSTEM VERSIONED'", $source);
        $this->assertStringContainsString("TABLE_TYPE='BASE TABLE'", $source);
    }

    public function testCompareObjectUsesExplicitMenuArgumentInsteadOfSuperglobal(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../../App/Library/Compare/SchemaCompareEngine.php');
        $methodStart = strpos($source, 'public function compareObject(');
        $methodEnd = strpos($source, 'public function getDbLinkFromId', $methodStart);

        $this->assertNotFalse($methodStart);
        $this->assertNotFalse($methodEnd);

        $methodBody = substr($source, $methodStart, $methodEnd - $methodStart);

        $this->assertStringContainsString('string $menu', $methodBody);
        $this->assertStringNotContainsString('$_GET', $methodBody);
        $this->assertStringContainsString('$query[$menu]', $methodBody);
    }

    public function testExecMultiRejectsNonArrayQueriesBeforeUsingDbLink(): void
    {
        $engine = new SchemaCompareEngine();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('PMACTRL-652');

        $engine->execMulti('SHOW TABLES', null);
    }

    public function testEmptyExecMultiReturnsEmptyArrayWithoutDbLinkCall(): void
    {
        $engine = new SchemaCompareEngine();

        $this->assertSame([], $engine->execMulti([], null));
    }
}
