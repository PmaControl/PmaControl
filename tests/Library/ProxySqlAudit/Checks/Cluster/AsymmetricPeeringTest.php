<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Cluster\AsymmetricPeering;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class AsymmetricPeeringTest extends TestCase
{
    public function testEmitsInfoListingLocalPeers(): void
    {
        $db = new AsymPeerFakeDb([
            ['hostname' => 'px1.example', 'port' => 6032],
            ['hostname' => 'px2.example', 'port' => 6032],
            ['hostname' => 'px3.example', 'port' => 6032],
        ]);
        $findings = (new AsymmetricPeering())->run($db);
        $this->assertCount(1, $findings);
        $f = $findings[0];
        $this->assertSame(Finding::SEVERITY_INFO, $f->severity);
        $this->assertSame('cluster', $f->category);
        $this->assertSame('cluster.asymmetric-peering', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString('cannot be verified', $f->title);
        $this->assertStringContainsString('px1.example:6032', $f->evidence);
        $this->assertStringContainsString('px2.example:6032', $f->evidence);
        $this->assertStringContainsString('px3.example:6032', $f->evidence);
        $this->assertStringContainsString('runtime_proxysql_servers', $f->fix);
    }

    public function testEmptyWhenNoLocalPeers(): void
    {
        $db = new AsymPeerFakeDb([]);
        $this->assertSame([], (new AsymmetricPeering())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new AsymPeerFakeDb(returnsFalse: true);
        $this->assertSame([], (new AsymmetricPeering())->run($db));
    }
}

final class AsymPeerFakeDb
{
    private array $rows;
    private bool $returnsFalse;
    private int $cursor = 0;
    public function __construct(array $rows = [], bool $returnsFalse = false)
    {
        $this->rows = $rows;
        $this->returnsFalse = $returnsFalse;
    }
    public function sql_query_silent(string $sql)
    {
        if ($this->returnsFalse) return false;
        $this->cursor = 0;
        return 'mock';
    }
    public function sql_fetch_array($res, int $mode = MYSQLI_ASSOC)
    {
        if ($this->cursor >= count($this->rows)) return null;
        return $this->rows[$this->cursor++];
    }
}
