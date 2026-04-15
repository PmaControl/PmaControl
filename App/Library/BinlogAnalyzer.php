<?php
/**
 * BinlogAnalyzer — fetch binlog files from a master via MySQL protocol
 * (--read-from-remote-server), parse them with a version-matched mysqlbinlog
 * binary, and store the parsed statistics.
 *
 * Supports MySQL 5.6+ / 8.x / 9.x and MariaDB 10.x+ (x86_64 and aarch64).
 * Fetched binlogs are cached in data/binlog_analysis/{server_id}/ with a
 * 30-day TTL and per-file .meta.json sidecar for AI analysis.
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
    private $cachedAnalysis = null;
    private $cachedBinary = null;
    private $tmpDir;
    private $steps = [];

    private const CACHE_BASE = ROOT . '/data/binlog_analysis/';
    private const CACHE_TTL_DAYS = 30;

    private $cacheDir;

    public function __construct(int $analysisId)
    {
        $this->db = Sgbd::sql(DB_DEFAULT);
        $this->analysisId = $analysisId;
        $this->tmpDir = '/tmp/binlog_analysis_' . $analysisId;
    }

    /**
     * Set up the persistent cache directory for a given master server.
     * Layout: data/binlog_analysis/{id_mysql_server}/mysql-bin.000123
     */
    private function initCache(int $masterId): void
    {
        $this->cacheDir = self::CACHE_BASE . $masterId . '/';
        @mkdir($this->cacheDir, 0755, true);
    }

    /**
     * Check if a binlog file is already cached and still valid (< TTL).
     */
    private function getCachedBinlog(string $binlogName): ?string
    {
        if (empty($this->cacheDir)) return null;
        $path = $this->cacheDir . $binlogName;
        if (file_exists($path) && filesize($path) > 4) {
            $age = time() - filemtime($path);
            if ($age < self::CACHE_TTL_DAYS * 86400) {
                return $path;
            }
        }
        return null;
    }

    /**
     * Store a fetched binlog in the persistent cache.
     */
    private function cacheBinlog(string $binlogName, string $tmpPath): void
    {
        if (empty($this->cacheDir)) return;
        if (!file_exists($tmpPath) || filesize($tmpPath) < 4) return;
        $dest = $this->cacheDir . $binlogName;
        @copy($tmpPath, $dest);
    }

    /**
     * Write a metadata JSON sidecar for a cached binlog file.
     * Stored as: data/binlog_analysis/{server_id}/{binlog_name}.meta.json
     *
     * Contains structured data suitable for AI/LLM analysis:
     * - file info (name, size, timestamps)
     * - server context (master id, version, server_id)
     * - transaction summary (count, size, parallelism)
     * - DML breakdown per table
     * - volume timeline
     * - DDL statements
     */
    private function writeBinlogMetadata(string $binlogName, array $analysis, array $gtidStats, array $dmlStats, array $volumeStats, array $ddlResult): void
    {
        if (empty($this->cacheDir)) return;

        $filePath = $this->cacheDir . $binlogName;
        $metaPath = $this->cacheDir . $binlogName . '.meta.json';

        $meta = [
            '_schema_version' => 1,
            '_generated_at'   => date('Y-m-d H:i:s'),
            '_generator'      => 'PmaControl BinlogAnalyzer',

            'file' => [
                'name'      => $binlogName,
                'size_bytes' => file_exists($filePath) ? filesize($filePath) : 0,
                'cached_at' => date('Y-m-d H:i:s', file_exists($filePath) ? filemtime($filePath) : time()),
            ],

            'server' => [
                'master_id'      => (int)($analysis['id_mysql_server__master'] ?? 0),
                'slave_id'       => (int)($analysis['id_mysql_server'] ?? 0),
                'mysql_version'  => $analysis['mysql_version'] ?? '',
                'connection_name' => $analysis['connection_name'] ?? '',
                'server_id'      => (int)($analysis['server_id'] ?? 0),
            ],

            'time_range' => [
                'start' => $analysis['time_start'] ?? '',
                'end'   => $analysis['time_end'] ?? '',
            ],

            'transactions' => [
                'total'          => $gtidStats['total_transactions'] ?? 0,
                'total_size_bytes' => $gtidStats['total_size'] ?? 0,
                'max_size_bytes' => $gtidStats['max_size'] ?? 0,
                'large_100k'     => $gtidStats['large_100k'] ?? 0,
                'large_500k'     => $gtidStats['large_500k'] ?? 0,
                'sequential_pct' => $gtidStats['sequential_pct'] ?? 0,
                'max_parallelism' => $gtidStats['max_parallelism'] ?? 0,
            ],

            'dml' => [
                'total_rows' => ($dmlStats['inserts'] ?? 0) + ($dmlStats['updates'] ?? 0) + ($dmlStats['deletes'] ?? 0),
                'inserts'    => $dmlStats['inserts'] ?? 0,
                'updates'    => $dmlStats['updates'] ?? 0,
                'deletes'    => $dmlStats['deletes'] ?? 0,
                'databases'  => $dmlStats['db_count'] ?? 0,
                'top_tables' => array_slice($dmlStats['top_tables'] ?? [], 0, 20),
            ],

            'volume_per_second' => [
                'peak_txn_per_sec'   => $volumeStats['peak_txn'] ?? 0,
                'peak_bytes_per_sec' => $volumeStats['peak_bytes'] ?? 0,
                'data_points'        => count($volumeStats['data'] ?? []),
            ],

            'ddl' => [
                'count'   => $ddlResult['count'] ?? 0,
                'details' => array_slice($ddlResult['details'] ?? [], 0, 50),
            ],
        ];

        @file_put_contents($metaPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Purge cached binlog files older than TTL across all servers.
     */
    public static function purgeCacheOlderThan(int $days = self::CACHE_TTL_DAYS): int
    {
        $count = 0;
        $cutoff = time() - ($days * 86400);
        $base = self::CACHE_BASE;
        if (!is_dir($base)) return 0;

        foreach (glob($base . '*/') as $serverDir) {
            foreach (glob($serverDir . '*') as $file) {
                if (is_file($file) && filemtime($file) < $cutoff) {
                    @unlink($file);
                    $count++;
                }
            }
            // Remove empty server dirs
            if (is_dir($serverDir) && count(glob($serverDir . '*')) === 0) {
                @rmdir($serverDir);
            }
        }
        return $count;
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
            $masterId = (int) $analysis['id_mysql_server__master'];
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

            // Step 4 — Select mysqlbinlog binary (needed for remote fetch too)
            $binary = $this->getMysqlbinlogBinary($version);
            $this->addStep('binary', "Selecting mysqlbinlog binary for version $version...");
            $this->ensureBinary($binary);
            $binaryVersion = trim(shell_exec(escapeshellarg($binary) . " --version 2>&1") ?: 'unknown');
            $this->updateLastStep("Using: " . basename($binary) . " — " . $binaryVersion);

            // Step 5 — Get MySQL credentials for remote binlog fetch
            $this->addStep('credentials', "Decrypting MySQL credentials for master...");
            $mysqlCreds = $this->getMysqlCredentials($master);
            $this->updateLastStep("MySQL user: " . $mysqlCreds['user'] . "@" . $master['ip'] . ":" . $master['port']);

            // Step 6 — Find binlog files using slave's current position as anchor
            $this->addStep('find_binlogs', "Finding binlog files for time range (using slave position as anchor)...");
            $binlogFiles = $this->findBinlogFilesFromSlavePos($binary, $mysqlCreds, $master, $analysis);
            if (empty($binlogFiles)) {
                throw new \Exception("No binlog files found for the specified time range");
            }
            $this->db->sql_query("UPDATE binlog_analysis SET binlog_files = '" . $this->db->sql_real_escape_string(json_encode($binlogFiles)) . "' WHERE id = " . $this->analysisId);
            $this->updateLastStep("Found " . count($binlogFiles) . " binlog file(s): " . implode(', ', $binlogFiles));

            // Step 7 — Fetch binlogs (with persistent cache)
            $this->initCache($masterId);
            self::purgeCacheOlderThan();

            // Get binlog sizes from master for dynamic timeout
            $binlogSizes = $this->getBinlogSizes($mysqlCreds, $binlogFiles);

            @mkdir($this->tmpDir, 0755, true);
            foreach ($binlogFiles as $idx => $binlogName) {
                $this->addStep('fetch', "Fetching $binlogName (" . ($idx + 1) . "/" . count($binlogFiles) . ")...");
                $cachedPath = $this->getCachedBinlog($binlogName);
                if ($cachedPath) {
                    $localPath = $this->tmpDir . '/' . $binlogName;
                    @symlink($cachedPath, $localPath);
                    $sizeMb = round(filesize($cachedPath) / 1048576, 1);
                    $this->updateLastStep("Cache hit: $binlogName — $sizeMb MB (cached)");
                } else {
                    $expectedSize = $binlogSizes[$binlogName] ?? 0;
                    $this->fetchBinlogRemote($binary, $mysqlCreds, $master, $binlogName, $analysis['time_start'], $analysis['time_end'], $expectedSize);
                    $localPath = $this->tmpDir . '/' . $binlogName;
                    $localSize = file_exists($localPath) ? filesize($localPath) : 0;
                    $sizeMb = round($localSize / 1048576, 1);
                    $this->cacheBinlog($binlogName, $localPath);
                    $this->updateLastStep("Fetched $binlogName — $sizeMb MB (via --read-from-remote-server, cached)");
                }
            }

            // Step 7b — Probe time range per binlog file
            $this->addStep('file_ranges', "Probing time boundaries for each binlog file...");
            $fileRanges = [];
            foreach ($binlogFiles as $binlogName) {
                $localPath = $this->tmpDir . '/' . $binlogName;
                $realPath = is_link($localPath) ? readlink($localPath) : $localPath;
                if (!file_exists($realPath)) continue;
                $size = filesize($realPath);
                $first = $this->probeLocalBinlogTimestamp($realPath, $binary, 'first');
                $last = $this->probeLocalBinlogTimestamp($realPath, $binary, 'last');
                $fileRanges[] = [
                    'name'  => $binlogName,
                    'size'  => $size,
                    'start' => $first,
                    'end'   => $last,
                ];
            }
            $this->updateLastStep(count($fileRanges) . " file(s) probed");

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

            // Enrich file ranges with per-file DML data and store
            foreach ($fileRanges as &$fr) {
                $fr['tables'] = $dmlStats['dml_per_file'][$fr['name']] ?? [];
            }
            unset($fr);
            $this->db->sql_query("UPDATE binlog_analysis SET binlog_file_ranges = '"
                . $this->db->sql_real_escape_string(json_encode($fileRanges))
                . "' WHERE id = " . $this->analysisId);
            $this->invalidateAnalysisCache();

            // Step 10 — Parse: volume per second
            $this->addStep('parse_volume', "Computing volume per second (timestamps + transaction_length)...");
            $volumeStats = $this->parseVolumePerSecond();
            $this->updateLastStep(
                count($volumeStats['data']) . " seconds of data, peak " . $volumeStats['peak_txn'] . " txn/s, peak " . round($volumeStats['peak_bytes'] / 1024) . " KB/s"
            );

            // Step 11 — Parse: DDL
            $this->addStep('parse_ddl', "Checking for DDL statements (CREATE/ALTER/DROP)...");
            $ddlResult = $this->parseDdlStatements();
            $ddlCount = $ddlResult['count'];
            $this->updateLastStep($ddlCount > 0 ? "$ddlCount DDL statement(s) found" : "No DDL — 100% DML row-based");

            // Step 12 — Write metadata sidecar for each cached binlog
            $this->addStep('metadata', "Writing metadata JSON for cached binlogs...");
            foreach ($binlogFiles as $binlogName) {
                $this->writeBinlogMetadata($binlogName, $analysis, $gtidStats, $dmlStats, $volumeStats, $ddlResult);
            }
            $this->updateLastStep(count($binlogFiles) . " metadata file(s) written");

            // Step 13 — Compile & store results
            $this->addStep('store', "Compiling final report and storing results...");
            $this->storeResults($gtidStats, $dmlStats, $volumeStats, $ddlResult, $analysis);
            $this->updateLastStep("Report stored successfully");

            // Step 14 — Cleanup
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
        if ($this->cachedAnalysis !== null) {
            return $this->cachedAnalysis;
        }
        $res = $this->db->sql_query("SELECT * FROM binlog_analysis WHERE id = " . (int) $this->analysisId);
        $row = $this->db->sql_fetch_array($res, MYSQLI_ASSOC);
        $this->cachedAnalysis = $row ?: null;
        return $this->cachedAnalysis;
    }

    /**
     * Force refresh the cached analysis (after UPDATE statements).
     */
    private function invalidateAnalysisCache(): void
    {
        $this->cachedAnalysis = null;
    }

    private function getServer(int $id): ?array
    {
        $res = $this->db->sql_query("SELECT * FROM mysql_server WHERE id = " . $id);
        $row = $this->db->sql_fetch_array($res, MYSQLI_ASSOC);
        return $row ?: null;
    }

    private function getMasterVersion(int $masterId): ?string
    {
        // Primary source: global_variable table (populated by Aspirateur)
        $res = $this->db->sql_query(
            "SELECT value FROM global_variable WHERE id_mysql_server = $masterId AND variable_name = 'version' LIMIT 1"
        );
        if ($res && $row = $this->db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            return $row['value'];
        }

        // Fallback: Extraction2 time-series (may have data before global_variable is populated)
        $versionData = Extraction2::display(array('version'), array($masterId));
        if (!empty($versionData[$masterId]['version'])) {
            return (string) $versionData[$masterId]['version'];
        }

        // Last resort: direct query on the master (just after server creation, no collected data yet)
        try {
            $master = $this->getServer($masterId);
            if ($master) {
                $creds = $this->getMysqlCredentials($master);
                $link = new \mysqli($creds['host'], $creds['user'], $creds['password'], '', $creds['port']);
                if (!$link->connect_error) {
                    $qr = $link->query("SELECT @@version AS v");
                    if ($qr && $vrow = $qr->fetch_assoc()) {
                        $link->close();
                        return $vrow['v'];
                    }
                    $link->close();
                }
            }
        } catch (\Throwable $e) {}

        return null;
    }

    // ------------------------------------------------------------------
    //  MySQL credentials
    // ------------------------------------------------------------------

    /**
     * Get MySQL credentials for the master server, decrypted.
     */
    private function getMysqlCredentials(array $master): array
    {
        $password = $master['passwd'];
        if (!empty($master['is_password_crypted']) && $master['is_password_crypted'] == 1) {
            $password = Crypt::decrypt($password, CRYPT_KEY);
        }
        return [
            'user'     => $master['login'],
            'password' => $password,
            'host'     => $master['ip'],
            'port'     => (int) ($master['port'] ?: 3306),
        ];
    }

    // ------------------------------------------------------------------
    //  Remote binlog discovery (via MySQL protocol)
    // ------------------------------------------------------------------

    /**
     * Smart binlog discovery: use the slave's current IO position as an anchor point
     * to avoid scanning the entire SHOW BINARY LOGS list. The slave knows exactly
     * which binlog file it's reading from the master right now, so we only need to
     * look at a small window around that position.
     */
    private function findBinlogFilesFromSlavePos(string $binary, array $creds, array $master, array $analysis): array
    {
        $slaveId = (int) $analysis['id_mysql_server'];
        $connName = $analysis['connection_name'] ?? '';

        // Get slave's current binlog position from the slave server
        $slaveServer = $this->getServer($slaveId);
        $slaveCreds = $this->getMysqlCredentials($slaveServer);
        $slaveLink = new \mysqli($slaveCreds['host'], $slaveCreds['user'], $slaveCreds['password'], '', $slaveCreds['port']);
        if ($slaveLink->connect_error) {
            return $this->findBinlogFilesRemote($binary, $creds, $master, $analysis['time_start'], $analysis['time_end']);
        }

        // Multi-source: query the specific channel, not the default one
        $slaveStatus = null;
        $isMariaDBSlave = (stripos($slaveServer['version'] ?? $this->detectVersionFromLink($slaveLink), 'mariadb') !== false);

        if (!empty($connName)) {
            if ($isMariaDBSlave) {
                $res = @$slaveLink->query("SHOW SLAVE '" . $slaveLink->real_escape_string($connName) . "' STATUS");
            } else {
                $res = @$slaveLink->query("SHOW REPLICA STATUS FOR CHANNEL '" . $slaveLink->real_escape_string($connName) . "'");
                if (!$res) $res = @$slaveLink->query("SHOW SLAVE STATUS FOR CHANNEL '" . $slaveLink->real_escape_string($connName) . "'");
            }
            if ($res && $res->num_rows > 0) {
                $slaveStatus = $res->fetch_assoc();
                $res->free();
            }
        }

        // Fallback: default channel (single-source or channel query failed)
        if (!$slaveStatus) {
            $res = @$slaveLink->query("SHOW REPLICA STATUS");
            if (!$res) $res = @$slaveLink->query("SHOW SLAVE STATUS");
            if ($res && $res->num_rows > 0) {
                // For multi-source, find the row matching our channel
                if (!empty($connName)) {
                    while ($row = $res->fetch_assoc()) {
                        $cn = $row['Connection_name'] ?? $row['Channel_Name'] ?? '';
                        if ($cn === $connName) { $slaveStatus = $row; break; }
                    }
                } else {
                    $slaveStatus = $res->fetch_assoc();
                }
                $res->free();
            }
        }

        $slaveLink->close();

        if (!$slaveStatus) {
            return $this->findBinlogFilesRemote($binary, $creds, $master, $analysis['time_start'], $analysis['time_end']);
        }

        // Current master binlog being read by the IO thread
        $currentBinlog = $slaveStatus['Master_Log_File'] ?? $slaveStatus['Source_Log_File'] ?? '';
        // Extract numeric suffix: mysql-bin.1054501 → 1054501
        if (!preg_match('/\.(\d+)$/', $currentBinlog, $m)) {
            return $this->findBinlogFilesRemote($binary, $creds, $master, $analysis['time_start'], $analysis['time_end']);
        }
        $currentNum = (int) $m[1];
        $prefix = substr($currentBinlog, 0, strrpos($currentBinlog, '.'));

        // Also get the master's current binlog position (may be ahead if slave has lag)
        $masterLink = new \mysqli($creds['host'], $creds['user'], $creds['password'], '', $creds['port']);
        $masterCurrentNum = $currentNum;
        if (!$masterLink->connect_error) {
            // SHOW MASTER STATUS removed in MySQL 8.4, try BINARY LOG STATUS first
            $mRes = null;
            try { $mRes = $masterLink->query("SHOW BINARY LOG STATUS"); } catch (\Throwable $e) {}
            if (!$mRes) {
                try { $mRes = $masterLink->query("SHOW MASTER STATUS"); } catch (\Throwable $e) {}
            }
            if ($mRes && $mRow = $mRes->fetch_assoc()) {
                $masterFile = $mRow['File'] ?? '';
                if (preg_match('/\.(\d+)$/', $masterFile, $mm)) {
                    $masterCurrentNum = (int) $mm[1];
                }
                $mRes->free();
            }
            $masterLink->close();
        }

        // The time range could be between the slave's current read position and the
        // master's current write position (if the slave is lagging behind).
        $now = time();
        $rangeEnd = strtotime($analysis['time_end']);
        $rangeStart = strtotime($analysis['time_start']);
        $ageSeconds = max(1, $now - $rangeStart);
        $rangeDuration = max(1, $rangeEnd - $rangeStart);

        // Estimate how many binlogs back from the slave position we need
        $estimatedBinlogsBack = max(20, (int)(($ageSeconds + $rangeDuration) / 30));

        // Range: go back from slave position AND forward to master position
        $startNum = max(1, $currentNum - $estimatedBinlogsBack);
        $endNum = $masterCurrentNum; // include all up to master's current file

        $connArgs = $this->buildRemoteArgs($creds);

        $lagFiles = $masterCurrentNum - $currentNum;
        $lagInfo = $lagFiles > 0 ? " (slave lag: $lagFiles files behind master)" : "";
        $this->updateLastStep("Slave at $currentBinlog, master at $prefix." . str_pad($masterCurrentNum, strlen($m[1]), '0', STR_PAD_LEFT) . "$lagInfo. Scanning $prefix." . $startNum . " → $prefix." . $endNum . " (~" . ($endNum - $startNum + 1) . " candidates)...");

        // Binary search within this narrow range
        $candidates = [];
        for ($i = $startNum; $i <= $endNum; $i++) {
            $candidates[] = ['name' => $prefix . '.' . str_pad($i, strlen($m[1]), '0', STR_PAD_LEFT)];
        }

        $startTs = strtotime($analysis['time_start']);
        $endTs = strtotime($analysis['time_end']);

        // Binary search for start file
        $startIdx = $this->binarySearchBinlog($binary, $connArgs, $candidates, $startTs);
        $endIdx = $this->binarySearchBinlog($binary, $connArgs, $candidates, $endTs);

        $from = max(0, $startIdx - 1);
        $to = min(count($candidates) - 1, $endIdx);

        $selectedFiles = [];
        for ($i = $from; $i <= $to; $i++) {
            $selectedFiles[] = $candidates[$i]['name'];
        }

        if (empty($selectedFiles)) {
            // Fallback
            $selectedFiles[] = $currentBinlog;
        }

        return $selectedFiles;
    }

    /**
     * Find binlog files that cover the requested time range.
     * Uses a direct MySQL connection + binary search on binlog list.
     * Fallback method when slave position is not available.
     */
    private function findBinlogFilesRemote(string $binary, array $creds, array $master, string $timeStart, string $timeEnd): array
    {
        $link = new \mysqli($creds['host'], $creds['user'], $creds['password'], '', $creds['port']);
        if ($link->connect_error) {
            throw new \Exception("Cannot connect to master MySQL " . $creds['host'] . ":" . $creds['port'] . " — " . $link->connect_error);
        }

        $res = $link->query("SHOW BINARY LOGS");
        $allBinlogs = [];
        while ($row = $res->fetch_assoc()) {
            $name = $row['Log_name'] ?? '';
            $size = (int) ($row['File_size'] ?? 0);
            if ($name) {
                $allBinlogs[] = ['name' => $name, 'size' => $size];
            }
        }
        $res->free();
        $link->close();

        if (empty($allBinlogs)) {
            throw new \Exception("SHOW BINARY LOGS returned empty on master");
        }

        $this->updateLastStep("SHOW BINARY LOGS: " . count($allBinlogs) . " files. Binary-searching for time range...");

        $startTs = strtotime($timeStart);
        $endTs = strtotime($timeEnd);
        $connArgs = $this->buildRemoteArgs($creds);

        // Binary search: find the first binlog that starts AFTER $startTs
        // The file just before it is the one that contains $startTs
        $startIdx = $this->binarySearchBinlog($binary, $connArgs, $allBinlogs, $startTs);
        $endIdx = $this->binarySearchBinlog($binary, $connArgs, $allBinlogs, $endTs);

        // Include one file before startIdx (it may contain events in our range)
        $from = max(0, $startIdx - 1);
        $to = min(count($allBinlogs) - 1, $endIdx);

        $selectedFiles = [];
        for ($i = $from; $i <= $to; $i++) {
            $selectedFiles[] = $allBinlogs[$i]['name'];
        }

        if (empty($selectedFiles)) {
            $selectedFiles[] = end($allBinlogs)['name'];
        }

        return $selectedFiles;
    }

    /**
     * Binary search: find the index of the binlog file that contains the given timestamp.
     * Returns the index of the last file whose first event is <= $targetTs.
     */
    private function binarySearchBinlog(string $binary, string $connArgs, array $binlogs, int $targetTs): int
    {
        $lo = 0;
        $hi = count($binlogs) - 1;
        $result = 0;

        while ($lo <= $hi) {
            $mid = intdiv($lo + $hi, 2);
            $fileTs = $this->probeBinlogTimestamp($binary, $connArgs, $binlogs[$mid]['name']);

            if ($fileTs === null) {
                // Can't read this file, try next
                $lo = $mid + 1;
                continue;
            }

            if ($fileTs <= $targetTs) {
                $result = $mid;
                $lo = $mid + 1;
            } else {
                $hi = $mid - 1;
            }
        }

        return $result;
    }

    /**
     * Probe the first event timestamp of a binlog file via --read-from-remote-server.
     */
    private function probeBinlogTimestamp(string $binary, string $connArgs, string $binlogName): ?int
    {
        // 8192 bytes covers Format Description + Previous GTIDs + first real event
        $cmd = escapeshellarg($binary) . " $connArgs"
             . " --stop-position=8192 " . escapeshellarg($binlogName)
             . " 2>/dev/null | grep -aoP '^#\\d{6}\\s+\\d+:\\d+:\\d+' | head -1";
        $firstTs = trim(shell_exec($cmd) ?: '');

        if (empty($firstTs)) return null;

        if (preg_match('/^#(\d{2})(\d{2})(\d{2})\s+(\d+:\d+:\d+)$/', $firstTs, $tm)) {
            $year = 2000 + (int) $tm[1];
            return strtotime("$year-$tm[2]-$tm[3] $tm[4]");
        }

        return null;
    }

    // ------------------------------------------------------------------
    //  Remote binlog fetch (like IO thread)
    // ------------------------------------------------------------------

    /**
     * Fetch a binlog from the master using --read-from-remote-server.
     * This works like the replica IO thread — pure MySQL protocol, no SSH.
     *
     * IMPORTANT: --raw mode ignores --start-datetime/--stop-datetime on
     * mysqlbinlog <= 5.7 (it acts like a slave IO thread and blocks).
     * We use --raw WITHOUT datetime filters — the time filtering is done
     * later during the parse phase.
     */
    private const STALL_TIMEOUT = 120; // seconds without file growth → kill

    private function fetchBinlogRemote(string $binary, array $creds, array $master, string $binlogName, string $timeStart, string $timeEnd, int $expectedSizeBytes = 0): void
    {
        $connArgs = $this->buildRemoteArgs($creds);
        $localPath = $this->tmpDir . '/' . $binlogName;

        // --raw without datetime filters: downloads the full file and exits
        // --stop-never is NOT used (we want it to stop after one file)
        $cmd = escapeshellarg($binary) . " $connArgs"
             . " --raw --result-file=" . escapeshellarg($this->tmpDir . '/')
             . " " . escapeshellarg($binlogName)
             . " 2>&1";

        // Launch in background and monitor file growth
        $logFile = $this->tmpDir . '/' . $binlogName . '.fetch.log';
        $bgCmd = $cmd . " > " . escapeshellarg($logFile) . " 2>&1 & echo $!";
        $pid = (int) trim(shell_exec($bgCmd) ?: '0');

        if ($pid <= 0) {
            throw new \Exception("Failed to launch mysqlbinlog for $binlogName");
        }

        // Poll: kill if file stops growing for STALL_TIMEOUT seconds
        $lastSize = 0;
        $lastGrowth = time();

        while ($this->isProcessAlive($pid)) {
            sleep(10);

            $currentSize = file_exists($localPath) ? filesize($localPath) : 0;
            clearstatcache(true, $localPath);
            $currentSize = file_exists($localPath) ? filesize($localPath) : 0;

            if ($currentSize > $lastSize) {
                $lastSize = $currentSize;
                $lastGrowth = time();
            } elseif ((time() - $lastGrowth) > self::STALL_TIMEOUT) {
                // Stalled — kill the process
                @posix_kill($pid, 9);
                $output = @file_get_contents($logFile) ?: '';
                @unlink($logFile);
                throw new \Exception("Fetch stalled for $binlogName — no growth for " . self::STALL_TIMEOUT . "s (last size: " . round($lastSize / 1048576, 1) . " MB): " . substr($output, 0, 300));
            }
        }

        @unlink($logFile);

        if (!file_exists($localPath) || filesize($localPath) < 4) {
            $output = '';
            throw new \Exception("Failed to fetch $binlogName via --read-from-remote-server: " . substr($output, 0, 300));
        }
    }

    /**
     * Build connection arguments for mysqlbinlog --read-from-remote-server.
     */
    private $tmpCnfFiles = [];

    /**
     * Build connection arguments for mysqlbinlog using a temporary .my.cnf file
     * instead of --password= to avoid exposing credentials in ps output.
     */
    private function buildRemoteArgs(array $creds): string
    {
        $tmpCnf = tempnam(sys_get_temp_dir(), 'pma_mycnf_');
        $content = "[client]\n"
            . "user=" . $creds['user'] . "\n"
            . "password=" . $creds['password'] . "\n"
            . "host=" . $creds['host'] . "\n"
            . "port=" . (int) $creds['port'] . "\n";
        file_put_contents($tmpCnf, $content);
        chmod($tmpCnf, 0600);

        $this->tmpCnfFiles[] = $tmpCnf;

        return " --defaults-extra-file=" . escapeshellarg($tmpCnf)
             . " --read-from-remote-server";
    }

    // ------------------------------------------------------------------
    //  mysqlbinlog binary selection
    // ------------------------------------------------------------------

    private const BINLOG_BASE_DIR = '/srv/www/pmacontrol/bin/mysqlbinlog/';

    /**
     * Get the binary directory for the current platform architecture.
     * Layout: bin/mysqlbinlog/{x86_64,aarch64}/mysqlbinlog-{version}
     */
    private static function getBinlogDir(): string
    {
        $arch = php_uname('m'); // x86_64, aarch64, arm64
        if ($arch === 'arm64') $arch = 'aarch64';
        $dir = self::BINLOG_BASE_DIR . $arch . '/';
        if (is_dir($dir)) {
            return $dir;
        }
        // Fallback to base dir (legacy flat layout)
        return self::BINLOG_BASE_DIR;
    }

    /**
     * Select the right mysqlbinlog binary based on the master's MySQL/MariaDB version.
     * Binaries are stored as: bin/mysqlbinlog/{arch}/mysqlbinlog-{version}
     */
    private function getMysqlbinlogBinary(string $version): string
    {
        if ($this->cachedBinary !== null) {
            return $this->cachedBinary;
        }
        $this->cachedBinary = $this->resolveMysqlbinlogBinary($version);
        return $this->cachedBinary;
    }

    private function resolveMysqlbinlogBinary(string $version): string
    {
        $dir = self::getBinlogDir();
        $vLower = strtolower($version);

        if (strpos($vLower, 'mariadb') !== false) {
            $bin = $dir . 'mysqlbinlog-mariadb';
            if (file_exists($bin) && filesize($bin) > 1024) return $bin;
            // Fallback: system-installed mariadb-binlog (common on ARM64)
            foreach (['/usr/bin/mariadb-binlog', '/usr/bin/mysqlbinlog'] as $sysBin) {
                if (is_executable($sysBin)) return $sysBin;
            }
            throw new \Exception("mysqlbinlog binary for MariaDB not found at $bin — install mariadb-client or copy mariadb-binlog to $bin");
        }

        // MySQL Oracle: extract major.minor  "8.0.44" → "8.0"
        if (preg_match('/^(\d+\.\d+)/', $version, $m)) {
            $majorMinor = $m[1];

            // Exact match first
            $bin = $dir . 'mysqlbinlog-' . $majorMinor;
            if (file_exists($bin)) return $bin;

            // Fallback: try closest compatible version
            $available = glob($dir . 'mysqlbinlog-*');
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
        $fallback = $dir . 'mysqlbinlog-8.4';
        if (file_exists($fallback)) return $fallback;

        throw new \Exception("No suitable mysqlbinlog binary found in $dir for version $version (arch: " . php_uname('m') . ")");
    }

    private function ensureBinary(string $binary): void
    {
        if (!file_exists($binary)) {
            throw new \Exception("mysqlbinlog binary not found: $binary — run: ls -la " . self::getBinlogDir());
        }
        if (!is_executable($binary)) {
            // Try PHP chmod first (works if www-data owns the file)
            @chmod($binary, 0755);
            if (!is_executable($binary)) {
                // Fallback: try shell chmod (works if sudo/setfacl is available)
                @shell_exec("chmod +x " . escapeshellarg($binary) . " 2>/dev/null");
            }
            if (!is_executable($binary)) {
                throw new \Exception("mysqlbinlog binary exists but is not executable: $binary — run: chmod +x " . self::getBinlogDir() . "*");
            }
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

        // Add only binlog files (exclude .meta.json or other non-binlog files)
        $files = glob($this->tmpDir . '/*');
        sort($files);
        foreach ($files as $f) {
            if (preg_match('/\.(json|log|txt|md)$/i', $f)) continue;
            $cmd .= " " . escapeshellarg($f);
        }

        return $cmd;
    }

    // ------------------------------------------------------------------
    //  Parsing stages
    // ------------------------------------------------------------------

    private function parseGtidEvents(): array
    {
        $analysis = $this->getAnalysis();
        $version = $analysis['mysql_version'] ?? '';
        $isMariaDB = (stripos($version, 'mariadb') !== false);

        if ($isMariaDB) {
            return $this->parseGtidEventsMariaDB();
        }

        // MySQL 5.x doesn't have transaction_length/last_committed — use MariaDB-style Xid parsing
        $numericVersion = preg_replace('/[^0-9.]/', '', $version);
        if (version_compare($numericVersion, '8.0', '<')) {
            return $this->parseGtidEventsMariaDB();
        }

        return $this->parseGtidEventsMySQL();
    }

    /**
     * MySQL 8+: parse transaction_length, last_committed, sequence_number from GTID events.
     */
    private function parseGtidEventsMySQL(): array
    {
        $cmd = $this->buildBinlogCmd(false);
        $cmdGtid = $cmd . " 2>/dev/null | grep -aE '(transaction_length|last_committed|sequence_number)=' | grep -aoP '(transaction_length|last_committed|sequence_number)=\\d+'";

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

    /**
     * MariaDB: no transaction_length/last_committed/sequence_number.
     * Count transactions via Xid events (InnoDB commit markers) and
     * compute sizes from end_log_pos differences between BEGIN and COMMIT/Xid.
     */
    private function parseGtidEventsMariaDB(): array
    {
        $cmd = $this->buildBinlogCmd(false);

        // Extract: timestamp, event type, end_log_pos for each event
        // MariaDB binlog format: #260414  0:01:25 server id 123  end_log_pos 12345  Query/Xid/...
        $cmdParse = $cmd . " 2>/dev/null | grep -aoP '^#\\d{6}\\s+\\d+:\\d+:\\d+\\s+server id\\s+\\d+\\s+end_log_pos\\s+\\d+.*?(Query|Xid|GTID)'"
                  . " | grep -aoP '(end_log_pos\\s+\\d+|Query|Xid|GTID)'";

        $output = shell_exec($cmdParse . " 2>/dev/null") ?: '';

        $txnCount = 0;
        $txnSizes = [];
        $beginPos = null;

        foreach (explode("\n", trim($output)) as $line) {
            $line = trim($line);
            if (preg_match('/end_log_pos\s+(\d+)/', $line, $m)) {
                $currentPos = (int) $m[1];
            } elseif ($line === 'Xid') {
                // Xid = InnoDB transaction commit
                $txnCount++;
                if ($beginPos !== null && isset($currentPos)) {
                    $size = $currentPos - $beginPos;
                    if ($size > 0) $txnSizes[] = $size;
                }
                $beginPos = null;
            } elseif ($line === 'Query') {
                // Could be BEGIN or COMMIT
                // We track position; BEGIN sets the start, COMMIT is tracked via Xid
                if ($beginPos === null && isset($currentPos)) {
                    $beginPos = $currentPos;
                }
            } elseif ($line === 'GTID') {
                // MariaDB GTID event marks the start of a new transaction group
                if (isset($currentPos)) {
                    $beginPos = $currentPos;
                }
            }
        }

        // Fallback: if the above didn't work well, just count Xid events directly
        if ($txnCount === 0) {
            $cmdXid = $cmd . " 2>/dev/null | grep -ac 'Xid'";
            $txnCount = (int) trim(shell_exec($cmdXid . " 2>/dev/null") ?: '0');
        }

        $totalSize = array_sum($txnSizes);
        $maxSize = empty($txnSizes) ? 0 : max($txnSizes);
        $large100k = count(array_filter($txnSizes, function($s) { return $s >= 100000; }));
        $large500k = count(array_filter($txnSizes, function($s) { return $s >= 500000; }));

        // MariaDB parallelism: group transactions by second from GTID events.
        // Transactions in the same second were committed together and can be
        // replayed in parallel (in optimistic mode).
        $cmd = $this->buildBinlogCmd(false);
        $cmdGtidTs = $cmd . " 2>/dev/null | grep -aoP '^#\\d{6}\\s+\\d+:\\d+:\\d+.*GTID.*trans' | grep -aoP '^#\\d{6}\\s+\\d+:\\d+:\\d+' ";
        $tsOutput = shell_exec($cmdGtidTs . " 2>/dev/null") ?: '';

        $txnPerSec = [];
        foreach (explode("\n", trim($tsOutput)) as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $txnPerSec[$line] = ($txnPerSec[$line] ?? 0) + 1;
            }
        }

        $maxParallelism = empty($txnPerSec) ? 0 : max($txnPerSec);
        // "Sequential" in MariaDB context: seconds where only 1 txn committed (no parallelism)
        $singleTxnSecs = count(array_filter($txnPerSec, function($c) { return $c === 1; }));
        $totalSecs = count($txnPerSec);
        $seqPct = $totalSecs > 0 ? round($singleTxnSecs / $totalSecs * 100, 1) : 0;

        $distrib = [];
        foreach ($txnPerSec as $count) {
            $distrib[$count] = ($distrib[$count] ?? 0) + 1;
        }
        ksort($distrib);

        return [
            'total_transactions' => $txnCount,
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
        $analysis = $this->getAnalysis();
        $version = $analysis['mysql_version'] ?? '';
        $binary = $this->getMysqlbinlogBinary($version);

        $inserts = 0;
        $updates = 0;
        $deletes = 0;
        $tableDml = [];
        $databases = [];
        $dmlPerFile = [];

        // Parse each file individually for per-file DML breakdown
        $files = glob($this->tmpDir . '/*');
        sort($files);
        foreach ($files as $f) {
            if (preg_match('/\.(json|log|txt|md)$/i', $f)) continue;
            $fileName = basename($f);

            $cmd = escapeshellarg($binary) . " -v --base64-output=DECODE-ROWS"
                 . " --start-datetime=" . escapeshellarg($analysis['time_start'])
                 . " --stop-datetime=" . escapeshellarg($analysis['time_end'])
                 . " " . escapeshellarg($f)
                 . " 2>/dev/null | grep -aoP '### (INSERT INTO|UPDATE|DELETE FROM) \`[^\`]+\`\.\`[^\`]+\`'";

            $output = shell_exec($cmd . " 2>/dev/null") ?: '';

            $fileDml = [];

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
                    if (!isset($fileDml[$key])) {
                        $fileDml[$key] = ['table' => $key, 'inserts' => 0, 'updates' => 0, 'deletes' => 0];
                    }
                    switch ($op) {
                        case 'INSERT INTO': $tableDml[$key]['inserts']++; $fileDml[$key]['inserts']++; $inserts++; break;
                        case 'UPDATE':      $tableDml[$key]['updates']++; $fileDml[$key]['updates']++; $updates++; break;
                        case 'DELETE FROM': $tableDml[$key]['deletes']++; $fileDml[$key]['deletes']++; $deletes++; break;
                    }
                }
            }

            $dmlPerFile[$fileName] = array_values($fileDml);
        }

        usort($tableDml, function ($a, $b) {
            return ($b['inserts'] + $b['updates'] + $b['deletes']) - ($a['inserts'] + $a['updates'] + $a['deletes']);
        });

        return [
            'inserts'    => $inserts,
            'updates'    => $updates,
            'deletes'    => $deletes,
            'top_tables'   => array_slice($tableDml, 0, 30),
            'db_count'     => count($databases),
            'dml_per_file' => $dmlPerFile,
        ];
    }

    private function parseVolumePerSecond(): array
    {
        $analysis = $this->getAnalysis();
        $isMariaDB = (stripos($analysis['mysql_version'] ?? '', 'mariadb') !== false);

        $cmd = $this->buildBinlogCmd(false);

        // MySQL 8+ has transaction_length in GTID events; MySQL 5.x and MariaDB don't
        $version = $analysis['mysql_version'] ?? '';
        $hasTxnLength = !$isMariaDB && version_compare(preg_replace('/[^0-9.]/', '', $version), '8.0', '>=');

        if ($hasTxnLength) {
            // MySQL 8+: use transaction_length from GTID events
            $cmdTs = $cmd . " 2>/dev/null | grep -aP 'transaction_length=\\d+' | grep -aoP '^#\\d{6}\\s+\\d+:\\d+:\\d+.*transaction_length=\\d+'";
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
        } else {
            // MariaDB: count Xid events per second and use end_log_pos delta as volume proxy
            $cmdTs = $cmd . " 2>/dev/null | grep -aoP '^#\\d{6}\\s+\\d+:\\d+:\\d+\\s+server id\\s+\\d+\\s+end_log_pos\\s+\\d+.*?Xid'";
            $output = shell_exec($cmdTs . " 2>/dev/null") ?: '';

            $volumePerSec = [];
            $txnPerSec = [];
            $lastPos = [];

            foreach (explode("\n", trim($output)) as $line) {
                if (preg_match('/#(\d{2})(\d{2})(\d{2})\s+(\d+:\d+:\d+).*end_log_pos\s+(\d+)/', $line, $m)) {
                    $fullTs = (2000 + (int)$m[1]) . '-' . $m[2] . '-' . $m[3] . ' ' . $m[4];
                    $pos = (int) $m[5];
                    $txnPerSec[$fullTs] = ($txnPerSec[$fullTs] ?? 0) + 1;

                    // Estimate bytes from pos delta (skip negative = new binlog file boundary)
                    if (isset($lastPos[$fullTs])) {
                        $delta = $pos - $lastPos[$fullTs];
                        if ($delta > 0) {
                            $volumePerSec[$fullTs] = ($volumePerSec[$fullTs] ?? 0) + $delta;
                        }
                    } else {
                        $volumePerSec[$fullTs] = ($volumePerSec[$fullTs] ?? 0);
                    }
                    $lastPos[$fullTs] = $pos;
                }
            }

            // If volume is 0 for all, estimate from total file size / seconds
            if (array_sum($volumePerSec) === 0 && !empty($txnPerSec)) {
                $totalSize = 0;
                foreach (glob($this->tmpDir . '/*') as $f) {
                    if (preg_match('/\.(json|log|txt|md)$/i', $f)) continue;
                    $totalSize += filesize($f);
                }
                $avgPerTxn = count($txnPerSec) > 0 ? $totalSize / array_sum($txnPerSec) : 0;
                foreach ($txnPerSec as $ts => $count) {
                    $volumePerSec[$ts] = (int) ($count * $avgPerTxn);
                }
            }
        }

        ksort($volumePerSec);
        $data = [];
        $peakBytes = 0;
        $peakTxn = 0;
        $minTxn = PHP_INT_MAX;
        foreach ($volumePerSec as $ts => $bytes) {
            $txn = $txnPerSec[$ts] ?? 0;
            $data[] = ['ts' => $ts, 'bytes' => $bytes, 'txn' => $txn];
            if ($bytes > $peakBytes) $peakBytes = $bytes;
            if ($txn > $peakTxn) $peakTxn = $txn;
            if ($txn < $minTxn) $minTxn = $txn;
        }
        if ($minTxn === PHP_INT_MAX) $minTxn = 0;

        return [
            'data'       => $data,
            'peak_bytes' => $peakBytes,
            'peak_txn'   => $peakTxn,
            'min_txn'    => $minTxn,
        ];
    }

    /**
     * Parse DDL statements with full detail: type, database, table, statement.
     */
    private function parseDdlStatements(): array
    {
        // Use --base64-output=NEVER to skip all binary row data.
        // DDL is always logged as plain-text Query events, so we lose nothing.
        // Without this, 200 MB of base64 flows through the pipe and kills PHP.
        $analysis = $this->getAnalysis();
        $version = $analysis['mysql_version'] ?? '';
        $binary = $this->getMysqlbinlogBinary($version);

        $cmd = escapeshellarg($binary) . " --base64-output=NEVER"
             . " --start-datetime=" . escapeshellarg($analysis['time_start'])
             . " --stop-datetime=" . escapeshellarg($analysis['time_end']);

        $files = glob($this->tmpDir . '/*');
        sort($files);
        foreach ($files as $f) {
            if (preg_match('/\.(json|log|txt|md)$/i', $f)) continue;
            $cmd .= " " . escapeshellarg($f);
        }

        // Extract timestamp lines + use <db> + DDL lines.
        // Timestamps are on lines like: #260414  0:01:18 server id 123 ...
        $cmdDdl = "timeout 60 " . $cmd . " 2>/dev/null | grep -aiP '(^#\\d{6}\\s+\\d+:\\d+:\\d+|^use\s|^\\s*(CREATE|ALTER|DROP|TRUNCATE|RENAME)\\s)'";
        $output = shell_exec($cmdDdl . " 2>/dev/null") ?: '';

        $details = [];
        $currentDb = '';
        $currentTs = '';

        foreach (explode("\n", trim($output)) as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Track timestamp from event header lines: #260414  0:01:18
            if (preg_match('/^#(\d{2})(\d{2})(\d{2})\s+(\d+:\d+:\d+)/', $line, $tsm)) {
                $year = 2000 + (int) $tsm[1];
                $currentTs = "$year-$tsm[2]-$tsm[3] $tsm[4]";
                continue;
            }

            // Track current database from "use `dbname`" statements
            if (preg_match('/^use\s+`?([^`;\s]+)`?/i', $line, $m)) {
                $currentDb = $m[1];
                continue;
            }

            // Parse DDL: extract type and table name
            // Patterns: CREATE TABLE `db`.`tbl`, ALTER TABLE `tbl`, DROP TABLE IF EXISTS `tbl`, etc.
            $type = '';
            $table = '';
            $db = $currentDb;

            if (preg_match('/^\s*(CREATE|ALTER|DROP|TRUNCATE|RENAME)\s+(TABLE|INDEX|DATABASE|SCHEMA|VIEW|TRIGGER|PROCEDURE|FUNCTION|EVENT)\s+(?:IF\s+(?:NOT\s+)?EXISTS\s+)?`?([^`\s(]+)`?(?:\.`?([^`\s(]+)`?)?/i', $line, $m)) {
                $type = strtoupper($m[1]) . ' ' . strtoupper($m[2]);
                if (!empty($m[4])) {
                    // db.table format
                    $db = $m[3];
                    $table = $m[4];
                } else {
                    $table = $m[3];
                }
            } elseif (preg_match('/^\s*(CREATE|ALTER|DROP|TRUNCATE|RENAME)\s+/i', $line, $m)) {
                $type = strtoupper($m[1]);
                // Try to extract object name
                if (preg_match('/`([^`]+)`(?:\.`([^`]+)`)?/', $line, $tm)) {
                    if (!empty($tm[2])) {
                        $db = $tm[1];
                        $table = $tm[2];
                    } else {
                        $table = $tm[1];
                    }
                }
            }

            if ($type) {
                $stmt = substr($line, 0, 200);
                $details[] = [
                    'datetime'  => $currentTs,
                    'type'      => $type,
                    'database'  => $db,
                    'table'     => $table,
                    'statement' => $stmt,
                ];
            }
        }

        return [
            'count'   => count($details),
            'details' => $details,
        ];
    }

    // ------------------------------------------------------------------
    //  Store final results
    // ------------------------------------------------------------------

    private function storeResults(array $gtid, array $dml, array $volume, array $ddlResult, array $analysis): void
    {
        $totalFileSize = 0;
        foreach (glob($this->tmpDir . '/*') as $f) {
            if (preg_match('/\.(json|log|txt|md)$/i', $f)) continue;
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
            "total_ddl = " . (int) $ddlResult['count'],
            "ddl_details = '" . $this->db->sql_real_escape_string(json_encode($ddlResult['details'] ?? [])) . "'",
            "max_txn_size_bytes = " . (int) $gtid['max_size'],
            "large_txn_100k = " . (int) $gtid['large_100k'],
            "large_txn_500k = " . (int) $gtid['large_500k'],
            "peak_txn_per_sec = " . (int) $volume['peak_txn'],
            "min_txn_per_sec = " . (int) ($volume['min_txn'] ?? 0),
            "avg_txn_per_sec = " . (float) $avgTxnSec,
            "sequential_pct = " . (float) $gtid['sequential_pct'],
            "max_parallelism = " . (int) $gtid['max_parallelism'],
            "parallelism_distribution = '" . $this->db->sql_real_escape_string(json_encode($gtid['parallelism_distrib'])) . "'",
            "parallelism_per_second = '" . $this->db->sql_real_escape_string(json_encode($volume['data'])) . "'",
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
        $analysis = $this->getAnalysis();
        $slaveId = (int)($analysis['id_mysql_server'] ?? 0);
        $masterId = (int)($analysis['id_mysql_server__master'] ?? 0);
        $version = $analysis['mysql_version'] ?? '';
        $isMariaDB = (stripos($version, 'mariadb') !== false);

        // Fetch master + slave config for context-aware recommendations
        $masterVars = Extraction2::display(array(
            'variables::binlog_format', 'variables::binlog_row_image',
            'variables::sync_binlog', 'variables::innodb_flush_log_at_trx_commit',
            'variables::binlog_transaction_dependency_tracking',
            'variables::slave_parallel_workers', 'variables::replica_parallel_workers',
            'variables::slave_parallel_threads',
            'variables::slave_parallel_type', 'variables::slave_parallel_mode',
            'variables::log_slave_updates',
            'variables::binlog_expire_logs_seconds', 'variables::expire_logs_days',
            'variables::max_binlog_size',
        ), array($masterId));
        $mv = $masterVars[$masterId] ?? [];

        $slaveVars = Extraction2::display(array(
            'variables::slave_parallel_workers', 'variables::replica_parallel_workers',
            'variables::slave_parallel_threads', 'variables::slave_parallel_mode',
            'variables::slave_parallel_type',
            'variables::log_slave_updates',
            'variables::relay_log_space_limit',
        ), array($slaveId));
        $sv = $slaveVars[$slaveId] ?? [];

        $totalRows = $dml['inserts'] + $dml['updates'] + $dml['deletes'];

        // ── Parallelism recommendations ──

        $slaveWorkers = (int)($sv['replica_parallel_workers'] ?? $sv['slave_parallel_workers'] ?? $sv['slave_parallel_threads'] ?? 0);

        if ($volume['peak_txn'] > 200 && $slaveWorkers < 8) {
            $recs[] = "Peak " . number_format($volume['peak_txn']) . " txn/s but slave has only $slaveWorkers parallel workers. Increase to >= 8.";
        } elseif ($volume['peak_txn'] > 200) {
            $recs[] = "High write throughput (peak " . number_format($volume['peak_txn']) . " txn/s). Slave has $slaveWorkers workers — monitor lag.";
        }

        if ($gtid['sequential_pct'] > 25) {
            $tracking = $mv['binlog_transaction_dependency_tracking'] ?? '';
            if (strtolower($tracking) !== 'writeset' && !$isMariaDB) {
                $recs[] = "Sequential ratio " . $gtid['sequential_pct'] . "%. Set binlog_transaction_dependency_tracking=WRITESET on master to improve parallelism.";
            } elseif ($isMariaDB) {
                $mode = $sv['slave_parallel_mode'] ?? 'conservative';
                if (strtolower($mode) === 'conservative') {
                    $recs[] = "Sequential ratio " . $gtid['sequential_pct'] . "%. Consider slave_parallel_mode=optimistic for better parallelism.";
                }
            }
        }

        if ($gtid['max_parallelism'] < 4 && $gtid['total_transactions'] > 100) {
            $recs[] = "Max parallelism only " . $gtid['max_parallelism'] . " txn/group. Workers > " . $gtid['max_parallelism'] . " will idle.";
        }

        if ($slaveWorkers > 0 && $gtid['max_parallelism'] > 0 && $slaveWorkers < $gtid['max_parallelism']) {
            $recs[] = "Slave has $slaveWorkers workers but binlogs show up to " . $gtid['max_parallelism'] . " parallel txn. Increase workers to match.";
        }

        // ── Transaction size ──

        if ($gtid['large_500k'] > 0) {
            $maxMb = round($gtid['max_size'] / 1048576, 1);
            $recs[] = $gtid['large_500k'] . " transaction(s) > 500 KB (max {$maxMb} MB). Large txn block the SQL applier and increase lag.";
        } elseif ($gtid['large_100k'] > 10) {
            $recs[] = $gtid['large_100k'] . " transactions > 100 KB. Consider splitting batch operations.";
        }

        // ── Binlog config ──

        $binlogFormat = strtoupper($mv['binlog_format'] ?? '');
        if ($binlogFormat === 'STATEMENT') {
            $recs[] = "Master uses binlog_format=STATEMENT. Row-based (ROW) is safer for replication and required for WRITESET tracking.";
        } elseif ($binlogFormat === 'MIXED') {
            $recs[] = "Master uses binlog_format=MIXED. Consider ROW for consistent parallel replication.";
        }

        $rowImage = strtoupper($mv['binlog_row_image'] ?? 'FULL');
        if ($rowImage === 'FULL' && $totalRows > 100000) {
            $recs[] = "binlog_row_image=FULL — every column is logged for UPDATE/DELETE. MINIMAL reduces binlog size significantly for wide tables.";
        }

        // ── Durability ──

        $syncBinlog = (int)($mv['sync_binlog'] ?? 1);
        $innoFlush = (int)($mv['innodb_flush_log_at_trx_commit'] ?? 1);
        if ($syncBinlog !== 1 || $innoFlush !== 1) {
            $recs[] = "Master durability: sync_binlog=$syncBinlog, innodb_flush_log_at_trx_commit=$innoFlush. Values != 1 risk data loss on crash.";
        }

        // ── Throughput ──

        if ($duration > 0 && $totalRows / $duration > 3000) {
            $recs[] = "Row throughput ~" . number_format($totalRows / $duration) . " rows/s. Sustained high-volume writes.";
        }

        // ── Multi-tenant ──

        if ($dml['db_count'] > 50) {
            $recs[] = $dml['db_count'] . " databases modified. Multi-tenant pattern — consider database-level filtering (replicate_do_db).";
        } elseif ($dml['db_count'] > 10) {
            $recs[] = $dml['db_count'] . " databases modified during this window.";
        }

        // ── DDL impact ──

        $ddlCount = (int)($analysis['total_ddl'] ?? 0);
        if ($ddlCount > 0) {
            $recs[] = "$ddlCount DDL statement(s) detected. DDL in binlogs blocks the SQL applier (single-threaded for DDL).";
        }

        // ── Binlog rotation ──

        $binlogFiles = json_decode($analysis['binlog_files'] ?? '[]', true);
        $fileCount = count($binlogFiles);
        if ($fileCount > 1 && $duration > 0) {
            $rotationInterval = round($duration / $fileCount);
            if ($rotationInterval < 30) {
                $recs[] = "Binlog rotates every ~{$rotationInterval}s ($fileCount files in {$duration}s). Very high write volume — consider increasing max_binlog_size.";
            }
        }

        // ── log_slave_updates ──

        $masterLSU = strtoupper($mv['log_slave_updates'] ?? '');
        $slaveLSU = strtoupper($sv['log_slave_updates'] ?? '');
        if ($slaveLSU === 'OFF' || $slaveLSU === '0') {
            $recs[] = "Slave has log_slave_updates=OFF. Cascading replication (slave of slave) won't work. Enable if this slave has downstream replicas.";
        }

        return $recs;
    }

    // ------------------------------------------------------------------
    //  Helpers
    // ------------------------------------------------------------------

    /**
     * Query SHOW BINARY LOGS on the master to get file sizes for timeout calculation.
     * Returns [filename => size_bytes]. Silently returns empty on failure.
     */
    private function getBinlogSizes(array $creds, array $binlogFiles): array
    {
        $sizes = [];
        try {
            $link = new \mysqli($creds['host'], $creds['user'], $creds['password'], '', $creds['port']);
            if ($link->connect_error) return $sizes;
            $res = $link->query("SHOW BINARY LOGS");
            if ($res) {
                $wanted = array_flip($binlogFiles);
                while ($row = $res->fetch_assoc()) {
                    $name = $row['Log_name'] ?? '';
                    if (isset($wanted[$name])) {
                        $sizes[$name] = (int)($row['File_size'] ?? 0);
                    }
                }
                $res->free();
            }
            $link->close();
        } catch (\Throwable $e) {}
        return $sizes;
    }

    /**
     * Probe the first or last event timestamp from a LOCAL binlog file.
     */
    private function probeLocalBinlogTimestamp(string $filePath, string $binary, string $which = 'first'): ?string
    {
        if ($which === 'last') {
            // Read only the last 64KB of the file to find the last timestamp
            $size = @filesize($filePath) ?: 0;
            $startPos = max(4, $size - 65536);
            $cmd = escapeshellarg($binary) . " --start-position=" . $startPos
                 . " " . escapeshellarg($filePath)
                 . " 2>/dev/null | grep -aoP '^#\\d{6}\\s+\\d+:\\d+:\\d+' | tail -1";
        } else {
            $cmd = escapeshellarg($binary) . " --stop-position=8192 "
                 . escapeshellarg($filePath)
                 . " 2>/dev/null | grep -aoP '^#\\d{6}\\s+\\d+:\\d+:\\d+' | head -1";
        }

        $ts = trim(shell_exec($cmd) ?: '');
        if (empty($ts)) return null;

        if (preg_match('/^#(\d{2})(\d{2})(\d{2})\s+(\d+:\d+:\d+)$/', $ts, $m)) {
            return (2000 + (int)$m[1]) . '-' . $m[2] . '-' . $m[3] . ' ' . $m[4];
        }
        return null;
    }

    private function isProcessAlive(int $pid): bool
    {
        return $pid > 0 && @posix_kill($pid, 0);
    }

    private function detectVersionFromLink(\mysqli $link): string
    {
        $res = @$link->query("SELECT @@version AS v");
        if ($res && $row = $res->fetch_assoc()) {
            $res->free();
            return $row['v'] ?? '';
        }
        return '';
    }

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
            foreach (glob($this->tmpDir . '/*') ?: [] as $f) {
                @unlink($f);
            }
            @rmdir($this->tmpDir);
        }
        // Remove temporary .my.cnf files containing credentials
        foreach ($this->tmpCnfFiles as $cnf) {
            @unlink($cnf);
        }
        $this->tmpCnfFiles = [];
    }
}
