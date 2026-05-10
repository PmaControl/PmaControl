<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1190 — without an ACL line for `Slave/setBinlogRowImage` the
 * picker would hit the same auth-redirect-as-HTML trap as #1187. Pin
 * the line in the sample (which is what install.sh ships) and assert
 * it is registered as a *Member*-rank action — ReadOnly users must
 * not be able to mutate server config.
 */
final class SetBinlogRowImageAclTest extends TestCase
{
    private string $aclConfig;

    protected function setUp(): void
    {
        $contents = file_get_contents(__DIR__ . '/../../config_sample/acl.config.ini');
        $this->assertNotFalse($contents);
        $this->aclConfig = $contents;
    }

    public function testSetBinlogRowImageIsRegistered(): void
    {
        $this->assertMatchesRegularExpression(
            '/^\s*[A-Za-z]+\[\]\s*=\s*"Slave\/setBinlogRowImage"\s*$/m',
            $this->aclConfig,
            'Slave/setBinlogRowImage must be allow-listed in config_sample/acl.config.ini'
        );
    }

    public function testSetBinlogRowImageIsAtMemberRankNotReadOnly(): void
    {
        // ReadOnly users must not be able to mutate server config —
        // the line must use `Member[]` (or stricter) and never
        // `ReadOnly[]`.
        $this->assertDoesNotMatchRegularExpression(
            '/^\s*ReadOnly\[\]\s*=\s*"Slave\/setBinlogRowImage"\s*$/m',
            $this->aclConfig,
            'Slave/setBinlogRowImage must not be granted to ReadOnly'
        );
        $this->assertMatchesRegularExpression(
            '/^\s*Member\[\]\s*=\s*"Slave\/setBinlogRowImage"\s*$/m',
            $this->aclConfig,
            'Slave/setBinlogRowImage must be granted at Member rank'
        );
    }
}
