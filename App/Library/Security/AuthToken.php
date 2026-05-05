<?php

declare(strict_types=1);

namespace App\Library\Security;

use RuntimeException;
use Throwable;

final class AuthToken
{
    private const TOKEN_BYTES = 32;
    private const TOKEN_PATTERN = '/\A[a-f0-9]{64}\z/i';

    public static function generateToken(): string
    {
        try {
            return bin2hex(random_bytes(self::TOKEN_BYTES));
        } catch (Throwable $throwable) {
            throw new RuntimeException('Unable to generate authentication token', 0, $throwable);
        }
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', strtolower($token));
    }

    public static function verifyToken(string $token, ?string $storedHash, ?string $expiresAt, ?int $now = null): bool
    {
        $token = trim($token);
        $storedHash = trim((string) $storedHash);

        if (!preg_match(self::TOKEN_PATTERN, $token) || !preg_match(self::TOKEN_PATTERN, $storedHash)) {
            return false;
        }

        if (self::isExpired($expiresAt, $now)) {
            return false;
        }

        return hash_equals(strtolower($storedHash), self::hashToken($token));
    }

    public static function expiresAt(int $ttlSeconds, ?int $now = null): string
    {
        return gmdate('Y-m-d H:i:s', ($now ?? time()) + $ttlSeconds);
    }

    public static function isExpired(?string $expiresAt, ?int $now = null): bool
    {
        $expiresAt = trim((string) $expiresAt);
        if ($expiresAt === '') {
            return true;
        }

        $expiresAtTimestamp = strtotime($expiresAt . ' UTC');
        if ($expiresAtTimestamp === false) {
            return true;
        }

        return $expiresAtTimestamp <= ($now ?? time());
    }

    public static function buildHttpsUrl(array $server, string $path, ?string $trustedOrigin = null): string
    {
        $origin = CsrfGuard::trustedOrigin($trustedOrigin);
        if ($origin === null) {
            $origin = 'https://' . self::normalizeHost($server);
        }

        $origin = preg_replace('#^http://#i', 'https://', $origin);

        return rtrim((string) $origin, '/') . '/' . ltrim($path, '/');
    }

    private static function normalizeHost(array $server): string
    {
        $host = trim((string) ($server['SERVER_NAME'] ?? $server['HTTP_HOST'] ?? 'localhost'));
        $host = explode(',', $host)[0];
        $host = preg_replace('/[^A-Za-z0-9.:\-\[\]]/', '', $host);

        return $host === '' ? 'localhost' : $host;
    }
}
