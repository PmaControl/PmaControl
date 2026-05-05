<?php

declare(strict_types=1);

namespace App\Library\Llm;

use App\Library\Security\Identifier;

final class SqlAssistant
{
    public const DEFAULT_ENDPOINT = 'http://127.0.0.1:11434/api/generate';
    public const DEFAULT_MODEL = 'llama3';
    public const DEFAULT_TIMEOUT_SECONDS = 15;
    public const DEFAULT_MAX_INPUT_BYTES = 20000;

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public static function normalizeConfig(array $config): array
    {
        $llm = isset($config['llm']) && is_array($config['llm']) ? $config['llm'] : $config;

        return [
            'enabled' => (bool) ($llm['enabled'] ?? false),
            'endpoint' => self::normalizeString($llm['endpoint'] ?? self::DEFAULT_ENDPOINT, self::DEFAULT_ENDPOINT),
            'model' => self::normalizeString($llm['model'] ?? self::DEFAULT_MODEL, self::DEFAULT_MODEL),
            'timeout_seconds' => self::normalizeInteger(
                $llm['timeout_seconds'] ?? self::DEFAULT_TIMEOUT_SECONDS,
                self::DEFAULT_TIMEOUT_SECONDS,
                1,
                120
            ),
            'max_input_bytes' => self::normalizeInteger(
                $llm['max_input_bytes'] ?? self::DEFAULT_MAX_INPUT_BYTES,
                self::DEFAULT_MAX_INPUT_BYTES,
                1024,
                200000
            ),
            'allow_remote_endpoint' => (bool) ($llm['allow_remote_endpoint'] ?? false),
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @return array{allowed: bool, error: string}
     */
    public static function validateRuntimeConfig(array $config): array
    {
        $config = self::normalizeConfig($config);
        if (!$config['enabled']) {
            return ['allowed' => false, 'error' => 'LLM assistant disabled'];
        }

        return self::validateEndpoint(
            (string) $config['endpoint'],
            (bool) $config['allow_remote_endpoint']
        );
    }

    /**
     * @return array{allowed: bool, error: string}
     */
    public static function validateEndpoint(string $endpoint, bool $allowRemoteEndpoint = false): array
    {
        $parts = parse_url($endpoint);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return ['allowed' => false, 'error' => 'Invalid LLM endpoint URL'];
        }

        if (!in_array($parts['scheme'], ['http', 'https'], true)) {
            return ['allowed' => false, 'error' => 'Unsupported LLM endpoint scheme'];
        }

        if (!$allowRemoteEndpoint && !self::isLocalHost((string) $parts['host'])) {
            return ['allowed' => false, 'error' => 'Remote LLM endpoint is disabled'];
        }

        return ['allowed' => true, 'error' => ''];
    }

    public static function normalizeInput($input, int $maxBytes = self::DEFAULT_MAX_INPUT_BYTES): ?string
    {
        if (!is_scalar($input)) {
            return null;
        }

        $value = trim((string) $input);
        if ($value === '' || strlen($value) > $maxBytes) {
            return null;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public static function buildPayload(string $input, array $config): array
    {
        $config = self::normalizeConfig($config);

        return [
            'model' => $config['model'],
            'prompt' => $input,
            'system' => self::buildSystemPrompt(),
            'stream' => false,
        ];
    }

    public static function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a senior MySQL and MariaDB performance expert.

Your only goal is to detect missing indexes caused by full table scans.

Rules:
- Never suggest existing indexes.
- Never suggest speculative indexes.
- Never suggest schema changes other than adding an index.
- Never rewrite or optimize queries.
- Only suggest indexes that eliminate a demonstrated full table scan.
- Ask for missing input when SHOW CREATE TABLE or EXPLAIN is absent.

Return only valid JSON in one of these formats:
{"status":"OK","indexes":[{"table":"table_name","columns":["col1","col2"]}]}
{"status":"ERROR","missing_input":["SHOW CREATE TABLE","EXPLAIN"]}
PROMPT;
    }

    public static function extractModelResponse(string $httpResponse): ?string
    {
        $json = json_decode($httpResponse, true);
        if (!is_array($json)) {
            return null;
        }

        $response = $json['response'] ?? null;
        return is_string($response) && trim($response) !== '' ? $response : null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function parseResponse(string $raw): array
    {
        $clean = self::extractJsonText($raw);
        $data = json_decode($clean, true);
        if (!is_array($data) || !isset($data['status'])) {
            return [
                'status' => 'ERROR',
                'error' => 'Invalid JSON returned by LLM',
                'raw' => $raw,
            ];
        }

        $status = strtoupper((string) $data['status']);
        if ($status !== 'OK') {
            return [
                'status' => 'ERROR',
                'error' => (string) ($data['error'] ?? ''),
                'missing_input' => self::normalizeStringList($data['missing_input'] ?? []),
            ];
        }

        return [
            'status' => 'OK',
            'indexes' => self::normalizeIndexSuggestions($data['indexes'] ?? []),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $indexes
     * @return array<int, string>
     */
    public static function buildAlterStatements(array $indexes): array
    {
        $statements = [];
        foreach (self::normalizeIndexSuggestions($indexes) as $index) {
            $quotedColumns = array_map(
                static fn (string $column): string => Identifier::quoteSqlIdentifier($column),
                $index['columns']
            );

            $indexName = self::buildIndexName($index['table'], $index['columns']);
            $statements[] = 'ALTER TABLE '
                . Identifier::quoteSqlIdentifier($index['table'])
                . ' ADD INDEX '
                . Identifier::quoteSqlIdentifier($indexName)
                . ' ('
                . implode(', ', $quotedColumns)
                . ');';
        }

        return $statements;
    }

    /**
     * @param mixed $value
     */
    private static function normalizeString($value, string $fallback): string
    {
        if (!is_scalar($value)) {
            return $fallback;
        }

        $string = trim((string) $value);
        return $string !== '' ? $string : $fallback;
    }

    /**
     * @param mixed $value
     */
    private static function normalizeInteger($value, int $default, int $min, int $max): int
    {
        $integer = is_numeric($value) ? (int) $value : $default;
        return max($min, min($max, $integer));
    }

    private static function isLocalHost(string $host): bool
    {
        return in_array(strtolower(trim($host, '[]')), ['localhost', '127.0.0.1', '::1'], true);
    }

    private static function extractJsonText(string $raw): string
    {
        $clean = trim($raw);
        if (preg_match('/\A```(?:json)?\s*(.*?)\s*```\z/is', $clean, $matches) === 1) {
            return trim($matches[1]);
        }

        $clean = preg_replace('/\Ajson\s*/i', '', $clean) ?? $clean;
        $start = strpos($clean, '{');
        $end = strrpos($clean, '}');
        if ($start !== false && $end !== false && $end >= $start) {
            return substr($clean, $start, $end - $start + 1);
        }

        return $clean;
    }

    /**
     * @param mixed $indexes
     * @return array<int, array{table: string, columns: array<int, string>}>
     */
    private static function normalizeIndexSuggestions($indexes): array
    {
        if (!is_array($indexes)) {
            return [];
        }

        $normalized = [];
        foreach ($indexes as $index) {
            if (!is_array($index)) {
                continue;
            }

            $table = (string) ($index['table'] ?? '');
            if (!Identifier::isSqlIdentifier($table)) {
                continue;
            }

            $columns = self::normalizeIdentifierList($index['columns'] ?? []);
            if ($columns === []) {
                continue;
            }

            $normalized[] = [
                'table' => $table,
                'columns' => $columns,
            ];
        }

        return $normalized;
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private static function normalizeIdentifierList($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $identifiers = [];
        foreach ($value as $identifier) {
            if (!is_scalar($identifier)) {
                return [];
            }

            $identifier = (string) $identifier;
            if (!Identifier::isSqlIdentifier($identifier)) {
                return [];
            }

            $identifiers[] = $identifier;
        }

        return array_values(array_unique($identifiers));
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private static function normalizeStringList($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $strings = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $strings[] = trim((string) $item);
            }
        }

        return array_values(array_filter($strings, static fn (string $item): bool => $item !== ''));
    }

    /**
     * @param array<int, string> $columns
     */
    private static function buildIndexName(string $table, array $columns): string
    {
        $name = 'idx_' . $table . '_' . implode('_', $columns);
        $name = preg_replace('/[^A-Za-z0-9_]+/', '_', $name) ?? 'idx_llm_suggestion';
        $name = trim($name, '_');

        if ($name === '') {
            return 'idx_llm_suggestion';
        }

        return substr($name, 0, 64);
    }
}
