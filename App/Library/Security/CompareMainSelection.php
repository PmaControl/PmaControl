<?php

declare(strict_types=1);

namespace App\Library\Security;

final class CompareMainSelection
{
    public const SERVER_ORIGINAL = 'id_mysql_server__original';
    public const SERVER_COMPARE = 'id_mysql_server__compare';
    public const DATABASE_ORIGINAL = 'database__original';
    public const DATABASE_COMPARE = 'database__compare';

    private const FIELDS = [
        self::SERVER_ORIGINAL,
        self::SERVER_COMPARE,
        self::DATABASE_ORIGINAL,
        self::DATABASE_COMPARE,
    ];

    public static function evaluate(array $get, array $server): array
    {
        $method = strtoupper((string)($server['REQUEST_METHOD'] ?? 'GET'));
        if ($method !== 'GET' && $method !== 'HEAD') {
            return self::outcome(405, 'Method Not Allowed', ['Allow' => 'GET, HEAD']);
        }

        $selection = self::normalize($get);
        if ($selection === null) {
            return self::outcome(400, 'Invalid compare selection');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'selection' => $selection,
        ];
    }

    public static function normalize(array $get): ?array
    {
        $selection = [
            self::SERVER_ORIGINAL => null,
            self::SERVER_COMPARE => null,
            self::DATABASE_ORIGINAL => null,
            self::DATABASE_COMPARE => null,
        ];

        if (!isset($get['compare_main'])) {
            return $selection;
        }

        if (!is_array($get['compare_main'])) {
            return null;
        }

        foreach ($get['compare_main'] as $field => $value) {
            if (!is_string($field) || !array_key_exists($field, $selection) || !is_scalar($value)) {
                return null;
            }

            $text = trim((string)$value);
            if ($text === '') {
                continue;
            }

            if ($field === self::SERVER_ORIGINAL || $field === self::SERVER_COMPARE) {
                if (!ctype_digit($text) || (int)$text < 1) {
                    return null;
                }

                $selection[$field] = (int)$text;
                continue;
            }

            if (!Identifier::isDatabaseName($text)) {
                return null;
            }

            $selection[$field] = $text;
        }

        return $selection;
    }

    public static function isComplete(array $selection): bool
    {
        foreach (self::FIELDS as $field) {
            if (!array_key_exists($field, $selection) || $selection[$field] === null) {
                return false;
            }
        }

        return true;
    }

    public static function toRoute(array $selection): string
    {
        $parts = [];
        foreach (self::FIELDS as $field) {
            if (!array_key_exists($field, $selection) || $selection[$field] === null) {
                continue;
            }

            $parts[] = 'compare_main:'.$field.':'.(string)$selection[$field];
        }

        return implode('/', $parts);
    }

    public static function applyToGet(array $selection): void
    {
        $compareMain = [];
        foreach (self::FIELDS as $field) {
            if (array_key_exists($field, $selection) && $selection[$field] !== null) {
                $compareMain[$field] = (string)$selection[$field];
            }
        }

        if ($compareMain === []) {
            unset($_GET['compare_main']);
            return;
        }

        // Legacy Form helpers restore selected values from $_GET.
        $_GET['compare_main'] = $compareMain;
    }

    private static function outcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'selection' => null,
        ];
    }
}
