<?php

namespace App\Library\Kpi;

use Glial\Sgbd\Sgbd;

final class EventLog
{
    private const CONNECTION_SLOT = 'kpi_event_log';

    public static function recordEvent(string $type, string $message, ?int $idMysqlServer = null, ?string $date = null, $db = null): array
    {
        $ownConnection = !is_object($db);
        if ($ownConnection) {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
        }

        $locked = false;
        try {
            $date = self::dateTime($date ?? 'now');
            $locked = self::acquireLock($db, $type, $idMysqlServer);
            $openId = self::fetchOpenEventId($db, $type, $idMysqlServer);
            if ($openId !== null) {
                $db->sql_query(self::buildUpdateOpenEventMessageSql($db, $openId, $message));

                return ['action' => 'existing', 'id' => $openId, 'type' => $type, 'date' => $date];
            }

            $db->sql_query(self::buildInsertEventSql($db, $type, $message, $idMysqlServer, $date));

            return [
                'action' => 'opened',
                'id' => self::fetchOpenEventId($db, $type, $idMysqlServer),
                'type' => $type,
                'date' => $date,
            ];
        } finally {
            if ($locked) {
                self::releaseLock($db, $type, $idMysqlServer);
            }

            if ($ownConnection && is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function closeEvent(string $type, ?int $idMysqlServer = null, ?string $date = null, $db = null): array
    {
        $ownConnection = !is_object($db);
        if ($ownConnection) {
            $db = Sgbd::sql(DB_DEFAULT, self::CONNECTION_SLOT);
        }

        $locked = false;
        try {
            $date = self::dateTime($date ?? 'now');
            $locked = self::acquireLock($db, $type, $idMysqlServer);
            $openId = self::fetchOpenEventId($db, $type, $idMysqlServer);
            if ($openId === null) {
                return ['action' => 'none', 'id' => null, 'type' => $type, 'date' => $date];
            }

            $db->sql_query(self::buildCloseEventSql($db, $openId, $date));

            return ['action' => 'closed', 'id' => $openId, 'type' => $type, 'date' => $date];
        } finally {
            if ($locked) {
                self::releaseLock($db, $type, $idMysqlServer);
            }

            if ($ownConnection && is_object($db) && method_exists($db, 'sql_close')) {
                $db->sql_close();
            }
        }
    }

    public static function buildOpenEventLookupSql($db, string $type, ?int $idMysqlServer = null): string
    {
        return 'SELECT `id` FROM `event_log` '
            .'WHERE `type` = '.self::sqlLiteral($db, $type).' '
            .'AND '.self::mysqlServerCondition($idMysqlServer).' '
            .'AND `date_end` IS NULL '
            .'ORDER BY `id` DESC LIMIT 1;';
    }

    public static function buildInsertEventSql($db, string $type, string $message, ?int $idMysqlServer, string $date): string
    {
        return 'INSERT INTO `event_log` '
            .'(`id_mysql_server`, `id_proxysql_server`, `id_maxscale_server`, `id_docker_host`, `type`, `message`, `date_start`, `date_end`) '
            .'VALUES ('
            .($idMysqlServer === null ? 'NULL' : (int)$idMysqlServer).', '
            .'NULL, NULL, NULL, '
            .self::sqlLiteral($db, $type).', '
            .self::sqlLiteral($db, $message).', '
            .self::sqlLiteral($db, self::dateTime($date)).', '
            .'NULL);';
    }

    public static function buildUpdateOpenEventMessageSql($db, int $idEvent, string $message): string
    {
        return 'UPDATE `event_log` SET `message` = '.self::sqlLiteral($db, $message).' '
            .'WHERE `id` = '.$idEvent.' AND `date_end` IS NULL;';
    }

    public static function buildCloseEventSql($db, int $idEvent, string $date): string
    {
        return 'UPDATE `event_log` SET `date_end` = '.self::sqlLiteral($db, self::dateTime($date)).' '
            .'WHERE `id` = '.$idEvent.' AND `date_end` IS NULL;';
    }

    public static function buildAcquireLockSql($db, string $type, ?int $idMysqlServer = null): string
    {
        return 'SELECT GET_LOCK('.self::sqlLiteral($db, self::lockName($type, $idMysqlServer)).', 2) AS `locked`;';
    }

    public static function buildReleaseLockSql($db, string $type, ?int $idMysqlServer = null): string
    {
        return 'SELECT RELEASE_LOCK('.self::sqlLiteral($db, self::lockName($type, $idMysqlServer)).') AS `released`;';
    }

    private static function acquireLock($db, string $type, ?int $idMysqlServer): bool
    {
        if ((int)self::fetchScalar($db, self::buildAcquireLockSql($db, $type, $idMysqlServer)) !== 1) {
            throw new \RuntimeException('Unable to acquire KPI event lock for '.$type.'.');
        }

        return true;
    }

    private static function releaseLock($db, string $type, ?int $idMysqlServer): void
    {
        $db->sql_query(self::buildReleaseLockSql($db, $type, $idMysqlServer));
    }

    private static function fetchScalar($db, string $sql)
    {
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_NUM);
        if (!is_array($row)) {
            return null;
        }

        return array_key_exists(0, $row) ? $row[0] : reset($row);
    }

    private static function fetchOpenEventId($db, string $type, ?int $idMysqlServer): ?int
    {
        $res = $db->sql_query(self::buildOpenEventLookupSql($db, $type, $idMysqlServer));
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
        if (!is_array($row) || empty($row['id'])) {
            return null;
        }

        return (int)$row['id'];
    }

    private static function mysqlServerCondition(?int $idMysqlServer): string
    {
        return $idMysqlServer === null ? '`id_mysql_server` IS NULL' : '`id_mysql_server` = '.(int)$idMysqlServer;
    }

    private static function lockName(string $type, ?int $idMysqlServer): string
    {
        return 'pmacontrol_kpi_event_'.substr(sha1($type.':'.($idMysqlServer ?? 'global')), 0, 40);
    }

    private static function dateTime(string $value): string
    {
        $date = new \DateTimeImmutable(trim($value) !== '' ? $value : 'now');

        return $date->format('Y-m-d H:i:s');
    }

    private static function sqlLiteral($db, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value)) {
            return (string)$value;
        }

        if (is_float($value)) {
            return number_format($value, 6, '.', '');
        }

        if (!method_exists($db, 'sql_real_escape_string')) {
            throw new \InvalidArgumentException('Database adapter must expose sql_real_escape_string().');
        }

        return "'".$db->sql_real_escape_string((string)$value)."'";
    }
}
