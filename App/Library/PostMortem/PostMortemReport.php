<?php

namespace App\Library\PostMortem;

use App\Library\Kpi\KpiServerDrilldown;

final class PostMortemReport
{
    public const DEFAULT_RADIUS_SECONDS = 86400;
    public const MAX_RADIUS_SECONDS = 604800;

    /**
     * @param callable|null $serverPayloadFactory Signature: fn(int $serverId, array $options): array
     */
    public static function buildPayload(int $serverId, array $query = [], ?callable $serverPayloadFactory = null): array
    {
        $window = self::normalizeWindow($query);
        $payload = self::emptyPayload($serverId, $window);

        if ($serverId <= 0) {
            $payload['warnings'][] = 'Select a server to build a post-mortem report.';
            return $payload;
        }

        try {
            $kpiOptions = [
                'now' => $window['end'],
                'window_start' => $window['start'],
            ];
            $kpiPayload = $serverPayloadFactory !== null
                ? $serverPayloadFactory($serverId, $kpiOptions)
                : KpiServerDrilldown::buildPayload($serverId, $kpiOptions);
        } catch (\Throwable $e) {
            $payload['degraded'] = true;
            $payload['warnings'][] = self::sanitizeMessage($e->getMessage());
            return $payload;
        }

        $payload['server'] = $kpiPayload['server'] ?? null;
        $payload['status'] = $kpiPayload['status'] ?? $payload['status'];
        $payload['last_success_at'] = $kpiPayload['last_success_at'] ?? null;
        $payload['last_error'] = $kpiPayload['last_error'] ?? null;
        $payload['timeline'] = $kpiPayload['timeline'] ?? $payload['timeline'];
        $payload['attempts'] = array_slice($kpiPayload['attempts'] ?? [], 0, 50);
        $payload['sparklines'] = $kpiPayload['sparklines'] ?? [];
        $payload['variable_diff'] = $kpiPayload['variable_diff'] ?? $payload['variable_diff'];
        $payload['generated_at'] = $kpiPayload['generated_at'] ?? self::formatDateTime(new \DateTimeImmutable('now'));
        $payload['degraded'] = !empty($kpiPayload['degraded']);
        $payload['warnings'] = array_values(array_filter(array_merge(
            $payload['warnings'],
            $kpiPayload['warnings'] ?? []
        )));

        if (empty($payload['server'])) {
            $payload['degraded'] = true;
            $payload['warnings'][] = 'Server not found.';
        }

        return $payload;
    }

    public static function normalizeServerId(array $param): int
    {
        $value = $param[0] ?? null;
        if (!is_scalar($value) || !preg_match('/^[1-9][0-9]*$/', (string)$value)) {
            return 0;
        }

        return (int)$value;
    }

    public static function normalizeWindow(array $query): array
    {
        $from = self::dateTimeFromValue($query['from'] ?? null);
        $to = self::dateTimeFromValue($query['to'] ?? null);

        if ($from !== null && $to !== null && $from <= $to) {
            if (($to->getTimestamp() - $from->getTimestamp()) > self::MAX_RADIUS_SECONDS) {
                $from = $to->modify('-'.self::MAX_RADIUS_SECONDS.' seconds');
            }

            return self::windowPayload($from, $to, null);
        }

        $around = self::dateTimeFromValue($query['around'] ?? null) ?? new \DateTimeImmutable('now');
        $radiusSeconds = self::normalizeRadiusSeconds($query['radius'] ?? null);
        $start = $around->modify('-'.$radiusSeconds.' seconds');

        return self::windowPayload($start, $around, $radiusSeconds);
    }

    public static function normalizeRadiusSeconds($value): int
    {
        if (is_numeric($value)) {
            $seconds = (int)$value;
        } else {
            $raw = strtolower(trim((string)$value));
            if (!preg_match('/^([1-9][0-9]*)(m|h|d)$/', $raw, $match)) {
                return self::DEFAULT_RADIUS_SECONDS;
            }

            $seconds = (int)$match[1];
            if ($match[2] === 'm') {
                $seconds *= 60;
            } elseif ($match[2] === 'h') {
                $seconds *= 3600;
            } else {
                $seconds *= 86400;
            }
        }

        if ($seconds < 60) {
            return 60;
        }

        return min($seconds, self::MAX_RADIUS_SECONDS);
    }

    private static function emptyPayload(int $serverId, array $window): array
    {
        return [
            'server_id' => $serverId,
            'server' => null,
            'window' => $window,
            'status' => ['label' => 'UNKNOWN', 'class' => 'unknown', 'since' => null, 'value' => null],
            'last_success_at' => null,
            'last_error' => null,
            'timeline' => ['summary' => ['up' => 0, 'readonly' => 0, 'down' => 0, 'unknown' => 0], 'points' => []],
            'attempts' => [],
            'sparklines' => [],
            'variable_diff' => ['available' => false, 'rows' => [], 'has_more' => false, 'message' => ''],
            'generated_at' => self::formatDateTime(new \DateTimeImmutable('now')),
            'degraded' => false,
            'warnings' => [],
        ];
    }

    private static function windowPayload(\DateTimeImmutable $start, \DateTimeImmutable $end, ?int $radiusSeconds): array
    {
        return [
            'start' => self::formatDateTime($start),
            'end' => self::formatDateTime($end),
            'radius_seconds' => $radiusSeconds,
        ];
    }

    private static function dateTimeFromValue($value): ?\DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }

        if (!is_scalar($value) || trim((string)$value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable((string)$value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function formatDateTime(\DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    private static function sanitizeMessage(string $message): string
    {
        $message = preg_replace('/((?:password|passwd|pwd|secret|token)\s*[=:]\s*)[^\s;&]+/i', '$1***', $message) ?? $message;
        $message = preg_replace('/([a-z][a-z0-9+.-]*:\/\/[^:\s@]+):[^@\s]+@/i', '$1:***@', $message) ?? $message;

        return trim(mb_substr($message, 0, 512));
    }
}
