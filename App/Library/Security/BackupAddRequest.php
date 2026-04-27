<?php

declare(strict_types=1);

namespace App\Library\Security;

final class BackupAddRequest
{
    private const BACKUP_NAME_PATTERN = '/^[A-Za-z0-9 _.-]{1,128}$/';
    private const CRON_FIELD_PATTERN = '/^[0-9*,\/-]{1,32}$/';

    public static function evaluate(array $post, array $server, array $session, string $scope): array
    {
        $guard = CsrfGuard::check($post, $server, $session, $scope);
        if (!$guard['allowed']) {
            return self::outcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $payload = self::normalize($post);
        if ($payload === null) {
            return self::outcome(400, 'Invalid backup add payload');
        }

        return self::outcome(200, '', [], $payload);
    }

    public static function normalize(array $post): ?array
    {
        $backupMain = GroupedFormRequest::normalize($post, 'backup_main', self::backupMainRules());
        $crontab = GroupedFormRequest::normalize($post, 'crontab', self::crontabRules());

        if ($backupMain === null || $crontab === null) {
            return null;
        }

        return [
            'backup_main' => [
                'name' => $backupMain['name'],
                'id_mysql_server' => $backupMain['id_mysql_server'],
                'database' => $backupMain['database'] === [] ? '0' : PositiveIntegerSelection::toCsv($backupMain['database']),
                'id_backup_storage_area' => $backupMain['id_backup_storage_area'],
                'id_backup_type' => $backupMain['id_backup_type'],
            ],
            'crontab' => $crontab,
        ];
    }

    public static function buildCrontabRecord(array $payload): array
    {
        return [
            'crontab' => array_replace($payload['crontab'], [
                'command' => '',
                'comment' => '',
            ]),
        ];
    }

    public static function buildBackupMainRecord(array $payload, int $idCrontab, string $dateInserted): array
    {
        return [
            'backup_main' => array_replace($payload['backup_main'], [
                'id_crontab' => $idCrontab,
                'is_active' => 1,
                'date_inserted' => $dateInserted,
            ]),
        ];
    }

    public static function attachCommand(array $record, int $idBackupMain, string $command): array
    {
        $record['backup_main']['id'] = $idBackupMain;
        $record['backup_main']['command'] = $command;

        return $record;
    }

    private static function backupMainRules(): array
    {
        return [
            'name' => ['type' => 'string', 'required' => true, 'min' => 1, 'max' => 128, 'pattern' => self::BACKUP_NAME_PATTERN],
            'id_mysql_server' => ['type' => 'int', 'required' => true, 'min' => 1],
            'database' => [
                'type' => 'list',
                'required' => false,
                'default' => [],
                'max_items' => 512,
                'item_type' => 'int',
                'item_min' => 1,
            ],
            'id_backup_storage_area' => ['type' => 'int', 'required' => true, 'min' => 1],
            'id_backup_type' => ['type' => 'int', 'required' => true, 'min' => 1],
        ];
    }

    private static function crontabRules(): array
    {
        $cronField = ['type' => 'string', 'required' => true, 'min' => 1, 'max' => 32, 'pattern' => self::CRON_FIELD_PATTERN];

        return [
            'minute' => $cronField,
            'hour' => $cronField,
            'day_of_month' => $cronField,
            'month' => $cronField,
            'day_of_week' => $cronField,
        ];
    }

    private static function outcome(
        int $statusCode,
        string $message,
        array $headers = [],
        ?array $payload = null
    ): array {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'backup' => $payload,
        ];
    }
}
