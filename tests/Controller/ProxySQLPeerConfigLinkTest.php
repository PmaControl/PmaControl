<?php

declare(strict_types=1);

use App\Controller\ProxySQL;
use PHPUnit\Framework\TestCase;

/**
 * Pin the canonical "hostname:port" key the controller hands to the
 * view as `$data['proxysql_server_links']` (#893). The view looks up
 * each peer row via this key, so any drift between the two sides
 * silently breaks the cross-link.
 */
final class ProxySQLPeerConfigLinkTest extends TestCase
{
    public function testKeyIsLowercasedAndTrimmedHostnameWithIntegerPort(): void
    {
        $this->assertSame(
            '10.68.68.232:6032',
            ProxySQL::proxysqlServerLinkKey('10.68.68.232', 6032)
        );

        // Trim + lowercase — operators sometimes paste with whitespace
        // or mixed case in the proxysql_servers table.
        $this->assertSame(
            'proxy-1.example.com:6032',
            ProxySQL::proxysqlServerLinkKey('  Proxy-1.Example.com  ', 6032)
        );
    }

    public function testEmptyMapReturnedWhenNotOnProxySqlServersTab(): void
    {
        // Off-tab callers must not pay the DB round-trip.
        $this->assertSame([], ProxySQL::buildProxysqlServerLinks('MYSQL_SERVERS', 8));
        $this->assertSame([], ProxySQL::buildProxysqlServerLinks('mysql_users', 8));
        $this->assertSame([], ProxySQL::buildProxysqlServerLinks('', 8));
    }

    public function testViewWiresThePeerLinkOnHostnameCellsForBothTables(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/ProxySQL/config.view.php');
        $this->assertNotFalse($view);

        // Helper closure that builds the icon link.
        $this->assertStringContainsString('renderPeerProxySqlLink', $view);

        // Editable + runtime tables both opt in only when the table is
        // exactly `proxysql_servers`. Other tabs must not gain an extra
        // column.
        $this->assertStringContainsString("\$table_name === 'proxysql_servers'", $view);

        // The link must target the peer's PROXYSQL_SERVERS tab so the
        // operator lands on the equivalent view.
        $this->assertStringContainsString("'/PROXYSQL_SERVERS/'", $view);

        // Suffix is appended only on the hostname cell — not every
        // cell, which would duplicate the icon.
        $this->assertStringContainsString("\$field === 'hostname'", $view);
    }
}
