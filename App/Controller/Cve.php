<?php

namespace App\Controller;

use App\Library\Security\CsrfGuard;
use Glial\Security\Csrf;
use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;

class Cve extends Controller
{
    private const EXCLUSIONS_CSRF_SCOPE = 'cve.exclusions';
    private const EXCLUSIONS_LIMIT = 250;
    private const EXCLUSIONS_MAX_VISIBLE = 500;

    public function index($param)
    {
        $this->title = __('CVE Inventory');
        $db = Sgbd::sql(DB_DEFAULT);

        $data = [
            'is_ready' => true,
            'filter' => 'all',
            'query' => '',
            'products' => [],
            'stats' => [
                'total_cves' => 0,
                'critical_cves' => 0,
                'high_cves' => 0,
                'known_exploited_cves' => 0,
                'affected_versions' => 0,
            ],
            'cves' => [],
            'limit' => 250,
        ];

        if (!$this->tableExists($db, 'cve_catalog') || !$this->tableExists($db, 'cve_product_affected_version')) {
            $data['is_ready'] = false;
            $this->set('data', $data);
            return;
        }

        $routeParams = $this->normalizeRouteParameters($param);
        $products = $this->loadProducts($db);
        $filter = $this->normalizeProductFilter($routeParams[0] ?? 'all', $products);
        $query = self::normalizeSearchQuery($_GET['q'] ?? '');

        $data['filter'] = $filter;
        $data['query'] = $query;
        $data['products'] = $products;
        $data['stats'] = $this->loadStats($db, $filter, $query);
        $data['cves'] = $this->loadCves($db, $filter, $query, (int)$data['limit']);

        $this->set('data', $data);
    }

    public function exclusions($param = [])
    {
        $this->title = __('CVE exclusions');
        $db = Sgbd::sql(DB_DEFAULT);
        $query = self::normalizeSearchQuery($_GET['q'] ?? '');

        $data = [
            'is_ready' => true,
            'query' => $query,
            'rows' => [],
            'stats' => [
                'total_cves' => 0,
                'disabled_cves' => 0,
                'shown_cves' => 0,
            ],
            'limit' => self::EXCLUSIONS_LIMIT,
            'cve_exclusions_csrf_field' => Csrf::DEFAULT_FIELD,
            'cve_exclusions_csrf_token' => Csrf::issueToken($_SESSION, self::EXCLUSIONS_CSRF_SCOPE),
        ];

        if (!$this->tableExists($db, 'cve_catalog')
            || !$this->tableExists($db, 'cve_product_affected_version')
            || !$this->tableExists($db, 'cve_exclusion')
        ) {
            $data['is_ready'] = false;
            $this->set('data', $data);
            return;
        }

        if (CsrfGuard::isPost($_SERVER)) {
            $request = self::evaluateExclusionsRequest($_POST, $_SERVER, $_SESSION);
            if ($request['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendExclusionsError($request['status'], $request['body'], $request['headers']);
                return;
            }

            $changedCveIds = $this->applyExclusionRows($db, $request['rows']);
            $this->invalidateServerCveCache($db, $changedCveIds);

            set_flash(
                'success',
                __('CVE exclusions'),
                sprintf(__('%d CVE visibility rule(s) updated.'), count($changedCveIds))
            );

            $redirect = LINK.'cve/exclusions';
            if ($query !== '') {
                $redirect .= '?q='.urlencode($query);
            }
            header('location: '.$redirect);
            exit;
        }

        $data['stats'] = $this->loadExclusionStats($db);
        $data['rows'] = $this->loadExclusionRows($db, $query, self::EXCLUSIONS_LIMIT);
        $data['stats']['shown_cves'] = count($data['rows']);

        $this->set('data', $data);
    }

    public static function evaluateExclusionsRequest(array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::EXCLUSIONS_CSRF_SCOPE)) {
            return self::buildExclusionsOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $rows = self::normalizeCveExclusionsPayload($post);
        if ($rows === null) {
            return self::buildExclusionsOutcome(400, 'Invalid CVE exclusions payload');
        }

        return self::buildExclusionsOutcome(200, '', [], $rows);
    }

    /**
     * @return array<string,bool>|null
     */
    public static function normalizeCveExclusionsPayload(array $post): ?array
    {
        if (!isset($post['visible_cve_ids']) || !is_array($post['visible_cve_ids']) || $post['visible_cve_ids'] === []) {
            return null;
        }

        if (count($post['visible_cve_ids']) > self::EXCLUSIONS_MAX_VISIBLE) {
            return null;
        }

        $disabledValues = $post['disabled_cve_ids'] ?? [];
        if (!is_array($disabledValues)) {
            return null;
        }

        $disabled = [];
        foreach ($disabledValues as $value) {
            $cveId = self::normalizeCveId($value);
            if ($cveId === null) {
                return null;
            }
            $disabled[$cveId] = true;
        }

        $rows = [];
        foreach ($post['visible_cve_ids'] as $value) {
            $cveId = self::normalizeCveId($value);
            if ($cveId === null) {
                return null;
            }
            $rows[$cveId] = isset($disabled[$cveId]);
        }

        foreach (array_keys($disabled) as $cveId) {
            if (!array_key_exists($cveId, $rows)) {
                return null;
            }
        }

        return $rows === [] ? null : $rows;
    }

    public static function normalizeSearchQuery($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        $query = trim((string)$value);
        $query = (string)preg_replace('/[\x00-\x1F\x7F]/', '', $query);

        if (strlen($query) > 120) {
            $query = substr($query, 0, 120);
        }

        return $query;
    }

    private function tableExists($db, string $table): bool
    {
        $table = $db->sql_real_escape_string($table);
        $res = $db->sql_query("SHOW TABLES LIKE '{$table}'");

        return $db->sql_num_rows($res) > 0;
    }

    private function normalizeRouteParameters($param): array
    {
        if (is_array($param)) {
            return $param;
        }

        if ($param === null || $param === '') {
            return [];
        }

        if (is_scalar($param)) {
            return [$param];
        }

        return [];
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function loadProducts($db): array
    {
        $exclusionTableExists = $this->tableExists($db, 'cve_exclusion');
        $catalogJoin = $exclusionTableExists
            ? " LEFT JOIN `cve_catalog` c ON c.`id` = av.`id_cve_catalog`"
            : '';
        $exclusionJoin = $exclusionTableExists
            ? " LEFT JOIN `cve_exclusion` cx ON cx.`cve_id` = c.`cve_id` AND cx.`is_disabled` = 1"
            : '';
        $exclusionWhere = $exclusionTableExists ? " AND cx.`cve_id` IS NULL" : '';

        $sql = "SELECT p.`product_code`, p.`product_name`, p.`product_family`, p.`icon_class`, p.`color`,"
            . " COUNT(DISTINCT av.`id_cve_catalog`) AS `cve_count`,"
            . " COUNT(av.`id`) AS `affected_count`"
            . " FROM `cve_product` p"
            . " LEFT JOIN `cve_product_affected_version` av"
            . "   ON av.`id_cve_product` = p.`id` AND av.`is_current` = 1"
            . $catalogJoin
            . $exclusionJoin
            . " WHERE p.`is_active` = 1{$exclusionWhere}"
            . " GROUP BY p.`id`, p.`product_code`, p.`product_name`, p.`product_family`, p.`icon_class`, p.`color`"
            . " ORDER BY FIELD(p.`product_code`, 'mysql', 'mysql_cluster', 'mysql_client', 'mysql_connectors',"
            . " 'mysql_enterprise_backup', 'mysql_enterprise_firewall', 'mysql_enterprise_monitor', 'mysql_installer',"
            . " 'mysql_shell', 'mysql_shell_vscode', 'mysql_workbench', 'enterprise_manager_mysql',"
            . " 'mariadb', 'percona', 'xtrabackup', 'pmm', 'mariadb_backup',"
            . " 'proxysql', 'maxscale',"
            . " 'haproxy', 'vitess', 'tidb', 'singlestore', 'aurora_mysql', 'rds_mysql', 'galera'), p.`product_name`";

        $res = $db->sql_query($sql);
        $products = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $products[(string)$row['product_code']] = $row;
        }

        return $products;
    }

    /**
     * @param array<string,array<string,mixed>> $products
     */
    private function normalizeProductFilter($value, array $products): string
    {
        $value = strtolower(trim((string)$value));
        if ($value === '' || $value === 'all') {
            return 'all';
        }

        if (preg_match('/^[a-z0-9_]+$/', $value) !== 1) {
            return 'all';
        }

        return isset($products[$value]) ? $value : 'all';
    }

    /**
     * @return array{total_cves:int,critical_cves:int,high_cves:int,known_exploited_cves:int,affected_versions:int}
     */
    private function loadStats($db, string $filter, string $query): array
    {
        $where = $this->productWhere($db, $filter);
        $searchWhere = $this->searchWhere($db, $query, 'c', 'av');
        $exclusionJoin = $this->exclusionJoin($db, 'c', 'cx');
        $exclusionWhere = $this->exclusionWhere($db, 'cx');
        $sql = "SELECT"
            . " COUNT(DISTINCT c.`id`) AS `total_cves`,"
            . " COUNT(DISTINCT CASE WHEN c.`severity` = 'critical' THEN c.`id` END) AS `critical_cves`,"
            . " COUNT(DISTINCT CASE WHEN c.`severity` = 'high' THEN c.`id` END) AS `high_cves`,"
            . " COUNT(DISTINCT CASE WHEN c.`known_exploited` = 1 THEN c.`id` END) AS `known_exploited_cves`,"
            . " COUNT(av.`id`) AS `affected_versions`"
            . " FROM `cve_catalog` c"
            . " INNER JOIN `cve_product_affected_version` av ON av.`id_cve_catalog` = c.`id` AND av.`is_current` = 1"
            . $exclusionJoin
            . " WHERE 1=1 {$where}{$searchWhere}{$exclusionWhere}";

        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC) ?: [];

        return [
            'total_cves' => (int)($row['total_cves'] ?? 0),
            'critical_cves' => (int)($row['critical_cves'] ?? 0),
            'high_cves' => (int)($row['high_cves'] ?? 0),
            'known_exploited_cves' => (int)($row['known_exploited_cves'] ?? 0),
            'affected_versions' => (int)($row['affected_versions'] ?? 0),
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function loadCves($db, string $filter, string $query, int $limit): array
    {
        $where = $this->productWhere($db, $filter);
        $selectedWhere = $this->productWhere($db, $filter, 'av0');
        $searchWhere = $this->searchWhere($db, $query, 'c', 'av');
        $selectedSearchWhere = $this->searchWhere($db, $query, 'c0', 'av0');
        $exclusionJoin = $this->exclusionJoin($db, 'c', 'cx');
        $exclusionWhere = $this->exclusionWhere($db, 'cx');
        $selectedExclusionJoin = $this->exclusionJoin($db, 'c0', 'cx0');
        $selectedExclusionWhere = $this->exclusionWhere($db, 'cx0');
        $limit = max(25, min(500, $limit));

        $sql = "SELECT c.`id`, c.`cve_id`, c.`title`, c.`summary`, c.`severity`, c.`cvss_v2_score`,"
            . " c.`cvss_v3_score`, c.`cvss_v4_score`, c.`published_at`, c.`last_modified_at`,"
            . " c.`known_exploited`, c.`known_ransomware_campaign_use`, c.`source_codes_json`,"
            . " p.`product_code`, p.`product_name`, p.`icon_class`, p.`color`,"
            . " av.`version_text`, av.`fixed_version`, av.`source_code`, av.`source_url`,"
            . " av.`match_method`, av.`match_confidence`"
            . " FROM ("
            . "   SELECT c0.`id`"
            . "   FROM `cve_catalog` c0"
            . "   INNER JOIN `cve_product_affected_version` av0 ON av0.`id_cve_catalog` = c0.`id` AND av0.`is_current` = 1"
            . $selectedExclusionJoin
            . "   WHERE 1=1 {$selectedWhere}{$selectedSearchWhere}{$selectedExclusionWhere}"
            . "   GROUP BY c0.`id`, c0.`severity`, c0.`known_exploited`, c0.`published_at`, c0.`cve_id`"
            . "   ORDER BY FIELD(c0.`severity`, 'critical', 'high', 'medium', 'low', 'none', 'unknown'),"
            . "            c0.`known_exploited` DESC,"
            . "            c0.`published_at` DESC,"
            . "            c0.`cve_id` DESC"
            . "   LIMIT {$limit}"
            . " ) selected"
            . " INNER JOIN `cve_catalog` c ON c.`id` = selected.`id`"
            . " INNER JOIN `cve_product_affected_version` av ON av.`id_cve_catalog` = c.`id` AND av.`is_current` = 1"
            . " INNER JOIN `cve_product` p ON p.`id` = av.`id_cve_product`"
            . $exclusionJoin
            . " WHERE 1=1 {$where}{$searchWhere}{$exclusionWhere}"
            . " ORDER BY FIELD(c.`severity`, 'critical', 'high', 'medium', 'low', 'none', 'unknown'),"
            . "          c.`known_exploited` DESC,"
            . "          c.`published_at` DESC,"
            . "          c.`cve_id` DESC,"
            . "          p.`product_name`, av.`source_code`";

        $res = $db->sql_query($sql);
        $cves = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $id = (int)$row['id'];
            if (!isset($cves[$id])) {
                $cves[$id] = [
                    'id' => $id,
                    'cve_id' => $row['cve_id'],
                    'title' => $row['title'],
                    'summary' => $row['summary'],
                    'severity' => $row['severity'],
                    'cvss_v2_score' => $row['cvss_v2_score'],
                    'cvss_v3_score' => $row['cvss_v3_score'],
                    'cvss_v4_score' => $row['cvss_v4_score'],
                    'published_at' => $row['published_at'],
                    'last_modified_at' => $row['last_modified_at'],
                    'known_exploited' => (int)$row['known_exploited'],
                    'known_ransomware_campaign_use' => $row['known_ransomware_campaign_use'],
                    'source_codes_json' => $row['source_codes_json'],
                    'affected' => [],
                    'impacted_servers' => [],
                ];
            }

            $cves[$id]['affected'][] = [
                'product_code' => $row['product_code'],
                'product_name' => $row['product_name'],
                'icon_class' => $row['icon_class'],
                'color' => $row['color'],
                'version_text' => $row['version_text'],
                'fixed_version' => $row['fixed_version'],
                'source_code' => $row['source_code'],
                'source_url' => $row['source_url'],
                'match_method' => $row['match_method'],
                'match_confidence' => $row['match_confidence'],
            ];
        }

        $this->attachImpactedServers($db, $cves);

        return array_values($cves);
    }

    private function searchWhere($db, string $query, string $catalogAlias = 'c', string $affectedAlias = 'av'): string
    {
        if ($query === '') {
            return '';
        }

        $like = "'%".$db->sql_real_escape_string($query)."%'";
        return " AND ("
            . "{$catalogAlias}.`cve_id` LIKE {$like}"
            . " OR {$catalogAlias}.`title` LIKE {$like}"
            . " OR {$catalogAlias}.`summary` LIKE {$like}"
            . " OR {$affectedAlias}.`version_text` LIKE {$like}"
            . " OR {$affectedAlias}.`fixed_version` LIKE {$like}"
            . " OR {$affectedAlias}.`source_code` LIKE {$like}"
            . ")";
    }

    /**
     * @param array<int,array<string,mixed>> $cves
     */
    private function attachImpactedServers($db, array &$cves): void
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
            . " sc.`match_method`, sc.`match_confidence`,"
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
            ];
        }
    }

    private function productWhere($db, string $filter, string $alias = 'av'): string
    {
        if ($filter === 'all') {
            return '';
        }

        $filter = $db->sql_real_escape_string($filter);

        return " AND {$alias}.`product_code` = '{$filter}'";
    }

    private function exclusionJoin($db, string $catalogAlias, string $exclusionAlias): string
    {
        if (!$this->tableExists($db, 'cve_exclusion')) {
            return '';
        }

        return " LEFT JOIN `cve_exclusion` {$exclusionAlias}"
            . " ON {$exclusionAlias}.`cve_id` = {$catalogAlias}.`cve_id`"
            . " AND {$exclusionAlias}.`is_disabled` = 1";
    }

    private function exclusionWhere($db, string $exclusionAlias): string
    {
        if (!$this->tableExists($db, 'cve_exclusion')) {
            return '';
        }

        return " AND {$exclusionAlias}.`cve_id` IS NULL";
    }

    /**
     * @return array{total_cves:int,disabled_cves:int,shown_cves:int}
     */
    private function loadExclusionStats($db): array
    {
        $sql = "SELECT"
            . " (SELECT COUNT(*) FROM `cve_catalog`) AS `total_cves`,"
            . " ("
            . "   SELECT COUNT(*)"
            . "   FROM `cve_exclusion` x"
            . "   INNER JOIN `cve_catalog` c ON c.`cve_id` = x.`cve_id`"
            . "   WHERE x.`is_disabled` = 1"
            . " ) AS `disabled_cves`";
        $res = $db->sql_query($sql);
        $row = $db->sql_fetch_array($res, MYSQLI_ASSOC) ?: [];

        return [
            'total_cves' => (int)($row['total_cves'] ?? 0),
            'disabled_cves' => (int)($row['disabled_cves'] ?? 0),
            'shown_cves' => 0,
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function loadExclusionRows($db, string $query, int $limit): array
    {
        $limit = max(25, min(self::EXCLUSIONS_MAX_VISIBLE, $limit));
        $where = '';
        if ($query !== '') {
            $like = '%'.$db->sql_real_escape_string($query).'%';
            $where = " WHERE ("
                . "c.`cve_id` LIKE '{$like}'"
                . " OR c.`title` LIKE '{$like}'"
                . " OR c.`summary` LIKE '{$like}'"
                . " OR c.`source_codes_json` LIKE '{$like}'"
                . " OR av.`product_code` LIKE '{$like}'"
                . " OR p.`product_name` LIKE '{$like}'"
                . ")";
        }

        $sql = "SELECT c.`id`, c.`cve_id`,"
            . " MAX(c.`title`) AS `title`,"
            . " MAX(c.`summary`) AS `summary`,"
            . " MAX(c.`severity`) AS `severity`,"
            . " MAX(c.`cvss_v2_score`) AS `cvss_v2_score`,"
            . " MAX(c.`cvss_v3_score`) AS `cvss_v3_score`,"
            . " MAX(c.`cvss_v4_score`) AS `cvss_v4_score`,"
            . " MAX(c.`published_at`) AS `published_at`,"
            . " MAX(c.`last_modified_at`) AS `last_modified_at`,"
            . " MAX(COALESCE(x.`is_disabled`, 0)) AS `is_disabled`,"
            . " MAX(x.`reason`) AS `reason`,"
            . " MAX(x.`disabled_by`) AS `disabled_by`,"
            . " MAX(x.`disabled_at`) AS `disabled_at`,"
            . " COUNT(DISTINCT av.`id`) AS `affected_count`,"
            . " GROUP_CONCAT(DISTINCT av.`product_code` ORDER BY av.`product_code` SEPARATOR ',') AS `product_codes`,"
            . " GROUP_CONCAT(DISTINCT p.`product_name` ORDER BY p.`product_name` SEPARATOR ', ') AS `products`"
            . " FROM `cve_catalog` c"
            . " LEFT JOIN `cve_exclusion` x ON x.`cve_id` = c.`cve_id`"
            . " LEFT JOIN `cve_product_affected_version` av ON av.`id_cve_catalog` = c.`id` AND av.`is_current` = 1"
            . " LEFT JOIN `cve_product` p ON p.`id` = av.`id_cve_product`"
            . $where
            . " GROUP BY c.`id`, c.`cve_id`"
            . " ORDER BY MAX(COALESCE(x.`is_disabled`, 0)) DESC,"
            . " FIELD(MAX(c.`severity`), 'critical', 'high', 'medium', 'low', 'none', 'unknown'),"
            . " MAX(c.`published_at`) DESC,"
            . " c.`cve_id` DESC"
            . " LIMIT {$limit}";

        $res = $db->sql_query($sql);
        $rows = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param array<string,bool> $rows
     * @return list<string>
     */
    private function applyExclusionRows($db, array $rows): array
    {
        $changedCveIds = [];
        $actor = self::currentActor();

        foreach ($rows as $cveId => $disabled) {
            $cveSql = $db->sql_real_escape_string($cveId);
            $reasonSql = $disabled
                ? "'".$db->sql_real_escape_string('Manually disabled from SuperAdmin CVE exclusions.')."'"
                : 'NULL';
            $actorSql = $disabled ? "'".$db->sql_real_escape_string($actor)."'" : 'NULL';
            $disabledAtSql = $disabled ? 'NOW()' : 'NULL';
            $isDisabled = $disabled ? 1 : 0;

            $sql = "INSERT INTO `cve_exclusion`"
                . " (`cve_id`, `is_disabled`, `reason`, `disabled_by`, `disabled_at`)"
                . " VALUES ('{$cveSql}', {$isDisabled}, {$reasonSql}, {$actorSql}, {$disabledAtSql})"
                . " ON DUPLICATE KEY UPDATE"
                . " `is_disabled` = VALUES(`is_disabled`),"
                . " `reason` = VALUES(`reason`),"
                . " `disabled_by` = VALUES(`disabled_by`),"
                . " `disabled_at` = VALUES(`disabled_at`),"
                . " `date_updated` = NOW()";
            $db->sql_query($sql);
            $changedCveIds[] = $cveId;
        }

        return $changedCveIds;
    }

    /**
     * @param list<string> $cveIds
     */
    private function invalidateServerCveCache($db, array $cveIds): void
    {
        if ($cveIds === [] || !$this->tableExists($db, 'cve_server_cache')) {
            return;
        }

        $quoted = [];
        foreach ($cveIds as $cveId) {
            $quoted[] = "'".$db->sql_real_escape_string($cveId)."'";
        }

        $db->sql_query(
            "DELETE sc"
            . " FROM `cve_server_cache` sc"
            . " INNER JOIN `cve_catalog` c ON c.`id` = sc.`id_cve_catalog`"
            . " WHERE c.`cve_id` IN (".implode(',', $quoted).")"
        );
    }

    private static function normalizeCveId($value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $cveId = strtoupper(trim((string)$value));

        return preg_match('/^CVE-\d{4}-\d{4,}$/', $cveId) === 1 ? $cveId : null;
    }

    private static function currentActor(): string
    {
        foreach (['REMOTE_USER', 'PHP_AUTH_USER', 'USER'] as $key) {
            if (!empty($_SERVER[$key]) && is_scalar($_SERVER[$key])) {
                $actor = trim((string)$_SERVER[$key]);
                if ($actor !== '') {
                    return substr($actor, 0, 128);
                }
            }
        }

        return 'pmacontrol';
    }

    private static function buildExclusionsOutcome(int $statusCode, string $message, array $headers = [], ?array $rows = null): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    private static function sendExclusionsError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name.': '.$value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }
}
