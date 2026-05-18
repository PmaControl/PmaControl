<?php

declare(strict_types=1);

namespace App\Library;

final class ReplicationFiltersAudit
{
    private const FILTER_KEYS = [
        'Replicate_Do_DB',
        'Replicate_Ignore_DB',
        'Replicate_Do_Table',
        'Replicate_Ignore_Table',
        'Replicate_Wild_Do_Table',
        'Replicate_Wild_Ignore_Table',
        'Replicate_Rewrite_DB',
        'replicate_do_db',
        'replicate_ignore_db',
        'replicate_do_table',
        'replicate_ignore_table',
        'replicate_wild_do_table',
        'replicate_wild_ignore_table',
        'replicate_rewrite_db',
    ];

    /**
     * @return array{active:bool, rows:list<array{channel:string,type:string,pattern:string,severity:string,audit_message:string}>}
     */
    public static function audit(array $statusRow, array $context = []): array
    {
        $rows = [];
        $channel = (string)($statusRow['Connection_name'] ?? $statusRow['Channel_Name'] ?? $context['channel'] ?? '');
        $binlogFormat = strtoupper((string)($context['binlog_format'] ?? $statusRow['Binlog_Format'] ?? ''));

        foreach (self::FILTER_KEYS as $key) {
            if (!array_key_exists($key, $statusRow)) {
                continue;
            }
            $value = trim((string)$statusRow[$key]);
            if ($value === '') {
                continue;
            }
            foreach (self::splitPatterns($value) as $pattern) {
                $severity = $binlogFormat === 'STATEMENT' ? 'critical' : 'warning';
                $rows[] = [
                    'channel' => $channel,
                    'type' => self::normalizeType($key),
                    'pattern' => $pattern,
                    'severity' => $severity,
                    'audit_message' => $severity === 'critical'
                        ? 'Replication filter active with STATEMENT binlog format.'
                        : 'Replication filter active; document the operator intent.',
                ];
            }
        }

        return [
            'active' => $rows !== [],
            'rows' => $rows,
        ];
    }

    /**
     * @return list<string>
     */
    private static function splitPatterns(string $value): array
    {
        $out = [];
        foreach (preg_split('/\s*,\s*/', $value) ?: [] as $part) {
            $part = trim($part);
            if ($part !== '') {
                $out[] = $part;
            }
        }

        return $out;
    }

    private static function normalizeType(string $key): string
    {
        $key = strtolower($key);
        $key = preg_replace('/^replicate_/', '', $key) ?? $key;

        return str_replace('_', ' ', $key);
    }
}
