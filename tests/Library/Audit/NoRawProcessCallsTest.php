<?php

declare(strict_types=1);

namespace Tests\Library\Audit;

use PHPUnit\Framework\TestCase;

/**
 * Static guard against new raw process calls (#1235 post-MVP #6).
 *
 * Codex review of the audit module flagged that the `AuditedProcess`
 * wrapper only audits sub-processes that *go through it*. Anyone adding
 * a new `exec` / `shell_exec` / `passthru` / `proc_open` / `popen` /
 * `system` call site bypasses the audit pipeline. This test scans the
 * tree on every CI run and fails if a new site appears outside the
 * allowlist below.
 *
 * To add a legitimately-unaudited site (rare):
 *   1. Justify why in a comment next to the call.
 *   2. Append the relative file path to `self::ALLOWLIST` below.
 *   3. Reference the issue / commit that introduced it.
 *
 * Migrate-to-audit is the preferred path: replace `exec()` /
 * `shell_exec()` with `\App\Library\Audit\AuditedProcess::run()` so the
 * sub-process gets a `subprocess_log` row.
 */
final class NoRawProcessCallsTest extends TestCase
{
    /**
     * Files that legitimately call raw process functions today. Reduce
     * this list as call sites get migrated to `AuditedProcess`.
     *
     * Path is relative to repo root.
     *
     * @var list<string>
     */
    private const ALLOWLIST = [
        // Wrapper itself — uses proc_open/shell_exec by design.
        'App/Library/Audit/AuditedProcess.php',
        // Bootstrap re-exec (CliRootGuard) — runs before audit can boot.
        'App/Library/CliRootGuard.php',
        'App/Webroot/index.php',
        // Pre-audit subsystems pending migration to AuditedProcess
        // (#1235 follow-ups). Each new file added below should also
        // get a TODO comment at the call site referencing #1235.
        'App/Controller/About.php',
        'App/Controller/Agent.php',
        'App/Controller/Archives.php',
        'App/Controller/Aspirateur.php',
        'App/Controller/Backup.php',
        'App/Controller/Benchmark.php',
        'App/Controller/Binlog.php',
        'App/Controller/Blackhole.php',
        'App/Controller/Cleaner.php',
        'App/Controller/Control.php',
        'App/Controller/Crontab.php',
        'App/Controller/Daemon.php',
        'App/Controller/Database.php',
        'App/Controller/Demo.php',
        'App/Controller/DeployRsaKey.php',
        'App/Controller/Docker.php',
        'App/Controller/Dot3.php',
        'App/Controller/Install.php',
        'App/Controller/Job.php',
        'App/Controller/Listener.php',
        'App/Controller/Mysql.php',
        'App/Controller/MysqlServer.php',
        'App/Controller/MysqlUser.php',
        'App/Controller/Mysqlsys.php',
        'App/Controller/Pmm.php',
        'App/Controller/Query.php',
        'App/Controller/Release.php',
        'App/Controller/Scan.php',
        'App/Controller/Schema.php',
        'App/Controller/Server.php',
        'App/Controller/Slave.php',
        'App/Controller/Ssh.php',
        'App/Controller/StorageArea.php',
        'App/Controller/Translation.php',
        'App/Controller/Tunnel.php',
        'App/Controller/Worker.php',
        'App/Library/Archive/ArchiveLoader.php',
        'App/Library/BinlogAnalyzer.php',
        'App/Library/BlackholeRelay.php',
        'App/Library/Database/RefreshArtifact.php',
        'App/Library/File.php',
        'App/Library/Git.php',
        'App/Library/Graphviz.php',
        'App/Library/Kpi/KpiProcessDrilldown.php',
        'App/Library/Mysql.php',
        'App/Library/OrphanRefreshScanner.php',
        'App/Library/PluginPackage.php',
        'App/Library/Ssh.php',
        'App/Library/System.php',
        'App/Service/Ndb/NdbMgmShowFetcher.php',
        // View files with `shell_exec` daemon-liveness checks. Should
        // move to a controller helper eventually.
        'App/view/Backup/dump.view.php',
        'App/view/Backup/listing.view.php',
        'App/view/Cleaner/index.view.php',
        'App/view/Daemon/index.view.php',
    ];

    /** Functions we forbid outside the allowlist. */
    private const FORBIDDEN = [
        'exec',
        'shell_exec',
        'passthru',
        'proc_open',
        'popen',
        'system',
    ];

    public function testNoNewRawProcessCalls(): void
    {
        $root = dirname(__DIR__, 3); // repo root
        $appDir = $root . '/App';

        $offenders = [];
        $allowlist = array_flip(self::ALLOWLIST);

        $rii = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($appDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($rii as $file) {
            /** @var \SplFileInfo $file */
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $rel = ltrim(substr($file->getPathname(), strlen($root)), '/');
            if (isset($allowlist[$rel])) {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (self::FORBIDDEN as $fn) {
                // Match `fn(` not preceded by a `>`, `:`, alphanumeric or
                // underscore — same as a proper word boundary for the
                // function-call form, without picking up `->exec(` (SSH
                // helper methods) or `$db->exec(` (PDO).
                if (preg_match('/(?<![>\w:])' . preg_quote($fn, '/') . '\s*\(/', $contents) === 1) {
                    $offenders[] = $rel . ' uses ' . $fn . '()';
                    break; // one offence per file is enough
                }
            }
        }

        $msg = "Raw process calls detected outside the allowlist. Migrate to "
             . "\\App\\Library\\Audit\\AuditedProcess::run() so the call is "
             . "tracked in subprocess_log (audit module #1235), or add the file "
             . "to NoRawProcessCallsTest::ALLOWLIST with a justification.\n  - "
             . implode("\n  - ", $offenders);
        $this->assertSame([], $offenders, $msg);
    }

    /**
     * Sanity guard on the allowlist itself: every entry must still point
     * at an existing file, otherwise the list rots and lets new offences
     * slip through (a stale entry is a free pass).
     */
    public function testAllowlistEntriesPointAtExistingFiles(): void
    {
        $root = dirname(__DIR__, 3);
        foreach (self::ALLOWLIST as $rel) {
            $this->assertFileExists($root . '/' . $rel, "stale allowlist entry: $rel");
        }
    }
}
