<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #579 — Database::databaseLoad() builds a `myloader` command per
 * selected database. The legacy code passed `-B <db>` (alias for
 * `--database`, the *rename target*), which made myloader load every
 * `*.sql` file in the dump directory into that single target DB and
 * caused a cascade of cross-DB collisions (Duplicate entry / Column
 * count mismatch / Unknown column) when more than one database was
 * selected on /database/refresh.
 *
 * The correct flag is `-s` / `--source-db`, which filters the dump by
 * source database name. This test asserts the source contract at the
 * file level, mirroring DatabaseRefreshRedirectTest's pattern.
 */
final class DatabaseRefreshLoadFlagTest extends TestCase
{
    private string $controllerSource;
    private string $loadBody;

    protected function setUp(): void
    {
        $this->controllerSource = (string) file_get_contents(
            __DIR__ . '/../../App/Controller/Database.php'
        );
        $this->assertNotSame('', $this->controllerSource, 'Database.php must be readable');

        $start = strpos($this->controllerSource, 'public function databaseLoad($param)');
        $this->assertNotFalse($start, 'Database::databaseLoad must exist');

        // databaseLoad() ends just before the next public method (rename()).
        $end = strpos($this->controllerSource, 'public function rename($param)', $start);
        $this->assertNotFalse($end, 'rename() must follow databaseLoad()');

        $this->loadBody = substr($this->controllerSource, $start, $end - $start);
    }

    public function testDatabaseLoadUsesSourceDbFilter(): void
    {
        $this->assertMatchesRegularExpression(
            '/\$to_dump\s*=\s*[\'"]-s\s/s',
            $this->loadBody,
            'databaseLoad() must use myloader -s (filter by source DB) when restoring a single DB from a multi-DB dump (#579)'
        );
    }

    public function testDatabaseLoadDoesNotRenameWithDashB(): void
    {
        // myloader's -B/--database renames every file in the dump dir into
        // the same target — exactly the cross-loading bug of #579. The flag
        // must not appear in the per-DB cmd builder.
        $this->assertDoesNotMatchRegularExpression(
            '/\$to_dump\s*=\s*[\'"]-B\s/s',
            $this->loadBody,
            'databaseLoad() must NOT pass -B to myloader — it loads every dumped DB into the same target (#579)'
        );
    }

    public function testDatabaseLoadCommandPassesPerDbFlagToMyloader(): void
    {
        // Sanity: the assembled $cmd interpolates $to_dump between -o and -d.
        $this->assertStringContainsString(
            '-o $to_dump -d ',
            $this->loadBody,
            'databaseLoad() must inject the per-DB flag ($to_dump) into the myloader command between -o and -d'
        );
        $this->assertStringContainsString(
            'myloader',
            $this->loadBody,
            'databaseLoad() must invoke myloader'
        );
    }
}
