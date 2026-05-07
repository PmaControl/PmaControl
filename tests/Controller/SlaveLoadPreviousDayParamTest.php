<?php

declare(strict_types=1);

use App\Controller\Slave;
use PHPUnit\Framework\TestCase;

/**
 * Pin Slave::extractConnectionNameParam — issue #816 regression guard.
 *
 * "Load previous day" used to send `…/<day>//ajax:true/` when the
 * replica had no connection_name; Apache collapsed the empty segment
 * and `ajax:true` (a Glial key:value param) ended up as the
 * connection_name slot, filtering every row out.
 *
 * Real connection_names can never contain ":" (sanitizeConnectionName
 * strips it), so any colon-bearing positional segment must be
 * treated as framework noise.
 */
final class SlaveLoadPreviousDayParamTest extends TestCase
{
    public function testEmptyStringReturnsEmpty(): void
    {
        $this->assertSame('', Slave::extractConnectionNameParam(''));
    }

    public function testKeyValueLikeAjaxTrueIsRejected(): void
    {
        $this->assertSame('', Slave::extractConnectionNameParam('ajax:true'));
    }

    public function testAnyColonBearingValueIsRejected(): void
    {
        $this->assertSame('', Slave::extractConnectionNameParam('foo:bar'));
        $this->assertSame('', Slave::extractConnectionNameParam(':leading'));
        $this->assertSame('', Slave::extractConnectionNameParam('trailing:'));
    }

    public function testValidConnectionNamePassesThrough(): void
    {
        $this->assertSame('shard_a', Slave::extractConnectionNameParam('shard_a'));
        $this->assertSame('repl-1.east', Slave::extractConnectionNameParam('repl-1.east'));
    }

    public function testInvalidCharactersAreStrippedNotRejected(): void
    {
        // sanitizeConnectionName strips, doesn't reject — keep that contract.
        $this->assertSame('shardA', Slave::extractConnectionNameParam('shard A'));
    }
}
