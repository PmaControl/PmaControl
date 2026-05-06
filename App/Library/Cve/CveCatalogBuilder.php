<?php

declare(strict_types=1);

namespace App\Library\Cve;

use PDO;

final class CveCatalogBuilder
{
    private const SEVERITY_RANK = [
        'unknown' => 0,
        'none' => 1,
        'low' => 2,
        'medium' => 3,
        'high' => 4,
        'critical' => 5,
    ];

    private PDO $pdo;

    /** @var array<string,int> */
    private array $productIds = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** @return array{cves:int,affected_versions:int,products:int} */
    public function rebuild(): array
    {
        $this->seedProducts();
        $this->loadProductIds();

        $catalog = [];
        $affected = [];

        $this->collectNvd($catalog, $affected);
        $this->collectOracleCpu($catalog, $affected);
        $this->collectMariaDbSecurity($catalog, $affected);
        $this->collectPerconaAdvisories($catalog, $affected);
        $this->collectComponentGhsa($catalog, $affected);
        $this->collectGhsa($catalog, $affected);
        $this->collectOsv($catalog, $affected);
        $this->collectAwsSecurityBulletins($catalog, $affected);
        $this->applyCisaKevOverlay($catalog);

        $this->pdo->beginTransaction();
        try {
            $this->clearCatalog();

            $catalogIds = [];
            foreach ($catalog as $cveId => $row) {
                $catalogIds[$cveId] = $this->insertCatalog($cveId, $row);
            }

            $insertedAffected = 0;
            $seenHashes = [];
            foreach ($affected as $row) {
                $cveId = $row['cve_id'];
                $productCode = $row['product_code'];
                if (!isset($catalogIds[$cveId], $this->productIds[$productCode])) {
                    continue;
                }

                $hashKey = $cveId . ':' . $productCode . ':' . $row['source_code'] . ':' . $row['match_hash'];
                if (isset($seenHashes[$hashKey])) {
                    continue;
                }
                $seenHashes[$hashKey] = true;

                $this->insertAffectedVersion($catalogIds[$cveId], $this->productIds[$productCode], $row);
                $insertedAffected++;
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return [
            'cves' => count($catalog),
            'affected_versions' => $insertedAffected,
            'products' => count($this->productIds),
        ];
    }

    /** @param array<string,array<string,mixed>> $catalog @param list<array<string,mixed>> $affected */
    private function collectNvd(array &$catalog, array &$affected): void
    {
        foreach ($this->fetchAll("SELECT * FROM `cve_source_nvd` WHERE `is_current` = 1") as $row) {
            $cveId = (string)($row['cve_id'] ?? '');
            $raw = $this->decodeJson($row['raw_json'] ?? null);
            if ($cveId === '' || $raw === null) {
                continue;
            }

            $summary = self::englishDescription($raw);
            $this->mergeCatalog($catalog, $cveId, [
                'title' => self::titleFromSummary($summary),
                'summary' => $summary,
                'severity' => self::severity((string)($row['severity'] ?? 'unknown')),
                'cvss_v2_score' => $this->nullableFloat($row['cvss_v2_score'] ?? null),
                'cvss_v3_score' => $this->nullableFloat($row['cvss_v3_score'] ?? null),
                'cvss_v4_score' => $this->nullableFloat($row['cvss_v4_score'] ?? null),
                'published_at' => $row['published_at'] ?? null,
                'last_modified_at' => $row['last_modified_at'] ?? null,
                'cwe_json' => $row['cwe_json'] ?? null,
                'references_json' => $row['references_json'] ?? null,
                'source_code' => 'nvd',
            ]);

            $matched = false;
            foreach ($this->decodeJson($row['cpe_match_json'] ?? null) ?? [] as $match) {
                if (!is_array($match) || ($match['vulnerable'] ?? true) === false) {
                    continue;
                }

                $productCode = self::productFromCpeCriteria((string)($match['criteria'] ?? ''));
                if ($productCode === null) {
                    continue;
                }

                $matched = true;
                $affected[] = $this->affectedRow($cveId, $productCode, [
                    'version_text' => self::versionTextFromCpeMatch($match),
                    'version_start_including' => $match['versionStartIncluding'] ?? null,
                    'version_start_excluding' => $match['versionStartExcluding'] ?? null,
                    'version_end_including' => $match['versionEndIncluding'] ?? null,
                    'version_end_excluding' => $match['versionEndExcluding'] ?? null,
                    'source_code' => 'nvd',
                    'source_url' => 'https://nvd.nist.gov/vuln/detail/' . rawurlencode($cveId),
                    'match_method' => 'cpe',
                    'match_confidence' => 'high',
                    'raw_match_json' => $this->json($match),
                ]);
            }

            if (!$matched) {
                foreach (self::productsFromText((string)$summary) as $productCode) {
                    $affected[] = $this->affectedRow($cveId, $productCode, [
                        'version_text' => 'Affected versions not structured in NVD CPE',
                        'source_code' => 'nvd',
                        'source_url' => 'https://nvd.nist.gov/vuln/detail/' . rawurlencode($cveId),
                        'match_method' => 'heuristic',
                        'match_confidence' => 'low',
                        'raw_match_json' => $this->json(['description' => $summary]),
                    ]);
                }
            }
        }
    }

    /** @param array<string,array<string,mixed>> $catalog @param list<array<string,mixed>> $affected */
    private function collectOracleCpu(array &$catalog, array &$affected): void
    {
        foreach ($this->fetchAll("SELECT * FROM `cve_source_oracle_cpu` WHERE `is_current` = 1 AND `cve_id` IS NOT NULL") as $row) {
            $cveId = (string)$row['cve_id'];
            $this->mergeCatalog($catalog, $cveId, [
                'severity' => self::severityFromScore($row['base_score'] ?? null),
                'cvss_v3_score' => $this->nullableFloat($row['base_score'] ?? null),
                'published_at' => $row['release_date'] ?? null,
                'last_modified_at' => $row['release_date'] ?? null,
                'source_code' => 'oracle_cpu',
            ]);

            $affected[] = $this->affectedRow($cveId, 'mysql', [
                'version_text' => $this->nullableString($row['affected_versions'] ?? null) ?: 'Oracle CPU advisory',
                'fixed_version' => $row['fixed_versions'] ?? null,
                'source_code' => 'oracle_cpu',
                'source_url' => $row['source_url'] ?? null,
                'match_method' => 'oracle_cpu',
                'match_confidence' => 'medium',
                'raw_match_json' => $row['raw_json'] ?? null,
            ]);
        }
    }

    /** @param array<string,array<string,mixed>> $catalog @param list<array<string,mixed>> $affected */
    private function collectMariaDbSecurity(array &$catalog, array &$affected): void
    {
        foreach ($this->fetchAll("SELECT * FROM `cve_source_mariadb_security` WHERE `is_current` = 1 AND `cve_id` IS NOT NULL") as $row) {
            $cveId = (string)$row['cve_id'];
            $this->mergeCatalog($catalog, $cveId, [
                'severity' => self::severity((string)($row['severity'] ?? 'unknown')),
                'published_at' => $row['release_date'] ?? null,
                'last_modified_at' => $row['release_date'] ?? null,
                'source_code' => 'mariadb_security',
            ]);

            $affected[] = $this->affectedRow($cveId, 'mariadb', [
                'version_text' => $this->nullableString($row['affected_versions'] ?? null) ?: 'MariaDB versions before fixed release',
                'fixed_version' => $row['fixed_versions'] ?? null,
                'source_code' => 'mariadb_security',
                'source_url' => $row['advisory_url'] ?? null,
                'match_method' => 'vendor_advisory',
                'match_confidence' => 'high',
                'raw_match_json' => $row['raw_json'] ?? null,
            ]);
        }
    }

    /** @param array<string,array<string,mixed>> $catalog @param list<array<string,mixed>> $affected */
    private function collectPerconaAdvisories(array &$catalog, array &$affected): void
    {
        foreach ($this->fetchAll("SELECT * FROM `cve_source_percona_advisory` WHERE `is_current` = 1 AND `cve_id` IS NOT NULL") as $row) {
            $cveId = (string)$row['cve_id'];
            $this->mergeCatalog($catalog, $cveId, [
                'severity' => self::severity((string)($row['severity'] ?? 'unknown')),
                'published_at' => $row['published_at'] ?? null,
                'last_modified_at' => $row['updated_at'] ?? null,
                'source_code' => 'percona_advisory',
            ]);

            $affected[] = $this->affectedRow($cveId, 'percona', [
                'version_text' => $this->nullableString($row['affected_versions'] ?? null) ?: 'Percona advisory or NVD-derived range',
                'fixed_version' => $row['fixed_versions'] ?? null,
                'source_code' => 'percona_advisory',
                'source_url' => $row['source_url'] ?? null,
                'match_method' => 'vendor_advisory',
                'match_confidence' => 'medium',
                'raw_match_json' => $row['raw_json'] ?? null,
            ]);
        }
    }

    /** @param array<string,array<string,mixed>> $catalog @param list<array<string,mixed>> $affected */
    private function collectComponentGhsa(array &$catalog, array &$affected): void
    {
        foreach ($this->fetchAll("SELECT * FROM `cve_source_component_ghsa` WHERE `is_current` = 1 AND `cve_id` IS NOT NULL") as $row) {
            $cveId = (string)$row['cve_id'];
            $raw = $this->decodeJson($row['raw_json'] ?? null) ?? [];
            $summary = $this->nullableString($raw['summary'] ?? null);
            $this->mergeCatalog($catalog, $cveId, [
                'title' => $summary,
                'summary' => $this->nullableString($raw['description'] ?? null) ?: $summary,
                'severity' => self::severity((string)($row['severity'] ?? 'unknown')),
                'published_at' => $row['published_at'] ?? null,
                'last_modified_at' => $row['updated_at'] ?? null,
                'references_json' => $row['references_json'] ?? null,
                'source_code' => 'component_ghsa',
            ]);

            $productCode = self::normalizeProductCode((string)($row['component'] ?? ''));
            if ($productCode === null) {
                continue;
            }

            $affected[] = $this->affectedRow($cveId, $productCode, [
                'version_text' => $this->nullableString($row['vulnerable_version_range'] ?? null) ?: 'GHSA affected range',
                'fixed_version' => $row['patched_versions'] ?? null,
                'source_code' => 'component_ghsa',
                'source_url' => $raw['html_url'] ?? ($raw['url'] ?? null),
                'match_method' => 'ghsa',
                'match_confidence' => 'high',
                'raw_match_json' => $row['raw_json'] ?? null,
            ]);
        }
    }

    /** @param array<string,array<string,mixed>> $catalog @param list<array<string,mixed>> $affected */
    private function collectGhsa(array &$catalog, array &$affected): void
    {
        foreach ($this->fetchAll("SELECT * FROM `cve_source_ghsa` WHERE `is_current` = 1") as $row) {
            $raw = $this->decodeJson($row['raw_json'] ?? null) ?? [];
            $cveIds = self::cveIdsFromValues([$row['cve_id'] ?? null, $row['identifiers_json'] ?? null, $row['raw_json'] ?? null]);
            $products = self::productsFromGhsa($raw);
            if ($cveIds === [] || $products === []) {
                continue;
            }

            foreach ($cveIds as $cveId) {
                $this->mergeCatalog($catalog, $cveId, [
                    'title' => $this->nullableString($raw['summary'] ?? null),
                    'summary' => $this->nullableString($raw['description'] ?? null) ?: $this->nullableString($raw['summary'] ?? null),
                    'severity' => self::severity((string)($row['severity'] ?? 'unknown')),
                    'cvss_v3_score' => $this->nullableFloat($row['cvss_score'] ?? null),
                    'published_at' => $row['published_at'] ?? null,
                    'last_modified_at' => $row['updated_at'] ?? null,
                    'cwe_json' => $row['cwe_json'] ?? null,
                    'references_json' => $row['references_json'] ?? null,
                    'source_code' => 'ghsa',
                ]);

                foreach ($products as $productCode => $versionText) {
                    $affected[] = $this->affectedRow($cveId, $productCode, [
                        'version_text' => $versionText ?: 'GHSA affected range',
                        'source_code' => 'ghsa',
                        'source_url' => $raw['html_url'] ?? ($raw['url'] ?? null),
                        'match_method' => 'ghsa',
                        'match_confidence' => 'high',
                        'raw_match_json' => $row['raw_json'] ?? null,
                    ]);
                }
            }
        }
    }

    /** @param array<string,array<string,mixed>> $catalog @param list<array<string,mixed>> $affected */
    private function collectOsv(array &$catalog, array &$affected): void
    {
        foreach ($this->fetchAll("SELECT * FROM `cve_source_osv` WHERE `is_current` = 1") as $row) {
            $raw = $this->decodeJson($row['raw_json'] ?? null) ?? [];
            $cveIds = self::cveIdsFromValues([$row['aliases_json'] ?? null, $row['raw_json'] ?? null]);
            $products = self::productsFromOsv($raw);
            if ($cveIds === [] || $products === []) {
                continue;
            }

            foreach ($cveIds as $cveId) {
                $this->mergeCatalog($catalog, $cveId, [
                    'title' => $row['summary'] ?? null,
                    'summary' => $row['summary'] ?? null,
                    'severity' => self::severityFromJson($row['severity_json'] ?? null),
                    'published_at' => $row['published_at'] ?? null,
                    'last_modified_at' => $row['modified_at'] ?? null,
                    'references_json' => $row['references_json'] ?? null,
                    'source_code' => 'osv',
                ]);

                foreach ($products as $productCode => $versionText) {
                    $affected[] = $this->affectedRow($cveId, $productCode, [
                        'version_text' => $versionText ?: 'OSV affected range',
                        'source_code' => 'osv',
                        'source_url' => self::firstReferenceUrl($raw),
                        'match_method' => 'osv',
                        'match_confidence' => 'medium',
                        'raw_match_json' => $row['raw_json'] ?? null,
                    ]);
                }
            }
        }
    }

    /** @param array<string,array<string,mixed>> $catalog @param list<array<string,mixed>> $affected */
    private function collectAwsSecurityBulletins(array &$catalog, array &$affected): void
    {
        foreach ($this->fetchAll("SELECT * FROM `cve_source_aws_security_bulletin` WHERE `is_current` = 1 AND `cve_id` IS NOT NULL") as $row) {
            $cveId = (string)$row['cve_id'];
            $productCode = self::productFromAwsRow($row);
            if ($productCode === null) {
                continue;
            }

            $this->mergeCatalog($catalog, $cveId, [
                'published_at' => $row['published_at'] ?? null,
                'last_modified_at' => $row['updated_at'] ?? null,
                'source_code' => 'aws_security_bulletin',
            ]);

            $affected[] = $this->affectedRow($cveId, $productCode, [
                'version_text' => $this->nullableString($row['affected_engine_versions'] ?? null) ?: 'AWS engine versions before fixed release',
                'fixed_version' => $row['fixed_engine_versions'] ?? null,
                'source_code' => 'aws_security_bulletin',
                'source_url' => $row['source_url'] ?? null,
                'match_method' => 'aws',
                'match_confidence' => 'high',
                'raw_match_json' => $row['raw_json'] ?? null,
            ]);
        }
    }

    /** @param array<string,array<string,mixed>> $catalog */
    private function applyCisaKevOverlay(array &$catalog): void
    {
        foreach ($this->fetchAll("SELECT * FROM `cve_source_cisa_kev` WHERE `is_current` = 1") as $row) {
            $cveId = (string)($row['cve_id'] ?? '');
            if ($cveId === '' || !isset($catalog[$cveId])) {
                continue;
            }

            $catalog[$cveId]['known_exploited'] = 1;
            $catalog[$cveId]['known_ransomware_campaign_use'] = $this->nullableString($row['known_ransomware_campaign_use'] ?? null);
            $catalog[$cveId]['sources']['cisa_kev'] = true;
        }
    }

    /** @param array<string,array<string,mixed>> $catalog @param array<string,mixed> $row */
    private function mergeCatalog(array &$catalog, string $cveId, array $row): void
    {
        if (!isset($catalog[$cveId])) {
            $catalog[$cveId] = [
                'title' => null,
                'summary' => null,
                'severity' => 'unknown',
                'cvss_v2_score' => null,
                'cvss_v3_score' => null,
                'cvss_v4_score' => null,
                'published_at' => null,
                'last_modified_at' => null,
                'known_exploited' => 0,
                'known_ransomware_campaign_use' => null,
                'cwe_json' => null,
                'references_json' => null,
                'sources' => [],
            ];
        }

        foreach (['title', 'summary', 'cwe_json', 'references_json'] as $field) {
            if (empty($catalog[$cveId][$field]) && !empty($row[$field])) {
                $catalog[$cveId][$field] = $row[$field];
            }
        }

        foreach (['cvss_v2_score', 'cvss_v3_score', 'cvss_v4_score'] as $field) {
            if (isset($row[$field]) && is_numeric($row[$field])) {
                $current = $catalog[$cveId][$field];
                $catalog[$cveId][$field] = $current === null ? (float)$row[$field] : max((float)$current, (float)$row[$field]);
            }
        }

        $severity = self::severity((string)($row['severity'] ?? 'unknown'));
        if (self::SEVERITY_RANK[$severity] > self::SEVERITY_RANK[$catalog[$cveId]['severity']]) {
            $catalog[$cveId]['severity'] = $severity;
        }

        $catalog[$cveId]['published_at'] = self::earliestDate($catalog[$cveId]['published_at'], $row['published_at'] ?? null);
        $catalog[$cveId]['last_modified_at'] = self::latestDate($catalog[$cveId]['last_modified_at'], $row['last_modified_at'] ?? null);

        if (!empty($row['source_code'])) {
            $catalog[$cveId]['sources'][(string)$row['source_code']] = true;
        }
    }

    /** @param array<string,mixed> $data @return array<string,mixed> */
    private function affectedRow(string $cveId, string $productCode, array $data): array
    {
        $raw = $data['raw_match_json'] ?? null;
        $hashPayload = [
            'cve_id' => $cveId,
            'product_code' => $productCode,
            'version_text' => $data['version_text'] ?? null,
            'fixed_version' => $data['fixed_version'] ?? null,
            'source_code' => $data['source_code'] ?? null,
            'method' => $data['match_method'] ?? null,
            'raw' => $raw,
        ];

        return [
            'cve_id' => $cveId,
            'product_code' => $productCode,
            'version_text' => $this->nullableString($data['version_text'] ?? null),
            'version_start_including' => $this->nullableString($data['version_start_including'] ?? null),
            'version_start_excluding' => $this->nullableString($data['version_start_excluding'] ?? null),
            'version_end_including' => $this->nullableString($data['version_end_including'] ?? null),
            'version_end_excluding' => $this->nullableString($data['version_end_excluding'] ?? null),
            'fixed_version' => $this->nullableString($data['fixed_version'] ?? null),
            'source_code' => (string)($data['source_code'] ?? 'unknown'),
            'source_url' => $this->nullableString($data['source_url'] ?? null),
            'match_method' => (string)($data['match_method'] ?? 'heuristic'),
            'match_confidence' => (string)($data['match_confidence'] ?? 'medium'),
            'raw_match_json' => is_string($raw) ? $raw : ($raw === null ? null : $this->json($raw)),
            'match_hash' => hash('sha256', $this->json($hashPayload)),
        ];
    }

    private function insertCatalog(string $cveId, array $row): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO `cve_catalog`"
            . " (`cve_id`, `title`, `summary`, `severity`, `cvss_v2_score`, `cvss_v3_score`, `cvss_v4_score`,"
            . " `published_at`, `last_modified_at`, `known_exploited`, `known_ransomware_campaign_use`,"
            . " `cwe_json`, `references_json`, `source_codes_json`)"
            . " VALUES (:cve_id, :title, :summary, :severity, :cvss_v2_score, :cvss_v3_score, :cvss_v4_score,"
            . " :published_at, :last_modified_at, :known_exploited, :known_ransomware_campaign_use,"
            . " :cwe_json, :references_json, :source_codes_json)"
        );
        $stmt->execute([
            'cve_id' => $cveId,
            'title' => $row['title'],
            'summary' => $row['summary'],
            'severity' => $row['severity'],
            'cvss_v2_score' => $row['cvss_v2_score'],
            'cvss_v3_score' => $row['cvss_v3_score'],
            'cvss_v4_score' => $row['cvss_v4_score'],
            'published_at' => self::dateTimeOrNull($row['published_at']),
            'last_modified_at' => self::dateTimeOrNull($row['last_modified_at']),
            'known_exploited' => (int)$row['known_exploited'],
            'known_ransomware_campaign_use' => $row['known_ransomware_campaign_use'],
            'cwe_json' => $row['cwe_json'],
            'references_json' => $row['references_json'],
            'source_codes_json' => $this->json(array_keys($row['sources'])),
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    private function insertAffectedVersion(int $catalogId, int $productId, array $row): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO `cve_product_affected_version`"
            . " (`id_cve_catalog`, `id_cve_product`, `product_code`, `version_text`,"
            . " `version_start_including`, `version_start_excluding`, `version_end_including`, `version_end_excluding`,"
            . " `fixed_version`, `source_code`, `source_url`, `match_method`, `match_confidence`, `raw_match_json`, `match_hash`)"
            . " VALUES (:id_cve_catalog, :id_cve_product, :product_code, :version_text,"
            . " :version_start_including, :version_start_excluding, :version_end_including, :version_end_excluding,"
            . " :fixed_version, :source_code, :source_url, :match_method, :match_confidence, :raw_match_json, :match_hash)"
        );
        $stmt->execute([
            'id_cve_catalog' => $catalogId,
            'id_cve_product' => $productId,
            'product_code' => $row['product_code'],
            'version_text' => $row['version_text'],
            'version_start_including' => $row['version_start_including'],
            'version_start_excluding' => $row['version_start_excluding'],
            'version_end_including' => $row['version_end_including'],
            'version_end_excluding' => $row['version_end_excluding'],
            'fixed_version' => $row['fixed_version'],
            'source_code' => $row['source_code'],
            'source_url' => self::limitString($row['source_url'], 1024),
            'match_method' => $row['match_method'],
            'match_confidence' => $row['match_confidence'],
            'raw_match_json' => $row['raw_match_json'],
            'match_hash' => $row['match_hash'],
        ]);
    }

    private function clearCatalog(): void
    {
        $this->pdo->exec("DELETE FROM `cve_server_cache`");
        $this->pdo->exec("DELETE FROM `cve_product_affected_version`");
        $this->pdo->exec("DELETE FROM `cve_catalog`");
    }

    private function seedProducts(): void
    {
        $products = [
            ['mysql', 'MySQL Server', 'mysql_like', 'fa fa-database', '#e97b00'],
            ['mariadb', 'MariaDB Server', 'mysql_like', 'fa fa-database', '#003545'],
            ['percona', 'Percona Server', 'mysql_like', 'fa fa-database', '#c3281c'],
            ['proxysql', 'ProxySQL', 'proxy', 'fa fa-random', '#1f2937'],
            ['maxscale', 'MariaDB MaxScale', 'proxy', 'fa fa-exchange', '#00a7c8'],
            ['haproxy', 'HAProxy', 'proxy', 'fa fa-share-alt', '#2563eb'],
            ['vitess', 'Vitess', 'mysql_like', 'fa fa-sitemap', '#7c3aed'],
            ['tidb', 'TiDB', 'mysql_like', 'fa fa-database', '#0f766e'],
            ['singlestore', 'SingleStoreDB', 'mysql_like', 'fa fa-bolt', '#111827'],
            ['aurora_mysql', 'Amazon Aurora MySQL', 'cloud', 'fa fa-cloud', '#ff9900'],
            ['rds_mysql', 'Amazon RDS for MySQL', 'cloud', 'fa fa-cloud', '#527fff'],
            ['galera', 'Galera Cluster', 'component', 'fa fa-object-group', '#16a34a'],
        ];

        $stmt = $this->pdo->prepare(
            "INSERT INTO `cve_product` (`product_code`, `product_name`, `product_family`, `icon_class`, `color`)"
            . " VALUES (?, ?, ?, ?, ?)"
            . " ON DUPLICATE KEY UPDATE `product_name` = VALUES(`product_name`),"
            . " `product_family` = VALUES(`product_family`), `icon_class` = VALUES(`icon_class`),"
            . " `color` = VALUES(`color`), `is_active` = 1"
        );
        foreach ($products as $product) {
            $stmt->execute($product);
        }
    }

    private function loadProductIds(): void
    {
        $this->productIds = [];
        foreach ($this->fetchAll("SELECT `id`, `product_code` FROM `cve_product` WHERE `is_active` = 1") as $row) {
            $this->productIds[(string)$row['product_code']] = (int)$row['id'];
        }
    }

    /** @return list<array<string,mixed>> */
    private function fetchAll(string $sql): array
    {
        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            return [];
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<mixed>|null */
    private function decodeJson(mixed $json): ?array
    {
        if (!is_string($json) || trim($json) === '') {
            return null;
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function json(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function nullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float)$value : null;
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    public static function productFromCpeCriteria(string $criteria): ?string
    {
        $parts = explode(':', strtolower($criteria));
        $vendor = $parts[3] ?? '';
        $product = $parts[4] ?? '';

        if (($vendor === 'oracle' || $vendor === 'mysql') && in_array($product, ['mysql', 'mysql_server'], true)) {
            return 'mysql';
        }
        if ($vendor === 'mariadb' && $product === 'mariadb') {
            return 'mariadb';
        }
        if ($vendor === 'mariadb' && $product === 'maxscale') {
            return 'maxscale';
        }
        if ($vendor === 'percona' && str_contains($product, 'percona')) {
            return 'percona';
        }
        if ($vendor === 'proxysql' || $product === 'proxysql') {
            return 'proxysql';
        }
        if ($vendor === 'haproxy' || $product === 'haproxy') {
            return 'haproxy';
        }
        if ($vendor === 'vitess' || $product === 'vitess') {
            return 'vitess';
        }
        if ($vendor === 'pingcap' || $product === 'tidb') {
            return 'tidb';
        }
        if ($vendor === 'singlestore' || $vendor === 'memsql' || $product === 'singlestore' || $product === 'memsql') {
            return 'singlestore';
        }
        if ($vendor === 'amazon' && $product === 'aurora_mysql') {
            return 'aurora_mysql';
        }
        if ($vendor === 'amazon' && $product === 'rds_mysql') {
            return 'rds_mysql';
        }
        if ($vendor === 'codership' || $product === 'galera_cluster') {
            return 'galera';
        }

        return null;
    }

    /** @param array<string,mixed> $match */
    public static function versionTextFromCpeMatch(array $match): string
    {
        $criteria = (string)($match['criteria'] ?? '');
        $parts = explode(':', $criteria);
        $exact = isset($parts[5]) ? self::cleanCpeValue($parts[5]) : '';
        $ranges = [];

        foreach ([
            'versionStartIncluding' => '>=',
            'versionStartExcluding' => '>',
            'versionEndIncluding' => '<=',
            'versionEndExcluding' => '<',
        ] as $key => $operator) {
            if (isset($match[$key]) && is_scalar($match[$key]) && trim((string)$match[$key]) !== '') {
                $ranges[] = $operator . ' ' . trim((string)$match[$key]);
            }
        }

        if ($exact !== '' && $exact !== '*' && $exact !== '-') {
            array_unshift($ranges, $exact);
        }

        return $ranges === [] ? 'all versions matching CPE' : implode(', ', array_unique($ranges));
    }

    private static function cleanCpeValue(string $value): string
    {
        return str_replace(['\\:', '\\*', '\\-'], [':', '*', '-'], trim($value));
    }

    public static function normalizeProductCode(string $value): ?string
    {
        $value = strtolower(trim($value));
        $aliases = [
            'mysql' => 'mysql',
            'mysql_server' => 'mysql',
            'mariadb' => 'mariadb',
            'mariadb server' => 'mariadb',
            'percona' => 'percona',
            'percona_server' => 'percona',
            'proxysql' => 'proxysql',
            'maxscale' => 'maxscale',
            'haproxy' => 'haproxy',
            'vitess' => 'vitess',
            'tidb' => 'tidb',
            'pingcap/tidb' => 'tidb',
            'singlestore' => 'singlestore',
            'memsql' => 'singlestore',
            'aurora_mysql' => 'aurora_mysql',
            'aurora mysql' => 'aurora_mysql',
            'rds_mysql' => 'rds_mysql',
            'rds for mysql' => 'rds_mysql',
            'galera' => 'galera',
        ];

        return $aliases[$value] ?? null;
    }

    /** @return list<string> */
    private static function productsFromText(string $text): array
    {
        $text = strtolower($text);
        $products = [];
        foreach ([
            'proxysql' => '/\bproxysql\b/',
            'maxscale' => '/\bmaxscale\b/',
            'haproxy' => '/\bhaproxy\b/',
            'vitess' => '/\bvitess\b/',
            'tidb' => '/\btidb\b|\bpingcap\b/',
            'singlestore' => '/\bsinglestore\b|\bmemsql\b/',
            'mariadb' => '/\bmariadb\b/',
            'percona' => '/\bpercona\b/',
            'mysql' => '/\bmysql\b/',
        ] as $product => $pattern) {
            if (preg_match($pattern, $text) === 1) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /** @return array<string,string> */
    private static function productsFromGhsa(array $raw): array
    {
        $products = [];
        foreach (($raw['vulnerabilities'] ?? []) as $vulnerability) {
            if (!is_array($vulnerability)) {
                continue;
            }

            $package = strtolower((string)($vulnerability['package']['name'] ?? ''));
            $product = self::productFromPackageName($package);
            if ($product === null) {
                continue;
            }

            $range = (string)($vulnerability['vulnerable_version_range'] ?? '');
            $products[$product] = trim($range);
        }

        return $products;
    }

    /** @return array<string,string> */
    private static function productsFromOsv(array $raw): array
    {
        $products = [];
        foreach (($raw['affected'] ?? []) as $affected) {
            if (!is_array($affected)) {
                continue;
            }

            $package = strtolower((string)($affected['package']['name'] ?? ''));
            $product = self::productFromPackageName($package);
            if ($product === null) {
                continue;
            }

            $products[$product] = self::osvAffectedVersionText($affected);
        }

        return $products;
    }

    private static function productFromPackageName(string $package): ?string
    {
        if (str_contains($package, 'vitess')) {
            return 'vitess';
        }
        if (str_contains($package, 'pingcap/tidb') || str_ends_with($package, '/tidb')) {
            return 'tidb';
        }
        if (str_contains($package, 'proxysql')) {
            return 'proxysql';
        }
        if (str_contains($package, 'maxscale')) {
            return 'maxscale';
        }
        if (str_contains($package, 'haproxy')) {
            return 'haproxy';
        }

        return null;
    }

    private static function osvAffectedVersionText(array $affected): string
    {
        $chunks = [];
        foreach (($affected['ranges'] ?? []) as $range) {
            if (!is_array($range)) {
                continue;
            }

            $events = [];
            foreach (($range['events'] ?? []) as $event) {
                if (!is_array($event)) {
                    continue;
                }
                foreach (['introduced' => '>=', 'fixed' => '<', 'last_affected' => '<='] as $key => $operator) {
                    if (isset($event[$key])) {
                        $events[] = $operator . ' ' . $event[$key];
                    }
                }
            }
            if ($events !== []) {
                $chunks[] = implode(', ', $events);
            }
        }

        if ($chunks !== []) {
            return implode('; ', array_unique($chunks));
        }

        $versions = $affected['versions'] ?? [];
        if (is_array($versions) && $versions !== []) {
            return implode(', ', array_slice(array_map('strval', $versions), 0, 30));
        }

        return 'OSV affected range';
    }

    private static function productFromAwsRow(array $row): ?string
    {
        $text = strtolower((string)($row['product'] ?? '') . ' ' . (string)($row['engine'] ?? '') . ' ' . (string)($row['service'] ?? ''));
        if (str_contains($text, 'aurora')) {
            return 'aurora_mysql';
        }
        if (str_contains($text, 'rds') || str_contains($text, 'mysql')) {
            return 'rds_mysql';
        }

        return null;
    }

    /** @return list<string> */
    private static function cveIdsFromValues(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }
            if (preg_match_all('/CVE-\d{4}-\d{4,}/i', (string)$value, $matches)) {
                foreach ($matches[0] as $match) {
                    $ids[strtoupper($match)] = true;
                }
            }
        }

        return array_keys($ids);
    }

    private static function englishDescription(array $raw): ?string
    {
        foreach (($raw['descriptions'] ?? []) as $description) {
            if (is_array($description) && ($description['lang'] ?? '') === 'en') {
                return trim((string)($description['value'] ?? '')) ?: null;
            }
        }

        return null;
    }

    private static function titleFromSummary(?string $summary): ?string
    {
        if ($summary === null) {
            return null;
        }

        $summary = trim($summary);
        if ($summary === '') {
            return null;
        }

        $sentenceEnd = strpos($summary, '.');
        $title = $sentenceEnd === false ? $summary : substr($summary, 0, $sentenceEnd + 1);

        return self::limitString($title, 512);
    }

    private static function firstReferenceUrl(array $raw): ?string
    {
        foreach (($raw['references'] ?? []) as $reference) {
            if (is_array($reference) && !empty($reference['url'])) {
                return (string)$reference['url'];
            }
        }

        return null;
    }

    private static function severity(string $severity): string
    {
        $severity = strtolower(trim($severity));
        return array_key_exists($severity, self::SEVERITY_RANK) ? $severity : 'unknown';
    }

    private static function severityFromScore(mixed $score): string
    {
        if (!is_numeric($score)) {
            return 'unknown';
        }

        $score = (float)$score;
        if ($score >= 9.0) {
            return 'critical';
        }
        if ($score >= 7.0) {
            return 'high';
        }
        if ($score >= 4.0) {
            return 'medium';
        }
        if ($score > 0.0) {
            return 'low';
        }

        return 'none';
    }

    private static function severityFromJson(?string $json): string
    {
        if (!is_string($json) || trim($json) === '') {
            return 'unknown';
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return 'unknown';
        }

        $best = 'unknown';
        foreach ($decoded as $entry) {
            $severity = is_array($entry) ? self::severity((string)($entry['type'] ?? $entry['score'] ?? 'unknown')) : 'unknown';
            if (self::SEVERITY_RANK[$severity] > self::SEVERITY_RANK[$best]) {
                $best = $severity;
            }
        }

        return $best;
    }

    private static function earliestDate(mixed $current, mixed $candidate): ?string
    {
        $current = self::dateTimeOrNull($current);
        $candidate = self::dateTimeOrNull($candidate);
        if ($current === null) {
            return $candidate;
        }
        if ($candidate === null) {
            return $current;
        }

        return strtotime($candidate) < strtotime($current) ? $candidate : $current;
    }

    private static function latestDate(mixed $current, mixed $candidate): ?string
    {
        $current = self::dateTimeOrNull($current);
        $candidate = self::dateTimeOrNull($candidate);
        if ($current === null) {
            return $candidate;
        }
        if ($candidate === null) {
            return $current;
        }

        return strtotime($candidate) > strtotime($current) ? $candidate : $current;
    }

    private static function dateTimeOrNull(mixed $value): ?string
    {
        if (!is_scalar($value) || trim((string)$value) === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable((string)$value))->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function limitString(?string $value, int $limit): ?string
    {
        if ($value === null || strlen($value) <= $limit) {
            return $value;
        }

        return substr($value, 0, $limit);
    }
}
