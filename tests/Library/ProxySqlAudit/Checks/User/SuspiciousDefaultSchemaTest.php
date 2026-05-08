<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\User\SuspiciousDefaultSchema;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class SuspiciousDefaultSchemaTest extends TestCase
{
    public function testWarningPerOffendingUser(): void
    {
        $db = new SuspSchemaFakeDb([
            ['username' => 'a', 'default_schema' => 'information_schema'],
            ['username' => 'b', 'default_schema' => 'mysql'],
        ]);
        $findings = (new SuspiciousDefaultSchema())->run($db);
        $this->assertCount(2, $findings);
        foreach ($findings as $f) {
            $this->assertSame(Finding::SEVERITY_WARNING, $f->severity);
            $this->assertSame('user', $f->category);
            $this->assertSame('user.suspicious-default-schema', $f->checkId);
            $this->assertNull($f->ticket);
            $this->assertStringContainsString('LOAD MYSQL USERS TO RUNTIME', $f->fix);
        }
        $this->assertStringContainsString("'a'",                  $findings[0]->title);
        $this->assertStringContainsString("information_schema",   $findings[0]->title);
        $this->assertStringContainsString("'b'",                  $findings[1]->title);
        $this->assertStringContainsString("mysql",                $findings[1]->title);
    }

    public function testEmptyWhenNoSystemSchemaUsage(): void
    {
        $db = new SuspSchemaFakeDb([]);
        $this->assertSame([], (new SuspiciousDefaultSchema())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new SuspSchemaFakeDb(returnsFalse: true);
        $this->assertSame([], (new SuspiciousDefaultSchema())->run($db));
    }
}

final class SuspSchemaFakeDb
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
