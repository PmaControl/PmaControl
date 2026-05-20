<?php

declare(strict_types=1);

use App\Library\Ofuscate;
use PHPUnit\Framework\TestCase;

final class OfuscateTest extends TestCase
{
    public function testIpCurrentlyReturnsOriginalValue(): void
    {
        $this->assertSame('192.168.1.10', Ofuscate::ip('192.168.1.10'));
        $this->assertSame('not-an-ip', Ofuscate::ip('not-an-ip'));
    }
}
