<?php

declare(strict_types=1);

namespace App\Library\Security;

final class SignedJsonCache
{
    private const MAC_PREFIX = 'sha256:';
    private const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public static function read(string $path, string $scope, int $version, ?string $key = null): ?array
    {
        $secret = self::resolveKey($key);
        if ($secret === null || !is_file($path) || !is_readable($path)) {
            return null;
        }

        $contents = self::readLocked($path);
        if ($contents === null || $contents === '') {
            return null;
        }

        try {
            $envelope = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            unset($exception);
            return null;
        }

        if (!is_array($envelope)
            || ($envelope['v'] ?? null) !== $version
            || ($envelope['scope'] ?? null) !== $scope
            || !isset($envelope['payload'])
            || !is_array($envelope['payload'])
            || !self::isValidMac($envelope['mac'] ?? null)
        ) {
            return null;
        }

        $payloadJson = self::encodePayload($envelope['payload']);
        if ($payloadJson === null) {
            return null;
        }

        $expectedMac = self::computeMac($version, $scope, $payloadJson, $secret);
        if (!hash_equals($expectedMac, $envelope['mac'])) {
            return null;
        }

        return $envelope['payload'];
    }

    public static function write(string $path, string $scope, int $version, array $payload, ?string $key = null): void
    {
        $secret = self::resolveKey($key);
        if ($secret === null) {
            throw new \RuntimeException('Signed JSON cache requires CRYPT_KEY');
        }

        if (!self::containsJsonValuesOnly($payload)) {
            throw new \InvalidArgumentException('Signed JSON cache payload must contain JSON values only');
        }

        $payloadJson = self::encodePayload($payload);
        if ($payloadJson === null) {
            throw new \InvalidArgumentException('Signed JSON cache payload cannot be encoded');
        }

        $directory = dirname($path);
        self::ensureDirectory($directory);

        $envelope = [
            'v' => $version,
            'scope' => $scope,
            'payload' => $payload,
            'mac' => self::computeMac($version, $scope, $payloadJson, $secret),
        ];

        $json = json_encode($envelope, self::JSON_OPTIONS | JSON_THROW_ON_ERROR);
        $tmpPath = $path.'.tmp.'.bin2hex(random_bytes(4));

        try {
            if (file_put_contents($tmpPath, $json, LOCK_EX) === false) {
                throw new \RuntimeException('Unable to write signed JSON cache temporary file');
            }

            if (!chmod($tmpPath, 0600)) {
                throw new \RuntimeException('Unable to chmod signed JSON cache temporary file');
            }

            if (!rename($tmpPath, $path)) {
                throw new \RuntimeException('Unable to move signed JSON cache into place');
            }
        } finally {
            if (is_file($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }

    private static function readLocked(string $path): ?string
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return null;
        }

        $locked = false;
        try {
            if (flock($handle, LOCK_SH) === false) {
                return null;
            }

            $locked = true;
            $contents = stream_get_contents($handle);
        } finally {
            if ($locked) {
                flock($handle, LOCK_UN);
            }
            fclose($handle);
        }

        return $contents === false ? null : $contents;
    }

    private static function ensureDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create signed JSON cache directory');
        }

        chmod($directory, 0700);
    }

    private static function resolveKey(?string $key): ?string
    {
        if ($key !== null) {
            return $key === '' ? null : $key;
        }

        if (!defined('CRYPT_KEY')) {
            return null;
        }

        $constant = constant('CRYPT_KEY');

        return is_string($constant) && $constant !== '' ? $constant : null;
    }

    private static function computeMac(int $version, string $scope, string $payloadJson, string $key): string
    {
        return self::MAC_PREFIX.hash_hmac('sha256', $version.'|'.$scope.'|'.$payloadJson, $key);
    }

    private static function isValidMac($mac): bool
    {
        return is_string($mac) && preg_match('/^sha256:[a-f0-9]{64}$/', $mac) === 1;
    }

    private static function encodePayload(array $payload): ?string
    {
        try {
            return json_encode($payload, self::JSON_OPTIONS | JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            unset($exception);
            return null;
        }
    }

    private static function containsJsonValuesOnly($value): bool
    {
        if ($value === null || is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            return true;
        }

        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $child) {
            if (!self::containsJsonValuesOnly($child)) {
                return false;
            }
        }

        return true;
    }
}
