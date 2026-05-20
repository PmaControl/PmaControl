<?php

declare(strict_types=1);

namespace App\Library\Digest;

use DateTimeImmutable;
use DateTimeZone;
use Glial\Sgbd\Sgbd;

final class DigestReportService
{
    public const DEFAULT_LIMIT = 5;
    public const MAX_LIMIT = 20;

    private const REPORTS = [
        'digest_summary' => [
            'slug' => 'digest_summary',
            'title' => 'Digest summary',
            'description' => 'Daily or weekly summary of slow digests and index signals.',
        ],
    ];

    private const FREQUENCIES = ['daily', 'weekly'];

    public static function getReports(): array
    {
        return self::REPORTS;
    }

    public static function buildDashboardData(): array
    {
        $warnings = [];
        $groups = [];
        $schedules = [];

        try {
            $db = Sgbd::sql(DB_DEFAULT);
            $groups = self::fetchGroups($db, $warnings);
            if (self::tableExists($db, 'digest_report_schedule')) {
                $schedules = self::fetchSchedules($db, $warnings);
            } else {
                $warnings[] = 'Digest report tables are not installed yet.';
            }
        } catch (\Throwable $exception) {
            $warnings[] = 'Digest report storage unavailable: ' . self::sanitizeMessage($exception->getMessage());
        }

        return [
            'reports' => array_values(self::getReports()),
            'groups' => $groups,
            'schedules' => $schedules,
            'frequencies' => self::FREQUENCIES,
            'days_of_week' => self::daysOfWeek(),
            'default_limit' => self::DEFAULT_LIMIT,
            'max_limit' => self::MAX_LIMIT,
            'cron_command' => self::cronCommand(),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    public static function normalizeSchedulePayload($input, ?DateTimeImmutable $now = null): array
    {
        if (!is_array($input)) {
            return self::validation(false, ['Invalid digest report payload']);
        }

        $errors = [];
        $id = self::positiveInt($input['id'] ?? null, true);
        $name = self::cleanText($input['name'] ?? '', 100);
        if ($name === '') {
            $errors[] = 'Name is required.';
        }

        $reportSlug = strtolower(self::cleanText($input['report_slug'] ?? 'digest_summary', 64));
        if (!isset(self::REPORTS[$reportSlug])) {
            $errors[] = 'Unknown digest report.';
        }

        $groupId = self::positiveInt($input['id_group'] ?? null, false);
        if ($groupId <= 0) {
            $errors[] = 'Recipient group is required.';
        }

        $frequency = strtolower(self::cleanText($input['frequency'] ?? 'daily', 16));
        if (!in_array($frequency, self::FREQUENCIES, true)) {
            $errors[] = 'Invalid frequency.';
            $frequency = 'daily';
        }

        $timeOfDay = self::normalizeTimeOfDay($input['time_of_day'] ?? '07:00');
        if ($timeOfDay === null) {
            $errors[] = 'Invalid time of day.';
            $timeOfDay = '07:00:00';
        }

        $dayOfWeek = self::positiveInt($input['day_of_week'] ?? 1, false);
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            $errors[] = 'Invalid day of week.';
            $dayOfWeek = 1;
        }

        $limitRows = self::positiveInt($input['limit_rows'] ?? self::DEFAULT_LIMIT, false);
        if ($limitRows < 1) {
            $limitRows = self::DEFAULT_LIMIT;
        }
        $limitRows = min($limitRows, self::MAX_LIMIT);

        $payload = [
            'id' => $id,
            'name' => $name,
            'report_slug' => $reportSlug,
            'id_group' => $groupId,
            'frequency' => $frequency,
            'time_of_day' => $timeOfDay,
            'day_of_week' => $dayOfWeek,
            'limit_rows' => $limitRows,
            'is_active' => !empty($input['is_active']) ? 1 : 0,
            'next_run_at' => self::calculateNextRun($frequency, $timeOfDay, $dayOfWeek, $now),
        ];

        return self::validation($errors === [], $errors, $payload);
    }

    public static function saveSchedule(array $payload): int
    {
        return self::saveScheduleWithDb(Sgbd::sql(DB_DEFAULT), $payload);
    }

    public static function saveScheduleWithDb($db, array $payload): int
    {
        $id = (int)($payload['id'] ?? 0);
        $fields = [
            'name' => self::quote($db, $payload['name'] ?? ''),
            'report_slug' => self::quote($db, $payload['report_slug'] ?? 'digest_summary'),
            'id_group' => (int)($payload['id_group'] ?? 0),
            'frequency' => self::quote($db, $payload['frequency'] ?? 'daily'),
            'is_active' => (int)($payload['is_active'] ?? 0),
            'time_of_day' => self::quote($db, $payload['time_of_day'] ?? '07:00:00'),
            'day_of_week' => (int)($payload['day_of_week'] ?? 1),
            'limit_rows' => (int)($payload['limit_rows'] ?? self::DEFAULT_LIMIT),
            'next_run_at' => self::quote($db, $payload['next_run_at'] ?? self::calculateNextRun('daily', '07:00:00', 1)),
        ];

        if ($id > 0) {
            $assignments = [];
            foreach ($fields as $field => $value) {
                $assignments[] = '`' . $field . '` = ' . $value;
            }
            $assignments[] = '`updated_at` = NOW()';
            $db->sql_query('UPDATE `digest_report_schedule` SET ' . implode(', ', $assignments) . ' WHERE `id` = ' . $id . ' LIMIT 1');

            return $id;
        }

        $db->sql_query(
            'INSERT INTO `digest_report_schedule` (`' . implode('`, `', array_keys($fields)) . '`, `created_at`, `updated_at`)'
            . ' VALUES (' . implode(', ', array_values($fields)) . ', NOW(), NOW())'
        );

        return (int)$db->sql_insert_id();
    }

    public static function runDueSchedules(bool $dryRun = false, ?callable $mailer = null): array
    {
        $db = Sgbd::sql(DB_DEFAULT);
        if (!self::tableExists($db, 'digest_report_schedule')) {
            return ['status' => 'error', 'message' => 'Digest report tables are not installed.', 'processed' => 0, 'items' => []];
        }

        $warnings = [];
        $rows = self::fetchRows(
            $db,
            "SELECT s.*, g.name AS group_name
             FROM digest_report_schedule s
             INNER JOIN `group` g ON g.id = s.id_group
             WHERE s.is_active = 1
               AND (s.next_run_at IS NULL OR s.next_run_at <= NOW())
             ORDER BY COALESCE(s.next_run_at, '1970-01-01 00:00:00'), s.id
             LIMIT 20",
            $warnings,
            'due schedules'
        );

        $items = [];
        foreach ($rows as $row) {
            $items[] = self::runScheduleRow($db, $row, $dryRun, $mailer);
        }

        return [
            'status' => empty($warnings) ? 'ok' : 'warning',
            'message' => empty($warnings) ? 'Digest report run completed.' : implode(' ', $warnings),
            'processed' => count($items),
            'items' => $items,
        ];
    }

    public static function runScheduleId(int $scheduleId, bool $dryRun = false, ?callable $mailer = null): array
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $warnings = [];
        $rows = self::fetchRows(
            $db,
            "SELECT s.*, g.name AS group_name
             FROM digest_report_schedule s
             INNER JOIN `group` g ON g.id = s.id_group
             WHERE s.id = " . $scheduleId . "
             LIMIT 1",
            $warnings,
            'schedule'
        );

        if (!$rows) {
            return ['status' => 'error', 'message' => 'Digest report schedule not found.', 'processed' => 0, 'items' => []];
        }

        return [
            'status' => 'ok',
            'message' => 'Digest report run completed.',
            'processed' => 1,
            'items' => [self::runScheduleRow($db, $rows[0], $dryRun, $mailer)],
        ];
    }

    public static function runScheduleRow($db, array $schedule, bool $dryRun = false, ?callable $mailer = null): array
    {
        $warnings = [];
        $recipients = self::fetchRecipients($db, (int)($schedule['id_group'] ?? 0), $warnings);
        $payload = self::buildRuntimePayload($db, $schedule, $warnings);
        $subject = self::buildSubject($payload);
        $htmlBody = self::renderHtml($payload);
        $textBody = self::renderText($payload);
        $status = 'ok';
        $errors = [];

        if (!$recipients) {
            $status = 'error';
            $errors[] = 'No active recipient with e-mail in group.';
        }

        foreach ($recipients as $recipient) {
            if ($dryRun) {
                continue;
            }

            $sent = self::sendMail($mailer, $recipient, $subject, $htmlBody, $textBody);
            self::recordDelivery($db, (int)$schedule['id'], $recipient, $sent ? 'ok' : 'error', $sent ? '' : 'mail() returned false', $subject, $textBody);
            if (!$sent) {
                $status = 'error';
                $errors[] = 'Mail send failed for ' . $recipient;
            }
        }

        if (!empty($warnings) && $status === 'ok') {
            $status = 'warning';
        }

        if (!$dryRun) {
            self::markScheduleRun($db, $schedule, $status, implode(' ', array_merge($warnings, $errors)));
        }

        return [
            'id' => (int)($schedule['id'] ?? 0),
            'name' => (string)($schedule['name'] ?? ''),
            'status' => $dryRun ? 'dry_run' : $status,
            'recipients' => $recipients,
            'subject' => $subject,
            'warnings' => $warnings,
            'errors' => $errors,
        ];
    }

    public static function buildRuntimePayload($db, array $schedule, array &$warnings): array
    {
        $days = self::windowDays($schedule['frequency'] ?? 'daily');
        $limit = self::normalizeLimit($schedule['limit_rows'] ?? self::DEFAULT_LIMIT);

        $summaryRows = self::fetchRows($db, self::buildSummarySql($days), $warnings, 'digest summary');
        $slowRows = self::fetchRows($db, self::buildSlowDigestSql($days, $limit), $warnings, 'slow digests');
        $indexRows = self::fetchRows($db, self::buildIndexSignalSql($days, $limit), $warnings, 'index signals');

        return self::buildPayloadFromRows($summaryRows, $slowRows, $indexRows, $schedule, $warnings);
    }

    public static function buildPayloadFromRows(array $summaryRows, array $slowRows, array $indexRows, array $schedule, array $warnings = []): array
    {
        $summary = $summaryRows[0] ?? [];
        $frequency = (string)($schedule['frequency'] ?? 'daily');
        $limit = self::normalizeLimit($schedule['limit_rows'] ?? self::DEFAULT_LIMIT);

        $slowDigests = array_map(static function (array $row): array {
            return [
                'server' => self::cleanLabel($row['display_name'] ?? $row['server'] ?? '-'),
                'client' => self::cleanLabel($row['client'] ?? '-'),
                'environment' => self::cleanLabel($row['environment'] ?? '-'),
                'schema' => self::cleanLabel($row['schema_name'] ?? '-'),
                'digest_text' => self::shortenSql((string)($row['digest_text'] ?? $row['query_sample_text'] ?? '')),
                'count_star' => self::toInt($row['count_star'] ?? 0),
                'sum_timer_wait' => self::toFloat($row['sum_timer_wait'] ?? 0.0),
                'max_timer_wait' => self::toFloat($row['max_timer_wait'] ?? 0.0),
                'rows_examined' => self::toInt($row['rows_examined'] ?? 0),
                'last_seen' => self::cleanLabel($row['last_seen'] ?? '-'),
            ];
        }, $slowRows);
        usort($slowDigests, static fn(array $left, array $right): int => $right['sum_timer_wait'] <=> $left['sum_timer_wait']);

        $indexSignals = array_map(static function (array $row): array {
            $noIndex = self::toInt($row['no_index_used'] ?? 0);
            $noGoodIndex = self::toInt($row['no_good_index_used'] ?? 0);

            return [
                'server' => self::cleanLabel($row['display_name'] ?? $row['server'] ?? '-'),
                'client' => self::cleanLabel($row['client'] ?? '-'),
                'environment' => self::cleanLabel($row['environment'] ?? '-'),
                'schema' => self::cleanLabel($row['schema_name'] ?? '-'),
                'digest_text' => self::shortenSql((string)($row['digest_text'] ?? $row['query_sample_text'] ?? '')),
                'count_star' => self::toInt($row['count_star'] ?? 0),
                'no_index_used' => $noIndex,
                'no_good_index_used' => $noGoodIndex,
                'score' => $noIndex + $noGoodIndex,
                'last_seen' => self::cleanLabel($row['last_seen'] ?? '-'),
            ];
        }, $indexRows);
        usort($indexSignals, static fn(array $left, array $right): int => $right['score'] <=> $left['score']);

        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'schedule' => [
                'id' => (int)($schedule['id'] ?? 0),
                'name' => self::cleanLabel($schedule['name'] ?? 'Digest report'),
                'frequency' => $frequency,
                'group_name' => self::cleanLabel($schedule['group_name'] ?? '-'),
                'limit_rows' => $limit,
            ],
            'window' => [
                'days' => self::windowDays($frequency),
                'min_date' => self::cleanLabel($summary['min_date'] ?? '-'),
                'max_date' => self::cleanLabel($summary['max_date'] ?? '-'),
            ],
            'summary' => [
                'servers' => self::toInt($summary['server_count'] ?? 0),
                'schemas' => self::toInt($summary['schema_count'] ?? 0),
                'digests' => self::toInt($summary['digest_count'] ?? 0),
                'events' => self::toInt($summary['count_star'] ?? 0),
                'errors' => self::toInt($summary['errors'] ?? 0),
                'warnings' => self::toInt($summary['warnings'] ?? 0),
                'total_wait' => self::toFloat($summary['sum_timer_wait'] ?? 0.0),
            ],
            'slow_digests' => array_slice($slowDigests, 0, $limit),
            'index_signals' => array_slice($indexSignals, 0, $limit),
            'warnings' => array_values(array_unique(array_filter($warnings))),
        ];
    }

    public static function renderText(array $payload): string
    {
        $lines = [
            (string)($payload['schedule']['name'] ?? 'Digest report'),
            'Generated at: ' . (string)($payload['generated_at'] ?? '-'),
            'Window: last ' . (string)($payload['window']['days'] ?? '-') . ' day(s), '
                . (string)($payload['window']['min_date'] ?? '-') . ' to ' . (string)($payload['window']['max_date'] ?? '-'),
            '',
            'Summary: ' . (string)($payload['summary']['servers'] ?? 0) . ' server(s), '
                . (string)($payload['summary']['schemas'] ?? 0) . ' schema(s), '
                . (string)($payload['summary']['digests'] ?? 0) . ' digest(s), '
                . self::formatNumber($payload['summary']['events'] ?? 0) . ' event(s).',
            '',
            'Slow digests',
        ];

        foreach (($payload['slow_digests'] ?? []) as $row) {
            $lines[] = '- ' . $row['server'] . ' / ' . $row['schema'] . ' / '
                . self::formatDuration($row['sum_timer_wait']) . ' total / '
                . self::formatNumber($row['count_star']) . ' calls / ' . $row['digest_text'];
        }

        $lines[] = '';
        $lines[] = 'Index signals';
        foreach (($payload['index_signals'] ?? []) as $row) {
            $lines[] = '- ' . $row['server'] . ' / ' . $row['schema'] . ' / score '
                . self::formatNumber($row['score']) . ' / ' . $row['digest_text'];
        }

        if (!empty($payload['warnings'])) {
            $lines[] = '';
            $lines[] = 'Warnings';
            foreach ($payload['warnings'] as $warning) {
                $lines[] = '- ' . (string)$warning;
            }
        }

        return implode("\n", $lines) . "\n";
    }

    public static function renderHtml(array $payload): string
    {
        $html = [];
        $html[] = '<!doctype html><html><body style="font-family:Arial,sans-serif;color:#1f2933">';
        $html[] = '<h2>' . self::h($payload['schedule']['name'] ?? 'Digest report') . '</h2>';
        $html[] = '<p>Generated at ' . self::h($payload['generated_at'] ?? '-') . '. Window: last '
            . self::h($payload['window']['days'] ?? '-') . ' day(s), '
            . self::h($payload['window']['min_date'] ?? '-') . ' to '
            . self::h($payload['window']['max_date'] ?? '-') . '.</p>';
        $html[] = '<p><strong>Summary:</strong> '
            . self::h($payload['summary']['servers'] ?? 0) . ' server(s), '
            . self::h($payload['summary']['schemas'] ?? 0) . ' schema(s), '
            . self::h($payload['summary']['digests'] ?? 0) . ' digest(s), '
            . self::h(self::formatNumber($payload['summary']['events'] ?? 0)) . ' event(s).</p>';
        $html[] = self::tableHtml('Slow digests', ['Server', 'Schema', 'Total wait', 'Calls', 'Digest'], array_map(
            static fn(array $row): array => [$row['server'], $row['schema'], self::formatDuration($row['sum_timer_wait']), self::formatNumber($row['count_star']), $row['digest_text']],
            $payload['slow_digests'] ?? []
        ));
        $html[] = self::tableHtml('Index signals', ['Server', 'Schema', 'No index', 'No good index', 'Digest'], array_map(
            static fn(array $row): array => [$row['server'], $row['schema'], self::formatNumber($row['no_index_used']), self::formatNumber($row['no_good_index_used']), $row['digest_text']],
            $payload['index_signals'] ?? []
        ));

        if (!empty($payload['warnings'])) {
            $html[] = '<h3>Warnings</h3><ul>';
            foreach ($payload['warnings'] as $warning) {
                $html[] = '<li>' . self::h($warning) . '</li>';
            }
            $html[] = '</ul>';
        }

        $html[] = '</body></html>';

        return implode('', $html);
    }

    public static function buildSummarySql(int $days): string
    {
        $days = max(1, min($days, 31));

        return "SELECT MIN(stat.date) AS min_date,
                       MAX(stat.date) AS max_date,
                       COUNT(DISTINCT mdd.id_mysql_server) AS server_count,
                       COUNT(DISTINCT mdd.id_mysql_database) AS schema_count,
                       COUNT(DISTINCT mdd.id_mysql_digest) AS digest_count,
                       SUM(stat.count_star) AS count_star,
                       SUM(stat.sum_errors) AS errors,
                       SUM(stat.sum_warnings) AS warnings,
                       SUM(stat.sum_timer_wait) AS sum_timer_wait
                FROM ts_mysql_digest_stat stat
                INNER JOIN mysql_database__mysql_digest mdd
                    ON mdd.id = stat.id_mysql_database__mysql_digest
                INNER JOIN mysql_server ms ON ms.id = mdd.id_mysql_server
                WHERE stat.date >= DATE_SUB(NOW(), INTERVAL " . $days . " DAY)
                  AND ms.is_deleted = 0";
    }

    public static function buildSlowDigestSql(int $days, int $limit): string
    {
        $days = max(1, min($days, 31));
        $limit = self::normalizeLimit($limit);

        return "SELECT ms.display_name,
                       c.libelle AS client,
                       e.libelle AS environment,
                       mdd.schema_name,
                       MAX(d.digest_text) AS digest_text,
                       SUM(stat.count_star) AS count_star,
                       SUM(stat.sum_timer_wait) AS sum_timer_wait,
                       MAX(stat.max_timer_wait) AS max_timer_wait,
                       SUM(stat.sum_rows_examined) AS rows_examined,
                       MAX(stat.last_seen) AS last_seen,
                       MAX(stat.query_sample_text) AS query_sample_text
                FROM ts_mysql_digest_stat stat
                INNER JOIN mysql_database__mysql_digest mdd
                    ON mdd.id = stat.id_mysql_database__mysql_digest
                INNER JOIN mysql_digest d ON d.id = mdd.id_mysql_digest
                INNER JOIN mysql_server ms ON ms.id = mdd.id_mysql_server
                LEFT JOIN client c ON c.id = ms.id_client
                LEFT JOIN environment e ON e.id = ms.id_environment
                WHERE stat.date >= DATE_SUB(NOW(), INTERVAL " . $days . " DAY)
                  AND ms.is_deleted = 0
                GROUP BY mdd.id, ms.display_name, c.libelle, e.libelle, mdd.schema_name
                ORDER BY SUM(stat.sum_timer_wait) DESC
                LIMIT " . $limit;
    }

    public static function buildIndexSignalSql(int $days, int $limit): string
    {
        $days = max(1, min($days, 31));
        $limit = self::normalizeLimit($limit);

        return "SELECT ms.display_name,
                       c.libelle AS client,
                       e.libelle AS environment,
                       mdd.schema_name,
                       MAX(d.digest_text) AS digest_text,
                       SUM(stat.count_star) AS count_star,
                       SUM(stat.sum_no_index_used) AS no_index_used,
                       SUM(stat.sum_no_good_index_used) AS no_good_index_used,
                       MAX(stat.last_seen) AS last_seen,
                       MAX(stat.query_sample_text) AS query_sample_text
                FROM ts_mysql_digest_stat stat
                INNER JOIN mysql_database__mysql_digest mdd
                    ON mdd.id = stat.id_mysql_database__mysql_digest
                INNER JOIN mysql_digest d ON d.id = mdd.id_mysql_digest
                INNER JOIN mysql_server ms ON ms.id = mdd.id_mysql_server
                LEFT JOIN client c ON c.id = ms.id_client
                LEFT JOIN environment e ON e.id = ms.id_environment
                WHERE stat.date >= DATE_SUB(NOW(), INTERVAL " . $days . " DAY)
                  AND ms.is_deleted = 0
                GROUP BY mdd.id, ms.display_name, c.libelle, e.libelle, mdd.schema_name
                HAVING SUM(stat.sum_no_index_used) + SUM(stat.sum_no_good_index_used) > 0
                ORDER BY SUM(stat.sum_no_index_used) + SUM(stat.sum_no_good_index_used) DESC
                LIMIT " . $limit;
    }

    public static function calculateNextRun(string $frequency, string $timeOfDay, int $dayOfWeek, ?DateTimeImmutable $now = null): string
    {
        $timezone = new DateTimeZone(date_default_timezone_get() ?: 'UTC');
        $now = $now ? $now->setTimezone($timezone) : new DateTimeImmutable('now', $timezone);
        $timeOfDay = self::normalizeTimeOfDay($timeOfDay) ?? '07:00:00';
        [$hour, $minute, $second] = array_map('intval', explode(':', $timeOfDay));

        if ($frequency === 'weekly') {
            $dayOfWeek = max(1, min($dayOfWeek, 7));
            $daysToAdd = ($dayOfWeek - (int)$now->format('N') + 7) % 7;
            $candidate = $now->setTime($hour, $minute, $second)->modify('+' . $daysToAdd . ' days');
            if ($candidate <= $now) {
                $candidate = $candidate->modify('+7 days');
            }

            return $candidate->format('Y-m-d H:i:s');
        }

        $candidate = $now->setTime($hour, $minute, $second);
        if ($candidate <= $now) {
            $candidate = $candidate->modify('+1 day');
        }

        return $candidate->format('Y-m-d H:i:s');
    }

    public static function normalizeLimit($limit): int
    {
        if (!is_numeric($limit)) {
            return self::DEFAULT_LIMIT;
        }

        $value = (int)$limit;
        return $value < 1 ? self::DEFAULT_LIMIT : min($value, self::MAX_LIMIT);
    }

    public static function daysOfWeek(): array
    {
        return [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
    }

    private static function fetchGroups($db, array &$warnings): array
    {
        return self::fetchRows($db, "SELECT id, name, description FROM `group` ORDER BY name, id", $warnings, 'groups');
    }

    private static function fetchSchedules($db, array &$warnings): array
    {
        return self::fetchRows(
            $db,
            "SELECT s.*, g.name AS group_name,
                    (SELECT COUNT(*) FROM user_main u WHERE u.id_group = s.id_group AND u.is_valid = 1 AND u.email <> '') AS recipient_count
             FROM digest_report_schedule s
             INNER JOIN `group` g ON g.id = s.id_group
             ORDER BY s.is_active DESC, COALESCE(s.next_run_at, '9999-12-31 23:59:59'), s.name",
            $warnings,
            'schedules'
        );
    }

    private static function fetchRecipients($db, int $groupId, array &$warnings): array
    {
        $rows = self::fetchRows(
            $db,
            "SELECT email FROM user_main WHERE is_valid = 1 AND id_group = " . $groupId . " AND email <> '' ORDER BY email",
            $warnings,
            'recipients'
        );

        return array_values(array_filter(array_map(static function (array $row): string {
            $email = trim((string)($row['email'] ?? ''));
            return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
        }, $rows)));
    }

    private static function fetchRows($db, string $sql, array &$warnings, string $label): array
    {
        try {
            $res = $db->sql_query($sql);
            $rows = [];
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $rows[] = $row;
            }

            return $rows;
        } catch (\Throwable $exception) {
            $warnings[] = ucfirst($label) . ' unavailable: ' . self::sanitizeMessage($exception->getMessage());
            return [];
        }
    }

    private static function tableExists($db, string $table): bool
    {
        $warnings = [];
        return self::fetchRows($db, 'SHOW TABLES LIKE ' . self::quote($db, $table), $warnings, 'table check') !== [];
    }

    private static function recordDelivery($db, int $scheduleId, string $recipient, string $status, string $error, string $subject, string $textBody): void
    {
        $db->sql_query(
            "INSERT INTO digest_report_delivery
             (`id_digest_report_schedule`, `recipient_email`, `sent_at`, `status`, `error_message`, `subject`, `body_checksum`)
             VALUES (" . $scheduleId . ', ' . self::quote($db, $recipient) . ', NOW(), '
             . self::quote($db, $status) . ', ' . self::quote($db, $error) . ', '
             . self::quote($db, $subject) . ', ' . self::quote($db, hash('sha256', $textBody)) . ')'
        );
    }

    private static function markScheduleRun($db, array $schedule, string $status, string $error): void
    {
        $id = (int)($schedule['id'] ?? 0);
        if ($id <= 0) {
            return;
        }

        $nextRunAt = self::calculateNextRun(
            (string)($schedule['frequency'] ?? 'daily'),
            (string)($schedule['time_of_day'] ?? '07:00:00'),
            (int)($schedule['day_of_week'] ?? 1)
        );

        $db->sql_query(
            "UPDATE digest_report_schedule
             SET last_run_at = NOW(),
                 last_status = " . self::quote($db, $status) . ",
                 last_error = " . self::quote($db, self::cleanText($error, 4096)) . ",
                 next_run_at = " . self::quote($db, $nextRunAt) . ",
                 updated_at = NOW()
             WHERE id = " . $id . "
             LIMIT 1"
        );
    }

    private static function sendMail(?callable $mailer, string $recipient, string $subject, string $htmlBody, string $textBody): bool
    {
        return $mailer !== null
            ? (bool)$mailer($recipient, $subject, $htmlBody, $textBody)
            : DigestReportMailer::send($recipient, $subject, $htmlBody, $textBody);
    }

    private static function buildSubject(array $payload): string
    {
        return '[PmaControl] ' . (string)($payload['schedule']['name'] ?? 'Digest report') . ' - last '
            . (string)($payload['window']['days'] ?? '-') . ' day(s)';
    }

    private static function tableHtml(string $title, array $columns, array $rows): string
    {
        $html = '<h3>' . self::h($title) . '</h3>';
        if (!$rows) {
            return $html . '<p>No data available.</p>';
        }

        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%"><thead><tr>';
        foreach ($columns as $column) {
            $html .= '<th align="left">' . self::h($column) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . self::h($value) . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table>';
    }

    private static function validation(bool $valid, array $errors = [], ?array $payload = null): array
    {
        return ['valid' => $valid, 'errors' => $errors, 'payload' => $payload];
    }

    private static function windowDays($frequency): int
    {
        return $frequency === 'weekly' ? 7 : 1;
    }

    private static function positiveInt($value, bool $allowZero): int
    {
        if (!is_scalar($value)) {
            return 0;
        }

        $value = trim((string)$value);
        if ($value === '' || !ctype_digit($value)) {
            return 0;
        }

        $int = (int)$value;
        return $int > 0 || ($allowZero && $int === 0) ? $int : 0;
    }

    private static function normalizeTimeOfDay($value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        if (!preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', trim((string)$value), $matches)) {
            return null;
        }

        $hour = (int)$matches[1];
        $minute = (int)$matches[2];
        $second = isset($matches[3]) ? (int)$matches[3] : 0;
        if ($hour > 23 || $minute > 59 || $second > 59) {
            return null;
        }

        return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
    }

    private static function cleanText($value, int $maxLength): string
    {
        $text = is_scalar($value) ? trim((string)$value) : '';
        $text = preg_replace('/[\r\n\t]+/', ' ', $text) ?? '';
        $text = preg_replace('/\s+/', ' ', $text) ?? '';

        return mb_substr($text, 0, $maxLength);
    }

    private static function cleanLabel($value): string
    {
        $label = self::cleanText($value, 255);
        return $label === '' ? '-' : $label;
    }

    private static function shortenSql(string $sql): string
    {
        $sql = self::cleanText($sql, 1000);
        return mb_strlen($sql) <= 260 ? $sql : mb_substr($sql, 0, 257) . '...';
    }

    private static function quote($db, $value): string
    {
        if (!method_exists($db, 'sql_real_escape_string')) {
            throw new \InvalidArgumentException('Database adapter must expose sql_real_escape_string().');
        }

        return "'" . $db->sql_real_escape_string((string)$value) . "'";
    }

    private static function toInt($value): int
    {
        return is_numeric($value) ? (int)$value : 0;
    }

    private static function toFloat($value): float
    {
        return is_numeric($value) ? (float)$value : 0.0;
    }

    private static function formatNumber($value): string
    {
        return number_format((float)$value, 0, '.', ' ');
    }

    private static function formatDuration($timerWait): string
    {
        $seconds = self::toFloat($timerWait) / 1000000000000;
        if ($seconds >= 1) {
            return number_format($seconds, 2, '.', ' ') . 's';
        }

        $milliseconds = $seconds * 1000;
        if ($milliseconds >= 1) {
            return number_format($milliseconds, 2, '.', ' ') . 'ms';
        }

        return number_format($milliseconds * 1000, 2, '.', ' ') . 'us';
    }

    private static function h($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function sanitizeMessage(string $message): string
    {
        return self::cleanText($message, 300);
    }

    private static function cronCommand(): string
    {
        $root = defined('APP_DIR') ? APP_DIR : getcwd();

        return 'php ' . rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Webroot' . DIRECTORY_SEPARATOR
            . 'index.php DigestReport run due';
    }
}
