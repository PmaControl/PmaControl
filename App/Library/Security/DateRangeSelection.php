<?php

declare(strict_types=1);

namespace App\Library\Security;

final class DateRangeSelection
{
    public const DATE_MIN = 'date_min';
    public const DATE_MAX = 'date_max';

    public static function normalizePositiveInt($value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '' || !ctype_digit($trimmed)) {
            return null;
        }

        $id = (int)$trimmed;
        return $id > 0 ? $id : null;
    }

    public static function normalizeDate($value): ?string
    {
        if (!is_string($value) && !is_int($value)) {
            return null;
        }

        $date = trim((string)$value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            return null;
        }

        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = \DateTimeImmutable::getLastErrors();
        if ($parsed === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        return $parsed->format('Y-m-d') === $date ? $date : null;
    }

    public static function normalizeSelection(
        $selection,
        string $dateMinField = self::DATE_MIN,
        string $dateMaxField = self::DATE_MAX
    ): ?array {
        if (!is_array($selection)) {
            return null;
        }

        $unexpectedFields = array_diff(array_keys($selection), [$dateMinField, $dateMaxField]);
        if ($unexpectedFields !== []) {
            return null;
        }

        $dateMin = self::normalizeDate($selection[$dateMinField] ?? null);
        $dateMax = self::normalizeDate($selection[$dateMaxField] ?? null);
        if ($dateMin === null || $dateMax === null || $dateMin > $dateMax) {
            return null;
        }

        return [
            $dateMinField => $dateMin,
            $dateMaxField => $dateMax,
        ];
    }
}
