<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Monitor\ServerVersionDrift;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class ServerVersionDriftTest extends TestCase
{
    public function testInfoListsConfiguredVersionAndBackends(): void
    {
        $db = new ServerVersionDriftFakeDb(
            ['variable_value' => '5.7.30'],
            [
                ['hostname' => 'm1', 'port' => 3306],
                ['hostname' => 'r1', 'port' => 3306],
            ]
        );
        $f = (new ServerVersionDrift())->run($db)[0];
        $this->assertSame(Finding::SEVERITY_INFO, $f->severity);
        $this->assertSame('monitor', $f->category);
        $this->assertSame('monitor.server-version-drift', $f->checkId);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString("'5.7.30'", $f->title);
        $this->assertStringContainsString("'5.7.30'", $f->evidence);
        $this->assertStringContainsString('m1:3306', $f->evidence);
        $this->assertStringContainsString('r1:3306', $f->evidence);
        $this->assertStringContainsString('SELECT @@global.version', $f->fix);
        $this->assertStringContainsString("SET mysql-server_version=", $f->fix);
    }

    public function testEmptyWhenServerVersionUnset(): void
    {
        $db = new ServerVersionDriftFakeDb(['variable_value' => '']);
        $this->assertSame([], (new ServerVersionDrift())->run($db));
    }

    public function testEmptyWhenNoBackends(): void
    {
        $db = new ServerVersionDriftFakeDb(['variable_value' => '8.0.36'], []);
        $this->assertSame([], (new ServerVersionDrift())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new ServerVersionDriftFakeDb(returnsFalse: true);
        $this->assertSame([], (new ServerVersionDrift())->run($db));
    }
}

final class ServerVersionDriftFakeDb
{
    private ?array $varRow;
    private array $backendRows;
    private bool $returnsFalse;
    private int $cursor = 0;
    private int $stage = 0;

    public function __construct(?array $varRow = null, array $backendRows = [], bool $returnsFalse = false)
    {
        $this->varRow = $varRow;
        $this->backendRows = $backendRows;
        $this->returnsFalse = $returnsFalse;
    }

    public function sql_query_silent(string $sql)
    {
        if ($this->returnsFalse) return false;
        $this->cursor = 0;
        $this->stage = stripos($sql, 'runtime_global_variables') !== false ? 0 : 1;
        return 'mock';
    }

    public function sql_fetch_array($res, int $mode = MYSQLI_ASSOC)
    {
        if ($this->stage === 0) {
            if ($this->cursor++ === 0 && $this->varRow !== null) return $this->varRow;
            return null;
        }
        if ($this->cursor >= count($this->backendRows)) return null;
        return $this->backendRows[$this->cursor++];
    }
}
