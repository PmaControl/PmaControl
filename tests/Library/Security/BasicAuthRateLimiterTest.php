<?php

declare(strict_types=1);

use App\Library\Security\BasicAuthRateLimiter;
use PHPUnit\Framework\TestCase;

final class BasicAuthRateLimiterTest extends TestCase
{
    public function testNormalizesUserAndRemoteAddressKeys(): void
    {
        $this->assertSame('apiuser', BasicAuthRateLimiter::normalizeUser(" api\nuser "));
        $this->assertSame('admin', BasicAuthRateLimiter::normalizeUser('Àdmin'));
        $this->assertSame('unknown', BasicAuthRateLimiter::normalizeUser(''));
        $this->assertSame('unknown', BasicAuthRateLimiter::normalizeUser('__IP__'));
        $this->assertSame('192.0.2.44', BasicAuthRateLimiter::remoteAddr(['REMOTE_ADDR' => '192.0.2.44']));
        $this->assertSame('unknown', BasicAuthRateLimiter::remoteAddr([]));
        $this->assertSame(64, strlen(BasicAuthRateLimiter::normalizeUser(str_repeat('a', 300))));
        $this->assertSame(45, strlen(BasicAuthRateLimiter::remoteAddr(['REMOTE_ADDR' => str_repeat('1', 80)])));
    }

    public function testFailureWindowBlocksAtThreshold(): void
    {
        $now = strtotime('2026-05-02 12:00:00');
        $this->assertIsInt($now);

        $state = BasicAuthRateLimiter::nextFailureState([
            'failure_count' => 4,
            'window_started_at' => date('Y-m-d H:i:s', $now - 120),
            'last_failed_at' => date('Y-m-d H:i:s', $now - 30),
            'blocked_until' => null,
        ], $now);
        $outcome = BasicAuthRateLimiter::evaluateState($state, $now);

        $this->assertSame(5, $state['failure_count']);
        $this->assertSame(date('Y-m-d H:i:s', $now + BasicAuthRateLimiter::LOCK_SECONDS), $state['blocked_until']);
        $this->assertFalse($outcome['allowed']);
        $this->assertSame(BasicAuthRateLimiter::LOCK_SECONDS, $outcome['retry_after']);
    }

    public function testExpiredWindowResetsFailureCount(): void
    {
        $now = strtotime('2026-05-02 12:00:00');
        $this->assertIsInt($now);

        $state = BasicAuthRateLimiter::nextFailureState([
            'failure_count' => 4,
            'window_started_at' => date('Y-m-d H:i:s', $now - BasicAuthRateLimiter::WINDOW_SECONDS - 1),
            'last_failed_at' => date('Y-m-d H:i:s', $now - 30),
            'blocked_until' => null,
        ], $now);

        $this->assertSame(1, $state['failure_count']);
        $this->assertSame(date('Y-m-d H:i:s', $now), $state['window_started_at']);
        $this->assertNull($state['blocked_until']);
    }

    public function testSqlBuildersEscapeUserAndRemoteAddress(): void
    {
        $db = new BasicAuthRateLimiterFakeDb();

        $fetch = BasicAuthRateLimiter::buildFetchStateSql($db, "api'user", "10.0.0.1' OR 1=1");
        $upsert = BasicAuthRateLimiter::buildUpsertFailureSql($db, "api'user", "10.0.0.1' OR 1=1", strtotime('2026-05-02 12:00:00'));
        $lock = BasicAuthRateLimiter::buildLockIfThresholdSql($db, "api'user", "10.0.0.1' OR 1=1", strtotime('2026-05-02 12:00:00'));
        $clear = BasicAuthRateLimiter::buildClearFailuresSql($db, "api'user", "10.0.0.1' OR 1=1");
        $purge = BasicAuthRateLimiter::buildPurgeOldRowsSql($db, strtotime('2026-05-02 12:00:00'));

        foreach ([$fetch, $upsert, $lock, $clear] as $sql) {
            $this->assertStringContainsString("api''user", $sql);
            $this->assertStringContainsString("10.0.0.1'' OR 1=1", $sql);
            $this->assertStringNotContainsString("api'user", $sql);
        }
        $this->assertStringContainsString('NULL', $upsert);
        $this->assertStringContainsString('`failure_count` + 1', $upsert);
        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $upsert);
        $this->assertStringContainsString('`failure_count` >= ' . BasicAuthRateLimiter::MAX_FAILURES, $lock);
        $this->assertStringContainsString('DELETE FROM `webservice_auth_failure`', $purge);
    }

    public function testRecordFailureUsesExistingStateAndWritesUpdatedCounter(): void
    {
        $now = strtotime('2026-05-02 12:00:00');
        $this->assertIsInt($now);

        $db = new BasicAuthRateLimiterFakeDb([
            (object)[
                'failure_count' => 4,
                'window_started_at' => date('Y-m-d H:i:s', $now - 120),
                'last_failed_at' => date('Y-m-d H:i:s', $now - 30),
                'blocked_until' => date('Y-m-d H:i:s', $now + BasicAuthRateLimiter::LOCK_SECONDS),
            ],
            (object)[
                'failure_count' => 1,
                'window_started_at' => date('Y-m-d H:i:s', $now),
                'last_failed_at' => date('Y-m-d H:i:s', $now),
                'blocked_until' => null,
            ],
        ]);

        $outcome = BasicAuthRateLimiter::recordFailure($db, 'api', '192.0.2.44', $now);

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(BasicAuthRateLimiter::LOCK_SECONDS, $outcome['retry_after']);
        $this->assertCount(7, $db->queries);
        $this->assertStringStartsWith('DELETE FROM `webservice_auth_failure`', $db->queries[0]);
        $this->assertStringStartsWith('INSERT INTO `webservice_auth_failure`', $db->queries[1]);
        $this->assertStringStartsWith('UPDATE `webservice_auth_failure` SET', $db->queries[2]);
        $this->assertStringStartsWith('SELECT failure_count', $db->queries[3]);
        $this->assertStringContainsString(BasicAuthRateLimiter::IP_BUCKET_USER, $db->queries[4]);
        $this->assertStringContainsString(date('Y-m-d H:i:s', $now + BasicAuthRateLimiter::LOCK_SECONDS), $db->queries[2]);
    }

    public function testCheckBlocksWhenGlobalIpBucketIsBlocked(): void
    {
        $now = strtotime('2026-05-02 12:00:00');
        $this->assertIsInt($now);

        $db = new BasicAuthRateLimiterFakeDb([
            false,
            (object)[
                'failure_count' => BasicAuthRateLimiter::MAX_FAILURES,
                'window_started_at' => date('Y-m-d H:i:s', $now - 60),
                'last_failed_at' => date('Y-m-d H:i:s', $now - 10),
                'blocked_until' => date('Y-m-d H:i:s', $now + 300),
            ],
        ]);

        $outcome = BasicAuthRateLimiter::check($db, 'different-user', '192.0.2.44', $now);

        $this->assertFalse($outcome['allowed']);
        $this->assertSame(300, $outcome['retry_after']);
        $this->assertStringContainsString(BasicAuthRateLimiter::IP_BUCKET_USER, $db->queries[1]);
    }

    public function testClearFailuresDoesNotClearGlobalIpBucket(): void
    {
        $db = new BasicAuthRateLimiterFakeDb();

        BasicAuthRateLimiter::clearFailures($db, 'api', '192.0.2.44');

        $this->assertCount(1, $db->queries);
        $this->assertStringContainsString("`user`='api'", $db->queries[0]);
        $this->assertStringNotContainsString(BasicAuthRateLimiter::IP_BUCKET_USER, $db->queries[0]);
    }
}

final class BasicAuthRateLimiterFakeDb
{
    /** @var list<string> */
    public array $queries = [];

    /** @var list<object|false|null> */
    private array $rows;

    /**
     * @param object|false|null|list<object|false|null> $rows
     */
    public function __construct($rows = null)
    {
        $this->rows = is_array($rows) ? array_values($rows) : [$rows];
    }

    public function sql_query(string $sql): string
    {
        $this->queries[] = $sql;

        return $sql;
    }

    public function sql_fetch_object(string $result): object|false
    {
        unset($result);
        $row = array_shift($this->rows);

        return $row ?? false;
    }

    public function sql_real_escape_string($value): string
    {
        return str_replace("'", "''", (string)$value);
    }
}
