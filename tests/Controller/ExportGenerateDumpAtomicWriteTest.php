<?php

declare(strict_types=1);

use App\Controller\Export;
use PHPUnit\Framework\TestCase;

/**
 * Issue #777: Export::generateDump used three chained shell_exec() calls
 * that wrote directly into sql/full/pmacontrol.sql via `>` and `>>`.
 * When the existing target was owned by another user (eg root from a
 * previous run), the redirection failed with "Permission denied" but
 * the controller never noticed (shell_exec drops the exit code), so
 * the dump was silently corrupted while the caller saw exit 0.
 *
 * The patched version still writes in place (the project's standard
 * sql/full/ directory is owned by the deployer, not by www-data, so we
 * cannot create new entries from a request handler) but goes through
 * testable helpers:
 *
 *   - generateDump() pre-checks is_writable() on the target file and
 *     throws a RuntimeException with the chown hint when it's not,
 *     instead of letting shell_exec swallow the failure.
 *   - dumpInto() runs the pipeline via exec(), which (unlike
 *     shell_exec) returns the exit status, and throws on non-zero.
 *   - buildDumpCommand() escapes every shell argument and uses
 *     --defaults-extra-file= so the password no longer appears on
 *     argv (and thus in `ps`).
 *   - writeDefaultsExtraFile() writes that 0600 [client] block.
 *
 * These tests pin the helpers' contracts so we don't regress the
 * shell-injection / password-leak surface.
 */
final class ExportGenerateDumpAtomicWriteTest extends TestCase
{
    public function testBuildDumpCommandQuotesEverythingAndUsesDefaultsFile(): void
    {
        $cmd = Export::buildDumpCommand(
            '/tmp/creds-abc',
            'pmacontrol',
            ['users', 'menu'],
            ['--skip-dump-date'],
            '/tmp/dump.tmp',
            '>'
        );

        $this->assertIsString($cmd);

        // --defaults-file= MUST be the first argument (mysqldump rule)
        // and must use the full-override form, not --defaults-extra-file
        // — otherwise /root/.my.cnf wins over our user/password when
        // invoked as root.
        $this->assertStringContainsString("mysqldump --defaults-file='/tmp/creds-abc'", $cmd);
        $this->assertStringNotContainsString('--defaults-extra-file', $cmd, 'Use --defaults-file= for full override (root precedence trap).');

        // No password leak: the legacy -p<plaintext> and --password forms
        // must both be gone.
        $this->assertStringNotContainsString(' -p', $cmd, 'Password must never be passed on the command line.');
        $this->assertStringNotContainsString(' --password', $cmd);
        // Same for the user — it's in the defaults file, not on argv.
        $this->assertStringNotContainsString('--user', $cmd);

        // Quoted database, table list, redirect target.
        $this->assertStringContainsString("'pmacontrol'", $cmd);
        $this->assertStringContainsString("'users'", $cmd);
        $this->assertStringContainsString("'menu'", $cmd);
        $this->assertStringContainsString("'--skip-dump-date'", $cmd);
        $this->assertStringContainsString("> '/tmp/dump.tmp'", $cmd);

        // Sed strips AUTO_INCREMENT to keep dumps stable across deploys
        // — same behavior as the legacy code, just plumbed through the
        // safer pipeline.
        $this->assertStringContainsString("sed 's/ AUTO_INCREMENT", $cmd);
    }

    public function testBuildDumpCommandSupportsAppendRedirect(): void
    {
        $cmd = Export::buildDumpCommand('/tmp/c', 'db', ['t'], [], '/tmp/o', '>>');
        $this->assertNotNull($cmd);
        $this->assertStringContainsString(">> '/tmp/o'", $cmd);
    }

    public function testBuildDumpCommandReturnsNullForEmptyTableSetSoCallerCanSkip(): void
    {
        // The legacy code sent `mysqldump db ` with no tables, which
        // dumps the whole DB silently — bug. Returning null lets
        // dumpInto() short-circuit instead.
        $this->assertNull(Export::buildDumpCommand('/tmp/c', 'db', [], [], '/tmp/o', '>'));
    }

    public function testBuildDumpCommandRejectsUnknownRedirectOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Export::buildDumpCommand('/tmp/c', 'db', ['t'], [], '/tmp/o', '|');
    }

    public function testBuildDumpCommandEscapesShellMetacharactersInDatabaseAndTables(): void
    {
        // Defense-in-depth: db/table names come from the catalog but
        // we still must not let a `; rm -rf /; #` substring escape the
        // shell context. escapeshellarg wraps in single quotes and
        // escapes embedded single quotes via the '\'' idiom — so even a
        // payload containing a single quote stays inside one shell
        // argument and is never interpreted as code.
        $cmd = Export::buildDumpCommand(
            '/tmp/c',
            "db'; rm -rf /; #",
            ["t'1", "t 2"],
            [],
            '/tmp/o',
            '>'
        );

        $this->assertIsString($cmd);

        // Single-quoted argument, with the embedded quote turned into
        // the canonical '\'' breakout-and-rejoin sequence. As long as
        // this exact form is present, the shell sees one argument, not
        // a command separator.
        $this->assertStringContainsString("'db'\\''; rm -rf /; #'", $cmd);
        $this->assertStringContainsString("'t'\\''1'", $cmd);
        $this->assertStringContainsString("'t 2'", $cmd);

        // And the bare unsafe form (which would actually delete files
        // if it landed unquoted) is not anywhere outside that wrapping.
        // We strip every single-quoted argument from the command and
        // make sure the dangerous fragment is gone from what's left.
        $strippedOfQuotedArgs = preg_replace("/'(?:[^']|'\\\\'')*'/", '', $cmd);
        $this->assertStringNotContainsString('rm -rf', (string) $strippedOfQuotedArgs);
    }

    public function testNormalizeDumpOwnershipOnNonExistingPathIsNoOp(): void
    {
        // Issue #777: post-dump ownership normalization must never fail
        // the dump itself — when the file does not exist (eg the dump
        // never wrote anything), this method must just return.
        $missing = sys_get_temp_dir().'/pmacontrol-no-such-file-'.bin2hex(random_bytes(6));
        Export::normalizeDumpOwnership($missing); // no exception expected
        $this->assertFileDoesNotExist($missing);
    }

    public function testNormalizeDumpOwnershipBestEffortChmodsTo664(): void
    {
        // The chown/chgrp branch only succeeds when the test process is
        // root (uncommon in CI), so we only assert on the chmod path
        // which always works on a file the caller can own.
        $path = tempnam(sys_get_temp_dir(), 'pmacontrol-dump-perms-');
        chmod($path, 0o600);

        try {
            Export::normalizeDumpOwnership($path);
            $perms = fileperms($path) & 0o777;
            $this->assertSame(0o664, $perms, 'normalizeDumpOwnership must drop the file to mode 0664.');
        } finally {
            @unlink($path);
        }
    }

    public function testWriteDefaultsExtraFileIsZero600AndContainsClientBlock(): void
    {
        $path = Export::writeDefaultsExtraFile('10.0.0.1', 3307, 'pmacontrol_user', 's3cret;\'"\\');

        $this->assertFileExists($path);

        try {
            $perms = fileperms($path) & 0o777;
            $this->assertSame(0o600, $perms, 'Credentials file must be readable only by the owner.');

            $contents = (string) file_get_contents($path);
            $this->assertStringStartsWith("[client]\n", $contents);
            $this->assertStringContainsString("\nhost=10.0.0.1\n", $contents);
            $this->assertStringContainsString("\nport=3307\n", $contents);
            $this->assertStringContainsString("\nuser=pmacontrol_user\n", $contents);
            // Password is written verbatim — mysqldump reads it from this
            // file, not from the shell, so we don't have to escape it.
            $this->assertStringContainsString("\npassword=s3cret;'\"\\\n", $contents);
        } finally {
            @unlink($path);
        }
    }
}
