<?php

declare(strict_types=1);

namespace App\Library\Export;

use mysqli;
use mysqli_result;
use RuntimeException;

/**
 * Pure-PHP replacement for the legacy `mysqldump | sed > file` pipeline
 * used by Export::generateDump().
 *
 * Why: the shell pipeline relied on the mysqldump binary being installed,
 * picked up /root/.my.cnf when invoked as root, leaked the password on
 * argv if not configured carefully, and had no error reporting because
 * shell_exec drops the exit code (cf. issue #777). Doing the dump with
 * a direct mysqli connection sidesteps all of that:
 *
 *   - No mysqldump binary dependency, no version drift between
 *     MariaDB 10.6 / 10.11 / MySQL 8.x clients.
 *   - No /etc/mysql/*.cnf, /root/.my.cnf, /home/$USER/.my.cnf precedence
 *     surprises — we use the connection Glial already opened with the
 *     project's encrypted credentials.
 *   - No shell pipe at all → no credentials in `ps -ef`, no
 *     escapeshellarg / pipefail gymnastics, exit status is simply a
 *     thrown RuntimeException.
 *   - Single pass over the file: fopen('w') / append, fwrite each
 *     row, fclose. No double-write, no temp file dance.
 *
 * The output format matches mysqldump closely enough that the existing
 * canonical sql/full/pmacontrol.sql diffs reasonably and the project's
 * test suite (which only greps for column names / index names) keeps
 * passing.
 */
final class NativeDumper
{
    /**
     * @param array<int,string> $tablesWithData       extended INSERT (one per table)
     * @param array<int,string> $tablesWithDataExpand one INSERT per row (--skip-extended-insert)
     * @param array<int,string> $tablesWithoutData    structure only (-d)
     */
    public static function dump(
        mysqli $conn,
        string $database,
        string $finalPath,
        array $tablesWithData,
        array $tablesWithDataExpand,
        array $tablesWithoutData
    ): void {
        if (file_exists($finalPath) && !is_writable($finalPath)) {
            throw new RuntimeException(sprintf(
                '%s exists but is not writable by the current process (likely owned by another user). '
                .'Fix with: chown www-data:www-data %1$s && chmod 664 %1$s',
                $finalPath
            ));
        }

        // Match what mysqldump opens: utf8mb4. Otherwise binary columns
        // come back transcoded and the dump can lose bytes.
        if (!@$conn->set_charset('utf8mb4')) {
            throw new RuntimeException('Unable to switch the export connection to utf8mb4: '.$conn->error);
        }

        $fh = @fopen($finalPath, 'w');
        if ($fh === false) {
            throw new RuntimeException('Unable to open '.$finalPath.' for writing.');
        }

        try {
            self::writeHeader($fh, $database, $conn);

            foreach ($tablesWithData as $table) {
                self::writeTableStructure($conn, $fh, $table);
                self::writeTableData($conn, $fh, $table, true);
            }

            foreach ($tablesWithDataExpand as $table) {
                self::writeTableStructure($conn, $fh, $table);
                self::writeTableData($conn, $fh, $table, false);
            }

            foreach ($tablesWithoutData as $table) {
                self::writeTableStructure($conn, $fh, $table);
            }

            self::writeFooter($fh);
        } finally {
            fclose($fh);
        }
    }

    /**
     * @param resource $fh
     */
    private static function writeHeader($fh, string $database, mysqli $conn): void
    {
        $serverVersion = (string) ($conn->server_info ?? 'unknown');
        $host          = (string) ($conn->host_info ?? '');

        fwrite($fh, "-- pmacontrol dump (NativeDumper) — equivalent to `mysqldump --skip-dump-date`\n");
        fwrite($fh, "-- Host: ".$host."   Database: ".$database."\n");
        fwrite($fh, "-- ------------------------------------------------------\n");
        fwrite($fh, "-- Server version\t".$serverVersion."\n\n");

        fwrite($fh, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
        fwrite($fh, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
        fwrite($fh, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
        fwrite($fh, "/*!40101 SET NAMES utf8mb4 */;\n");
        fwrite($fh, "/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;\n");
        fwrite($fh, "/*!40103 SET TIME_ZONE='+00:00' */;\n");
        fwrite($fh, "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;\n");
        fwrite($fh, "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n");
        fwrite($fh, "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n");
        fwrite($fh, "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n\n");
    }

    /**
     * @param resource $fh
     */
    private static function writeFooter($fh): void
    {
        fwrite($fh, "\n/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;\n\n");
        fwrite($fh, "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n");
        fwrite($fh, "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n");
        fwrite($fh, "/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;\n");
        fwrite($fh, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
        fwrite($fh, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
        fwrite($fh, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");
        fwrite($fh, "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n\n");
        fwrite($fh, "-- Dump completed\n");
    }

    /**
     * @param resource $fh
     */
    private static function writeTableStructure(mysqli $conn, $fh, string $table): void
    {
        $tableQuoted = '`'.str_replace('`', '``', $table).'`';

        fwrite($fh, "\n--\n");
        fwrite($fh, "-- Table structure for table ".$tableQuoted."\n");
        fwrite($fh, "--\n\n");

        fwrite($fh, "DROP TABLE IF EXISTS ".$tableQuoted.";\n");
        fwrite($fh, "/*!40101 SET @saved_cs_client     = @@character_set_client */;\n");
        fwrite($fh, "/*!40101 SET character_set_client = utf8mb4 */;\n");

        $res = $conn->query("SHOW CREATE TABLE ".$tableQuoted);
        if (!$res instanceof mysqli_result) {
            throw new RuntimeException('SHOW CREATE TABLE '.$tableQuoted.' failed: '.$conn->error);
        }
        $row = $res->fetch_assoc();
        $res->free();

        $createSql = (string) ($row['Create Table'] ?? $row['Create View'] ?? '');
        if ($createSql === '') {
            throw new RuntimeException('Empty CREATE TABLE for '.$tableQuoted);
        }

        // Same AUTO_INCREMENT stripping as the legacy `sed 's/ AUTO_INCREMENT=[0-9]*\b//'`.
        // Keeps dumps stable across deploys regardless of how many INSERTs
        // have run since the last truncate.
        $createSql = (string) preg_replace('/ AUTO_INCREMENT=\d+\b/', '', $createSql);

        fwrite($fh, $createSql.";\n");
        fwrite($fh, "/*!40101 SET character_set_client = @saved_cs_client */;\n");
    }

    /**
     * @param resource $fh
     */
    private static function writeTableData(mysqli $conn, $fh, string $table, bool $extended): void
    {
        $tableQuoted = '`'.str_replace('`', '``', $table).'`';

        fwrite($fh, "\n--\n");
        fwrite($fh, "-- Dumping data for table ".$tableQuoted."\n");
        fwrite($fh, "--\n\n");

        $res = $conn->query('SELECT * FROM '.$tableQuoted, MYSQLI_USE_RESULT);
        if (!$res instanceof mysqli_result) {
            throw new RuntimeException('SELECT FROM '.$tableQuoted.' failed: '.$conn->error);
        }

        try {
            $fields = $res->fetch_fields();
            if ($fields === []) {
                return;
            }

            $columnList = '('.implode(',', array_map(
                static fn($f) => '`'.str_replace('`', '``', $f->name).'`',
                $fields
            )).')';

            $insertPrefix = 'INSERT INTO '.$tableQuoted.' '.$columnList.' VALUES ';
            $rowsBuffered = [];
            $bufferLen    = 0;
            $maxBufferLen = 1_000_000; // ~1 MB per multi-row INSERT, mysqldump default-ish

            $started = false;
            $escape  = static fn(string $v): string => $conn->real_escape_string($v);

            while (($row = $res->fetch_array(MYSQLI_NUM)) !== null) {
                $values = '('.self::formatRow($escape, $fields, $row).')';

                if ($extended) {
                    $rowsBuffered[] = $values;
                    $bufferLen += strlen($values) + 1;

                    if ($bufferLen >= $maxBufferLen) {
                        fwrite($fh, $insertPrefix.implode(',', $rowsBuffered).";\n");
                        $rowsBuffered = [];
                        $bufferLen    = 0;
                    }
                    $started = true;
                } else {
                    fwrite($fh, $insertPrefix.$values.";\n");
                    $started = true;
                }
            }

            if ($extended && $rowsBuffered !== []) {
                fwrite($fh, $insertPrefix.implode(',', $rowsBuffered).";\n");
            }

            if (!$started) {
                fwrite($fh, "-- (table is empty)\n");
            }
        } finally {
            $res->free();
        }
    }

    /**
     * Escapes one row of mysqli data into the comma-separated value list
     * that goes inside `INSERT INTO t VALUES (…)`. Pure: takes an escape
     * callable so tests can run without a live mysqli.
     *
     * @param callable(string):string     $escape callable wrapping mysqli::real_escape_string
     * @param array<int,\stdClass|object> $fields one per column from fetch_fields()
     * @param array<int,mixed>            $row    fetch_array(MYSQLI_NUM)
     */
    public static function formatRow(callable $escape, array $fields, array $row): string
    {
        // MYSQLI_TYPE constants: DECIMAL=0, TINY=1, SHORT=2, LONG=3,
        // FLOAT=4, DOUBLE=5, NULL=6, LONGLONG=8, INT24=9, YEAR=13,
        // NEWDECIMAL=246. Emit those unquoted to match mysqldump.
        static $numericTypes = [0, 1, 2, 3, 4, 5, 8, 9, 13, 246];
        // MYSQLI_TYPE TINY_BLOB=249, MEDIUM_BLOB=250, LONG_BLOB=251, BLOB=252.
        static $blobTypes    = [249, 250, 251, 252];

        $parts = [];
        foreach ($row as $i => $value) {
            if ($value === null) {
                $parts[] = 'NULL';
                continue;
            }

            $type = (int) ($fields[$i]->type ?? 0);

            if (in_array($type, $numericTypes, true)) {
                $parts[] = (string) $value;
                continue;
            }

            // Binary blob types: emit as 0x… hex literal so the dump
            // round-trips even when the value contains NUL / control bytes.
            if (in_array($type, $blobTypes, true) && self::isBinaryField($fields[$i] ?? null)) {
                $parts[] = '0x'.bin2hex((string) $value);
                continue;
            }

            $parts[] = "'".$escape((string) $value)."'";
        }

        return implode(',', $parts);
    }

    /**
     * Detect whether a BLOB-typed column is actually binary (vs a TEXT
     * column which mysqli also reports as type=BLOB). We rely on the
     * BINARY_FLAG bit (128) — mysqldump uses the same heuristic.
     *
     * @param object|null $field
     */
    public static function isBinaryField($field): bool
    {
        if ($field === null) {
            return false;
        }
        $flags = (int) ($field->flags ?? 0);
        // MYSQLI_BINARY_FLAG = 128
        return ($flags & 128) === 128;
    }
}
