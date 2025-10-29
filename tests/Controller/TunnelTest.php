<?php
use PHPUnit\Framework\TestCase;
use App\Controller\Tunnel; 

class TunnelTest extends TestCase
{
    private Tunnel $parser;

    protected function setUp(): void
    {
        $this->parser = new Tunnel("","","");
    }

    
    public function testParseLocalTunnelWithRemoteIp()
    {
        $line = "root 1234 Tue Oct 14 01:32:49 2025 ? 00:00:00 ssh -i id_rsa -fN -L 8902:192.168.1.5:8989 -o ExitOnForwardFailure=yes";
        $result = $this->parser->parse([$line]);

        $this->assertEquals('L', $result['type']);
        $this->assertEquals('127.0.0.1', $result['local_host']);
        $this->assertEquals(8902, $result['local_port']);
        $this->assertEquals('192.168.1.5', $result['remote_host']);
        $this->assertEquals(8989, $result['remote_port']);

    }

    public function testParseLocalTunnelWithoutLocalIpUsesFinalIp()
    {
        $line = "root 5678 Tue Oct 14 01:32:49 2025 ? 00:00:00 ssh -i id_rsa -fN -L 8902:127.0.0.1:8989 -J user@212.234.81.130 user@192.168.114.22";
        $result = $this->parser->parse([$line]);

        $this->assertEquals('L', $result['type']);
        $this->assertEquals('127.0.0.1', $result['local_host']);
        $this->assertEquals(8902, $result['local_port']);
        $this->assertEquals('192.168.114.22', $result['remote_host']);
        $this->assertEquals(8989, $result['remote_port']);
        $this->assertCount(1, $result['jump_hosts']);
        $this->assertEquals('212.234.81.130', $result['jump_hosts'][0]['ip']);
        $this->assertEquals(22, $result['jump_hosts'][0]['port']);
    }

    public function testParseRemoteTunnel()
    {
        $line = "user 1111 Tue Oct 14 01:32:49 2025 ? 00:00:00 ssh -fN -R 3307:127.0.0.1:3306 remote@10.0.0.10";
        $result = $this->parser->parse([$line]);

        $this->assertEquals('R', $result['type']);
        $this->assertEquals(3307, $result['remote_port']);
        $this->assertEquals(3306, $result['local_port']);
        $this->assertEquals('10.0.0.10', $result['remote_host']);
    }

    public function testParseDynamicTunnel()
    {
        $line = "user 2222 Tue Oct 14 01:32:49 2025 ? 00:00:00 ssh -fN -D 1080 somehost";
        $result = $this->parser->parse([$line]);

        $this->assertEquals('D', $result['type']);
        $this->assertEquals(1080, $result['local_port']);
        $this->assertEquals('127.0.0.1', $result['local_host']);
    }

    public function testParseInvalidLineReturnsNull()
    {
        $line = "root 9999 Tue Oct 14 01:32:49 2025 ? 00:00:00 echo hello";
        $this->assertNull($this->parser->parse([$line]));
    }

    public function testParseIncompleteLineReturnsNull()
    {
        $line = "ssh -L 3306:127.0.0.1:3306";
        $this->assertNull($this->parser->parse([$line]));
    }

    public function testParseDateInfoValidDate()
    {
        $input = ["Tue Oct 14 01:32:49 2025"];

        $result = $this->parser->parseDateInfo($input);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('datetime_sql', $result);
        $this->assertArrayHasKey('seconds_diff', $result);

        $this->assertEquals('2025-10-14 01:32:49', $result['datetime_sql']);
        $this->assertIsInt($result['seconds_diff']);
        $this->assertGreaterThan(0, $result['seconds_diff']);
    }

    public function testParseDateInfoInvalidDate()
    {
        $input = ["invalid date string"];

        $result = $this->parser->parseDateInfo($input);

        $this->assertIsArray($result);
        $this->assertNull($result['datetime_sql']);
        $this->assertNull($result['seconds_diff']);

    }

    public function testParseDateInfoFutureDate()
    {
        $futureDate = (new \DateTime('+1 day'))->format('D M d H:i:s Y');
        $input = [$futureDate];

        $result = $this->parser->parseDateInfo($input);

        $this->assertIsArray($result);
        $this->assertNotNull($result['datetime_sql']);
        $this->assertLessThan(0, $result['seconds_diff']); // futur → négatif
    }

    public function testParseDateInfoNow()
    {
        //pour éviter un fail pendant le changement d'heure =)
        date_default_timezone_set('UTC');

        $now = (new \DateTime())->format('D M d H:i:s Y');
        $input = [$now];

        $result = $this->parser->parseDateInfo($input);

        $this->assertIsArray($result);
        $this->assertEquals((new \DateTime())->format('Y-m-d'), substr($result['datetime_sql'], 0, 10));
        $this->assertLessThanOrEqual(1, abs($result['seconds_diff'])); // quasi zéro 
    }

    public function testGetTunnelMockReturnsArray(): void
    {
        $result = Tunnel::getTunnel(['mock' => true]);
        $this->assertIsArray($result, 'getTunnel doit retourner un tableau');
    }

    public function testGetTunnelMockHasExpectedLines(): void
    {
        $result = Tunnel::getTunnel(['mock' => true]);

        $this->assertCount(2, $result, 'Le mock doit contenir 2 lignes');
        $this->assertStringContainsString('-L', $result[0], 'La première ligne doit contenir -L');
        $this->assertStringContainsString('-R', $result[1], 'La deuxième ligne doit contenir -R');
    }

    public function testGetTunnelRealCommandRunsWithoutError(): void
    {
        // ⚠️ Ce test ne vérifie rien s’il n’y a pas de tunnel actif
        $result = Tunnel::getTunnel([]);
        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(0, count($result));
    }
}
