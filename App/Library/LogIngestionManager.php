<?php

namespace App\Library;

use \Glial\Sgbd\Sgbd;

class LogIngestionManager
{
    const DEFAULT_TAIL_LINES = 600;

    public static function collectForServer($id_mysql_server)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id, id_mysql_server, log_path, last_inode, last_mtime, is_active
                FROM ssh_log_watch
                WHERE id_mysql_server = ".(int) $id_mysql_server." AND is_active = 1";
        $res = $db->sql_query($sql);

        while ($watch = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            self::collectWatch($watch);
        }
    }

    public static function collectAll()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id, id_mysql_server, log_path, last_inode, last_mtime, is_active
                FROM ssh_log_watch
                WHERE is_active = 1";
        $res = $db->sql_query($sql);

        while ($watch = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            self::collectWatch($watch);
        }
    }

    public static function collectWatch($watch)
    {
        $ssh = Ssh::ssh((int) $watch['id_mysql_server']);

        if (empty($ssh) || $ssh === false) {
            return;
        }

        $path = escapeshellarg($watch['log_path']);
        $stat = trim($ssh->exec("stat -c '%i|%Y' ".$path." 2>/dev/null"));

        if (empty($stat) || strpos($stat, '|') === false) {
            $ssh->disconnect();
            return;
        }

        list($inode, $mtime) = explode('|', $stat);

        $has_change = ((string) $watch['last_inode'] !== (string) $inode)
            || ((string) $watch['last_mtime'] !== (string) $mtime);

        if (! $has_change) {
            $ssh->disconnect();
            return;
        }

        $raw_lines = trim($ssh->exec("tail -n ".self::DEFAULT_TAIL_LINES." ".$path." 2>/dev/null"));
        $ssh->disconnect();

        if (empty($raw_lines)) {
            self::updateWatch((int) $watch['id'], $inode, $mtime);
            return;
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw_lines);

        foreach ($lines as $line) {
            $parsed = self::parseLine($line);
            if ($parsed === false) {
                continue;
            }

            self::storeEvent((int) $watch['id'], (int) $watch['id_mysql_server'], $parsed);
        }

        self::updateWatch((int) $watch['id'], $inode, $mtime);
    }

    public static function parseLine($line)
    {
        $line = trim((string) $line);

        if ($line === '') {
            return false;
        }

        $event_date = date('Y-m-d H:i:s');
        $level = 'INFO';

        if (preg_match('/^([A-Z][a-z]{2}\s+\d{1,2}\s+\d{2}:\d{2}:\d{2})\s+/', $line, $matches)) {
            $candidate = date('Y')." ".$matches[1];
            $timestamp = strtotime($candidate);
            if ($timestamp !== false) {
                $event_date = date('Y-m-d H:i:s', $timestamp);
            }
        }

        if (preg_match('/\b(DEBUG|INFO|NOTICE|WARN|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)\b/i', $line, $matches)) {
            $candidate = strtoupper($matches[1]);
            if ($candidate === 'WARN') {
                $candidate = 'WARNING';
            }
            $level = $candidate;
        }

        $normalized = preg_replace('/\d+/', '?', strtolower($line));

        return array(
            'event_date' => $event_date,
            'level' => $level,
            'message' => $line,
            'message_hash' => sha1($normalized),
        );
    }

    private static function storeEvent($id_watch, $id_mysql_server, $parsed)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $event_date = $db->sql_real_escape_string($parsed['event_date']);
        $bucket_minute = $db->sql_real_escape_string(substr($parsed['event_date'], 0, 16).':00');
        $level = $db->sql_real_escape_string($parsed['level']);
        $message = $db->sql_real_escape_string(substr($parsed['message'], 0, 4096));
        $hash = $db->sql_real_escape_string($parsed['message_hash']);

        $sql = "INSERT INTO ssh_log_event (`id_ssh_log_watch`,`id_mysql_server`,`event_date`,`bucket_minute`,`level`,`message`,`message_hash`,`count_seen`,`date_created`,`date_updated`)\n                VALUES (".(int) $id_watch.",".(int) $id_mysql_server.",'".$event_date."','".$bucket_minute."','".$level."','".$message."','".$hash."',1,NOW(),NOW())\n                ON DUPLICATE KEY UPDATE count_seen = count_seen + 1, event_date = VALUES(event_date), date_updated = NOW()";

        $db->sql_query($sql);
    }

    private static function updateWatch($id_watch, $inode, $mtime)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "UPDATE ssh_log_watch\n                SET last_inode='".$db->sql_real_escape_string((string) $inode)."',\n                    last_mtime='".$db->sql_real_escape_string((string) $mtime)."',\n                    date_last_collected = NOW()\n                WHERE id = ".(int) $id_watch;

        $db->sql_query($sql);
    }
}
