<?php

declare(strict_types=1);

use App\Controller\AuthSession as AuthSessionController;
use PHPUnit\Framework\TestCase;

final class AuthSessionTokenMismatchTest extends TestCase
{
    public function testMenuMigrationInsertsTokenMismatchUnderSuperAdmin(): void
    {
        $root = dirname(__DIR__, 2);
        $migration = (string) file_get_contents($root . '/sql/incremental_v2/20260506_menu_token_mismatch.sql');

        $this->assertStringContainsString("'AuthSession'", $migration);
        $this->assertStringContainsString("'tokenMismatch'", $migration);
        $this->assertStringContainsString("'Token mismatch'", $migration);
        $this->assertStringContainsString("'{LINK}AuthSession/tokenMismatch'", $migration);
        // SuperAdmin menu id is hardcoded at 249 in this codebase — same as 20260504_superadmin_error_menu.sql.
        $this->assertStringContainsString('249', $migration);
        // Idempotency guard so re-running the migration does not double-insert.
        $this->assertStringContainsString('@exists', $migration);
    }

    public function testTokenMismatchActionIsPublic(): void
    {
        $method = new ReflectionMethod(AuthSessionController::class, 'tokenMismatch');
        $this->assertTrue($method->isPublic());
    }

    public function testParseLogDistinguishesRevokesFromRotationLostRaceAndCountsByReason(): void
    {
        $logFile = tempnam(sys_get_temp_dir(), 'pmacontrol-auth-log-');
        $now = time();
        $today = gmdate('d-M-Y H:i:s', $now) . ' UTC';
        $eightDaysAgo = gmdate('d-M-Y H:i:s', $now - 8 * 86400) . ' UTC';
        $oneHourAgo = gmdate('d-M-Y H:i:s', $now - 3600) . ' UTC';

        file_put_contents($logFile, implode("\n", [
            "[$today] PersistentAuthSession: revoking selector=aaaaaaaa… reason=token_mismatch remote=10.0.0.1 ua=Mozilla/5.0",
            "[$today] PersistentAuthSession: revoking selector=bbbbbbbb… reason=fingerprint_mismatch remote=10.0.0.2 ua=curl/8.4",
            "[$today] PersistentAuthSession: rotation_lost_race selector=cccccccc… remote=10.0.0.3 — accepted via grace window (issue #773)",
            "[$oneHourAgo] PersistentAuthSession: rotation_lost_race selector=dddddddd… remote=10.0.0.4 — accepted via grace window (issue #773)",
            "[$eightDaysAgo] PersistentAuthSession: revoking selector=eeeeeeee… reason=token_mismatch remote=10.0.0.5 ua=oldbrowser",
            "[$today] PHP Notice: Unrelated PHP warning to make sure we ignore non-auth lines",
            "",
        ]) . "\n");

        $report = AuthSessionController::parseLog($logFile);

        @unlink($logFile);

        $this->assertCount(5, $report['events'], 'Each PersistentAuthSession line must produce one event; PHP notices are ignored.');

        $kinds = array_count_values(array_column($report['events'], 'kind'));
        $this->assertSame(3, $kinds['revoke'] ?? 0);
        $this->assertSame(2, $kinds['race'] ?? 0);

        $this->assertSame(5, $report['totals']['total']);
        $this->assertSame(2, $report['totals']['token_mismatch_total']);
        $this->assertSame(2, $report['totals']['rotation_lost_race_total']);

        // The 8-day-old event lands in last_7d=4 and last_24h=4 buckets only,
        // confirming the threshold logic actually filters anything.
        $this->assertSame(4, $report['totals']['last_24h']);
        $this->assertSame(4, $report['totals']['last_7d']);

        $this->assertArrayHasKey('token_mismatch', $report['reasons']);
        $this->assertArrayHasKey('fingerprint_mismatch', $report['reasons']);
        $this->assertArrayHasKey('rotation_lost_race', $report['reasons']);
        $this->assertSame(2, $report['reasons']['token_mismatch']['count']);
        $this->assertSame(1, $report['reasons']['fingerprint_mismatch']['count']);
        $this->assertSame(2, $report['reasons']['rotation_lost_race']['count']);

        // Newest first.
        $first = $report['events'][0];
        $this->assertNotSame('eeeeeeee', $first['selector'], 'Old events must not surface first.');
    }

    public function testParseLogReturnsEmptyShapeWhenFileMissing(): void
    {
        $report = AuthSessionController::parseLog('/tmp/this-file-does-not-exist-' . bin2hex(random_bytes(6)));

        $this->assertSame([], $report['events']);
        $this->assertSame([], $report['reasons']);
        $this->assertSame(0, $report['totals']['total']);
    }
}
