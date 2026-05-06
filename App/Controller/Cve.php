<?php

namespace App\Controller;

use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;

class Cve extends Controller
{
    public function index($param)
    {
        $this->title = __('CVE Inventory');
        $db = Sgbd::sql(DB_DEFAULT);

        $data = [
            'is_ready' => true,
            'filter' => 'all',
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

        $data['filter'] = $filter;
        $data['products'] = $products;
        $data['stats'] = $this->loadStats($db, $filter);
        $data['cves'] = $this->loadCves($db, $filter, (int)$data['limit']);

        $this->set('data', $data);
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
        $sql = "SELECT p.`product_code`, p.`product_name`, p.`product_family`, p.`icon_class`, p.`color`,"
            . " COUNT(DISTINCT av.`id_cve_catalog`) AS `cve_count`,"
            . " COUNT(av.`id`) AS `affected_count`"
            . " FROM `cve_product` p"
            . " LEFT JOIN `cve_product_affected_version` av"
            . "   ON av.`id_cve_product` = p.`id` AND av.`is_current` = 1"
            . " WHERE p.`is_active` = 1"
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
    private function loadStats($db, string $filter): array
    {
        $where = $this->productWhere($db, $filter);
        $sql = "SELECT"
            . " COUNT(DISTINCT c.`id`) AS `total_cves`,"
            . " COUNT(DISTINCT CASE WHEN c.`severity` = 'critical' THEN c.`id` END) AS `critical_cves`,"
            . " COUNT(DISTINCT CASE WHEN c.`severity` = 'high' THEN c.`id` END) AS `high_cves`,"
            . " COUNT(DISTINCT CASE WHEN c.`known_exploited` = 1 THEN c.`id` END) AS `known_exploited_cves`,"
            . " COUNT(av.`id`) AS `affected_versions`"
            . " FROM `cve_catalog` c"
            . " INNER JOIN `cve_product_affected_version` av ON av.`id_cve_catalog` = c.`id` AND av.`is_current` = 1"
            . " WHERE 1=1 {$where}";

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
    private function loadCves($db, string $filter, int $limit): array
    {
        $where = $this->productWhere($db, $filter);
        $selectedWhere = $this->productWhere($db, $filter, 'av0');
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
            . "   WHERE 1=1 {$selectedWhere}"
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
            . " WHERE 1=1 {$where}"
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

        return array_values($cves);
    }

    private function productWhere($db, string $filter, string $alias = 'av'): string
    {
        if ($filter === 'all') {
            return '';
        }

        $filter = $db->sql_real_escape_string($filter);

        return " AND {$alias}.`product_code` = '{$filter}'";
    }
}
