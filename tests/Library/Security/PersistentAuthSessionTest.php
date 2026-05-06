<?php

declare(strict_types=1);

use App\Library\Security\PersistentAuthSession;
use PHPUnit\Framework\TestCase;

final class PersistentAuthSessionTest extends TestCase
{
    public function testCookieParserAcceptsOnlyOpaqueSelectorVerifierPairs(): void
    {
        $selector = str_repeat('a', 32);
        $verifier = str_repeat('b', 64);

        $parsed = PersistentAuthSession::parseCookieValue($selector . '.' . $verifier);

        $this->assertSame(['selector' => $selector, 'verifier' => $verifier], $parsed);
        $this->assertNull(PersistentAuthSession::parseCookieValue(str_repeat('c', 60)));
        $this->assertNull(PersistentAuthSession::parseCookieValue($selector . ':not-hex'));
        $this->assertNull(PersistentAuthSession::parseCookieValue(['not' => 'scalar']));
    }

    public function testIssueStoresOnlyVerifierHashAndExpiresLegacyCookies(): void
    {
        $db = new Issue624PersistentAuthFakeDb();
        $auth = new Issue624PersistentAuthFakeAuth((object) ['id' => 42, 'id_group' => 3]);
        $server = $this->server();
        $cookies = [];
        $setter = $this->cookieRecorder($cookies);

        $created = PersistentAuthSession::issueForAuthenticatedUser(
            $auth,
            $db,
            $server,
            [],
            $setter,
            $this->randomBytesQueue([str_repeat("\x01", 16), str_repeat("\x02", 32)]),
            1710000000
        );

        $this->assertTrue($created);
        $sql = implode("\n", $db->queries);
        $rawVerifier = str_repeat('02', 32);
        $this->assertStringContainsString('DELETE FROM `user_persistent_auth_session`', $db->queries[0]);
        $this->assertStringContainsString('LIMIT 9', $db->queries[1]);
        $this->assertStringContainsString('INSERT INTO `user_persistent_auth_session`', $sql);
        $this->assertStringContainsString('date_absolute_expires', $sql);
        $this->assertStringContainsString(PersistentAuthSession::hashVerifier($rawVerifier), $sql);
        $this->assertStringNotContainsString($rawVerifier, $sql);
        $this->assertStringNotContainsString($server['HTTP_USER_AGENT'], $sql);
        $this->assertStringNotContainsString($server['REMOTE_ADDR'], $sql);

        // Issue #762: opaque cookie must be set BEFORE legacy cookies are
        // expired — otherwise an INSERT failure between the two leaves the
        // browser without any auth cookie at all (already merged below in
        // testIssueKeepsLegacyCookiesWhenInsertFails).
        $this->assertSame(
            [
                PersistentAuthSession::COOKIE_NAME,
                PersistentAuthSession::LEGACY_COOKIE_LOGIN,
                PersistentAuthSession::LEGACY_COOKIE_PASSWORD,
            ],
            array_column($cookies, 'name')
        );
        $this->assertSame(str_repeat('01', 16) . '.' . $rawVerifier, $cookies[0]['value']);
        $this->assertGreaterThan(1710000000, $cookies[0]['options']['expires']);
    }

    public function testIssueKeepsLegacyCookiesWhenInsertFails(): void
    {
        $db = new Issue624PersistentAuthFakeDb();
        $db->failNextWrite = true;
        $auth = new Issue624PersistentAuthFakeAuth((object) ['id' => 42, 'id_group' => 3]);
        $server = $this->server();
        $cookies = [];
        $setter = $this->cookieRecorder($cookies);

        $created = PersistentAuthSession::issueForAuthenticatedUser(
            $auth,
            $db,
            $server,
            [],
            $setter,
            $this->randomBytesQueue([str_repeat("\x01", 16), str_repeat("\x02", 32)]),
            1710000000
        );

        $this->assertFalse($created, 'Failed INSERT should be reported up to the caller.');
        $this->assertSame(
            [],
            $cookies,
            'When the INSERT fails (e.g. user_persistent_auth_session table is missing), '
            . 'no cookie mutation must happen — neither the new opaque cookie nor the '
            . 'legacy login/password cookies. Otherwise the user is logged out on the '
            . 'next redirect (regression #762).'
        );
    }

    public function testOpaqueCookieAuthenticatesUserAndRotatesVerifier(): void
    {
        $selector = str_repeat('a', 32);
        $verifier = str_repeat('b', 64);
        $server = $this->server();
        $fingerprint = PersistentAuthSession::fingerprint($server);
        $db = new Issue624PersistentAuthFakeDb([
            (object) [
                'persistent_auth_session_id' => 7,
                'token_hash' => PersistentAuthSession::hashVerifier($verifier),
                'previous_token_hash' => null,
                'previous_token_expires' => null,
                'user_agent_hash' => $fingerprint['user_agent_hash'],
                'ip_hash' => $fingerprint['ip_hash'],
                'date_last_used' => null,
                'date_expires' => '2026-12-01 00:00:00',
                'date_absolute_expires' => '2026-12-15 00:00:00',
                'date_revoked' => null,
                'id' => 42,
                'id_group' => 5,
                'login' => 'admin',
            ],
        ]);
        $auth = new Issue624PersistentAuthFakeAuth();
        $cookies = [];

        $authenticated = PersistentAuthSession::authenticate(
            $auth,
            $db,
            [PersistentAuthSession::COOKIE_NAME => $selector . '.' . $verifier],
            $server,
            [],
            $this->cookieRecorder($cookies),
            $this->randomBytesQueue([str_repeat("\x03", 32)]),
            1710000000
        );

        $this->assertTrue($authenticated);
        $this->assertSame(42, $auth->getIdUserTriingLogin());
        $this->assertSame(42, Issue624PersistentAuthFakeAuth::$id_user_main);
        $this->assertSame(5, $auth->getAccess());
        $sql = implode("\n", $db->queries);
        $this->assertStringContainsString('UPDATE `user_persistent_auth_session` SET', $sql);
        $this->assertStringContainsString("AND `token_hash` = '" . PersistentAuthSession::hashVerifier($verifier) . "'", $sql);
        $this->assertSame(PersistentAuthSession::COOKIE_NAME, $cookies[0]['name']);
        $this->assertSame($selector . '.' . str_repeat('03', 32), $cookies[0]['value']);
    }

    public function testConcurrentRotationLossAcceptsSessionViaGraceWindow(): void
    {
        // Issue #773: when two parallel requests arrive with the same cookie,
        // only one wins the CAS rotation. Before the fix the loser revoked
        // the session; with the grace window it must still authenticate
        // because its verifier is now in `previous_token_hash` (server-side)
        // or will land there once the winner commits.
        $selector = str_repeat('a', 32);
        $verifier = str_repeat('b', 64);
        $server = $this->server();
        $fingerprint = PersistentAuthSession::fingerprint($server);
        $db = new Issue624PersistentAuthFakeDb([
            (object) [
                'persistent_auth_session_id' => 7,
                'token_hash' => PersistentAuthSession::hashVerifier($verifier),
                'previous_token_hash' => null,
                'previous_token_expires' => null,
                'user_agent_hash' => $fingerprint['user_agent_hash'],
                'ip_hash' => $fingerprint['ip_hash'],
                'date_last_used' => null,
                'date_expires' => '2026-12-01 00:00:00',
                'date_absolute_expires' => '2026-12-15 00:00:00',
                'date_revoked' => null,
                'id' => 42,
                'id_group' => 5,
            ],
        ], 0);
        $auth = new Issue624PersistentAuthFakeAuth();
        $cookies = [];

        $authenticated = PersistentAuthSession::authenticate(
            $auth,
            $db,
            [PersistentAuthSession::COOKIE_NAME => $selector . '.' . $verifier],
            $server,
            [],
            $this->cookieRecorder($cookies),
            $this->randomBytesQueue([str_repeat("\x03", 32)]),
            1710000000
        );

        $this->assertTrue($authenticated, 'Lost rotation race must not log out the user (issue #773).');
        $this->assertSame(42, $auth->getIdUserTriingLogin());
        $sql = implode("\n", $db->queries);
        $this->assertStringNotContainsString('SET `date_revoked`', $sql, 'No revoke on rotation race.');
        $this->assertSame([], $cookies, 'Loser of rotation race must not emit Set-Cookie.');
    }

    public function testInFlightVerifierMatchingPreviousHashAuthenticatesWithoutRotating(): void
    {
        // Once the winner's UPDATE has committed, in-flight requests still
        // carrying the old verifier hit `previous_token_hash` directly. They
        // must authenticate without re-rotating and without sending a new
        // Set-Cookie (which would fight the cookie the winner already set).
        $selector = str_repeat('a', 32);
        $oldVerifier = str_repeat('b', 64);
        $newVerifier = str_repeat('c', 64);
        $server = $this->server();
        $fingerprint = PersistentAuthSession::fingerprint($server);
        $db = new Issue624PersistentAuthFakeDb([
            (object) [
                'persistent_auth_session_id' => 7,
                'token_hash' => PersistentAuthSession::hashVerifier($newVerifier),
                'previous_token_hash' => PersistentAuthSession::hashVerifier($oldVerifier),
                'previous_token_expires' => gmdate('Y-m-d H:i:s', 1710000000 + 8),
                'user_agent_hash' => $fingerprint['user_agent_hash'],
                'ip_hash' => $fingerprint['ip_hash'],
                'date_last_used' => gmdate('Y-m-d H:i:s', 1710000000),
                'date_expires' => '2026-12-01 00:00:00',
                'date_absolute_expires' => '2026-12-15 00:00:00',
                'date_revoked' => null,
                'id' => 42,
                'id_group' => 5,
            ],
        ]);
        $auth = new Issue624PersistentAuthFakeAuth();
        $cookies = [];

        $authenticated = PersistentAuthSession::authenticate(
            $auth,
            $db,
            [PersistentAuthSession::COOKIE_NAME => $selector . '.' . $oldVerifier],
            $server,
            [],
            $this->cookieRecorder($cookies),
            null,
            1710000000
        );

        $this->assertTrue($authenticated);
        $this->assertSame(42, $auth->getIdUserTriingLogin());
        $sql = implode("\n", $db->queries);
        $this->assertStringNotContainsString('UPDATE `user_persistent_auth_session`', $sql, 'Must not touch the row when serving via previous-token grace.');
        $this->assertSame([], $cookies);
    }

    public function testCooldownSkipsRotationAndJustTouchesRow(): void
    {
        // A second request within ROTATE_COOLDOWN_SECONDS keeps the existing
        // verifier and only refreshes date_last_used. This is what kills the
        // AJAX/auto-refresh storm that used to drown the rotation pipeline.
        $selector = str_repeat('a', 32);
        $verifier = str_repeat('b', 64);
        $server = $this->server();
        $fingerprint = PersistentAuthSession::fingerprint($server);
        $db = new Issue624PersistentAuthFakeDb([
            (object) [
                'persistent_auth_session_id' => 7,
                'token_hash' => PersistentAuthSession::hashVerifier($verifier),
                'previous_token_hash' => null,
                'previous_token_expires' => null,
                'user_agent_hash' => $fingerprint['user_agent_hash'],
                'ip_hash' => $fingerprint['ip_hash'],
                'date_last_used' => gmdate('Y-m-d H:i:s', 1710000000 - 5),
                'date_expires' => '2026-12-01 00:00:00',
                'date_absolute_expires' => '2026-12-15 00:00:00',
                'date_revoked' => null,
                'id' => 42,
                'id_group' => 5,
            ],
        ]);
        $auth = new Issue624PersistentAuthFakeAuth();
        $cookies = [];

        $authenticated = PersistentAuthSession::authenticate(
            $auth,
            $db,
            [PersistentAuthSession::COOKIE_NAME => $selector . '.' . $verifier],
            $server,
            [],
            $this->cookieRecorder($cookies),
            null,
            1710000000
        );

        $this->assertTrue($authenticated);
        $sql = implode("\n", $db->queries);
        $this->assertStringContainsString('UPDATE `user_persistent_auth_session`', $sql, 'Touch UPDATE must run.');
        $this->assertStringNotContainsString('`previous_token_hash` = `token_hash`', $sql, 'No rotation during cooldown.');
        $this->assertStringNotContainsString("`token_hash` = '", $sql, 'No new token_hash assignment during cooldown.');
        $this->assertSame([], $cookies, 'No Set-Cookie during cooldown.');
    }

    public function testAjaxRequestNeverRotatesEvenAfterCooldown(): void
    {
        // AJAX requests are the worst race amplifier (parallel by nature, fire
        // continuously from dashboards). Even if the cooldown elapsed, we
        // still skip rotation on AJAX and only touch the row.
        $selector = str_repeat('a', 32);
        $verifier = str_repeat('b', 64);
        $server = $this->server();
        $server['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $fingerprint = PersistentAuthSession::fingerprint($server);
        $db = new Issue624PersistentAuthFakeDb([
            (object) [
                'persistent_auth_session_id' => 7,
                'token_hash' => PersistentAuthSession::hashVerifier($verifier),
                'previous_token_hash' => null,
                'previous_token_expires' => null,
                'user_agent_hash' => $fingerprint['user_agent_hash'],
                'ip_hash' => $fingerprint['ip_hash'],
                'date_last_used' => gmdate('Y-m-d H:i:s', 1710000000 - 600),
                'date_expires' => '2026-12-01 00:00:00',
                'date_absolute_expires' => '2026-12-15 00:00:00',
                'date_revoked' => null,
                'id' => 42,
                'id_group' => 5,
            ],
        ]);
        $auth = new Issue624PersistentAuthFakeAuth();
        $cookies = [];

        $authenticated = PersistentAuthSession::authenticate(
            $auth,
            $db,
            [PersistentAuthSession::COOKIE_NAME => $selector . '.' . $verifier],
            $server,
            [],
            $this->cookieRecorder($cookies),
            null,
            1710000000
        );

        $this->assertTrue($authenticated);
        $this->assertStringNotContainsString("`token_hash` = '", implode("\n", $db->queries), 'AJAX must never rotate.');
        $this->assertSame([], $cookies);
    }

    public function testReplayAfterRotationRevokesSession(): void
    {
        $selector = str_repeat('a', 32);
        $oldVerifier = str_repeat('b', 64);
        $newVerifier = str_repeat('c', 64);
        $server = $this->server();
        $fingerprint = PersistentAuthSession::fingerprint($server);
        $db = new Issue624PersistentAuthFakeDb([
            (object) [
                'persistent_auth_session_id' => 7,
                'token_hash' => PersistentAuthSession::hashVerifier($newVerifier),
                'previous_token_hash' => null,
                'previous_token_expires' => null,
                'user_agent_hash' => $fingerprint['user_agent_hash'],
                'ip_hash' => $fingerprint['ip_hash'],
                'date_last_used' => null,
                'date_expires' => '2026-12-01 00:00:00',
                'date_absolute_expires' => '2026-12-15 00:00:00',
                'date_revoked' => null,
                'id' => 42,
                'id_group' => 5,
            ],
        ]);
        $auth = new Issue624PersistentAuthFakeAuth();
        $cookies = [];

        $authenticated = PersistentAuthSession::authenticate(
            $auth,
            $db,
            [PersistentAuthSession::COOKIE_NAME => $selector . '.' . $oldVerifier],
            $server,
            [],
            $this->cookieRecorder($cookies),
            null,
            1710000000
        );

        $this->assertFalse($authenticated);
        $this->assertStringContainsString('SET `date_revoked`', implode("\n", $db->queries));
        $this->assertSame('', $cookies[0]['value']);
    }

    public function testFingerprintMismatchRevokesOpaqueSession(): void
    {
        $selector = str_repeat('a', 32);
        $verifier = str_repeat('b', 64);
        $db = new Issue624PersistentAuthFakeDb([
            (object) [
                'persistent_auth_session_id' => 7,
                'token_hash' => PersistentAuthSession::hashVerifier($verifier),
                'previous_token_hash' => null,
                'previous_token_expires' => null,
                'user_agent_hash' => hash('sha256', 'different-agent'),
                'ip_hash' => null,
                'date_last_used' => null,
                'date_expires' => '2026-12-01 00:00:00',
                'date_absolute_expires' => '2026-12-15 00:00:00',
                'date_revoked' => null,
                'id' => 42,
                'id_group' => 5,
            ],
        ]);
        $auth = new Issue624PersistentAuthFakeAuth();
        $cookies = [];

        $authenticated = PersistentAuthSession::authenticate(
            $auth,
            $db,
            [PersistentAuthSession::COOKIE_NAME => $selector . '.' . $verifier],
            $this->server(),
            [],
            $this->cookieRecorder($cookies),
            null,
            1710000000
        );

        $this->assertFalse($authenticated);
        $this->assertSame(0, $auth->getIdUserTriingLogin());
        $this->assertStringContainsString('SET `date_revoked`', implode("\n", $db->queries));
        $this->assertSame(PersistentAuthSession::COOKIE_NAME, $cookies[0]['name']);
        $this->assertSame('', $cookies[0]['value']);
    }

    public function testAuthReflectionFailsClosedWhenGlialShapeChanges(): void
    {
        $selector = str_repeat('a', 32);
        $verifier = str_repeat('b', 64);
        $server = $this->server();
        $fingerprint = PersistentAuthSession::fingerprint($server);
        $db = new Issue624PersistentAuthFakeDb([
            (object) [
                'persistent_auth_session_id' => 7,
                'token_hash' => PersistentAuthSession::hashVerifier($verifier),
                'previous_token_hash' => null,
                'previous_token_expires' => null,
                'user_agent_hash' => $fingerprint['user_agent_hash'],
                'ip_hash' => $fingerprint['ip_hash'],
                'date_last_used' => null,
                'date_expires' => '2026-12-01 00:00:00',
                'date_absolute_expires' => '2026-12-15 00:00:00',
                'date_revoked' => null,
                'id' => 42,
                'id_group' => 5,
            ],
        ]);
        $auth = new Issue624PersistentAuthBrokenAuth();
        $cookies = [];

        $authenticated = PersistentAuthSession::authenticate(
            $auth,
            $db,
            [PersistentAuthSession::COOKIE_NAME => $selector . '.' . $verifier],
            $server,
            [],
            $this->cookieRecorder($cookies),
            $this->randomBytesQueue([str_repeat("\x03", 32)]),
            1710000000
        );

        $this->assertFalse($authenticated);
        $this->assertNull($auth->getUser());
        $this->assertStringContainsString('SET `date_revoked`', implode("\n", $db->queries));
        $this->assertSame('', $cookies[0]['value']);
    }

    public function testRevokeCurrentExpiresOpaqueAndLegacyCookies(): void
    {
        $db = new Issue624PersistentAuthFakeDb();
        $cookies = [];
        $selector = str_repeat('a', 32);
        $verifier = str_repeat('b', 64);

        PersistentAuthSession::revokeCurrent(
            $db,
            [PersistentAuthSession::COOKIE_NAME => $selector . '.' . $verifier],
            $this->server(),
            [],
            $this->cookieRecorder($cookies),
            1710000000
        );

        $this->assertStringContainsString('WHERE `selector` = \'' . $selector . '\'', implode("\n", $db->queries));
        $this->assertSame(
            [
                PersistentAuthSession::COOKIE_NAME,
                PersistentAuthSession::LEGACY_COOKIE_LOGIN,
                PersistentAuthSession::LEGACY_COOKIE_PASSWORD,
            ],
            array_column($cookies, 'name')
        );
    }

    public function testSourceNoLongerConfiguresPasswordDerivedCookieHash(): void
    {
        $bootstrap = file_get_contents(__DIR__ . '/../../../App/Webroot/Bootstrap.php');
        $user = file_get_contents(__DIR__ . '/../../../App/Controller/User.php');
        $config = file_get_contents(__DIR__ . '/../../../config_sample/auth.config.php');

        $this->assertIsString($bootstrap);
        $this->assertIsString($user);
        $this->assertIsString($config);
        $this->assertStringNotContainsString('setFctToHashCookie', $bootstrap);
        $this->assertStringNotContainsString('HTTP_USER_AGENT\'].$_SERVER[\'REMOTE_ADDR', $bootstrap);
        $this->assertStringContainsString('PersistentAuthSession::authenticate', $bootstrap);
        $this->assertStringContainsString('PersistentAuthSession::hasLegacyCookies', $bootstrap);
        $this->assertStringContainsString('if ($legacyPersistentAuth && $is_auth)', $bootstrap);
        $this->assertStringContainsString('PersistentAuthSession::issueForAuthenticatedUser', $user);
        $this->assertStringContainsString('PersistentAuthSession::revokeCurrent', $user);
        $this->assertStringContainsString('PersistentAuthSession::deleteLegacyCookies', $user);
        $this->assertStringContainsString('define("AUTH_SESSION_TIME",1209600);', $config);
        $this->assertStringContainsString('define("AUTH_SESSION_ABSOLUTE_TIME",2592000);', $config);
    }

    public function testSchemaContainsPersistentSessionTable(): void
    {
        $migration = file_get_contents(__DIR__ . '/../../../sql/incremental_v2/20260502_persistent_auth_sessions.sql');
        $fullSchema = file_get_contents(__DIR__ . '/../../../sql/full/pmacontrol.sql');

        $this->assertIsString($migration);
        $this->assertIsString($fullSchema);
        foreach ([
            'user_persistent_auth_session',
            'selector',
            'token_hash',
            'date_last_used',
            'date_absolute_expires',
            'date_revoked',
            'uniq_user_persistent_auth_selector',
        ] as $needle) {
            $this->assertStringContainsString($needle, $migration);
            $this->assertStringContainsString($needle, $fullSchema);
        }

        // Issue #773: grace-window columns are needed to absorb parallel
        // requests that race the verifier rotation.
        $graceMigration = file_get_contents(__DIR__ . '/../../../sql/incremental_v2/20260506_persistent_auth_grace_window.sql');
        $this->assertIsString($graceMigration);
        foreach (['previous_token_hash', 'previous_token_expires'] as $needle) {
            $this->assertStringContainsString($needle, $graceMigration);
            $this->assertStringContainsString($needle, $fullSchema);
        }
    }

    /**
     * @return array<string,string>
     */
    private function server(): array
    {
        return [
            'SERVER_NAME' => 'pmacontrol.test',
            'HTTPS' => 'on',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 PmaControl Test',
            'REMOTE_ADDR' => '203.0.113.10',
        ];
    }

    /**
     * @param array<int,array{name:string,value:string,options:array<string,mixed>}> $cookies
     */
    private function cookieRecorder(array &$cookies): callable
    {
        return static function (string $name, string $value, array $options) use (&$cookies): bool {
            $cookies[] = [
                'name' => $name,
                'value' => $value,
                'options' => $options,
            ];

            return true;
        };
    }

    /**
     * @param array<int,string> $chunks
     */
    private function randomBytesQueue(array $chunks): callable
    {
        return static function (int $bytes) use (&$chunks): string {
            $chunk = array_shift($chunks);
            if (!is_string($chunk) || strlen($chunk) !== $bytes) {
                throw new RuntimeException('Unexpected random_bytes request');
            }

            return $chunk;
        };
    }
}

final class Issue624PersistentAuthFakeResult
{
    /** @var array<int,object> */
    public $rows;

    /** @var int */
    public $index = 0;

    /**
     * @param array<int,object> $rows
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }
}

final class Issue624PersistentAuthFakeDb
{
    /** @var array<int,string> */
    public $queries = [];

    /** @var bool */
    public $failNextWrite = false;

    /** @var array<int,object> */
    private $rows;

    /** @var int */
    private $affectedRows;

    /**
     * @param array<int,object> $rows
     */
    public function __construct(array $rows = [], int $affectedRows = 1)
    {
        $this->rows = $rows;
        $this->affectedRows = $affectedRows;
    }

    /**
     * @return Issue624PersistentAuthFakeResult|bool
     */
    public function sql_query(string $sql)
    {
        $this->queries[] = $sql;
        if (stripos($sql, 'SELECT') === 0) {
            return new Issue624PersistentAuthFakeResult($this->rows);
        }

        if ($this->failNextWrite && stripos($sql, 'INSERT') === 0) {
            return false;
        }

        return true;
    }

    public function sql_error(): string
    {
        return $this->failNextWrite ? "Table 'pmacontrol.user_persistent_auth_session' doesn't exist" : '';
    }

    public function sql_num_rows(Issue624PersistentAuthFakeResult $result): int
    {
        return count($result->rows);
    }

    public function sql_fetch_object(Issue624PersistentAuthFakeResult $result)
    {
        return $result->rows[$result->index++] ?? null;
    }

    public function sql_affected_rows(): int
    {
        return $this->affectedRows;
    }

    public function sql_real_escape_string(string $value): string
    {
        return addslashes($value);
    }
}

final class Issue624PersistentAuthFakeAuth
{
    /** @var object|null */
    private $_user;

    /** @var int */
    private $id_user = 0;

    /** @var int */
    public static $id_user_main = 0;

    public function __construct(?object $user = null)
    {
        self::$id_user_main = 0;
        $this->_user = $user;
    }

    public function getUser(): ?object
    {
        return $this->_user;
    }

    public function getIdUserTriingLogin(): int
    {
        return $this->id_user;
    }

    public function getAccess(): int
    {
        return (int) ($this->_user->id_group ?? 1);
    }
}

final class Issue624PersistentAuthBrokenAuth
{
    /** @var object|null */
    private $_user = null;

    /** @var int */
    private $id_user = 0;

    public function getUser(): ?object
    {
        return $this->_user;
    }

    public function getIdUserTriingLogin(): int
    {
        return $this->id_user;
    }
}
