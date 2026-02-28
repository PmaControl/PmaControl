<?php

namespace App\Library;

class LogSmartStorage
{
    public static function fingerprint(string $source, string $message): string
    {
        $normalized = self::normalizeMessage($message);

        return sha1($source.'|'.$normalized);
    }

    public static function normalizeMessage(string $message): string
    {
        $normalized = strtolower(trim($message));
        $normalized = preg_replace('/\b\d+\b/', '<num>', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return (string) $normalized;
    }

    public static function detectLevel(string $message): string
    {
        $lower = strtolower($message);

        if (strpos($lower, 'critical') !== false || strpos($lower, 'panic') !== false || strpos($lower, 'fatal') !== false) {
            return 'critical';
        }

        if (strpos($lower, 'error') !== false || strpos($lower, 'failed') !== false || strpos($lower, 'exception') !== false) {
            return 'error';
        }

        if (strpos($lower, 'warn') !== false) {
            return 'warning';
        }

        return 'info';
    }

    public static function detectCategory(string $message): string
    {
        $lower = strtolower($message);

        if (strpos($lower, 'replication') !== false || strpos($lower, 'slave') !== false || strpos($lower, 'galera') !== false) {
            return 'replication';
        }

        if (strpos($lower, 'authentication') !== false || strpos($lower, 'access denied') !== false || strpos($lower, 'permission') !== false) {
            return 'security';
        }

        if (strpos($lower, 'disk') !== false || strpos($lower, 'io ') !== false || strpos($lower, 'latency') !== false) {
            return 'infrastructure';
        }

        if (strpos($lower, 'slow query') !== false || strpos($lower, 'deadlock') !== false || strpos($lower, 'lock wait') !== false) {
            return 'query';
        }

        return 'other';
    }
}
