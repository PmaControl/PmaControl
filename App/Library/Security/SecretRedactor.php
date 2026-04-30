<?php

namespace App\Library\Security;

final class SecretRedactor
{
    public const REDACTED = '[redacted]';
    private const DEBUG_MAX_DEPTH = 4;

    private const SENSITIVE_KEY_PARTS = [
        'password',
        'passwd',
        'pwd',
        'secret',
        'token',
        'apikey',
        'authorization',
        'privatekey',
        'keyauth',
    ];

    public static function redactedValue(): string
    {
        return self::REDACTED;
    }

    public static function debugValue($value, string $label = '')
    {
        return self::redactValue($value, 0, $label);
    }

    public static function jsonPayload(string $payload): string
    {
        if (trim($payload) === '') {
            return '';
        }

        $decoded = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return self::text($payload);
        }

        $encoded = json_encode(self::array($decoded), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded === false ? self::text($payload) : $encoded;
    }

    public static function array(array $payload): array
    {
        return self::redactArray($payload, 0);
    }

    public static function text(string $message): string
    {
        $message = preg_replace(
            '/-----BEGIN [A-Z0-9 ]*PRIVATE KEY-----[\s\S]*?-----END [A-Z0-9 ]*PRIVATE KEY-----/i',
            '[redacted private key]',
            $message
        ) ?? $message;

        $message = preg_replace(
            '/\b(?:ssh-(?:rsa|ed25519|dss)|ecdsa-sha2-nistp(?:256|384|521)|sk-(?:ssh-ed25519|ecdsa-sha2-nistp256)@openssh\.com)\s+[A-Za-z0-9+\/=]{50,}(?:\s+[^\r\n]*)?/i',
            '[redacted public key]',
            $message
        ) ?? $message;

        $message = self::commandLinePasswords($message);

        $message = preg_replace(
            "/\\b(MASTER_PASSWORD\\s*=\\s*)'[^']*'/i",
            "$1'***'",
            $message
        ) ?? $message;

        $message = preg_replace(
            "/\\b(IDENTIFIED\\s+BY\\s*)'[^']*'/i",
            "$1'***'",
            $message
        ) ?? $message;

        $message = preg_replace(
            '/("(?:password|passwd|pwd|secret|token|api[_-]?key|apikey|authorization|private[_-]?key|key_auth)"\s*:\s*)"[^"]*"/i',
            '$1"' . self::REDACTED . '"',
            $message
        ) ?? $message;

        $message = preg_replace(
            '/(^|[\s;&,])((?:password|passwd|pwd|secret|token|api[_-]?key|apikey|private[_-]?key|key_auth)\s*[=:]\s*)[^\s;&,]+/i',
            '$1$2***',
            $message
        ) ?? $message;

        $message = preg_replace(
            '/((?:authorization)\s*:\s*)(?:basic|bearer)?\s*[^\r\n,;]+/i',
            '$1***',
            $message
        ) ?? $message;

        return preg_replace('/([a-z][a-z0-9+.-]*:\/\/[^:\s@]+):[^@\s]+@/i', '$1:***@', $message) ?? $message;
    }

    public static function commandLinePasswords(string $message): string
    {
        $patterns = [
            '/(--password)([= ])([^\s"\'<>|&;]+)/',
            '/(-p)(=)([^\s"\'<>|&;]+)/',
            '/(?<![A-Za-z0-9-])(-p)( )([^\s"\'<>|&;-][^\s"\'<>|&;]*)/',
            '/(?<![A-Za-z0-9-])(-p)([^\s"\'<>|&;= -][^\s"\'<>|&;]*)/',
        ];
        $replacements = [
            '$1$2******',
            '$1$2******',
            '$1$2******',
            '$1******',
        ];

        return (string) preg_replace($patterns, $replacements, $message);
    }

    private static function redactArray(array $payload, int $depth): array
    {
        if ($depth >= self::DEBUG_MAX_DEPTH) {
            return ['...' => self::REDACTED];
        }

        $redacted = [];
        foreach ($payload as $key => $value) {
            $redacted[$key] = self::isSensitiveKey((string)$key)
                ? self::REDACTED
                : self::redactValue($value, $depth + 1, (string)$key);
        }

        return $redacted;
    }

    private static function redactValue($value, int $depth = 0, string $label = '')
    {
        if ($depth >= self::DEBUG_MAX_DEPTH) {
            return self::REDACTED;
        }

        if (self::isSensitiveKey($label) && !is_array($value) && !is_object($value)) {
            return self::REDACTED;
        }

        if (is_array($value)) {
            return self::redactArray($value, $depth);
        }

        if (is_string($value)) {
            return self::text($value);
        }

        if (is_object($value)) {
            if (self::isOpaqueObject($value)) {
                return '[' . get_class($value) . ' redacted]';
            }

            $publicProperties = get_object_vars($value);
            if ($publicProperties === []) {
                return '[' . get_class($value) . ' redacted]';
            }

            return (object) self::redactArray($publicProperties, $depth + 1);
        }

        return $value;
    }

    private static function isOpaqueObject(object $value): bool
    {
        return $value instanceof \PDO
            || $value instanceof \SplObjectStorage
            || (class_exists(\mysqli::class, false) && $value instanceof \mysqli);
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower((string)preg_replace('/[^a-z0-9]/i', '', $key));
        foreach (self::SENSITIVE_KEY_PARTS as $part) {
            if (str_contains($normalized, $part)) {
                return true;
            }
        }

        return false;
    }
}
