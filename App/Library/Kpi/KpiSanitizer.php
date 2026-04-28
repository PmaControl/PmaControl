<?php

namespace App\Library\Kpi;

final class KpiSanitizer
{
    public static function errorMessage($value, int $maxLength = 512): ?string
    {
        if ($value === null) {
            return null;
        }

        $message = trim((string)$value);
        if ($message === '') {
            return null;
        }

        $message = preg_replace('/((?:password|passwd|pwd|secret|token)\s*[=:]\s*)[^\s;&]+/i', '$1***', $message) ?? $message;
        $message = preg_replace('/([a-z][a-z0-9+.-]*:\/\/[^:\s@]+):[^@\s]+@/i', '$1:***@', $message) ?? $message;

        return substr($message, 0, $maxLength);
    }
}
