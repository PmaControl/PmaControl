<?php

declare(strict_types=1);

use App\Controller\Ssh;
use PHPUnit\Framework\TestCase;

/**
 * Pin Ssh::isLocalSshTarget() — the guard that stops tryAssociate()
 * from "successfully" deploying an SSH key on the pmacontrol host's
 * own sshd when a tunneled mysql_server row is misconfigured with
 * ip=127.0.0.1 and ssh_port=22 (the conventional local sshd port).
 *
 * Three real servers (218/219/220) hit this trap: their MySQL is
 * reached via 127.0.0.1:1000X tunnels but ssh_port stayed at the
 * default 22, so the orchestrator opened SSH to the local sshd and
 * silently failed (no link inserted, no actionable log).
 */
final class SshIsLocalSshTargetTest extends TestCase
{
    public function testRefusesIpv4LoopbackOnDefaultSshPort(): void
    {
        self::assertTrue(Ssh::isLocalSshTarget('127.0.0.1', 22));
        self::assertTrue(Ssh::isLocalSshTarget('127.0.0.2', 22));
        self::assertTrue(Ssh::isLocalSshTarget('127.255.255.254', 22));
    }

    public function testRefusesIpv6LoopbackAndLocalhostNameOnDefaultSshPort(): void
    {
        self::assertTrue(Ssh::isLocalSshTarget('::1', 22));
        self::assertTrue(Ssh::isLocalSshTarget('localhost', 22));
        self::assertTrue(Ssh::isLocalSshTarget('LOCALHOST', 22));
    }

    public function testAllowsLoopbackOnDedicatedTunnelPort(): void
    {
        // The valid pattern: ip=127.0.0.1, ssh_port=10101 (tunnel
        // forwarding to target's sshd). Must NOT be refused.
        self::assertFalse(Ssh::isLocalSshTarget('127.0.0.1', 10101));
        self::assertFalse(Ssh::isLocalSshTarget('127.0.0.1', 13322));
        self::assertFalse(Ssh::isLocalSshTarget('::1', 2222));
        self::assertFalse(Ssh::isLocalSshTarget('localhost', 2222));
    }

    public function testAllowsRemoteAddressOnAnyPort(): void
    {
        self::assertFalse(Ssh::isLocalSshTarget('192.168.100.101', 22));
        self::assertFalse(Ssh::isLocalSshTarget('10.68.68.21', 22));
        self::assertFalse(Ssh::isLocalSshTarget('203.0.113.5', 22));
    }

    public function testEmptyOrWhitespaceIpDoesNotMatch(): void
    {
        // An empty ip is a different bug — the guard must not over-trigger.
        self::assertFalse(Ssh::isLocalSshTarget('', 22));
        self::assertFalse(Ssh::isLocalSshTarget('   ', 22));
    }

    public function testGuardCalledFromTryAssociate(): void
    {
        // Pin the call-site so the guard cannot be silently removed
        // from tryAssociate() in a future refactor.
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Ssh.php');
        self::assertIsString($controller);
        self::assertStringContainsString(
            'self::isLocalSshTarget((string) $server[\'ip\'], (int) $server[\'ssh_port\'])',
            $controller,
            'tryAssociate must call isLocalSshTarget BEFORE fsockopen so the local sshd is never contacted'
        );
        // The guard must precede the fsockopen() that would otherwise
        // succeed against the local sshd.
        $guardPos = strpos($controller, 'isLocalSshTarget((string) $server[\'ip\']');
        $fsockopenPos = strpos($controller, '@fsockopen($server[\'ip\']');
        self::assertNotFalse($guardPos);
        self::assertNotFalse($fsockopenPos);
        self::assertLessThan($fsockopenPos, $guardPos,
            'guard must run BEFORE fsockopen so we never open TCP to the local sshd');
    }
}
