<?php

declare(strict_types=1);

use App\Library\GtidSet;
use PHPUnit\Framework\TestCase;

final class GtidSetTest extends TestCase
{
    public function testMysqlSubtractKeepsHoles(): void
    {
        $source = GtidSet::parse('00000000-0000-0000-0000-000000000001:1-5:8:11-13');
        $replica = GtidSet::parse('00000000-0000-0000-0000-000000000001:1-13');

        $this->assertSame('mysql', $source->family());
        $this->assertSame('00000000-0000-0000-0000-000000000001:6-7:9-10', $replica->subtract($source)->toString());
        $this->assertSame(9, $source->count());
    }

    public function testMariaDbSubtractAndNormalize(): void
    {
        $source = GtidSet::parse('0-1-1-3,0-1-5,0-2-10');
        $replica = GtidSet::parse('0-1-1-6,0-2-10-12');

        $this->assertSame('mariadb', $source->family());
        $this->assertSame('0-1-4,0-1-6,0-2-11-12', $replica->subtract($source)->toString());
    }

    public function testMariaDbColonCompatibilityForm(): void
    {
        $source = GtidSet::parse('0-99-42:1-2');
        $replica = GtidSet::parse('0-99-42:1-3');

        $this->assertSame('0-99-42:3', $replica->subtract($source)->toString());
    }

    public function testCrossFamilyIsIncompatible(): void
    {
        $mysql = GtidSet::parse('00000000-0000-0000-0000-000000000001:1-10');
        $maria = GtidSet::parse('0-1-1-10');

        $this->assertFalse($mysql->isCompatibleWith($maria));
        $this->assertSame('(incompatible GTID families)', $mysql->subtract($maria)->toString());
    }

    public function testIntersectionCountsSharedTransactions(): void
    {
        $a = GtidSet::parse('0-1-1-10');
        $b = GtidSet::parse('0-1-5-12');

        $this->assertSame(6, $a->intersect($b)->count());
        $this->assertSame('0-1-5-10', $a->intersect($b)->toString());
    }
}
