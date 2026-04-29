<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Http\HttpOutcome;
use App\Library\Security\CsrfGuard;
use App\Library\Security\PayloadValidator;
use Glial\Security\Csrf;

/**
 * Class responsible for format workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Format extends Controller
{
    private const FORMAT_INDEX_CSRF_SCOPE = 'format.index';
    private const FORMAT_SQL_SESSION_KEY = 'format_sql';
    private const FORMAT_SQL_MAX_BYTES = 1048576;
    private const FORMAT_SQL_MAX_AGE = 86400;
    private const FORMAT_SQL_MAX_ENTRIES = 20;

/**
 * Render format state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/format/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param)
    {

        $this->title = '<i class="fa fa-wpforms" aria-hidden="true"></i> '.__("Format SQL");

        if (CsrfGuard::isPost($_SERVER)) {
            $indexPost = self::evaluateIndexPost($_POST, $_SERVER, $_SESSION);
            if ($indexPost['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendFormatError($indexPost['status'], $indexPost['body'], $indexPost['headers']);
                return;
            }

            self::storeSqlInSession($_SESSION, $indexPost['hash'], $indexPost['sql']);

            header("location: ".LINK.$this->getClass()."/".__FUNCTION__."/".$indexPost['hash']);
            return;
        }

        $data = self::buildIndexData($param, $_SESSION);
        $data['format_index_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['format_index_csrf_token'] = Csrf::issueToken($_SESSION, self::FORMAT_INDEX_CSRF_SCOPE);

        $this->set('data', $data);
    }

    public static function evaluateIndexPost(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::FORMAT_INDEX_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return HttpOutcome::fromGuard($guard, ['sql' => '', 'hash' => '']);
        }

        $sql = self::normalizeSqlPayload($post);
        if ($sql === null) {
            return HttpOutcome::error(422, 'Invalid SQL payload', [], ['sql' => '', 'hash' => '']);
        }

        if (strlen($sql) > self::FORMAT_SQL_MAX_BYTES) {
            return HttpOutcome::error(413, 'SQL payload too large', [], ['sql' => '', 'hash' => '']);
        }

        return HttpOutcome::ok([
            'sql' => $sql,
            'hash' => md5($sql),
        ]);
    }

    public static function normalizeSqlPayload(array $post): ?string
    {
        $payload = PayloadValidator::validate($post, [
            'sql' => 'string',
        ]);
        if ($payload === null) {
            return null;
        }

        return $payload['sql'];
    }

    public static function storeSqlInSession(array &$session, string $hash, string $sql, ?int $now = null): void
    {
        $now = $now ?? time();
        self::pruneSqlSession($session, $now);
        $session[self::FORMAT_SQL_SESSION_KEY][$hash] = [
            'sql' => $sql,
            'created_at' => $now,
        ];
    }

    public static function getStoredSql(array $session, string $hash): ?string
    {
        if (!self::isFormatSqlHash($hash)) {
            return null;
        }

        $formatSqlSession = $session[self::FORMAT_SQL_SESSION_KEY] ?? [];
        $entry = is_array($formatSqlSession) ? ($formatSqlSession[$hash] ?? null) : null;
        if (is_array($entry) && isset($entry['sql']) && is_string($entry['sql'])) {
            return $entry['sql'];
        }
        if (is_string($entry)) {
            return $entry;
        }

        $legacyEntry = $session[$hash] ?? null;
        return is_string($legacyEntry) ? $legacyEntry : null;
    }

    public static function buildIndexData(array $param, array $session): array
    {
        $data = array();
        $hash = $param[0] ?? null;
        if (!is_scalar($hash)) {
            return $data;
        }

        $sql = self::getStoredSql($session, trim((string) $hash));
        if ($sql === null) {
            return $data;
        }

        $data['sql'] = $sql;
        if ($sql === '') {
            return $data;
        }

        $data['$queries'] = \SqlFormatter::splitQuery($sql);
        foreach ($data['$queries'] as $query) {
            $data['sql_formated'][] = \SqlFormatter::format($query);
        }

        return $data;
    }

    private static function pruneSqlSession(array &$session, int $now): void
    {
        if (!isset($session[self::FORMAT_SQL_SESSION_KEY]) || !is_array($session[self::FORMAT_SQL_SESSION_KEY])) {
            $session[self::FORMAT_SQL_SESSION_KEY] = [];
            return;
        }

        $entries = [];
        foreach ($session[self::FORMAT_SQL_SESSION_KEY] as $hash => $entry) {
            if (!is_string($hash) || !self::isFormatSqlHash($hash)) {
                continue;
            }

            $createdAt = is_array($entry) && isset($entry['created_at']) && is_numeric($entry['created_at'])
                ? (int) $entry['created_at']
                : $now;
            if ($now - $createdAt > self::FORMAT_SQL_MAX_AGE) {
                continue;
            }

            $entries[$hash] = $entry;
        }

        uasort($entries, static function ($left, $right): int {
            $leftCreatedAt = is_array($left) && isset($left['created_at']) && is_numeric($left['created_at']) ? (int) $left['created_at'] : 0;
            $rightCreatedAt = is_array($right) && isset($right['created_at']) && is_numeric($right['created_at']) ? (int) $right['created_at'] : 0;
            return $leftCreatedAt <=> $rightCreatedAt;
        });

        while (count($entries) >= self::FORMAT_SQL_MAX_ENTRIES) {
            array_shift($entries);
        }

        $session[self::FORMAT_SQL_SESSION_KEY] = $entries;
    }

    private static function isFormatSqlHash(string $hash): bool
    {
        return preg_match('/\\A[a-f0-9]{32}\\z/i', $hash) === 1;
    }

    private static function sendFormatError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $message;
    }

/**
 * Handle format state through `base64url_encode`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $data Input value for `data`.
 * @phpstan-param array<int|string,mixed> $data
 * @psalm-param array<int|string,mixed> $data
 * @return mixed Returned value for base64url_encode.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::base64url_encode()
 * @example /fr/format/base64url_encode
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function base64url_encode($data)
    {
        return strtr(base64_encode($val), '+/=', '-_,');
        //return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

/**
 * Handle format state through `base64url_decode`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $data Input value for `data`.
 * @phpstan-param array<int|string,mixed> $data
 * @psalm-param array<int|string,mixed> $data
 * @return mixed Returned value for base64url_decode.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::base64url_decode()
 * @example /fr/format/base64url_decode
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function base64url_decode($data)
    {
        return base64_decode(strtr($val, '-_,', '+/='));
        //return base64_decode(strtr($data, '-_', '+/').str_repeat('=', 3 - ( 3 + strlen($data)) % 4));
    }
}
/*
 * select if(`performance_schema`.`threads`.`PROCESSLIST_ID` is null,substring_index(`performance_schema`.`threads`.`NAME`,'/',-1),
 * concat(`performance_schema`.`threads`.`PROCESSLIST_USER`,'@',`performance_schema`.`threads`.`PROCESSLIST_HOST`)) AS `user`,
 * sum(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`COUNT_STAR`) AS `total`,
 * `sys4`.`format_time`(sum(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`SUM_TIMER_WAIT`)) AS `total_latency`,
 * `sys4`.`format_time`(min(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`MIN_TIMER_WAIT`)) AS `min_latency`,
 * `sys4`.`format_time`(avg(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`AVG_TIMER_WAIT`)) AS `avg_latency`,
 * `sys4`.`format_time`(max(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`MAX_TIMER_WAIT`)) AS `max_latency`,
 * `performance_schema`.`events_waits_summary_by_thread_by_event_name`.`THREAD_ID` AS `thread_id`,
 * `performance_schema`.`threads`.`PROCESSLIST_ID` AS `processlist_id`
 * from (`performance_schema`.`events_waits_summary_by_thread_by_event_name`
 * left join `performance_schema`.`threads` on(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`THREAD_ID` = `performance_schema`.`threads`.`THREAD_ID`))
 * where `performance_schema`.`events_waits_summary_by_thread_by_event_name`.`EVENT_NAME` like 'wait/io/file/%'
 * and `performance_schema`.`events_waits_summary_by_thread_by_event_name`.`SUM_TIMER_WAIT` > 0
 * group by `performance_schema`.`events_waits_summary_by_thread_by_event_name`.`THREAD_ID`,`performance_schema`.`threads`.`PROCESSLIST_ID`,
 * if(`performance_schema`.`threads`.`PROCESSLIST_ID` is null,substring_index(`performance_schema`.`threads`.`NAME`,'/',-1),
 * concat(`performance_schema`.`threads`.`PROCESSLIST_USER`,'@',`performance_schema`.`threads`.`PROCESSLIST_HOST`))
 * order by sum(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`SUM_TIMER_WAIT`) desc
 */
