<?php

namespace Tests\Controller;

use PHPUnit\Framework\TestCase;

class AspirateurVipServerUpsertTest extends TestCase
{
    public function testVipServerUpsertDoesNotUseLockingReadOnSystemVersionedTable(): void
    {
        $source = file_get_contents(__DIR__.'/../../App/Controller/Aspirateur.php');
        $this->assertIsString($source);

        $methodStart = strpos($source, 'private function upsertVipServerRoute');
        $this->assertIsInt($methodStart);

        $methodEnd = strpos($source, '/**', $methodStart + 1);
        $this->assertIsInt($methodEnd);

        $methodSource = substr($source, $methodStart, $methodEnd - $methodStart);

        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $methodSource);
        $this->assertStringNotContainsString('FOR UPDATE', strtoupper($methodSource));
    }
}
