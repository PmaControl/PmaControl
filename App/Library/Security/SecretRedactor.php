<?php

namespace App\Library\Security;

final class SecretRedactor
{
    public const REDACTED = '[redacted]';

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
        $redacted = [];
        foreach ($payload as $key => $value) {
            $redacted[$key] = self::isSensitiveKey((string)$key)
                ? self::REDACTED
                : self::redactValue($value);
        }

        return $redacted;
    }

    public static function text(string $message): string
    {
        $message = preg_replace(
            '/("(?:password|passwd|pwd|secret|token|api[_-]?key|apikey|authorization|private[_-]?key|key_auth)"\s*:\s*)"[^"]*"/i',
            '$1"' . self::REDACTED . '"',
            $message
        ) ?? $message;

        $message = preg_replace(
            '/((?:password|passwd|pwd|secret|token|api[_-]?key|apikey|private[_-]?key|key_auth)\s*[=:]\s*)[^\s;&,]+/i',
            '$1***',
            $message
        ) ?? $message;

        $message = preg_replace(
            '/((?:authorization)\s*:\s*)(?:basic|bearer)?\s*[^\r\n,;]+/i',
            '$1***',
            $message
        ) ?? $message;

        return preg_replace('/([a-z][a-z0-9+.-]*:\/\/[^:\s@]+):[^@\s]+@/i', '$1:***@', $message) ?? $message;
    }

    private static function redactValue($value)
    {
        if (is_array($value)) {
            return self::array($value);
        }

        if (is_string($value)) {
            return self::text($value);
        }

        return $value;
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
