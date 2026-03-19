<?php

namespace App\Library;

use Glial\Sgbd\Sgbd;

class ServerStateTimeline
{
    public const WINDOW_MINUTES = 60;
    public const AGGREGATION_BUCKET_SECONDS = 10;
    public const WINDOW_POINTS = 360;
    public const INITIAL_CACHE_TTL = 10;
    public const LIVE_CACHE_TTL = 5;

    public static function buildInitialPayload(array $servers): array
    {
        $serverIds = array_map('intval', array_column($servers, 'id'));
        $bucketEnd = self::getCurrentBucket();
        $bucketStart = $bucketEnd->modify('-' . ((self::WINDOW_POINTS - 1) * self::AGGREGATION_BUCKET_SECONDS) . ' seconds');
        $cacheKey = 'initial-' . md5(json_encode([$serverIds, $bucketStart->format('Y-m-d H:i:s'), $bucketEnd->format('Y-m-d H:i:s')]));

        return self::remember($cacheKey, self::INITIAL_CACHE_TTL, function () use ($servers, $serverIds, $bucketStart, $bucketEnd) {
            $rows = self::fetchAvailabilityRows($serverIds, $bucketStart, $bucketEnd);
            $currentStatuses = self::fetchCurrentStatuses($serverIds);
            $labels = self::buildLabels($bucketStart, $bucketEnd);
            $series = self::buildBucketSeries($serverIds, $rows, $bucketStart, $bucketEnd);
            $payloadServers = [];

            foreach ($servers as $server) {
                $serverId = (int) $server['id'];
                $payloadServers[] = [
                    'server_id' => $serverId,
                    'name' => $server['name'],
                    'display_html' => $server['display_html'] ?? $server['name'],
                    'current_status' => $currentStatuses[$serverId] ?? null,
                    'values' => $series[$serverId] ?? array_fill(0, self::WINDOW_MINUTES, null),
                    'ratio' => self::computeServerRatio($series[$serverId] ?? array_fill(0, self::WINDOW_MINUTES, null)),
                ];
            }

            return [
                'bucket_key' => $bucketEnd->format('Y-m-d H:i:s'),
                'labels' => $labels,
                'servers' => $payloadServers,
                'stats' => self::computeStats($payloadServers),
            ];
        });
    }

    public static function buildLivePayload(array $servers): array
    {
        $serverIds = array_map('intval', array_column($servers, 'id'));
        $bucketEnd = self::getCurrentBucket();
        $cacheKey = 'live-' . md5(json_encode([$serverIds, $bucketEnd->format('Y-m-d H:i:s')]));

        return self::remember($cacheKey, self::LIVE_CACHE_TTL, function () use ($serverIds, $bucketEnd) {
            $bucketStart = (clone $bucketEnd)->modify('-' . (self::AGGREGATION_BUCKET_SECONDS - 1) . ' seconds');
            $rows = self::fetchAvailabilityRows($serverIds, $bucketStart, $bucketEnd);
            $values = self::buildCurrentBucketValues($serverIds, $rows, $bucketEnd);
            $currentStatuses = self::fetchCurrentStatuses($serverIds);

            return [
                'bucket_key' => $bucketEnd->format('Y-m-d H:i:s'),
                'label' => $bucketEnd->format('H:i:s'),
                'values' => $values,
                'current_statuses' => $currentStatuses,
            ];
        });
    }

    public static function aggregateMinuteValues(array $values): ?int
    {
        $normalized = array_map([self::class, 'normalizeAvailability'], $values);

        if (in_array(0, $normalized, true)) {
            return 0;
        }

        if (in_array(1, $normalized, true)) {
            return 1;
        }

        return null;
    }

    public static function aggregateRowsByFiveSecondBuckets(array $rows): ?int
    {
        if (empty($rows)) {
            return null;
        }

        $buckets = [];

        foreach ($rows as $row) {
            $timestamp = strtotime((string) ($row['date'] ?? ''));

            if ($timestamp === false) {
                continue;
            }

            $bucketTimestamp = $timestamp - ($timestamp % self::AGGREGATION_BUCKET_SECONDS);
            $buckets[$bucketTimestamp][] = $row['value'] ?? null;
        }

        $bucketValues = [];
        foreach ($buckets as $bucketRows) {
            $bucketValues[] = self::aggregateMinuteValues($bucketRows);
        }

        return self::aggregateMinuteValues($bucketValues);
    }

    private static function fetchAvailabilityRows(array $serverIds, \DateTimeImmutable $dateStart, \DateTimeImmutable $dateEnd): array
    {
        if (empty($serverIds)) {
            return [];
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $result = Extraction2::extract(
            ['mysql_available'],
            $serverIds,
            [$dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s')],
            true,
            false
        );

        if ($result === false || !($result instanceof \mysqli_result)) {
            return [];
        }

        $rows = [];
        while ($row = $db->sql_fetch_array($result, MYSQLI_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    private static function fetchCurrentStatuses(array $serverIds): array
    {
        $display = Extraction2::display(['mysql_available'], $serverIds);
        $statuses = [];

        foreach ($serverIds as $serverId) {
            $value = $display[$serverId]['mysql_available'] ?? null;
            $statuses[(int) $serverId] = self::normalizeAvailability($value);
        }

        return $statuses;
    }

    private static function buildBucketSeries(array $serverIds, array $rows, \DateTimeImmutable $bucketStart, \DateTimeImmutable $bucketEnd): array
    {
        $series = [];
        $bucketMap = [];

        foreach ($serverIds as $serverId) {
            $series[(int) $serverId] = array_fill(0, self::WINDOW_POINTS, null);
        }

        $cursor = $bucketStart;
        $index = 0;
        while ($cursor <= $bucketEnd) {
            $bucketMap[$cursor->format('Y-m-d H:i:s')] = $index;
            $cursor = $cursor->modify('+' . self::AGGREGATION_BUCKET_SECONDS . ' seconds');
            $index++;
        }

        $grouped = [];
        foreach ($rows as $row) {
            $serverId = (int) ($row['id_mysql_server'] ?? 0);
            $timestamp = strtotime((string) ($row['date'] ?? ''));
            if ($timestamp === false) {
                continue;
            }

            $bucketTimestamp = $timestamp - ($timestamp % self::AGGREGATION_BUCKET_SECONDS);
            $bucketKey = date('Y-m-d H:i:s', $bucketTimestamp);
            $grouped[$serverId][$bucketKey][] = $row['value'] ?? null;
        }

        foreach ($grouped as $serverId => $buckets) {
            foreach ($buckets as $bucketKey => $values) {
                if (!isset($bucketMap[$bucketKey])) {
                    continue;
                }

                $series[$serverId][$bucketMap[$bucketKey]] = self::aggregateMinuteValues($values);
            }
        }

        return $series;
    }

    private static function buildCurrentBucketValues(array $serverIds, array $rows, \DateTimeImmutable $bucketEnd): array
    {
        $bucketKey = $bucketEnd->format('Y-m-d H:i:s');
        $values = [];

        foreach ($serverIds as $serverId) {
            $values[(int) $serverId] = null;
        }

        $grouped = [];
        foreach ($rows as $row) {
            $serverId = (int) ($row['id_mysql_server'] ?? 0);
            $timestamp = strtotime((string) ($row['date'] ?? ''));
            if ($timestamp === false) {
                continue;
            }

            $rowBucketKey = date('Y-m-d H:i:s', $timestamp - ($timestamp % self::AGGREGATION_BUCKET_SECONDS));
            if ($rowBucketKey !== $bucketKey) {
                continue;
            }

            $grouped[$serverId][] = $row['value'] ?? null;
        }

        foreach ($grouped as $serverId => $serverValues) {
            $values[$serverId] = self::aggregateMinuteValues($serverValues);
        }

        return $values;
    }

    private static function buildLabels(\DateTimeImmutable $bucketStart, \DateTimeImmutable $bucketEnd): array
    {
        $labels = [];
        $cursor = $bucketStart;

        while ($cursor <= $bucketEnd) {
            $labels[] = $cursor->format('H:i:s');
            $cursor = $cursor->modify('+' . self::AGGREGATION_BUCKET_SECONDS . ' seconds');
        }

        return $labels;
    }

    private static function normalizeAvailability($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ((string) $value === '0') {
            return 0;
        }

        if ((string) $value === '1') {
            return 1;
        }

        return null;
    }

    private static function computeStats(array $servers): array
    {
        $zeroCount = 0;
        $oneCount = 0;
        $totalCount = 0;

        foreach ($servers as $server) {
            foreach (($server['values'] ?? []) as $value) {
                $totalCount++;

                if ($value === 0) {
                    $zeroCount++;
                } elseif ($value === 1) {
                    $oneCount++;
                }
            }
        }

        return [
            'zero' => $zeroCount,
            'one' => $oneCount,
            'total' => $totalCount,
        ];
    }

    private static function computeServerRatio(array $values): array
    {
        $oneCount = 0;
        $signalCount = 0;

        foreach ($values as $value) {
            if ($value === 1) {
                $oneCount++;
                $signalCount++;
            } elseif ($value === 0) {
                $signalCount++;
            }
        }

        return [
            'one' => $oneCount,
            'signal' => $signalCount,
            'label' => $oneCount . ' / ' . $signalCount,
        ];
    }

    private static function getCurrentBucket(): \DateTimeImmutable
    {
        $timestamp = time();
        $bucketTimestamp = $timestamp - ($timestamp % self::AGGREGATION_BUCKET_SECONDS);
        return new \DateTimeImmutable(date('Y-m-d H:i:s', $bucketTimestamp));
    }

    private static function remember(string $cacheKey, int $ttl, callable $callback): array
    {
        $cacheDir = TMP . 'cache/server-state/';
        $cacheFile = $cacheDir . $cacheKey . '.json';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }

        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            $payload = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($payload)) {
                return $payload;
            }
        }

        $payload = $callback();
        file_put_contents($cacheFile, json_encode($payload));

        return $payload;
    }
}
