<?php

declare(strict_types=1);

use App\Library\ProxySqlAudit\Checks\User\FrontendBackendTwinMissing;
use App\Library\ProxySqlAudit\Finding;
use PHPUnit\Framework\TestCase;

final class FrontendBackendTwinMissingTest extends TestCase
{
    public function testCriticalWhenBackendTwinIsMissing(): void
    {
        // Direct shape from #148: pmacontrol with frontend=1, backend=0.
        $db = new TwinMissingFakeDb([
            ['username' => 'pmacontrol', 'f' => 1, 'b' => 0],
        ]);
        $f = (new FrontendBackendTwinMissing())->run($db)[0];
        $this->assertSame(Finding::SEVERITY_CRITICAL, $f->severity);
        $this->assertSame('user', $f->category);
        $this->assertSame('user.frontend-backend-twin-missing', $f->checkId);
        $this->assertSame('#148', $f->ticket);
        $this->assertStringContainsString("'pmacontrol'", $f->title);
        $this->assertStringContainsString('missing backend', $f->title);
        $this->assertStringContainsString('frontend_sum=1 backend_sum=0', $f->evidence);
        $this->assertStringContainsString("0, 1", $f->fix);
        $this->assertStringContainsString('LOAD MYSQL USERS TO RUNTIME', $f->fix);
    }

    public function testCriticalWhenFrontendTwinIsMissing(): void
    {
        $db = new TwinMissingFakeDb([
            ['username' => 'app', 'f' => 0, 'b' => 1],
        ]);
        $f = (new FrontendBackendTwinMissing())->run($db)[0];
        $this->assertSame(Finding::SEVERITY_CRITICAL, $f->severity);
        $this->assertStringContainsString('missing frontend', $f->title);
        $this->assertStringContainsString('1, 0', $f->fix);
    }

    public function testEmptyWhenAllUsersHaveBothRows(): void
    {
        $db = new TwinMissingFakeDb([]);
        $this->assertSame([], (new FrontendBackendTwinMissing())->run($db));
    }

    public function testEmptyOnSilentQueryFailure(): void
    {
        $db = new TwinMissingFakeDb(returnsFalse: true);
        $this->assertSame([], (new FrontendBackendTwinMissing())->run($db));
    }
}

final class TwinMissingFakeDb
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
