<?php

declare(strict_types=1);

use App\Service\Ndb\NdbAlertEmitter;
use PHPUnit\Framework\TestCase;

/**
 * Review #1023 P1 — without a unique index covering the upsert
 * semantics, every collection cycle inserted a fresh open row for a
 * persistent alert. The fix is twofold:
 *
 * - schema: a virtual `open_fingerprint` column + UNIQUE index lets
 *   `ON DUPLICATE KEY UPDATE` collapse repeats into one open row, while
 *   resolved/closed history rows stack freely (NULL on the virtual
 *   column).
 * - emitter: the existing `buildUpsertSql` already emits the right
 *   shape, so the test pins both the migration content AND the SQL
 *   shape so a future refactor cannot drop one half of the contract.
 */
final class NdbAlertEmitterUpsertIdempotencyTest extends TestCase
{
    public function testMigrationDeclaresOpenFingerprintUniqueIndex(): void
    {
        $sql = file_get_contents(
            __DIR__ . '/../../../sql/incremental_v2/'
            . '20260509_pmc_event_alert_open_fingerprint_unique.sql'
        );
        $this->assertNotFalse($sql, 'migration file must exist');

        // Pre-cleanup of duplicate open rows must be present, otherwise
        // ALTER TABLE ADD UNIQUE will fail on environments that already
        // ran the buggy version.
        $this->assertMatchesRegularExpression(
            '/DELETE\s+a\s+FROM\s+`?pmc_event_alert`?\s+a\s+JOIN\s+`?pmc_event_alert`?\s+b/i',
            $sql,
            'migration must dedupe open rows before adding the unique key'
        );

        // Virtual column + unique index on it: the only safe shape that
        // enforces 'one open per fingerprint' without rejecting the
        // resolved-history rows.
        $this->assertMatchesRegularExpression(
            '/ADD\s+COLUMN\s+`?open_fingerprint`?\s+BINARY\(32\)\s+AS/i',
            $sql,
            'migration must add open_fingerprint as a generated column'
        );
        $this->assertMatchesRegularExpression(
            "/CASE\s+WHEN\s+`?status`?\s*=\s*'open'/i",
            $sql,
            'open_fingerprint must be NULL outside status=open'
        );
        $this->assertMatchesRegularExpression(
            '/ADD\s+UNIQUE\s+KEY\s+`?uniq_open_fingerprint`?\s*\(\s*`?open_fingerprint`?\s*\)/i',
            $sql,
            'migration must add the unique key on open_fingerprint'
        );
    }

    public function testBuildUpsertSqlStillEmitsOnDuplicateKeyUpdate(): void
    {
        $sql = NdbAlertEmitter::buildUpsertSql(
            new FakeAlertDb(),
            7,
            [
                'code'     => 'NDB_FAKE_PERSISTENT',
                'severity' => 'warning',
                'entity'   => 'node:99',
                'title'    => 'fake',
                'message'  => 'm',
                'context'  => [],
            ],
            'fake-cluster'
        );

        $this->assertStringContainsString('INSERT INTO `pmc_event_alert`', $sql);
        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $sql);
        // The IF guard re-opens a previously resolved row should the
        // operator ever decide to widen the unique index back to
        // (fingerprint) alone — keep it for safety.
        $this->assertStringContainsString(
            "IF(`status` IN ('resolved','closed'), 'open', `status`)",
            $sql
        );
        // The fingerprint must be the deterministic hash so that the
        // same logical alert always lands on the same row.
        $this->assertStringContainsString(
            "UNHEX('" . NdbAlertEmitter::fingerprintHex(7, 'NDB_FAKE_PERSISTENT', 'node:99') . "')",
            $sql
        );
    }
}

/** Minimal DB stub that satisfies escape() inside buildUpsertSql. */
final class FakeAlertDb
{
    public function sql_real_escape_string(string $s): string
    {
        return str_replace(["'", "\\", "\0"], ["''", "\\\\", ''], $s);
    }
}
