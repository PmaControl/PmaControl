<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\Hostgroup\ReaderHostgroupReadOnlyOff;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class ReaderHostgroupReadOnlyOffTest extends TestCase
{
    public function testReturnsInfoWhenPmacontrolContextIsAbsent(): void
    {
        $proxy = new ReaderRoOffProxyDb([
            ['hostname' => 'r1', 'port' => 3306],
            ['hostname' => 'r2', 'port' => 3306],
        ]);

        $findings = (new ReaderHostgroupReadOnlyOff())->run($proxy);

        $this->assertCount(1, $findings);
        $this->assertSame(Finding::SEVERITY_INFO, $findings[0]->severity);
        $this->assertSame('hostgroup.reader-with-read-only-off', $findings[0]->checkId);
        $this->assertStringContainsString('Cross-source verification unavailable', $findings[0]->title);
        $this->assertStringContainsString('2 reader backend(s)', $findings[0]->evidence);
    }

    public function testReturnsEmptyWhenNoReaderBackends(): void
    {
        $proxy = new ReaderRoOffProxyDb([]);
        $this->assertSame(
            [],
            (new ReaderHostgroupReadOnlyOff())->run($proxy, ['pmacontrol_db' => new ReaderRoOffPmacDb()])
        );
    }

    public function testReturnsEmptyOnSilentQueryFailure(): void
    {
        $proxy = new ReaderRoOffProxyDb(returnsFalse: true);
        $this->assertSame([], (new ReaderHostgroupReadOnlyOff())->run($proxy));
    }

    public function testWarningPerReaderBackendWithReadOnlyOff(): void
    {
        $proxy = new ReaderRoOffProxyDb([
            ['hostname' => '10.0.0.10', 'port' => 3306],
            ['hostname' => '10.0.0.11', 'port' => 3306],
            ['hostname' => '10.0.0.12', 'port' => 3306],
        ]);
        // pmacontrol DB returns: 10.0.0.10 → id 100 → read_only=OFF (offending)
        //                       10.0.0.11 → id 101 → read_only=ON (clean)
        //                       10.0.0.12 → not in mysql_server (skipped)
        $pmac = new ReaderRoOffPmacDb([
            "ip='10.0.0.10' AND port=3306" => ['id' => 100],
            "ip='10.0.0.11' AND port=3306" => ['id' => 101],
        ], [
            100 => ['value' => 'OFF'],
            101 => ['value' => 'ON'],
        ]);
        $findings = (new ReaderHostgroupReadOnlyOff())->run($proxy, ['pmacontrol_db' => $pmac]);

        $this->assertCount(1, $findings);
        $f = $findings[0];
        $this->assertSame(Finding::SEVERITY_WARNING, $f->severity);
        $this->assertSame('hostgroup', $f->category);
        $this->assertNull($f->ticket);
        $this->assertStringContainsString('10.0.0.10:3306', $f->title);
        $this->assertStringContainsString('read_only=OFF', $f->title);
        $this->assertStringContainsString('mysql_server.id=100', $f->evidence);
        $this->assertStringContainsString('SET GLOBAL read_only', $f->fix);
    }
}

final class ReaderRoOffProxyDb
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

final class ReaderRoOffPmacDb
{
    /** @var array<string,array<string,mixed>> */
    private array $idLookup;
    /** @var array<int,array<string,mixed>> */
    private array $readOnlyByMs;
    /** @var array<int,array<string,mixed>|null> */
    private array $resultRows = [];
    private int $nextResId = 0;

    public function __construct(array $idLookup = [], array $readOnlyByMs = [])
    {
        $this->idLookup = $idLookup;
        $this->readOnlyByMs = $readOnlyByMs;
    }

    public function sql_real_escape_string(string $v): string { return addslashes($v); }

    public function sql_query_silent(string $sql)
    {
        if (preg_match("/mysql_server WHERE (ip='[^']*' AND port=\d+)/", $sql, $m)) {
            $key = $m[1];
            if (isset($this->idLookup[$key])) {
                $rid = ++$this->nextResId;
                $this->resultRows[$rid] = $this->idLookup[$key];
                return $rid;
            }
            return false;
        }
        if (preg_match("/v\\.id_mysql_server=(\\d+)/", $sql, $m)) {
            $idMs = (int) $m[1];
            if (isset($this->readOnlyByMs[$idMs])) {
                $rid = ++$this->nextResId;
                $this->resultRows[$rid] = $this->readOnlyByMs[$idMs];
                return $rid;
            }
            return false;
        }
        return false;
    }

    public function sql_fetch_array($res, int $mode = MYSQLI_ASSOC)
    {
        if (!isset($this->resultRows[$res])) return null;
        $row = $this->resultRows[$res];
        $this->resultRows[$res] = null; // single-row fetch
        return $row;
    }
}
