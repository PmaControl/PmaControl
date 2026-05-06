<?php

declare(strict_types=1);

namespace App\Library\Cve;

use App\Library\Format;
use App\Library\MysqlVersion;

final class ServerCveImpactMatcher
{
    private const CACHE_TTL_SECONDS = 86400;
    private const MAX_HOVER_CVES = 12;

    /**
     * @param list<array<string,mixed>> $servers
     * @param array<int,array<string,mixed>> $extraByServer
     * @return array<int,array<string,mixed>>
     */
    public static function loadForServerMain($db, array $servers, array $extraByServer, bool $refreshCache): array
    {
        if ($servers === [] || !self::tablesExist($db)) {
            return [];
        }

        $serverContexts = [];
        foreach ($servers as $server) {
            $serverId = (int)($server['id'] ?? 0);
            if ($serverId <= 0) {
                continue;
            }

            $context = self::serverContext($server, $extraByServer[$serverId] ?? []);
            if ($context === null) {
                continue;
            }

            $serverContexts[$serverId] = $context;
        }

        if ($serverContexts === []) {
            return [];
        }

        if ($refreshCache) {
            self::refreshCache($db, $serverContexts);
        }

        return self::loadCachedImpacts($db, $serverContexts);
    }

    /**
     * @param array<string,mixed> $server
     * @param array<string,mixed> $extra
     * @return array{product_code:string,version:string,version_comment:string}|null
     */
    public static function serverContext(array $server, array $extra): ?array
    {
        if (!empty($server['is_vip']) && (string)$server['is_vip'] === '1') {
            return null;
        }

        $version = trim((string)($extra['version'] ?? ''));
        $versionComment = trim((string)($extra['version_comment'] ?? ''));
        if ($version === '') {
            return null;
        }

        $isProxySql = (!empty($server['is_proxy']) && (string)$server['is_proxy'] === '1')
            || (!empty($extra['is_proxysql']) && (string)$extra['is_proxysql'] === '1');
        $productCode = $isProxySql ? 'proxysql' : self::productCodeFromVersion($version, $versionComment);
        $numericVersion = MysqlVersion::numeric(Format::getMySQLNumVersion($version, $versionComment)['number'] ?? $version);

        if ($productCode === null || $numericVersion === '') {
            return null;
        }

        return [
            'product_code' => $productCode,
            'version' => $numericVersion,
            'version_comment' => $versionComment,
        ];
    }

    public static function productCodeFromVersion(string $version, string $versionComment): ?string
    {
        $text = strtolower($version.' '.$versionComment);
        if (str_contains($text, 'aurora')) {
            return 'aurora_mysql';
        }
        if (str_contains($text, 'rds') && str_contains($text, 'mysql')) {
            return 'rds_mysql';
        }

        $format = Format::getMySQLNumVersion($version, $versionComment);
        $fork = strtolower(trim((string)($format['fork'] ?? 'mysql')));

        return match ($fork) {
            'mariadb' => 'mariadb',
            'percona' => 'percona',
            'proxysql' => 'proxysql',
            'maxscale' => 'maxscale',
            'singlestore' => 'singlestore',
            'mysql router' => null,
            default => 'mysql',
        };
    }

    /** @param array<string,mixed> $affected */
    public static function affectedRowMatchesVersion(array $affected, string $serverVersion): bool
    {
        $serverVersion = MysqlVersion::numeric($serverVersion);
        if ($serverVersion === '') {
            return false;
        }

        $hasRange = false;
        foreach ([
            'version_start_including' => '>=',
            'version_start_excluding' => '>',
            'version_end_including' => '<=',
            'version_end_excluding' => '<',
        ] as $field => $operator) {
            $reference = MysqlVersion::numeric((string)($affected[$field] ?? ''));
            if ($reference === '') {
                continue;
            }

            $hasRange = true;
            if (!version_compare($serverVersion, $reference, $operator)) {
                return false;
            }
        }

        if ($hasRange) {
            return true;
        }

        $exact = self::exactVersionFromText((string)($affected['version_text'] ?? ''));
        if ($exact === null) {
            return false;
        }

        return version_compare($serverVersion, $exact, '==');
    }

    /** @param array<string,mixed> $affected */
    public static function cacheMatchMethod(array $affected): string
    {
        foreach (['version_start_including', 'version_start_excluding', 'version_end_including', 'version_end_excluding'] as $field) {
            if (MysqlVersion::numeric((string)($affected[$field] ?? '')) !== '') {
                return 'version_range';
            }
        }

        return 'exact_version';
    }

    private static function exactVersionFromText(string $text): ?string
    {
        $text = trim($text);
        if (preg_match('/^\d+(?:\.\d+){0,3}(?:[a-z]\d+)?$/i', $text) !== 1) {
            return null;
        }

        $version = MysqlVersion::numeric($text);

        return $version === '' ? null : $version;
    }

    private static function tablesExist($db): bool
    {
        foreach (['cve_catalog', 'cve_product_affected_version', 'cve_server_cache'] as $table) {
            $table = $db->sql_real_escape_string($table);
            $res = $db->sql_query("SHOW TABLES LIKE '{$table}'");
            if (!$res || $db->sql_num_rows($res) === 0) {
                return false;
            }
        }

        return true;
    }

    private static function tableExists($db, string $table): bool
    {
        $table = $db->sql_real_escape_string($table);
        $res = $db->sql_query("SHOW TABLES LIKE '{$table}'");

        return $res && $db->sql_num_rows($res) > 0;
    }

    private static function exclusionJoin($db, string $catalogAlias, string $exclusionAlias): string
    {
        if (!self::tableExists($db, 'cve_exclusion')) {
            return '';
        }

        return " LEFT JOIN `cve_exclusion` {$exclusionAlias}"
            . " ON {$exclusionAlias}.`cve_id` = {$catalogAlias}.`cve_id`"
            . " AND {$exclusionAlias}.`is_disabled` = 1";
    }

    private static function exclusionWhere($db, string $exclusionAlias): string
    {
        if (!self::tableExists($db, 'cve_exclusion')) {
            return '';
        }

        return " AND {$exclusionAlias}.`cve_id` IS NULL";
    }

    /**
     * @param array<int,array{product_code:string,version:string,version_comment:string}> $serverContexts
     */
    private static function refreshCache($db, array $serverContexts): void
    {
        $staleServerIds = self::staleServerIds($db, $serverContexts);
        if ($staleServerIds === []) {
            return;
        }

        $productCodes = [];
        foreach ($staleServerIds as $serverId) {
            $productCodes[$serverContexts[$serverId]['product_code']] = true;
        }

        $affectedByProduct = self::affectedRowsByProduct($db, array_keys($productCodes));
        foreach ($staleServerIds as $serverId) {
            $context = $serverContexts[$serverId];
            $affectedRows = $affectedByProduct[$context['product_code']] ?? [];
            $matches = [];

            foreach ($affectedRows as $affected) {
                if (!self::affectedRowMatchesVersion($affected, $context['version'])) {
                    continue;
                }

                $cacheKey = (int)$affected['id_cve_catalog'].':'.(int)$affected['id_cve_product'];
                if (isset($matches[$cacheKey])) {
                    continue;
                }

                $matches[$cacheKey] = [
                    'id_cve_catalog' => (int)$affected['id_cve_catalog'],
                    'id_cve_product' => (int)$affected['id_cve_product'],
                    'product_code' => (string)$affected['product_code'],
                    'match_method' => self::cacheMatchMethod($affected),
                    'match_confidence' => (string)($affected['match_confidence'] ?? 'medium'),
                ];
            }

            self::replaceServerCache($db, $serverId, $context, array_values($matches));
        }
    }

    /**
     * @param array<int,array{product_code:string,version:string,version_comment:string}> $serverContexts
     * @return list<int>
     */
    private static function staleServerIds($db, array $serverContexts): array
    {
        $serverIds = array_keys($serverContexts);
        $idsSql = implode(',', array_map('intval', $serverIds));
        $sql = "SELECT `id_mysql_server`, `product_code`, `server_version`, MAX(`date_calculated`) AS `date_calculated`"
            . " FROM `cve_server_cache`"
            . " WHERE `is_active` = 1 AND `id_mysql_server` IN ({$idsSql})"
            . " GROUP BY `id_mysql_server`, `product_code`, `server_version`";
        $res = $db->sql_query($sql);
        $fresh = [];
        $threshold = time() - self::CACHE_TTL_SECONDS;

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $serverId = (int)$row['id_mysql_server'];
            if (!isset($serverContexts[$serverId])) {
                continue;
            }

            $context = $serverContexts[$serverId];
            $date = strtotime((string)($row['date_calculated'] ?? '')) ?: 0;
            if ((string)$row['product_code'] === $context['product_code']
                && MysqlVersion::numeric((string)$row['server_version']) === $context['version']
                && $date >= $threshold
            ) {
                $fresh[$serverId] = true;
            }
        }

        $stale = [];
        foreach ($serverIds as $serverId) {
            if (empty($fresh[$serverId])) {
                $stale[] = (int)$serverId;
            }
        }

        return $stale;
    }

    /**
     * @param list<string> $productCodes
     * @return array<string,list<array<string,mixed>>>
     */
    private static function affectedRowsByProduct($db, array $productCodes): array
    {
        if ($productCodes === []) {
            return [];
        }

        $quotedProducts = [];
        foreach ($productCodes as $productCode) {
            $quotedProducts[] = "'".$db->sql_real_escape_string($productCode)."'";
        }

        $exclusionJoin = self::exclusionJoin($db, 'c', 'cx');
        $exclusionWhere = self::exclusionWhere($db, 'cx');
        $catalogJoin = $exclusionJoin !== ''
            ? " INNER JOIN `cve_catalog` c ON c.`id` = av.`id_cve_catalog`"
            : '';

        $sql = "SELECT av.`id_cve_catalog`, av.`id_cve_product`, av.`product_code`, av.`version_text`,"
            . " av.`version_start_including`, av.`version_start_excluding`, av.`version_end_including`, av.`version_end_excluding`,"
            . " av.`match_confidence`"
            . " FROM `cve_product_affected_version` av"
            . $catalogJoin
            . $exclusionJoin
            . " WHERE av.`is_current` = 1"
            . "   AND av.`match_method` = 'cpe'"
            . "   AND av.`product_code` IN (".implode(',', $quotedProducts).")"
            . $exclusionWhere;
        $res = $db->sql_query($sql);
        $rows = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[(string)$row['product_code']][] = $row;
        }

        return $rows;
    }

    /**
     * @param array{product_code:string,version:string,version_comment:string} $context
     * @param list<array{id_cve_catalog:int,id_cve_product:int,product_code:string,match_method:string,match_confidence:string}> $matches
     */
    private static function replaceServerCache($db, int $serverId, array $context, array $matches): void
    {
        $db->sql_query("DELETE FROM `cve_server_cache` WHERE `id_mysql_server` = ".(int)$serverId);
        if ($matches === []) {
            return;
        }

        $serverVersion = $db->sql_real_escape_string($context['version']);
        $serverVersionComment = $db->sql_real_escape_string($context['version_comment']);
        $values = [];
        foreach ($matches as $match) {
            $values[] = '('
                .(int)$serverId.','
                .(int)$match['id_cve_catalog'].','
                .(int)$match['id_cve_product'].','
                ."'".$db->sql_real_escape_string($match['product_code'])."',"
                ."'".$serverVersion."',"
                ."'".$serverVersionComment."',"
                ."'".$db->sql_real_escape_string($match['match_method'])."',"
                ."'".$db->sql_real_escape_string($match['match_confidence'])."',"
                .'1,NOW())';
        }

        $sql = "INSERT INTO `cve_server_cache`"
            . " (`id_mysql_server`, `id_cve_catalog`, `id_cve_product`, `product_code`, `server_version`,"
            . " `server_version_comment`, `match_method`, `match_confidence`, `is_active`, `date_calculated`)"
            . " VALUES ".implode(',', $values)
            . " ON DUPLICATE KEY UPDATE"
            . " `server_version` = VALUES(`server_version`),"
            . " `server_version_comment` = VALUES(`server_version_comment`),"
            . " `match_method` = VALUES(`match_method`),"
            . " `match_confidence` = VALUES(`match_confidence`),"
            . " `is_active` = 1,"
            . " `date_calculated` = NOW()";
        $db->sql_query($sql);
    }

    /**
     * @param array<int,array{product_code:string,version:string,version_comment:string}> $serverContexts
     * @return array<int,array<string,mixed>>
     */
    private static function loadCachedImpacts($db, array $serverContexts): array
    {
        $serverIds = array_keys($serverContexts);
        $idsSql = implode(',', array_map('intval', $serverIds));
        $exclusionJoin = self::exclusionJoin($db, 'c', 'cx');
        $exclusionWhere = self::exclusionWhere($db, 'cx');
        $sql = "SELECT sc.`id_mysql_server`, sc.`product_code`, sc.`server_version`, sc.`match_method`, sc.`match_confidence`,"
            . " c.`cve_id`, c.`title`, c.`summary`, c.`severity`, c.`cvss_v2_score`, c.`cvss_v3_score`, c.`cvss_v4_score`,"
            . " c.`known_exploited`, c.`published_at`"
            . " FROM `cve_server_cache` sc"
            . " INNER JOIN `cve_catalog` c ON c.`id` = sc.`id_cve_catalog`"
            . $exclusionJoin
            . " WHERE sc.`is_active` = 1 AND sc.`id_mysql_server` IN ({$idsSql}){$exclusionWhere}"
            . " ORDER BY FIELD(c.`severity`, 'critical', 'high', 'medium', 'low', 'none', 'unknown'),"
            . "          c.`known_exploited` DESC, c.`published_at` DESC, c.`cve_id` DESC";
        $res = $db->sql_query($sql);
        $impacts = [];
        $seen = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $serverId = (int)$row['id_mysql_server'];
            if (!isset($serverContexts[$serverId])) {
                continue;
            }

            $context = $serverContexts[$serverId];
            if ((string)$row['product_code'] !== $context['product_code']
                || MysqlVersion::numeric((string)$row['server_version']) !== $context['version']
            ) {
                continue;
            }

            $cveId = (string)$row['cve_id'];
            if (isset($seen[$serverId][$cveId])) {
                continue;
            }
            $seen[$serverId][$cveId] = true;

            if (!isset($impacts[$serverId])) {
                $impacts[$serverId] = [
                    'count' => 0,
                    'critical' => 0,
                    'high' => 0,
                    'known_exploited' => 0,
                    'max_severity' => 'unknown',
                    'product_code' => $context['product_code'],
                    'server_version' => $context['version'],
                    'items' => [],
                ];
            }

            $severity = (string)$row['severity'];
            $impacts[$serverId]['count']++;
            if ($severity === 'critical') {
                $impacts[$serverId]['critical']++;
            }
            if ($severity === 'high') {
                $impacts[$serverId]['high']++;
            }
            if (!empty($row['known_exploited'])) {
                $impacts[$serverId]['known_exploited']++;
            }
            if (self::severityRank($severity) > self::severityRank((string)$impacts[$serverId]['max_severity'])) {
                $impacts[$serverId]['max_severity'] = $severity;
            }

            if (count($impacts[$serverId]['items']) < self::MAX_HOVER_CVES) {
                $impacts[$serverId]['items'][] = [
                    'cve_id' => $cveId,
                    'title' => $row['title'] ?: $row['summary'],
                    'severity' => $severity,
                    'score' => self::bestScore($row),
                    'known_exploited' => (int)$row['known_exploited'],
                    'match_method' => $row['match_method'],
                    'match_confidence' => $row['match_confidence'],
                    'published_at' => $row['published_at'],
                ];
            }
        }

        return $impacts;
    }

    /** @param array<string,mixed> $row */
    private static function bestScore(array $row): ?float
    {
        foreach (['cvss_v4_score', 'cvss_v3_score', 'cvss_v2_score'] as $field) {
            if (isset($row[$field]) && is_numeric($row[$field])) {
                return (float)$row[$field];
            }
        }

        return null;
    }

    private static function severityRank(string $severity): int
    {
        return [
            'unknown' => 0,
            'none' => 1,
            'low' => 2,
            'medium' => 3,
            'high' => 4,
            'critical' => 5,
        ][strtolower($severity)] ?? 0;
    }
}
