<?php
use PHPUnit\Framework\TestCase;
use App\Controller\Dot3;

class Dot3Test extends TestCase
{
    public function setUp(): void
    {
        dot3::$id_dot3_information = 1;
    }

    public function testMissingDot3InformationId()
    {
        dot3::$id_dot3_information = null;
        $this->expectExceptionMessageMatches('/PMACONTROL-1000/');
        dot3::getTunnel(['test' => 'value']);
    }

    public function testInvalidParameter()
    {
        $this->expectExceptionMessageMatches('/PMACONTROL-1001/');
        dot3::getTunnel([]);
    }
}