<?php

declare(strict_types=1);

use App\Controller\Server;
use PHPUnit\Framework\TestCase;

final class ServerGeoipLookupTest extends TestCase
{
    public function testGeoipCountryLookupOrdersByClosestNetworkStart(): void
    {
        $sql = Server::getGeoipCountryLookupSql(new FakeGeoipDb(), '89.30.104.134');

        $this->assertSame(
            "SELECT country_iso FROM data_geoip
                           WHERE network_start <= INET6_ATON('89.30.104.134')
                           AND network_end >= INET6_ATON('89.30.104.134')
                           ORDER BY network_start DESC
                           LIMIT 1",
            $sql
        );
    }

    public function testGeoipCountryLookupEscapesIpOnceAndReusesItForBothBounds(): void
    {
        $sql = Server::getGeoipCountryLookupSql(new FakeGeoipDb(), "2001:db8::1'");

        $this->assertSame(2, substr_count($sql, "INET6_ATON('2001:db8::1\\'')"));
        $this->assertStringContainsString('ORDER BY network_start DESC', $sql);
    }
}

final class FakeGeoipDb
{
    public function sql_real_escape_string(string $value): string
    {
        return str_replace("'", "\\'", $value);
    }
}
