<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1198 — Slave/setReplicationVariable mutates server config,
 * so it must (a) be in the ACL allow-list (otherwise the framework
 * 302-redirects to /user/connection and JS chokes — see #1187), and
 * (b) be at Member rank or stricter (ReadOnly users must not mutate).
 */
final class SetReplicationVariableAclTest extends TestCase
{
    private string $aclConfig;

    protected function setUp(): void
    {
        $contents = file_get_contents(__DIR__ . '/../../config_sample/acl.config.ini');
        $this->assertNotFalse($contents);
        $this->aclConfig = $contents;
    }

    public function testActionIsRegisteredAtMemberRank(): void
    {
        $this->assertMatchesRegularExpression(
            '/^\s*Member\[\]\s*=\s*"Slave\/setReplicationVariable"\s*$/m',
            $this->aclConfig,
            'Slave/setReplicationVariable must be allow-listed at Member rank'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/^\s*ReadOnly\[\]\s*=\s*"Slave\/setReplicationVariable"\s*$/m',
            $this->aclConfig,
            'Slave/setReplicationVariable must NOT be granted to ReadOnly'
        );
    }
}
