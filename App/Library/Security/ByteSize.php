<?php

declare(strict_types=1);

namespace App\Library\Security;

final class ByteSize
{
    private const UNIT_MULTIPLIERS = [
        '' => 1,
        'k' => 1024,
        'm' => 1048576,
        'g' => 1073741824,
    ];

    public static function parse($raw): ?int
    {
        if (!is_scalar($raw)) {
            return null;
        }

        $value = trim((string) $raw);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^([0-9]+)([KkMmGg]?)$/', $value, $matches) !== 1
            && preg_match('/^([0-9]+\.[0-9]+)([KkMmGg])$/', $value, $matches) !== 1) {
            return null;
        }

        $number = (float) $matches[1];
        $unit = strtolower($matches[2] ?? '');
        $multiplier = self::UNIT_MULTIPLIERS[$unit] ?? null;
        if ($number <= 0 || $multiplier === null) {
            return null;
        }

        $bytes = $number * $multiplier;
        if (!is_finite($bytes) || $bytes < 1 || $bytes > PHP_INT_MAX) {
            return null;
        }

        return (int) floor($bytes);
    }
}
