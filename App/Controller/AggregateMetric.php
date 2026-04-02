<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\MetricAggregate;
use Glial\Sgbd\Sgbd;
use Glial\Synapse\Controller;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AggregateMetric extends Controller
{
    private const RAW_CHUNK_SIZE = 32;
    private const ROLLUP_CHUNK_SIZE = 64;

    private Logger $logger;

    /**
     * @var array<string,bool>
     */
    private array $tableCache = [];

    /**
     * @var array<string,bool>
     */
    private array $columnCache = [];

    public function before($param)
    {
        $logger = new Logger("AggregateMetric");
        $handler = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;
    }

    public function aggregateRecentByServer($param)
    {
        Debug::parseDebug($param);

        $this->layout_name = false;
        $this->layout = false;
        $this->view = false;

        $idMysqlServer = isset($param[1]) ? (int) $param[1] : (int) ($param[0] ?? 0);
        if ($idMysqlServer < 1) {
            throw new \InvalidArgumentException("PMACTRL-AGG-001: missing id_mysql_server");
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_query("SET SESSION group_concat_max_len = 65535");

        $this->syncPolicies($db);

        $resolutions = MetricAggregate::getResolutions();
        $inserted = [
            '10s' => 0,
            '1m' => 0,
            '10m' => 0,
            '1h' => 0,
        ];

        $inserted['10s'] += $this->aggregateRawResolution($db, $idMysqlServer, $resolutions['10s']);
        $inserted['1m'] += $this->aggregateDerivedResolution($db, $idMysqlServer, $resolutions['10s'], $resolutions['1m']);
        $inserted['10m'] += $this->aggregateDerivedResolution($db, $idMysqlServer, $resolutions['1m'], $resolutions['10m']);
        $inserted['1h'] += $this->aggregateDerivedResolution($db, $idMysqlServer, $resolutions['10m'], $resolutions['1h']);

        $this->purgeRetention($db);

        $summary = [
            'id_mysql_server' => $idMysqlServer,
            'inserted' => $inserted,
            'date' => date('Y-m-d H:i:s'),
        ];

        $this->logger->notice("[AGGREGATE] server=".$idMysqlServer." inserted=".json_encode($inserted));
        echo json_encode($summary);
    }

    private function syncPolicies($db): void
    {
        $sql = "SELECT `id`, `name`, `type`, `from`, `radical` FROM `ts_variable` WHERE `type` IN ('INT','DOUBLE')";
        $res = $db->sql_query($sql);

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $displayPolicy = MetricAggregate::resolveDisplayPolicy($row);
            $statsPolicy = MetricAggregate::resolveStatisticsPolicy($row);

            $sqlInsert = "INSERT INTO `aggregate_metric_policy`
(`id_ts_variable`, `variable_name`, `value_type`, `variable_from`, `radical`, `display_policy`, `stats_policy`, `updated_at`)
VALUES (
".$this->quoteInt($row['id']).",
".$this->quoteString($db, $row['name']).",
".$this->quoteString($db, $row['type']).",
".$this->quoteString($db, $row['from']).",
".$this->quoteString($db, $row['radical']).",
".$this->quoteString($db, $displayPolicy).",
".$this->quoteString($db, $statsPolicy).",
NOW()
)
ON DUPLICATE KEY UPDATE
`variable_name` = VALUES(`variable_name`),
`value_type` = VALUES(`value_type`),
`variable_from` = VALUES(`variable_from`),
`radical` = VALUES(`radical`),
`display_policy` = VALUES(`display_policy`),
`stats_policy` = VALUES(`stats_policy`),
`updated_at` = NOW()";
            $db->sql_query($sqlInsert);
        }
    }

    private function aggregateRawResolution($db, int $idMysqlServer, array $resolution): int
    {
        $inserted = 0;
        $targetTable = (string) $resolution['table'];
        $start = $this->resolveStartTime(
            $db,
            $targetTable,
            $idMysqlServer,
            (int) $resolution['bootstrap_lookback'],
            (int) $resolution['overlap']
        );
        $bucketSeconds = (int) $resolution['seconds'];

        foreach (MetricAggregate::getRawNumericSources() as $source) {
            if (!$this->isSourceSupported($db, $source)) {
                continue;
            }

            $metricChunks = $this->getMetricIdChunks($db, (string) $source['scope'], self::RAW_CHUNK_SIZE);
            if (empty($metricChunks)) {
                continue;
            }

            $seriesExpr = $this->buildSeriesExpression($source);
            $valueExpr = $source['value_expr'];
            $scope = $source['scope'];

            foreach ($metricChunks as $metricIds) {
                $sql = "INSERT INTO `".$targetTable."`
(`bucket_start`, `id_mysql_server`, `id_ts_variable`, `source_scope`, `series_key`, `sample_count`, `value_last`, `value_avg`, `value_stddev`, `value_min`, `value_max`, `value_sum`, `value_sum_squares`, `first_ts`, `last_ts`, `date_updated`)
SELECT
    FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(`date`) / ".$bucketSeconds.") * ".$bucketSeconds.") AS `bucket_start`,
    `id_mysql_server`,
    `id_ts_variable`,
    ".$this->quoteString($db, $scope)." AS `source_scope`,
    ".$seriesExpr." AS `series_key`,
    COUNT(`date`) AS `sample_count`,
    CAST(SUBSTRING_INDEX(GROUP_CONCAT(CAST(".$valueExpr." AS CHAR) ORDER BY `date` DESC SEPARATOR ','), ',', 1) AS DOUBLE) AS `value_last`,
    AVG(".$valueExpr.") AS `value_avg`,
    STDDEV_POP(".$valueExpr.") AS `value_stddev`,
    MIN(".$valueExpr.") AS `value_min`,
    MAX(".$valueExpr.") AS `value_max`,
    SUM(".$valueExpr.") AS `value_sum`,
    SUM(POW(".$valueExpr.", 2)) AS `value_sum_squares`,
    MIN(`date`) AS `first_ts`,
    MAX(`date`) AS `last_ts`,
    NOW() AS `date_updated`
FROM `".$source['table']."`
WHERE `id_mysql_server` = ".$idMysqlServer."
  AND `date` >= ".$this->quoteString($db, $start)."
  AND `id_ts_variable` IN (".$this->implodeIntList($metricIds).")
GROUP BY `bucket_start`, `id_mysql_server`, `id_ts_variable`, `series_key`
ON DUPLICATE KEY UPDATE
`sample_count` = VALUES(`sample_count`),
`value_last` = VALUES(`value_last`),
`value_avg` = VALUES(`value_avg`),
`value_stddev` = VALUES(`value_stddev`),
`value_min` = VALUES(`value_min`),
`value_max` = VALUES(`value_max`),
`value_sum` = VALUES(`value_sum`),
`value_sum_squares` = VALUES(`value_sum_squares`),
`first_ts` = VALUES(`first_ts`),
`last_ts` = VALUES(`last_ts`),
`date_updated` = NOW()";

                $db->sql_query($sql);
                $inserted += max(0, (int) $db->sql_affected_rows());
            }
        }

        return $inserted;
    }

    private function aggregateDerivedResolution($db, int $idMysqlServer, array $sourceResolution, array $targetResolution): int
    {
        $sourceTable = (string) $sourceResolution['table'];
        $targetTable = (string) $targetResolution['table'];
        $targetSeconds = (int) $targetResolution['seconds'];
        $inserted = 0;
        $start = $this->resolveStartTime(
            $db,
            $targetTable,
            $idMysqlServer,
            (int) $targetResolution['bootstrap_lookback'],
            (int) $targetResolution['overlap']
        );

        $metricChunks = $this->getMetricIdChunks($db, null, self::ROLLUP_CHUNK_SIZE);
        foreach ($metricChunks as $metricIds) {
            $sql = "INSERT INTO `".$targetTable."`
(`bucket_start`, `id_mysql_server`, `id_ts_variable`, `source_scope`, `series_key`, `sample_count`, `value_last`, `value_avg`, `value_stddev`, `value_min`, `value_max`, `value_sum`, `value_sum_squares`, `first_ts`, `last_ts`, `date_updated`)
SELECT
    FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(`bucket_start`) / ".$targetSeconds.") * ".$targetSeconds.") AS `bucket_start`,
    `id_mysql_server`,
    `id_ts_variable`,
    `source_scope`,
    `series_key`,
    SUM(`sample_count`) AS `sample_count`,
    CAST(SUBSTRING_INDEX(GROUP_CONCAT(CAST(`value_last` AS CHAR) ORDER BY `last_ts` DESC, `bucket_start` DESC SEPARATOR ','), ',', 1) AS DOUBLE) AS `value_last`,
    CASE WHEN SUM(`sample_count`) > 0 THEN SUM(`value_sum`) / SUM(`sample_count`) ELSE NULL END AS `value_avg`,
    CASE
        WHEN SUM(`sample_count`) > 0 THEN SQRT(GREATEST((SUM(`value_sum_squares`) / SUM(`sample_count`)) - POW(SUM(`value_sum`) / SUM(`sample_count`), 2), 0))
        ELSE NULL
    END AS `value_stddev`,
    MIN(`value_min`) AS `value_min`,
    MAX(`value_max`) AS `value_max`,
    SUM(`value_sum`) AS `value_sum`,
    SUM(`value_sum_squares`) AS `value_sum_squares`,
    MIN(`first_ts`) AS `first_ts`,
    MAX(`last_ts`) AS `last_ts`,
    NOW() AS `date_updated`
FROM `".$sourceTable."`
WHERE `id_mysql_server` = ".$idMysqlServer."
  AND `bucket_start` >= ".$this->quoteString($db, $start)."
  AND `id_ts_variable` IN (".$this->implodeIntList($metricIds).")
GROUP BY FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(`bucket_start`) / ".$targetSeconds.") * ".$targetSeconds."), `id_mysql_server`, `id_ts_variable`, `source_scope`, `series_key`
ON DUPLICATE KEY UPDATE
`sample_count` = VALUES(`sample_count`),
`value_last` = VALUES(`value_last`),
`value_avg` = VALUES(`value_avg`),
`value_stddev` = VALUES(`value_stddev`),
`value_min` = VALUES(`value_min`),
`value_max` = VALUES(`value_max`),
`value_sum` = VALUES(`value_sum`),
`value_sum_squares` = VALUES(`value_sum_squares`),
`first_ts` = VALUES(`first_ts`),
`last_ts` = VALUES(`last_ts`),
`date_updated` = NOW()";

            $db->sql_query($sql);
            $inserted += max(0, (int) $db->sql_affected_rows());
        }

        return $inserted;
    }

    private function purgeRetention($db): void
    {
        $retentionDays = [
            'aggregate_metric_10s' => 14,
            'aggregate_metric_1m' => 180,
            'aggregate_metric_10m' => 365,
            'aggregate_metric_1h' => 365 * 5,
        ];

        foreach ($retentionDays as $table => $days) {
            if (!$this->tableExists($db, $table)) {
                continue;
            }

            $sql = "DELETE FROM `".$table."` WHERE `bucket_start` < DATE_SUB(NOW(), INTERVAL ".$days." DAY)";
            $db->sql_query($sql);
        }
    }

    private function resolveStartTime($db, string $table, int $idMysqlServer, int $bootstrapLookback, int $overlap): string
    {
        $sql = "SELECT MAX(`bucket_start`) AS `max_bucket_start` FROM `".$table."` WHERE `id_mysql_server` = ".$idMysqlServer;
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC);
        $maxBucketStart = trim((string) ($row['max_bucket_start'] ?? ''));

        if ($maxBucketStart === '') {
            return date('Y-m-d H:i:s', time() - $bootstrapLookback);
        }

        $timestamp = strtotime($maxBucketStart);
        if ($timestamp === false) {
            return date('Y-m-d H:i:s', time() - $bootstrapLookback);
        }

        return date('Y-m-d H:i:s', $timestamp - $overlap);
    }

    private function isSourceSupported($db, array $source): bool
    {
        $table = (string) $source['table'];

        if (!$this->tableExists($db, $table)) {
            return false;
        }

        if ($source['scope'] === 'slave') {
            return $this->columnExists($db, $table, 'connection_name');
        }

        if ($source['scope'] === 'digest') {
            return $this->columnExists($db, $table, 'id_ts_mysql_query');
        }

        return true;
    }

    private function buildSeriesExpression(array $source): string
    {
        if ($source['scope'] === 'slave') {
            return "COALESCE(CAST(`connection_name` AS CHAR), '')";
        }

        if ($source['scope'] === 'digest') {
            return "COALESCE(CAST(`id_ts_mysql_query` AS CHAR), '')";
        }

        return "''";
    }

    /**
     * @return array<int,array<int,int>>
     */
    private function getMetricIdChunks($db, ?string $radical, int $chunkSize): array
    {
        $sql = "SELECT `id_ts_variable`
FROM `aggregate_metric_policy`";

        if ($radical !== null) {
            $sql .= " WHERE `radical` = ".$this->quoteString($db, $radical);
        }

        $sql .= " ORDER BY `id_ts_variable`";

        $res = $db->sql_query($sql);
        $ids = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $ids[] = (int) $row['id_ts_variable'];
        }

        if (empty($ids)) {
            return [];
        }

        return array_map(
            static function (array $chunk): array {
                return array_values(array_filter($chunk, static fn ($id): bool => (int) $id > 0));
            },
            array_chunk($ids, max(1, $chunkSize))
        );
    }

    /**
     * @param array<int,int> $ids
     */
    private function implodeIntList(array $ids): string
    {
        return implode(',', array_map('intval', $ids));
    }

    private function tableExists($db, string $table): bool
    {
        if (array_key_exists($table, $this->tableCache)) {
            return $this->tableCache[$table];
        }

        $res = $db->sql_query("SHOW TABLES LIKE ".$this->quoteString($db, $table));
        $this->tableCache[$table] = $db->sql_num_rows($res) > 0;

        return $this->tableCache[$table];
    }

    private function columnExists($db, string $table, string $column): bool
    {
        $cacheKey = $table.'::'.$column;
        if (array_key_exists($cacheKey, $this->columnCache)) {
            return $this->columnCache[$cacheKey];
        }

        $sql = "SELECT 1
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = ".$this->quoteString($db, $table)."
  AND COLUMN_NAME = ".$this->quoteString($db, $column)."
LIMIT 1";
        $res = $db->sql_query($sql);
        $this->columnCache[$cacheKey] = $db->sql_num_rows($res) > 0;

        return $this->columnCache[$cacheKey];
    }

    private function quoteString($db, string $value): string
    {
        return "'".$db->sql_real_escape_string($value)."'";
    }

    /**
     * @param mixed $value
     */
    private function quoteInt($value): int
    {
        return (int) $value;
    }
}
