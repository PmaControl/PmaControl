<?php

declare(strict_types=1);

namespace App\Library\Cve;

use App\Library\Format;
use App\Library\MysqlVersion;

final class ServerCveImpactMatcher
{
    private const CACHE_TTL_SECONDS = 86400;
    private const MAX_HOVER_CVES = 12;

    public static function tablesAvailable($db): bool
    {
        return self::tablesExist($db);
    }

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
     * @return array{
     *   is_ready:bool,
     *   stats:array{total_cves:int,impacted_servers:int,critical_cves:int,high_cves:int,known_exploited_cves:int},
     *   cves:list<array<string,mixed>>
     * }
     */
    public static function loadParkSummary($db): array
    {
        $empty = [
            'is_ready' => false,
            'stats' => [
                'total_cves' => 0,
                'impacted_servers' => 0,
                'critical_cves' => 0,
                'high_cves' => 0,
                'known_exploited_cves' => 0,
            ],
            'cves' => [],
        ];

        if (!self::tablesExist($db)) {
            return $empty;
        }

        $empty['is_ready'] = true;
        $empty['stats'] = self::loadParkStats($db);
        $empty['cves'] = self::loadParkCves($db);

        return $empty;
    }

    /**
     * @param array{product_code:string,version:string,version_comment:string} $context
     * @return list<array<string,mixed>>
     */
    public static function loadServerDetails($db, int $serverId, array $context): array
    {
        if ($serverId <= 0 || !self::tablesExist($db)) {
            return [];
        }

        $productCode = $db->sql_real_escape_string($context['product_code']);
        $serverVersion = $db->sql_real_escape_string($context['version']);
        $sql = "SELECT sc.`id_mysql_server`, sc.`product_code`, sc.`server_version`,"
            . " sc.`match_method` AS `server_match_method`, sc.`match_confidence` AS `server_match_confidence`,"
            . " sc.`date_calculated`,"
            . " c.`id`, c.`cve_id`, c.`title`, c.`summary`, c.`severity`, c.`cvss_v2_score`,"
            . " c.`cvss_v3_score`, c.`cvss_v4_score`, c.`published_at`, c.`last_modified_at`,"
            . " c.`known_exploited`, c.`known_ransomware_campaign_use`, c.`references_json`, c.`source_codes_json`,"
            . " p.`product_code` AS `product_code_catalog`, p.`product_name`, p.`icon_class`, p.`color`,"
            . " av.`version_text`, av.`fixed_version`, av.`source_code`, av.`source_url`,"
            . " av.`match_method` AS `affected_match_method`, av.`match_confidence` AS `affected_match_confidence`"
            . " FROM `cve_server_cache` sc"
            . " INNER JOIN `cve_catalog` c ON c.`id` = sc.`id_cve_catalog`"
            . " INNER JOIN `cve_product` p ON p.`id` = sc.`id_cve_product`"
            . " LEFT JOIN `cve_product_affected_version` av"
            . "   ON av.`id_cve_catalog` = sc.`id_cve_catalog`"
            . "  AND av.`id_cve_product` = sc.`id_cve_product`"
            . "  AND av.`is_current` = 1"
            . " WHERE sc.`is_active` = 1"
            . "   AND sc.`id_mysql_server` = ".(int)$serverId
            . "   AND sc.`product_code` = '{$productCode}'"
            . "   AND sc.`server_version` = '{$serverVersion}'"
            . " ORDER BY FIELD(c.`severity`, 'critical', 'high', 'medium', 'low', 'none', 'unknown'),"
            . "          c.`known_exploited` DESC, c.`published_at` DESC, c.`cve_id` DESC,"
            . "          av.`source_code`, av.`version_text`";
        $res = $db->sql_query($sql);
        $cves = [];
        $seenAffected = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $id = (int)$row['id'];
            if (!isset($cves[$id])) {
                $cves[$id] = [
                    'id' => $id,
                    'cve_id' => $row['cve_id'],
                    'title' => $row['title'],
                    'summary' => $row['summary'],
                    'severity' => $row['severity'],
                    'score' => self::bestScore($row),
                    'cvss_v2_score' => $row['cvss_v2_score'],
                    'cvss_v3_score' => $row['cvss_v3_score'],
                    'cvss_v4_score' => $row['cvss_v4_score'],
                    'published_at' => $row['published_at'],
                    'last_modified_at' => $row['last_modified_at'],
                    'known_exploited' => (int)$row['known_exploited'],
                    'known_ransomware_campaign_use' => $row['known_ransomware_campaign_use'],
                    'product_code' => $row['product_code_catalog'],
                    'product_name' => $row['product_name'],
                    'icon_class' => $row['icon_class'],
                    'color' => $row['color'],
                    'server_match_method' => $row['server_match_method'],
                    'server_match_confidence' => $row['server_match_confidence'],
                    'date_calculated' => $row['date_calculated'],
                    'references' => self::referencesFromJson($row['references_json'] ?? null, 6),
                    'source_codes' => self::sourceCodesFromJson($row['source_codes_json'] ?? null),
                    'affected' => [],
                    'impacted_servers' => [],
                ];
            }

            $affectedKey = implode("\n", [
                (string)($row['source_code'] ?? ''),
                (string)($row['version_text'] ?? ''),
                (string)($row['fixed_version'] ?? ''),
                (string)($row['source_url'] ?? ''),
            ]);
            if ($affectedKey === "\n\n\n" || isset($seenAffected[$id][$affectedKey])) {
                continue;
            }
            $seenAffected[$id][$affectedKey] = true;

            $cves[$id]['affected'][] = [
                'version_text' => $row['version_text'],
                'fixed_version' => $row['fixed_version'],
                'source_code' => $row['source_code'],
                'source_url' => $row['source_url'],
                'match_method' => $row['affected_match_method'] ?: $row['server_match_method'],
                'match_confidence' => $row['affected_match_confidence'] ?: $row['server_match_confidence'],
            ];
        }

        self::attachImpactedServers($db, $cves);

        return array_values($cves);
    }

    /**
     * @param array<int,array<string,mixed>> $cves
     */
    private static function attachImpactedServers($db, array &$cves): void
    {
        if ($cves === []) {
            return;
        }

        $ids = array_map('intval', array_keys($cves));
        $ids = array_values(array_filter($ids, static function (int $id): bool {
            return $id > 0;
        }));
        if ($ids === []) {
            return;
        }

        $sql = "SELECT sc.`id_cve_catalog`, sc.`id_mysql_server`, sc.`product_code`, sc.`server_version`,"
            . " sc.`match_method`, sc.`match_confidence`, sc.`date_calculated`,"
            . " ms.`display_name`, ms.`name`, ms.`ip`, ms.`port`"
            . " FROM `cve_server_cache` sc"
            . " INNER JOIN `mysql_server` ms ON ms.`id` = sc.`id_mysql_server` AND ms.`is_deleted` = 0"
            . " WHERE sc.`is_active` = 1"
            . "   AND sc.`id_cve_catalog` IN (".implode(',', $ids).")"
            . " ORDER BY sc.`id_cve_catalog`, ms.`display_name`, ms.`name`, ms.`ip`, ms.`port`, sc.`product_code`";
        $res = $db->sql_query($sql);
        $seen = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $id = (int)($row['id_cve_catalog'] ?? 0);
            if ($id <= 0 || !isset($cves[$id])) {
                continue;
            }

            $serverId = (int)($row['id_mysql_server'] ?? 0);
            $productCode = (string)($row['product_code'] ?? '');
            $serverVersion = (string)($row['server_version'] ?? '');
            $key = $serverId."\n".$productCode."\n".$serverVersion;
            if (isset($seen[$id][$key])) {
                continue;
            }
            $seen[$id][$key] = true;

            $displayName = trim((string)($row['display_name'] ?? ''));
            if ($displayName === '') {
                $displayName = trim((string)($row['name'] ?? ''));
            }
            if ($displayName === '') {
                $displayName = trim((string)($row['ip'] ?? ''));
            }

            $cves[$id]['impacted_servers'][] = [
                'id_mysql_server' => $serverId,
                'display_name' => $displayName,
                'ip' => $row['ip'],
                'port' => $row['port'],
                'product_code' => $productCode,
                'server_version' => $serverVersion,
                'match_method' => $row['match_method'],
                'match_confidence' => $row['match_confidence'],
                'date_calculated' => $row['date_calculated'],
            ];
        }
    }

    /**
     * @return array{total_cves:int,impacted_servers:int,critical_cves:int,high_cves:int,known_exploited_cves:int}
     */
    private static function loadParkStats($db): array
    {
        $sql = "SELECT"
            . " COUNT(DISTINCT sc.`id_cve_catalog`) AS `total_cves`,"
            . " COUNT(DISTINCT sc.`id_mysql_server`) AS `impacted_servers`,"
            . " COUNT(DISTINCT CASE WHEN c.`severity` = 'critical' THEN c.`id` END) AS `critical_cves`,"
            . " COUNT(DISTINCT CASE WHEN c.`severity` = 'high' THEN c.`id` END) AS `high_cves`,"
            . " COUNT(DISTINCT CASE WHEN c.`known_exploited` = 1 THEN c.`id` END) AS `known_exploited_cves`"
            . " FROM `cve_server_cache` sc"
            . " INNER JOIN `cve_catalog` c ON c.`id` = sc.`id_cve_catalog`"
            . " INNER JOIN `mysql_server` ms ON ms.`id` = sc.`id_mysql_server` AND ms.`is_deleted` = 0"
            . " WHERE sc.`is_active` = 1";
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC) ?: [];

        return [
            'total_cves' => (int)($row['total_cves'] ?? 0),
            'impacted_servers' => (int)($row['impacted_servers'] ?? 0),
            'critical_cves' => (int)($row['critical_cves'] ?? 0),
            'high_cves' => (int)($row['high_cves'] ?? 0),
            'known_exploited_cves' => (int)($row['known_exploited_cves'] ?? 0),
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private static function loadParkCves($db): array
    {
        $sql = "SELECT c.`id`, c.`cve_id`, c.`title`, c.`summary`, c.`severity`, c.`cvss_v2_score`,"
            . " c.`cvss_v3_score`, c.`cvss_v4_score`, c.`published_at`, c.`last_modified_at`,"
            . " c.`known_exploited`, c.`known_ransomware_campaign_use`,"
            . " COUNT(DISTINCT sc.`id_mysql_server`) AS `impacted_servers`,"
            . " MAX(sc.`date_calculated`) AS `last_calculated`,"
            . " GROUP_CONCAT(DISTINCT CONCAT(p.`product_code`, CHAR(9), p.`product_name`, CHAR(9), p.`color`)"
            . "   ORDER BY p.`product_name` SEPARATOR '\\n') AS `products_raw`"
            . " FROM `cve_server_cache` sc"
            . " INNER JOIN `cve_catalog` c ON c.`id` = sc.`id_cve_catalog`"
            . " INNER JOIN `cve_product` p ON p.`id` = sc.`id_cve_product`"
            . " INNER JOIN `mysql_server` ms ON ms.`id` = sc.`id_mysql_server` AND ms.`is_deleted` = 0"
            . " WHERE sc.`is_active` = 1"
            . " GROUP BY c.`id`, c.`cve_id`, c.`title`, c.`summary`, c.`severity`, c.`cvss_v2_score`,"
            . "          c.`cvss_v3_score`, c.`cvss_v4_score`, c.`published_at`, c.`last_modified_at`,"
            . "          c.`known_exploited`, c.`known_ransomware_campaign_use`"
            . " ORDER BY FIELD(c.`severity`, 'critical', 'high', 'medium', 'low', 'none', 'unknown'),"
            . "          c.`known_exploited` DESC, `impacted_servers` DESC, c.`published_at` DESC, c.`cve_id` DESC";
        $res = $db->sql_query($sql);
        $rows = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $row['score'] = self::bestScore($row);
            $row['impacted_servers'] = (int)($row['impacted_servers'] ?? 0);
            $row['known_exploited'] = (int)($row['known_exploited'] ?? 0);
            $row['products'] = self::productsFromGroupConcat((string)($row['products_raw'] ?? ''));
            unset($row['products_raw']);
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return list<array{product_code:string,product_name:string,color:string}>
     */
    public static function productsFromGroupConcat(string $raw): array
    {
        $products = [];
        foreach (explode("\n", $raw) as $line) {
            if ($line === '') {
                continue;
            }

            $parts = explode("\t", $line);
            if (count($parts) < 3) {
                continue;
            }

            $products[] = [
                'product_code' => $parts[0],
                'product_name' => $parts[1],
                'color' => $parts[2],
            ];
        }

        return $products;
    }

    /**
     * @return list<array{url:string,label:string}>
     */
    public static function referencesFromJson($json, int $limit = 5): array
    {
        $json = trim((string)$json);
        if ($json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $references = [];
        self::collectReferences($decoded, $references, max(1, $limit));

        return self::labelReferencesForDisplay(array_values($references));
    }

    /**
     * @return list<string>
     */
    public static function sourceCodesFromJson($json): array
    {
        $decoded = json_decode((string)$json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $sources = [];
        foreach ($decoded as $source) {
            if (is_scalar($source)) {
                $source = trim((string)$source);
                if ($source !== '') {
                    $sources[$source] = true;
                }
            }
        }

        return array_keys($sources);
    }

    /**
     * @param array<mixed> $node
     * @param array<string,array{url:string,label:string}> $references
     */
    private static function collectReferences(array $node, array &$references, int $limit): void
    {
        if (count($references) >= $limit) {
            return;
        }

        if (!empty($node['url']) && is_string($node['url']) && preg_match('#^https?://#i', $node['url']) === 1) {
            $label = $node['source'] ?? $node['name'] ?? $node['url'];
            if (is_array($label)) {
                $label = implode(', ', array_filter(array_map('strval', $label)));
            }
            $url = $node['url'];
            if (!isset($references[$url])) {
                $references[$url] = [
                    'url' => $url,
                    'label' => trim((string)$label) !== '' ? trim((string)$label) : $url,
                ];
            }
        }

        foreach ($node as $child) {
            if (count($references) >= $limit) {
                return;
            }
            if (is_array($child)) {
                self::collectReferences($child, $references, $limit);
            }
        }
    }

    /**
     * @param list<array{url:string,label:string}> $references
     * @return list<array{url:string,label:string}>
     */
    private static function labelReferencesForDisplay(array $references): array
    {
        $byDomain = [];
        $metadata = [];

        foreach ($references as $index => $reference) {
            $url = (string)$reference['url'];
            $domain = self::referenceDomain($url);
            if ($domain === '') {
                $references[$index]['label'] = $url;
                continue;
            }

            $metadata[$index] = [
                'domain' => $domain,
                'segments' => self::referencePathSegments($url),
                'url' => $url,
            ];
            $byDomain[$domain][] = $index;
        }

        foreach ($byDomain as $domain => $indexes) {
            if (count($indexes) === 1) {
                $references[$indexes[0]]['label'] = $domain;
                continue;
            }

            $maxDepth = 0;
            foreach ($indexes as $index) {
                $maxDepth = max($maxDepth, count($metadata[$index]['segments']));
            }

            for ($depth = 1; $depth <= max(1, $maxDepth); $depth++) {
                $labels = [];
                $isUnique = true;

                foreach ($indexes as $index) {
                    $label = self::referenceLabelAtDepth(
                        $domain,
                        $metadata[$index]['segments'],
                        $depth
                    );
                    if (isset($labels[$label])) {
                        $isUnique = false;
                        break;
                    }
                    $labels[$label] = true;
                }

                if ($isUnique) {
                    foreach ($indexes as $index) {
                        $references[$index]['label'] = self::referenceLabelAtDepth(
                            $domain,
                            $metadata[$index]['segments'],
                            $depth
                        );
                    }
                    continue 2;
                }
            }

            $seenLabels = [];
            foreach ($indexes as $index) {
                $label = self::referenceFullLabel($domain, $metadata[$index]['url']);
                if (isset($seenLabels[$label])) {
                    $seenLabels[$label]++;
                    $label .= ' #' . $seenLabels[$label];
                } else {
                    $seenLabels[$label] = 1;
                }
                $references[$index]['label'] = $label;
            }
        }

        return $references;
    }

    private static function referenceDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || trim($host) === '') {
            return '';
        }

        $host = strtolower(trim($host));
        return preg_replace('/^www\./', '', $host) ?? $host;
    }

    /**
     * @return list<string>
     */
    private static function referencePathSegments(string $url): array
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || trim($path, '/') === '') {
            return [];
        }

        $segments = [];
        foreach (explode('/', trim($path, '/')) as $segment) {
            $segment = trim(rawurldecode($segment));
            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        return $segments;
    }

    /**
     * @param list<string> $segments
     */
    private static function referenceLabelAtDepth(string $domain, array $segments, int $depth): string
    {
        if ($segments === []) {
            return $domain;
        }

        return $domain . '/' . implode('/', array_slice($segments, 0, $depth));
    }

    private static function referenceFullLabel(string $domain, string $url): string
    {
        $label = self::referenceLabelAtDepth($domain, self::referencePathSegments($url), PHP_INT_MAX);
        $query = parse_url($url, PHP_URL_QUERY);
        if (is_string($query) && $query !== '') {
            $label .= '?' . $query;
        }

        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        if (is_string($fragment) && $fragment !== '') {
            $label .= '#' . $fragment;
        }

        return $label;
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
    public static function bestScore(array $row): ?float
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
