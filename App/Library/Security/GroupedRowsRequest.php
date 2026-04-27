<?php

declare(strict_types=1);

namespace App\Library\Security;

final class GroupedRowsRequest
{
    public const DEFAULT_MAX_ROWS = 64;

    public static function evaluate(
        array $post,
        array $server,
        array $session,
        string $scope,
        string $group,
        array $rules,
        string $invalidPayloadMessage,
        int $maxRows = self::DEFAULT_MAX_ROWS
    ): array {
        $guard = CsrfGuard::check($post, $server, $session, $scope);
        if (!$guard['allowed']) {
            return self::outcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $rows = self::normalize($post, $group, $rules, $maxRows);
        if ($rows === null) {
            return self::outcome(400, $invalidPayloadMessage);
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'rows' => $rows,
        ];
    }

    public static function normalize(array $post, string $group, array $rules, int $maxRows = self::DEFAULT_MAX_ROWS): ?array
    {
        if ($group === '' || !isset($post[$group]) || !is_array($post[$group]) || $post[$group] === []) {
            return null;
        }

        $source = $post[$group];
        foreach ($source as $field => $values) {
            if (!is_string($field) || !array_key_exists($field, $rules) || !is_array($values)) {
                return null;
            }
        }

        $rowCount = null;
        foreach ($rules as $field => $rule) {
            if (!is_string($field) || !is_array($rule)) {
                return null;
            }

            if (($rule['required'] ?? false) && !array_key_exists($field, $source)) {
                return null;
            }

            if (!array_key_exists($field, $source)) {
                continue;
            }

            $count = count($source[$field]);
            if ($rowCount === null) {
                $rowCount = $count;
                continue;
            }

            if ($count !== $rowCount) {
                return null;
            }
        }

        if ($rowCount === null || $rowCount < 1 || $rowCount > $maxRows) {
            return null;
        }

        $rows = [];
        for ($index = 0; $index < $rowCount; $index++) {
            $row = [];
            foreach ($rules as $field => $rule) {
                $raw = $source[$field][$index] ?? null;
                $value = self::normalizeValue($raw, $rule);
                if ($value === null) {
                    return null;
                }

                $row[$field] = $value;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private static function normalizeValue($raw, array $rule)
    {
        if ($raw === null) {
            if (!array_key_exists('default', $rule)) {
                return null;
            }

            $raw = $rule['default'];
        }

        if (!is_scalar($raw)) {
            return null;
        }

        $text = trim((string) $raw);
        if ($text === '' && array_key_exists('default', $rule)) {
            $text = trim((string) $rule['default']);
        }

        $type = (string) ($rule['type'] ?? 'string');
        if ($type === 'int') {
            if (!ctype_digit($text)) {
                return null;
            }

            $value = (int) $text;
            if (isset($rule['min']) && $value < (int) $rule['min']) {
                return null;
            }
            if (isset($rule['max']) && $value > (int) $rule['max']) {
                return null;
            }

            return $value;
        }

        if ($type !== 'string') {
            return null;
        }

        if (isset($rule['max']) && strlen($text) > (int) $rule['max']) {
            return null;
        }
        if (isset($rule['min']) && strlen($text) < (int) $rule['min']) {
            return null;
        }

        return $text;
    }

    private static function outcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'rows' => null,
        ];
    }
}
