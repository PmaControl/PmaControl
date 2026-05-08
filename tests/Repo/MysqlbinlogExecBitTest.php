<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Pin the exec bit on every vendored mysqlbinlog binary in the git
 * index — issue #820. Binlog Analysis aborts on the first chosen
 * binary that lacks `+x`, so any future drop-in must arrive with
 * mode `100755` or this test fails the suite before merge.
 */
final class MysqlbinlogExecBitTest extends TestCase
{
    public function testEveryVendoredBinaryIsExecutableInGitIndex(): void
    {
        $repoRoot = realpath(__DIR__ . '/../..');
        $this->assertNotFalse($repoRoot, 'repo root must resolve');

        // Read all entries under bin/mysqlbinlog/ from the git index, not
        // the working tree — a developer could `chmod +x` locally without
        // updating the index. We're guarding what gets committed.
        $cmd = sprintf(
            'git -C %s ls-files -s bin/mysqlbinlog/',
            escapeshellarg($repoRoot)
        );
        $output = [];
        $rc     = 0;
        exec($cmd . ' 2>&1', $output, $rc);

        $this->assertSame(0, $rc, "git ls-files failed:\n" . implode("\n", $output));
        $this->assertNotEmpty($output, 'expected vendored binaries under bin/mysqlbinlog/');

        $offenders = [];
        foreach ($output as $line) {
            // Format: <mode> <sha> <stage>\t<path>
            if (!preg_match('/^(\d+)\s+\S+\s+\d+\t(.+)$/', $line, $m)) {
                continue;
            }
            [$mode, $path] = [$m[1], $m[2]];

            // Only care about the actual mysqlbinlog-* binaries — directory
            // entries / READMEs (if ever added) don't need +x.
            if (!preg_match('#/mysqlbinlog-[^/]+$#', $path)) {
                continue;
            }
            if ($mode !== '100755') {
                $offenders[] = "{$path} (mode {$mode})";
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Vendored mysqlbinlog binaries must be committed with exec bit (100755):\n  - "
            . implode("\n  - ", $offenders)
            . "\nFix: git update-index --chmod=+x <path> && git commit"
        );
    }
}
