<?php
/**
 * BinlogAnalyzer — fetch binlog files from a master via SSH, analyze them
 * with mysqlbinlog inside a Docker container matching the master version,
 * and store the parsed statistics.
 *
 * Reports granular progress via the `progress` JSON column so the frontend
 * can show a live step-by-step log.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;
use \App\Library\Ssh;
use \App\Library\Chiffrement as Crypt;

class BinlogAnalyzer
{
    private $db;
    private $analysisId;
    private $tmpDir;
    private $steps = [];

    public function __construct(int $analysisId)
    {
        $this->db = Sgbd::sql(DB_DEFAULT);
        $this->analysisId = $analysisId;
        $this->tmpDir = '/tmp/binlog_analysis_' . $analysisId;
    }

    // ------------------------------------------------------------------
    //  Progress tracking
    // ------------------------------------------------------------------

    private function addStep(string $phase, string $message, string $status = 'running', array $extra = []): void
    {
        $step = [
            'phase'   => $phase,
            'message' => $message,
            'status'  => $status, // running | done | error
            'time'    => date('H:i:s'),
        ];
        if ($extra) {
            $step = array_merge($step, $extra);
        }
        $this->steps[] = $step;
        $this->flushProgress();
    }

    private function updateLastStep(string $message, string $status = 'done', array $extra = []): void
    {
        if (empty($this->steps)) return;
        $i = count($this->steps) - 1;
        $this->steps[$i]['message'] = $message;
        $this->steps[$i]['status'] = $status;
        $this->steps[$i]['time_end'] = date('H:i:s');
        if ($extra) {
            $this->steps[$i] = array_merge($this->steps[$i], $extra);
        }
        $this->flushProgress();
    }

    private function flushProgress(): void
    {
        $json = json_encode($this->steps, JSON_UNESCAPED_UNICODE);
        $this->db->sql_query(
            "UPDATE binlog_analysis SET progress = '" . $this->db->sql_real_escape_string($json) . "' WHERE id = " . $this->analysisId
        );
    }

    // ------------------------------------------------------------------
    //  Main pipeline
    // ------------------------------------------------------------------

    public function run(): bool
    {
        $this->updateStatus('running');

        try {
            // Step 1 — Load analysis record
            $this->addStep('init', 'Loading analysis parameters...');
            $analysis = $this->getAnalysis();
            if (!$analysis) {
                throw new \Exception("Analysis record not found");
            }
            $masterId = (int) $analysis['id_mysql_server_master'];
            $this->updateLastStep("Analysis #" . $this->analysisId . " — slave=" . $analysis['id_mysql_server'] . " master=$masterId range=[" . $analysis['time_start'] . " → " . $analysis['time_end'] . "]");

            // Step 2 — Get master server info
            $this->addStep('master_info', "Fetching master server info (id=$masterId)...");
            $master = $this->getServer($masterId);
            if (!$master) {
                throw new \Exception("Master server not found (id=$masterId)");
            }
            $this->updateLastStep("Master: " . ($master['display_name'] ?: $master['name']) . " — " . $master['ip'] . ":" . $master['port']);

            // Step 3 — Detect version
            $this->addStep('version', 'Detecting MySQL/MariaDB version on master...');
            $version = $this->getMasterVersion($masterId);
            if (!$version) {
                throw new \Exception("Cannot determine master MySQL version");
            }
            $this->db->sql_query("UPDATE binlog_analysis SET mysql_version = '" . $this->db->sql_real_escape_string($version) . "' WHERE id = " . $this->analysisId);
            $this->updateLastStep("Version: $version");

            // Step 4 — SSH to master
            $this->addStep('ssh', "Connecting via SSH to " . $master['ip'] . ":" . ($master['ssh_port'] ?: 22) . "...");
            $ssh = Ssh::ssh($masterId);
            if (!$ssh) {
                throw new \Exception("Cannot SSH to master server " . $master['ip']);
            }
            $this->updateLastStep("SSH connected to " . $master['ip']);

            // Step 5 — Find binlog files
            $this->addStep('find_binlogs', "Searching binlog files covering " . $analysis['time_start'] . " → " . $analysis['time_end'] . "...");
            $binlogFiles = $this->findBinlogFiles($ssh, $master, $analysis['time_start'], $analysis['time_end']);
            if (empty($binlogFiles)) {
                throw new \Exception("No binlog files found for the specified time range");
            }
            $names = array_map('basename', $binlogFiles);
            $this->db->sql_query("UPDATE binlog_analysis SET binlog_files = '" . $this->db->sql_real_escape_string(json_encode($names)) . "' WHERE id = " . $this->analysisId);
            $this->updateLastStep("Found " . count($binlogFiles) . " binlog file(s): " . implode(', ', $names));

            // Step 6 — Download binlog files
            @mkdir($this->tmpDir, 0755, true);
            foreach ($binlogFiles as $idx => $remotePath) {
                $name = basename($remotePath);
                $this->addStep('download', "Downloading $name (" . ($idx + 1) . "/" . count($binlogFiles) . ")...");
                $this->fetchBinlogFile($ssh, $remotePath);
                $localSize = filesize($this->tmpDir . '/' . $name);
                $sizeMb = round($localSize / 1048576, 1);
                $this->updateLastStep("Downloaded $name — $sizeMb MB");
            }

            // Step 7 — Select mysqlbinlog binary
            $binary = $this->getMysqlbinlogBinary($version);
            $this->addStep('binary', "Selecting mysqlbinlog binary for version $version...");
            $this->ensureBinary($binary);
            $binaryVersion = trim(shell_exec(escapeshellarg($binary) . " --version 2>&1") ?: 'unknown');
            $this->updateLastStep("Using: " . basename($binary) . " — " . $binaryVersion);

            // Step 8 — Parse: transaction metadata (GTID events)
            $this->addStep('parse_gtid', "Parsing transaction metadata (GTID, sizes, parallelism)...");
            $gtidStats = $this->parseGtidEvents();
            $this->updateLastStep(
                "Parsed " . $gtidStats['total_transactions'] . " transactions, total " . round($gtidStats['total_size'] / 1048576, 1) . " MB, " .
                $gtidStats['large_100k'] . " >100KB, " . $gtidStats['large_500k'] . " >500KB",
                'done',
                ['transactions' => $gtidStats['total_transactions']]
            );

            // Step 9 — Parse: DML row counts per table
            $this->addStep('parse_dml', "Decoding row events (INSERT/UPDATE/DELETE per table)...");
            $dmlStats = $this->parseDmlEvents();
            $totalRows = $dmlStats['inserts'] + $dmlStats['updates'] + $dmlStats['deletes'];
            $this->updateLastStep(
                "I:" . number_format($dmlStats['inserts']) . " U:" . number_format($dmlStats['updates']) . " D:" . number_format($dmlStats['deletes']) . " = " . number_format($totalRows) . " rows across " . $dmlStats['db_count'] . " databases",
                'done',
                ['total_rows' => $totalRows]
            );

            // Step 10 — Parse: volume per second
            $this->addStep('parse_volume', "Computing volume per second (timestamps + transaction_length)...");
            $volumeStats = $this->parseVolumePerSecond();
            $this->updateLastStep(
                count($volumeStats['data']) . " seconds of data, peak " . $volumeStats['peak_txn'] . " txn/s, peak " . round($volumeStats['peak_bytes'] / 1024) . " KB/s"
            );

            // Step 11 — Parse: DDL
            $this->addStep('parse_ddl', "Checking for DDL statements (CREATE/ALTER/DROP)...");
            $ddlCount = $this->parseDdlCount();
            $this->updateLastStep($ddlCount > 0 ? "$ddlCount DDL statement(s) found" : "No DDL — 100% DML row-based");

            // Step 12 — Compile & store results
            $this->addStep('store', "Compiling final report and storing results...");
            $this->storeResults($gtidStats, $dmlStats, $volumeStats, $ddlCount, $analysis);
            $this->updateLastStep("Report stored successfully");

            // Step 13 — Cleanup
            $this->addStep('cleanup', "Cleaning up temporary files...");
            $this->cleanup();
            $this->updateLastStep("Cleanup done — analysis complete");

            $this->updateStatus('done');
            $this->db->sql_query("UPDATE binlog_analysis SET completed_at = NOW() WHERE id = " . $this->analysisId);

            return true;

        } catch (\Exception $e) {
            $this->addStep('error', $e->getMessage(), 'error');
            $this->updateStatus('error', $e->getMessage());
            $this->cleanup();
            return false;
        }
    }

    // ------------------------------------------------------------------
    //  Data retrieval helpers
    // ------------------------------------------------------------------

    private function getAnalysis(): ?array
    {
        $res = $this->db->sql_query("SELECT * FROM binlog_analysis WHERE id = " . (int) $this->analysisId);
        $row = $this->db->sql_fetch_array($res, MYSQLI_ASSOC);
        return $row ?: null;
    }

    private function getServer(int $id): ?array
    {
        $res = $this->db->sql_query("SELECT * FROM mysql_server WHERE id = " . $id);
        $row = $this->db->sql_fetch_array($res, MYSQLI_ASSOC);
        return $row ?: null;
    }

    private function getMasterVersion(int $masterId): ?string
    {
        $res = $this->db->sql_query(
            "SELECT value FROM global_variable WHERE id_mysql_server = $masterId AND variable_name = 'version' LIMIT 1"
        );
        if ($res && $row = $this->db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            return $row['value'];
        }
        return null;
    }

    // ------------------------------------------------------------------
    //  Binlog file discovery
    // ------------------------------------------------------------------

    private function findBinlogFiles($ssh, array $master, string $timeStart, string $timeEnd): array
    {
        $datadir = trim($ssh->exec("mysql -N -e \"SELECT @@datadir\" 2>/dev/null"));
        if (empty($datadir)) {
            $datadir = '/var/lib/mysql/';
        }
        $datadir = rtrim($datadir, '/') . '/';

        $logBin = trim($ssh->exec("mysql -N -e \"SELECT @@log_bin_basename\" 2>/dev/null"));
        if (empty($logBin)) {
            $logBin = $datadir . 'mysql-bin';
        }

        // List binlog files from MySQL
        $binlogList = $ssh->exec("mysql -N -e \"SHOW BINARY LOGS\" 2>/dev/null");
        $allBinlogs = [];
        foreach (explode("\n", trim($binlogList)) as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 2) {
                $allBinlogs[] = ['name' => $parts[0], 'size' => (int) $parts[1]];
            }
        }

        if (empty($allBinlogs)) {
            throw new \Exception("No binary logs found on master (SHOW BINARY LOGS returned empty)");
        }

        $startTs = strtotime($timeStart);
        $endTs = strtotime($timeEnd);
        $selectedFiles = [];
        $lastCandidateBefore = null;

        foreach ($allBinlogs as $i => $bl) {
            $filePath = $datadir . $bl['name'];

            // Get first timestamp in this binlog
            $cmd = "mysqlbinlog --start-position=4 --read-from-remote-server " . escapeshellarg($filePath) . " 2>/dev/null | grep -oP '^#\\d{6}\\s+\\d+:\\d+:\\d+' | head -1 2>/dev/null";
            // Simpler: just read local file header
            $cmd = "mysqlbinlog " . escapeshellarg($filePath) . " --stop-position=500 2>/dev/null | grep -oP '^#\\d{6}\\s+\\d+:\\d+:\\d+' | head -1";
            $firstTs = trim($ssh->exec($cmd));

            if (empty($firstTs)) continue;

            if (preg_match('/^#(\d{2})(\d{2})(\d{2})\s+(\d+:\d+:\d+)$/', $firstTs, $tm)) {
                $year = 2000 + (int) $tm[1];
                $fileStartTs = strtotime("$year-$tm[2]-$tm[3] $tm[4]");
            } else {
                continue;
            }

            if ($fileStartTs > $endTs) {
                break;
            }

            if ($fileStartTs <= $startTs) {
                $lastCandidateBefore = $filePath;
            }

            if ($fileStartTs >= $startTs) {
                $selectedFiles[] = $filePath;
            }
        }

        // Include the file that starts before our range (it may contain events in our range)
        if ($lastCandidateBefore && !in_array($lastCandidateBefore, $selectedFiles)) {
            array_unshift($selectedFiles, $lastCandidateBefore);
        }

        // Fallback: use last binlog file if nothing found
        if (empty($selectedFiles) && !empty($allBinlogs)) {
            $last = end($allBinlogs);
            $selectedFiles[] = $datadir . $last['name'];
        }

        return $selectedFiles;
    }

    // ------------------------------------------------------------------
    //  File transfer
    // ------------------------------------------------------------------

    private function fetchBinlogFile($ssh, string $remotePath): void
    {
        $localPath = $this->tmpDir . '/' . basename($remotePath);
        $content = $ssh->exec("cat " . escapeshellarg($remotePath));
        if ($content === false || strlen($content) < 100) {
            throw new \Exception("Failed to fetch binlog file: " . basename($remotePath) . " (got " . strlen($content ?: '') . " bytes)");
        }
        file_put_contents($localPath, $content);
    }

    // ------------------------------------------------------------------
    //  mysqlbinlog binary selection
    // ------------------------------------------------------------------

    private const BINLOG_DIR = '/srv/www/pmacontrol/bin/mysqlbinlog/';

    /**
     * Select the right mysqlbinlog binary based on the master's MySQL/MariaDB version.
     * Binaries are stored as: mysqlbinlog-8.0, mysqlbinlog-8.4, mysqlbinlog-mariadb, etc.
     */
    private function getMysqlbinlogBinary(string $version): string
    {
        $vLower = strtolower($version);

        if (strpos($vLower, 'mariadb') !== false) {
            $bin = self::BINLOG_DIR . 'mysqlbinlog-mariadb';
            if (file_exists($bin)) return $bin;
            throw new \Exception("mysqlbinlog binary for MariaDB not found at $bin");
        }

        // MySQL Oracle: extract major.minor  "8.0.44" → "8.0"
        if (preg_match('/^(\d+\.\d+)/', $version, $m)) {
            $majorMinor = $m[1];

            // Exact match first
            $bin = self::BINLOG_DIR . 'mysqlbinlog-' . $majorMinor;
            if (file_exists($bin)) return $bin;

            // Fallback: try closest compatible version (8.4 can read 8.0, 9.x, etc.)
            $available = glob(self::BINLOG_DIR . 'mysqlbinlog-*');
            $candidates = [];
            foreach ($available as $path) {
                $name = basename($path);
                if (preg_match('/mysqlbinlog-(\d+\.\d+)/', $name, $vm)) {
                    $candidates[$vm[1]] = $path;
                }
            }

            // Pick highest version that is >= requested
            ksort($candidates, SORT_NATURAL);
            foreach ($candidates as $ver => $path) {
                if (version_compare($ver, $majorMinor, '>=')) {
                    return $path;
                }
            }

            // Last resort: pick the highest available
            if (!empty($candidates)) {
                return end($candidates);
            }
        }

        // Ultimate fallback
        $fallback = self::BINLOG_DIR . 'mysqlbinlog-8.4';
        if (file_exists($fallback)) return $fallback;

        throw new \Exception("No suitable mysqlbinlog binary found in " . self::BINLOG_DIR . " for version $version");
    }

    private function ensureBinary(string $binary): void
    {
        if (!file_exists($binary)) {
            throw new \Exception("mysqlbinlog binary not found: $binary");
        }
        if (!is_executable($binary)) {
            chmod($binary, 0755);
        }
    }

    // ------------------------------------------------------------------
    //  mysqlbinlog command builder (local binary, no Docker)
    // ------------------------------------------------------------------

    private function buildBinlogCmd(bool $verbose = false): string
    {
        $analysis = $this->getAnalysis();
        $version = $analysis['mysql_version'] ?? '';
        $binary = $this->getMysqlbinlogBinary($version);

        $cmd = escapeshellarg($binary);
        if ($verbose) {
            $cmd .= " -v --base64-output=DECODE-ROWS";
        }
        $cmd .= " --start-datetime=" . escapeshellarg($analysis['time_start']);
        $cmd .= " --stop-datetime=" . escapeshellarg($analysis['time_end']);

        // Add all local binlog files
        $files = glob($this->tmpDir . '/*');
        sort($files);
        foreach ($files as $f) {
            $cmd .= " " . escapeshellarg($f);
        }

        return $cmd;
    }

    // ------------------------------------------------------------------
    //  Parsing stages
    // ------------------------------------------------------------------

    private function parseGtidEvents(): array
    {
        $cmd = $this->buildBinlogCmd(false);
        $cmdGtid = $cmd . " 2>/dev/null | grep -E '(transaction_length|last_committed|sequence_number)=' | grep -oP '(transaction_length|last_committed|sequence_number)=\\d+'";

        $output = shell_exec($cmdGtid . " 2>/dev/null") ?: '';

        $transactions = [];
        $current = [];
        foreach (explode("\n", trim($output)) as $line) {
            if (preg_match('/^(transaction_length|last_committed|sequence_number)=(\d+)$/', trim($line), $m)) {
                $current[$m[1]] = (int) $m[2];
                if (count($current) === 3) {
                    $transactions[] = $current;
                    $current = [];
                }
            }
        }

        $totalSize = 0;
        $maxSize = 0;
        $large100k = 0;
        $large500k = 0;
        $sequential = 0;
        $lastCommittedGroups = [];

        foreach ($transactions as $t) {
            $len = $t['transaction_length'];
            $totalSize += $len;
            if ($len > $maxSize) $maxSize = $len;
            if ($len >= 100000) $large100k++;
            if ($len >= 500000) $large500k++;

            $lc = $t['last_committed'];
            $seq = $t['sequence_number'];
            if ($seq === $lc + 1) $sequential++;

            $lastCommittedGroups[$lc] = ($lastCommittedGroups[$lc] ?? 0) + 1;
        }

        $maxParallelism = empty($lastCommittedGroups) ? 0 : max($lastCommittedGroups);
        $seqPct = count($transactions) > 0 ? round($sequential / count($transactions) * 100, 1) : 0;

        $distrib = [];
        foreach ($lastCommittedGroups as $count) {
            $distrib[$count] = ($distrib[$count] ?? 0) + 1;
        }
        ksort($distrib);

        return [
            'total_transactions' => count($transactions),
            'total_size'         => $totalSize,
            'max_size'           => $maxSize,
            'large_100k'         => $large100k,
            'large_500k'         => $large500k,
            'sequential_pct'     => $seqPct,
            'max_parallelism'    => $maxParallelism,
            'parallelism_distrib' => $distrib,
        ];
    }

    private function parseDmlEvents(): array
    {
        $cmd = $this->buildBinlogCmd(true);
        $cmdDml = $cmd . " 2>/dev/null | grep -oP '### (INSERT INTO|UPDATE|DELETE FROM) \`[^\`]+\`\.\`[^\`]+\`'";

        $output = shell_exec($cmdDml . " 2>/dev/null") ?: '';

        $inserts = 0;
        $updates = 0;
        $deletes = 0;
        $tableDml = [];
        $databases = [];

        foreach (explode("\n", trim($output)) as $line) {
            if (preg_match('/### (INSERT INTO|UPDATE|DELETE FROM) `([^`]+)`\.`([^`]+)`/', trim($line), $m)) {
                $op = $m[1];
                $db = $m[2];
                $tbl = $m[3];
                $key = "`$db`.`$tbl`";
                $databases[$db] = true;

                if (!isset($tableDml[$key])) {
                    $tableDml[$key] = ['table' => $key, 'inserts' => 0, 'updates' => 0, 'deletes' => 0];
                }
                switch ($op) {
                    case 'INSERT INTO': $tableDml[$key]['inserts']++; $inserts++; break;
                    case 'UPDATE':      $tableDml[$key]['updates']++; $updates++; break;
                    case 'DELETE FROM': $tableDml[$key]['deletes']++; $deletes++; break;
                }
            }
        }

        usort($tableDml, function ($a, $b) {
            return ($b['inserts'] + $b['updates'] + $b['deletes']) - ($a['inserts'] + $a['updates'] + $a['deletes']);
        });

        return [
            'inserts'    => $inserts,
            'updates'    => $updates,
            'deletes'    => $deletes,
            'top_tables' => array_slice($tableDml, 0, 30),
            'db_count'   => count($databases),
        ];
    }

    private function parseVolumePerSecond(): array
    {
        $cmd = $this->buildBinlogCmd(false);
        // Extract lines like: #260413 20:27:51 ... transaction_length=1234
        $cmdTs = $cmd . " 2>/dev/null | grep -P 'transaction_length=\\d+' | grep -oP '^#\\d{6}\\s+\\d+:\\d+:\\d+.*transaction_length=\\d+'";

        $output = shell_exec($cmdTs . " 2>/dev/null") ?: '';

        $volumePerSec = [];
        $txnPerSec = [];

        foreach (explode("\n", trim($output)) as $line) {
            if (preg_match('/#(\d{2})(\d{2})(\d{2})\s+(\d+:\d+:\d+).*transaction_length=(\d+)/', $line, $m)) {
                $fullTs = (2000 + (int)$m[1]) . '-' . $m[2] . '-' . $m[3] . ' ' . $m[4];
                $txnLen = (int) $m[5];
                $volumePerSec[$fullTs] = ($volumePerSec[$fullTs] ?? 0) + $txnLen;
                $txnPerSec[$fullTs] = ($txnPerSec[$fullTs] ?? 0) + 1;
            }
        }

        ksort($volumePerSec);
        $data = [];
        $peakBytes = 0;
        $peakTxn = 0;
        foreach ($volumePerSec as $ts => $bytes) {
            $txn = $txnPerSec[$ts] ?? 0;
            $data[] = ['ts' => $ts, 'bytes' => $bytes, 'txn' => $txn];
            if ($bytes > $peakBytes) $peakBytes = $bytes;
            if ($txn > $peakTxn) $peakTxn = $txn;
        }

        return [
            'data'       => $data,
            'peak_bytes' => $peakBytes,
            'peak_txn'   => $peakTxn,
        ];
    }

    private function parseDdlCount(): int
    {
        $cmd = $this->buildBinlogCmd(true);
        $cmdDdl = $cmd . " 2>/dev/null | grep -icP '^\\s*(CREATE|ALTER|DROP|TRUNCATE|RENAME)\\s'";
        $count = (int) trim(shell_exec($cmdDdl . " 2>/dev/null") ?: '0');
        return $count;
    }

    // ------------------------------------------------------------------
    //  Store final results
    // ------------------------------------------------------------------

    private function storeResults(array $gtid, array $dml, array $volume, int $ddlCount, array $analysis): void
    {
        $totalFileSize = 0;
        foreach (glob($this->tmpDir . '/*') as $f) {
            $totalFileSize += filesize($f);
        }

        $volData = $volume['data'];
        $duration = 1;
        if (!empty($volData)) {
            $firstTs = strtotime($volData[0]['ts']);
            $lastTs = strtotime(end($volData)['ts']);
            $duration = max(1, $lastTs - $firstTs + 1);
        }

        $avgTxnSec = $duration > 0 ? round($gtid['total_transactions'] / $duration, 1) : 0;

        $recs = $this->generateRecommendations($gtid, $dml, $volume, $duration);

        $serverIdCmd = $this->buildBinlogCmd(false) . " 2>/dev/null | head -20";
        $headerRaw = shell_exec($serverIdCmd . " 2>/dev/null") ?: '';
        $serverId = 0;
        if (preg_match('/server id\s+(\d+)/', $headerRaw, $m)) {
            $serverId = (int) $m[1];
        }

        $sets = [
            "server_id = " . $serverId,
            "total_size_bytes = " . (int) $totalFileSize,
            "duration_seconds = " . (int) $duration,
            "total_transactions = " . (int) $gtid['total_transactions'],
            "total_inserts = " . (int) $dml['inserts'],
            "total_updates = " . (int) $dml['updates'],
            "total_deletes = " . (int) $dml['deletes'],
            "total_ddl = " . (int) $ddlCount,
            "max_txn_size_bytes = " . (int) $gtid['max_size'],
            "large_txn_100k = " . (int) $gtid['large_100k'],
            "large_txn_500k = " . (int) $gtid['large_500k'],
            "peak_txn_per_sec = " . (int) $volume['peak_txn'],
            "avg_txn_per_sec = " . (float) $avgTxnSec,
            "sequential_pct = " . (float) $gtid['sequential_pct'],
            "max_parallelism = " . (int) $gtid['max_parallelism'],
            "parallelism_distribution = '" . $this->db->sql_real_escape_string(json_encode($gtid['parallelism_distrib'])) . "'",
            "top_tables = '" . $this->db->sql_real_escape_string(json_encode($dml['top_tables'])) . "'",
            "databases_count = " . (int) $dml['db_count'],
            "volume_per_second = '" . $this->db->sql_real_escape_string(json_encode($volData)) . "'",
            "recommendations = '" . $this->db->sql_real_escape_string(json_encode($recs)) . "'",
        ];

        $this->db->sql_query("UPDATE binlog_analysis SET " . implode(', ', $sets) . " WHERE id = " . $this->analysisId);
    }

    private function generateRecommendations(array $gtid, array $dml, array $volume, int $duration): array
    {
        $recs = [];

        if ($volume['peak_txn'] > 200) {
            $recs[] = "High write throughput (peak " . number_format($volume['peak_txn']) . " txn/s). Ensure replica_parallel_workers >= 8.";
        }

        if ($gtid['sequential_pct'] > 25) {
            $recs[] = "Sequential transaction ratio is " . $gtid['sequential_pct'] . "%. Consider binlog_transaction_dependency_tracking = WRITESET.";
        }

        if ($gtid['max_parallelism'] < 4 && $gtid['total_transactions'] > 100) {
            $recs[] = "Max parallelism is only " . $gtid['max_parallelism'] . " txn/group. MTS workers will be underutilized.";
        }

        if ($gtid['large_500k'] > 0) {
            $recs[] = $gtid['large_500k'] . " transaction(s) > 500 KB. Large transactions block the SQL applier thread.";
        }

        if ($gtid['large_100k'] > 10) {
            $recs[] = $gtid['large_100k'] . " transactions > 100 KB. Consider splitting batch operations.";
        }

        $totalRows = $dml['inserts'] + $dml['updates'] + $dml['deletes'];
        if ($duration > 0 && $totalRows / $duration > 3000) {
            $recs[] = "Row throughput ~" . number_format($totalRows / $duration) . " rows/s. Sustained high-volume writes.";
        }

        if ($dml['db_count'] > 50) {
            $recs[] = $dml['db_count'] . " databases modified. Multi-tenant pattern may cause replication hot-spots.";
        }

        return $recs;
    }

    // ------------------------------------------------------------------
    //  Helpers
    // ------------------------------------------------------------------

    private function updateStatus(string $status, ?string $error = null): void
    {
        $sql = "UPDATE binlog_analysis SET status = '" . $this->db->sql_real_escape_string($status) . "'";
        if ($error !== null) {
            $sql .= ", error_message = '" . $this->db->sql_real_escape_string($error) . "'";
        }
        $sql .= " WHERE id = " . $this->analysisId;
        $this->db->sql_query($sql);
    }

    private function cleanup(): void
    {
        if (is_dir($this->tmpDir)) {
            array_map('unlink', glob($this->tmpDir . '/*') ?: []);
            @rmdir($this->tmpDir);
        }
    }
}
