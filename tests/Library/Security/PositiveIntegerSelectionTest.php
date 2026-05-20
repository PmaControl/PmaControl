<?php

declare(strict_types=1);

use App\Library\Security\PositiveIntegerSelection;
use PHPUnit\Framework\TestCase;

final class PositiveIntegerSelectionTest extends TestCase
{
    public function testNormalizeSingleAcceptsPositiveIntegerScalars(): void
    {
        $this->assertSame(7, PositiveIntegerSelection::normalizeSingle('7'));
        $this->assertSame(7, PositiveIntegerSelection::normalizeSingle(' 7 '));
        $this->assertSame(7, PositiveIntegerSelection::normalizeSingle(7));
        $this->assertSame(7, PositiveIntegerSelection::normalizeSingle('007'));
    }

    public function testNormalizeSingleRejectsSqlPayloadsAndNonPositiveValues(): void
    {
        $this->assertNull(PositiveIntegerSelection::normalizeSingle(''));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle('0'));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle('-1'));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle('1 OR 1=1'));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle('1; DROP TABLE menu'));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle("1\r\nLocation: https://evil.test"));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle(['1']));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle(null));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle((string) PHP_INT_MAX . '0'));
    }

    public function testNormalizeSingleHonorsMaxValue(): void
    {
        $this->assertSame(10, PositiveIntegerSelection::normalizeSingle('10', 10));
        $this->assertNull(PositiveIntegerSelection::normalizeSingle('11', 10));
    }

    public function testNormalizeListStillRejectsUnsafeValuesThroughSharedSingleNormalizer(): void
    {
        $this->assertSame([1, 2], PositiveIntegerSelection::normalizeList('1,2'));
        $this->assertNull(PositiveIntegerSelection::normalizeList('1,2 OR 1=1'));
        $this->assertNull(PositiveIntegerSelection::normalizeList((string) PHP_INT_MAX . '0'));
    }
}
