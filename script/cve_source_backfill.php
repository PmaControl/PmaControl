#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Backfill raw CVE source tables.
 *
 * Default scope:
 * - NVD yearly feeds from MySQL 4.1 era (2004) through current year.
 * - CISA KEV JSON catalog.
 * - OSV/GHSA package feeds for Vitess and TiDB.
 * - Oracle public CVE to advisory mapping for MySQL products.
 * - MariaDB community server CVE documentation.
 * - AWS Aurora MySQL CVE list and AWS security bulletin RSS pages.
 * - Oracle MySQL Risk Matrix pages for product-scoped Oracle CPU rows.
 *
 * Usage:
 *   php script/cve_source_backfill.php --database=pmacontrol
 *   php script/cve_source_backfill.php --source=nvd --nvd-start-year=2004 --nvd-end-year=2026
 *   php script/cve_source_backfill.php --defaults-file=/root/.my.cnf --database=pmacontrol
 */

final class CveSourceBackfill
{
    private const SOURCE_ALL = 'all';

    private PDO $pdo;
    private string $cacheDir;
    private int $httpTimeout;
    private int $sleepMicroseconds;
    private bool $verbose;

    /** @var array<string,int> */
    private array $sourceIds = [];

    /** @var array<string,array<string,mixed>> */
    private array $nvdRecords = [];

    public function __construct(PDO $pdo, array $options)
    {
        $this->pdo = $pdo;
        $this->cacheDir = rtrim((string)($options['cache-dir'] ?? __DIR__ . '/../tmp/cve-source-cache'), '/');
        $this->httpTimeout = max(5, (int)($options['http-timeout'] ?? 60));
        $this->sleepMicroseconds = max(0, (int)($options['sleep-us'] ?? 200000));
        $this->verbose = (bool)($options['verbose'] ?? true);
    }

    public function run(array $options): int
    {
        $this->ensureCacheDir();
        $source = (string)($options['source'] ?? self::SOURCE_ALL);
        $sources = $source === self::SOURCE_ALL ? [
            'nvd',
            'cisa_kev',
            'osv',
            'ghsa',
            'component_ghsa',
            'oracle_cpu',
            'mariadb_security',
            'percona_advisory',
            'aws_security_bulletin',
        ] : array_map('trim', explode(',', $source));

        $total = 0;
        foreach ($sources as $code) {
            if ($code === '') {
                continue;
            }

            $total += match ($code) {
                'nvd' => $this->loadNvd((int)$options['nvd-start-year'], (int)$options['nvd-end-year']),
                'cisa_kev' => $this->loadCisaKev(),
                'osv' => $this->loadOsv(),
                'ghsa' => $this->loadGhsa(false),
                'component_ghsa' => $this->loadGhsa(true),
                'oracle_cpu' => $this->loadOracleCpu(),
                'mariadb_security' => $this->loadMariaDbSecurity(),
                'percona_advisory' => $this->loadPerconaFromNvd(),
                'aws_security_bulletin' => $this->loadAwsSecurityBulletins(),
                default => throw new InvalidArgumentException("Unknown source: {$code}"),
            };
        }

        $this->line("Total rows seen: {$total}");
        return 0;
    }

    private function loadNvd(int $startYear, int $endYear): int
    {
        $runId = $this->startRun('nvd', 'backfill', (string)$startYear, (string)$endYear);
        $seen = 0;
        $inserted = 0;

        try {
            for ($year = $startYear; $year <= $endYear; $year++) {
                $url = "https://nvd.nist.gov/feeds/json/cve/2.0/nvdcve-2.0-{$year}.json.gz";
                $path = $this->download($url, "nvd-{$year}.json.gz");
                $json = gzdecode((string)file_get_contents($path));
                if ($json === false) {
                    throw new RuntimeException("Cannot decode {$path}");
                }

                $payload = $this->decodeJson($json, $url);
                foreach (($payload['vulnerabilities'] ?? []) as $entry) {
                    if (!is_array($entry) || !isset($entry['cve']) || !is_array($entry['cve'])) {
                        continue;
                    }

                    $cve = $entry['cve'];
                    if (!$this->isDatabaseProductCve($cve)) {
                        continue;
                    }

                    $seen++;
                    $row = $this->nvdRow($runId, $cve);
                    $this->nvdRecords[(string)$row['cve_id']] = $cve;
                    $inserted += $this->upsertHistory(
                        'cve_source_nvd',
                        ['cve_id' => (string)$row['cve_id']],
                        $row
                    );
                }

                $this->line("nvd {$year}: {$seen} matching rows so far");
                $this->pause();
            }

            $this->finishRun($runId, 'success', $seen, $inserted, 0);
        } catch (Throwable $e) {
            $this->finishRun($runId, 'failed', $seen, $inserted, 0, $e->getMessage());
            throw $e;
        }

        return $seen;
    }

    private function loadCisaKev(): int
    {
        $runId = $this->startRun('cisa_kev', 'backfill');
        $seen = 0;
        $inserted = 0;
        $url = 'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json';

        try {
            $payload = $this->decodeJson($this->httpGet($url), $url);
            foreach (($payload['vulnerabilities'] ?? []) as $item) {
                if (!is_array($item) || empty($item['cveID'])) {
                    continue;
                }

                $seen++;
                $row = [
                    'id_cve_feed_run' => $runId,
                    'cve_id' => (string)$item['cveID'],
                    'vendor_project' => $this->nullable($item['vendorProject'] ?? null),
                    'product' => $this->nullable($item['product'] ?? null),
                    'vulnerability_name' => $this->nullable($item['vulnerabilityName'] ?? null),
                    'date_added' => $this->dateOnly($item['dateAdded'] ?? null),
                    'due_date' => $this->dateOnly($item['dueDate'] ?? null),
                    'known_ransomware_campaign_use' => $this->nullable($item['knownRansomwareCampaignUse'] ?? null),
                    'required_action' => $this->nullable($item['requiredAction'] ?? null),
                    'notes' => $this->nullable($item['notes'] ?? null),
                    'raw_json' => $this->json($item),
                ];

                $inserted += $this->upsertHistory(
                    'cve_source_cisa_kev',
                    ['cve_id' => (string)$row['cve_id']],
                    $row
                );
            }

            $this->finishRun($runId, 'success', $seen, $inserted, 0);
        } catch (Throwable $e) {
            $this->finishRun($runId, 'failed', $seen, $inserted, 0, $e->getMessage());
            throw $e;
        }

        $this->line("cisa_kev: {$seen} rows");
        return $seen;
    }

    private function loadOsv(): int
    {
        $runId = $this->startRun('osv', 'backfill');
        $seen = 0;
        $inserted = 0;

        try {
            foreach ($this->osvPackages() as $package) {
                $response = $this->httpPostJson(
                    'https://api.osv.dev/v1/query',
                    ['package' => ['ecosystem' => $package['ecosystem'], 'name' => $package['name']]]
                );
                $payload = $this->decodeJson($response, 'https://api.osv.dev/v1/query');

                foreach (($payload['vulns'] ?? []) as $item) {
                    if (!is_array($item) || empty($item['id'])) {
                        continue;
                    }

                    $seen++;
                    $row = [
                        'id_cve_feed_run' => $runId,
                        'osv_id' => (string)$item['id'],
                        'published_at' => $this->dateTime($item['published'] ?? null),
                        'modified_at' => $this->dateTime($item['modified'] ?? null),
                        'withdrawn_at' => $this->dateTime($item['withdrawn'] ?? null),
                        'schema_version' => $this->nullable($item['schema_version'] ?? null),
                        'summary' => $this->nullable($item['summary'] ?? null),
                        'aliases_json' => $this->jsonOrNull($item['aliases'] ?? null),
                        'related_json' => $this->jsonOrNull($item['related'] ?? null),
                        'affected_json' => $this->jsonOrNull($item['affected'] ?? null),
                        'severity_json' => $this->jsonOrNull($item['severity'] ?? null),
                        'references_json' => $this->jsonOrNull($item['references'] ?? null),
                        'raw_json' => $this->json($item + ['pmacontrol_package_query' => $package]),
                    ];

                    $inserted += $this->upsertHistory(
                        'cve_source_osv',
                        ['osv_id' => (string)$row['osv_id']],
                        $row
                    );
                }

                $this->pause();
            }

            $this->finishRun($runId, 'success', $seen, $inserted, 0);
        } catch (Throwable $e) {
            $this->finishRun($runId, 'failed', $seen, $inserted, 0, $e->getMessage());
            throw $e;
        }

        $this->line("osv: {$seen} rows");
        return $seen;
    }

    private function loadGhsa(bool $componentOnly): int
    {
        $sourceCode = $componentOnly ? 'component_ghsa' : 'ghsa';
        $runId = $this->startRun($sourceCode, 'backfill');
        $seen = 0;
        $inserted = 0;

        try {
            foreach ($this->ghsaPackages() as $package) {
                if ($componentOnly && $package['component'] === null) {
                    continue;
                }

                $url = 'https://api.github.com/advisories?per_page=100'
                    . '&ecosystem=' . rawurlencode((string)$package['ecosystem'])
                    . '&affects=' . rawurlencode((string)$package['name']);
                $advisories = $this->decodeJson($this->httpGet($url, ['Accept: application/vnd.github+json']), $url);

                foreach ($advisories as $item) {
                    if (!is_array($item) || empty($item['ghsa_id'])) {
                        continue;
                    }

                    $seen++;
                    if ($componentOnly) {
                        $row = $this->componentGhsaRow($runId, $item, (string)$package['component']);
                        $inserted += $this->upsertHistory(
                            'cve_source_component_ghsa',
                            ['component' => (string)$row['component'], 'ghsa_id' => (string)$row['ghsa_id']],
                            $row
                        );
                    } else {
                        $row = $this->ghsaRow($runId, $item);
                        $inserted += $this->upsertHistory(
                            'cve_source_ghsa',
                            ['ghsa_id' => (string)$row['ghsa_id']],
                            $row
                        );
                    }
                }

                $this->pause();
            }

            $this->finishRun($runId, 'success', $seen, $inserted, 0);
        } catch (Throwable $e) {
            $this->finishRun($runId, 'failed', $seen, $inserted, 0, $e->getMessage());
            throw $e;
        }

        $this->line("{$sourceCode}: {$seen} rows");
        return $seen;
    }

    private function loadOracleCpu(): int
    {
        $runId = $this->startRun('oracle_cpu', 'backfill');
        $seen = 0;
        $inserted = 0;
        $mappingUrl = 'https://www.oracle.com/security-alerts/public-vuln-to-advisory-mapping.html';

        try {
            $rows = $this->htmlRows($this->httpGet($mappingUrl), $mappingUrl);
            foreach ($rows as $rowData) {
                $text = implode(' ', $rowData['cells']);
                if (!preg_match('/CVE-\d{4}-\d+/', $text, $m) || stripos($text, 'MySQL') === false) {
                    continue;
                }

                $cveId = $m[0];
                $product = $rowData['cells'][1] ?? 'MySQL';
                $advisory = $rowData['cells'][2] ?? null;
                $sourceUrl = $rowData['hrefs'][0] ?? $url;
                $seen++;

                $row = [
                    'id_cve_feed_run' => $runId,
                    'source_key' => 'oracle:' . $cveId . ':' . sha1((string)$advisory . '|' . $product),
                    'advisory_id' => $this->nullable($advisory),
                    'cpu_cycle' => $this->oracleCpuCycle((string)$advisory),
                    'release_date' => null,
                    'cve_id' => $cveId,
                    'product' => $this->nullable($product),
                    'component' => null,
                    'affected_versions' => null,
                    'fixed_versions' => null,
                    'base_score' => null,
                    'cvss_vector' => null,
                    'source_url' => $this->absoluteUrl($sourceUrl, 'https://www.oracle.com'),
                    'raw_json' => $this->json($rowData + ['source_url' => $mappingUrl]),
                ];

                $inserted += $this->upsertHistory(
                    'cve_source_oracle_cpu',
                    ['source_key' => (string)$row['source_key']],
                    $row
                );
            }

            foreach ($this->oracleMysqlRiskMatrixUrls() as $riskMatrixUrl) {
                $htmlUrl = strtok($riskMatrixUrl, '#') ?: $riskMatrixUrl;
                $riskRows = $this->oracleMysqlRiskMatrixRows($this->httpGet($htmlUrl), $riskMatrixUrl);
                foreach ($riskRows as $item) {
                    $seen++;
                    $row = [
                        'id_cve_feed_run' => $runId,
                        'source_key' => 'oracle:mysql-risk-matrix:' . $item['cpu_cycle'] . ':' . $item['cve_id'] . ':' . sha1($item['product'] . '|' . $item['component'] . '|' . $item['affected_versions']),
                        'advisory_id' => 'Oracle Critical Patch Update ' . $item['cpu_cycle'],
                        'cpu_cycle' => $item['cpu_cycle'],
                        'release_date' => null,
                        'cve_id' => $item['cve_id'],
                        'product' => $item['product'],
                        'component' => $item['component'],
                        'affected_versions' => $item['affected_versions'],
                        'fixed_versions' => null,
                        'base_score' => $item['base_score'],
                        'cvss_vector' => null,
                        'source_url' => $riskMatrixUrl,
                        'raw_json' => $this->json($item),
                    ];

                    $inserted += $this->upsertHistory(
                        'cve_source_oracle_cpu',
                        ['source_key' => (string)$row['source_key']],
                        $row
                    );
                }
            }

            $this->finishRun($runId, 'success', $seen, $inserted, 0);
        } catch (Throwable $e) {
            $this->finishRun($runId, 'failed', $seen, $inserted, 0, $e->getMessage());
            throw $e;
        }

        $this->line("oracle_cpu: {$seen} rows");
        return $seen;
    }

    private function loadMariaDbSecurity(): int
    {
        $runId = $this->startRun('mariadb_security', 'backfill');
        $seen = 0;
        $inserted = 0;
        $url = 'https://mariadb.com/docs/server/security/cve/community-server';

        try {
            foreach ($this->htmlRows($this->httpGet($url), $url) as $rowData) {
                $text = implode(' ', $rowData['cells']);
                if (!preg_match('/CVE-\d{4}-\d+/', $text, $m)) {
                    continue;
                }

                $cveId = $m[0];
                $score = $this->nullable($rowData['cells'][1] ?? null);
                $fixedVersion = $this->nullable($rowData['cells'][2] ?? null);
                $seen++;

                $row = [
                    'id_cve_feed_run' => $runId,
                    'source_key' => 'mariadb:' . $cveId . ':' . sha1((string)$fixedVersion),
                    'cve_id' => $cveId,
                    'advisory_url' => $rowData['hrefs'][0] ?? $url,
                    'product' => 'MariaDB Community Server',
                    'branch' => $this->mariadbBranch((string)$fixedVersion),
                    'affected_versions' => null,
                    'fixed_versions' => $fixedVersion,
                    'release_date' => null,
                    'severity' => $this->severityFromScore($score),
                    'raw_json' => $this->json($rowData + ['source_url' => $url, 'cvss_score' => $score]),
                ];

                $inserted += $this->upsertHistory(
                    'cve_source_mariadb_security',
                    ['source_key' => (string)$row['source_key']],
                    $row
                );
            }

            $this->finishRun($runId, 'success', $seen, $inserted, 0);
        } catch (Throwable $e) {
            $this->finishRun($runId, 'failed', $seen, $inserted, 0, $e->getMessage());
            throw $e;
        }

        $this->line("mariadb_security: {$seen} rows");
        return $seen;
    }

    private function loadPerconaFromNvd(): int
    {
        $runId = $this->startRun('percona_advisory', 'backfill');
        $seen = 0;
        $inserted = 0;

        try {
            foreach ($this->perconaNvdRecords() as $cveId => $cve) {
                if (!$this->mentions($cve, ['percona', 'xtradb'])) {
                    continue;
                }

                $seen++;
                $row = [
                    'id_cve_feed_run' => $runId,
                    'source_key' => 'percona:' . $cveId,
                    'advisory_id' => null,
                    'cve_id' => $cveId,
                    'product' => 'Percona Server for MySQL',
                    'affected_versions' => $this->nvdAffectedVersions($cve),
                    'fixed_versions' => null,
                    'published_at' => $this->dateTime($cve['published'] ?? null),
                    'updated_at' => $this->dateTime($cve['lastModified'] ?? null),
                    'severity' => $this->nvdSeverity($cve)['severity'],
                    'source_url' => 'https://nvd.nist.gov/vuln/detail/' . rawurlencode($cveId),
                    'raw_json' => $this->json(['source' => 'nvd-derived-percona', 'cve' => $cve]),
                ];

                $inserted += $this->upsertHistory(
                    'cve_source_percona_advisory',
                    ['source_key' => (string)$row['source_key']],
                    $row
                );
            }

            $this->finishRun($runId, 'success', $seen, $inserted, 0);
        } catch (Throwable $e) {
            $this->finishRun($runId, 'failed', $seen, $inserted, 0, $e->getMessage());
            throw $e;
        }

        $this->line("percona_advisory: {$seen} rows");
        return $seen;
    }

    /** @return iterable<string,array<string,mixed>> */
    private function perconaNvdRecords(): iterable
    {
        if ($this->nvdRecords !== []) {
            yield from $this->nvdRecords;
            return;
        }

        $stmt = $this->pdo->query(
            "SELECT `cve_id`, `raw_json` FROM `cve_source_nvd`"
            . " WHERE `is_current` = 1 AND (`raw_json` LIKE '%percona%' OR `raw_json` LIKE '%xtradb%')"
        );
        while ($row = $stmt->fetch()) {
            if (!is_array($row) || empty($row['cve_id']) || empty($row['raw_json'])) {
                continue;
            }

            $payload = json_decode((string)$row['raw_json'], true);
            if (is_array($payload)) {
                yield (string)$row['cve_id'] => $payload;
            }
        }
    }

    private function loadAwsSecurityBulletins(): int
    {
        $runId = $this->startRun('aws_security_bulletin', 'backfill');
        $seen = 0;
        $inserted = 0;

        try {
            foreach ($this->awsAuroraRows() as $item) {
                $seen++;
                $row = [
                    'id_cve_feed_run' => $runId,
                    'source_key' => 'aws:aurora_mysql:' . $item['cve_id'],
                    'bulletin_id' => 'AuroraMySQL.CVE_list',
                    'cve_id' => $item['cve_id'],
                    'service' => 'Amazon Aurora',
                    'product' => 'Amazon Aurora MySQL',
                    'engine' => 'aurora-mysql',
                    'affected_engine_versions' => null,
                    'fixed_engine_versions' => $item['fixed_versions'],
                    'published_at' => null,
                    'updated_at' => null,
                    'source_url' => $item['source_url'],
                    'raw_json' => $this->json($item),
                ];

                $inserted += $this->upsertHistory(
                    'cve_source_aws_security_bulletin',
                    ['source_key' => (string)$row['source_key']],
                    $row
                );
            }

            foreach ($this->awsSecurityRssRows() as $item) {
                $seen++;
                $row = [
                    'id_cve_feed_run' => $runId,
                    'source_key' => 'aws:rss:' . $item['bulletin_id'] . ':' . $item['cve_id'],
                    'bulletin_id' => $item['bulletin_id'],
                    'cve_id' => $item['cve_id'],
                    'service' => $item['service'],
                    'product' => $item['product'],
                    'engine' => $item['engine'],
                    'affected_engine_versions' => null,
                    'fixed_engine_versions' => null,
                    'published_at' => null,
                    'updated_at' => null,
                    'source_url' => $item['source_url'],
                    'raw_json' => $this->json($item),
                ];

                $inserted += $this->upsertHistory(
                    'cve_source_aws_security_bulletin',
                    ['source_key' => (string)$row['source_key']],
                    $row
                );
            }

            $this->finishRun($runId, 'success', $seen, $inserted, 0);
        } catch (Throwable $e) {
            $this->finishRun($runId, 'failed', $seen, $inserted, 0, $e->getMessage());
            throw $e;
        }

        $this->line("aws_security_bulletin: {$seen} rows");
        return $seen;
    }

    private function nvdRow(int $runId, array $cve): array
    {
        $severity = $this->nvdSeverity($cve);

        return [
            'id_cve_feed_run' => $runId,
            'cve_id' => (string)$cve['id'],
            'source_identifier' => $this->nullable($cve['sourceIdentifier'] ?? null),
            'vuln_status' => $this->nullable($cve['vulnStatus'] ?? null),
            'published_at' => $this->dateTime($cve['published'] ?? null),
            'last_modified_at' => $this->dateTime($cve['lastModified'] ?? null),
            'severity' => $severity['severity'],
            'cvss_v2_score' => $severity['v2'],
            'cvss_v3_score' => $severity['v3'],
            'cvss_v4_score' => $severity['v4'],
            'cwe_json' => $this->jsonOrNull($cve['weaknesses'] ?? null),
            'cpe_match_json' => $this->jsonOrNull($this->nvdCpeMatches($cve)),
            'references_json' => $this->jsonOrNull($cve['references'] ?? null),
            'raw_json' => $this->json($cve),
        ];
    }

    private function ghsaRow(int $runId, array $item): array
    {
        return [
            'id_cve_feed_run' => $runId,
            'ghsa_id' => (string)$item['ghsa_id'],
            'cve_id' => $this->nullable($item['cve_id'] ?? null),
            'severity' => $this->severityEnum($item['severity'] ?? null, ['low', 'medium', 'high', 'critical', 'unknown']),
            'cvss_score' => isset($item['cvss']['score']) ? (float)$item['cvss']['score'] : null,
            'cvss_vector' => $this->nullable($item['cvss']['vector_string'] ?? null),
            'published_at' => $this->dateTime($item['published_at'] ?? null),
            'updated_at' => $this->dateTime($item['updated_at'] ?? null),
            'github_reviewed_at' => $this->dateTime($item['github_reviewed_at'] ?? null),
            'identifiers_json' => $this->jsonOrNull($item['identifiers'] ?? null),
            'cwe_json' => $this->jsonOrNull($item['cwes'] ?? null),
            'vulnerabilities_json' => $this->jsonOrNull($item['vulnerabilities'] ?? null),
            'references_json' => $this->jsonOrNull($item['references'] ?? null),
            'raw_json' => $this->json($item),
        ];
    }

    private function componentGhsaRow(int $runId, array $item, string $component): array
    {
        $vulnerabilities = $item['vulnerabilities'] ?? [];
        $first = is_array($vulnerabilities) && isset($vulnerabilities[0]) && is_array($vulnerabilities[0]) ? $vulnerabilities[0] : [];

        return [
            'id_cve_feed_run' => $runId,
            'component' => $this->severityEnum($component, ['proxysql', 'maxscale', 'haproxy', 'vitess', 'other']),
            'ghsa_id' => (string)$item['ghsa_id'],
            'cve_id' => $this->nullable($item['cve_id'] ?? null),
            'ecosystem' => $this->nullable($first['package']['ecosystem'] ?? null),
            'package_name' => $this->nullable($first['package']['name'] ?? null),
            'severity' => $this->severityEnum($item['severity'] ?? null, ['low', 'medium', 'high', 'critical', 'unknown']),
            'vulnerable_version_range' => $this->nullable($first['vulnerable_version_range'] ?? null),
            'patched_versions' => $this->nullable($first['patched_versions'] ?? null),
            'published_at' => $this->dateTime($item['published_at'] ?? null),
            'updated_at' => $this->dateTime($item['updated_at'] ?? null),
            'references_json' => $this->jsonOrNull($item['references'] ?? null),
            'raw_json' => $this->json($item),
        ];
    }

    private function upsertHistory(string $table, array $naturalKey, array $row): int
    {
        $rawJson = (string)$row['raw_json'];
        $row['payload_hash'] = hash('sha256', $rawJson);
        $row['is_current'] = 1;
        $row['date_collected'] = $this->now();
        $row['date_last_seen'] = $this->now();

        $where = [];
        $params = [':payload_hash' => $row['payload_hash']];
        foreach ($naturalKey as $key => $value) {
            $param = ':key_' . $key;
            $where[] = "`{$key}` = {$param}";
            $params[$param] = $value;
        }

        $sql = "UPDATE `{$table}` SET `is_current` = 0 WHERE " . implode(' AND ', $where)
            . " AND `payload_hash` <> :payload_hash AND `is_current` = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $columns = array_keys($row);
        $quoted = array_map(static fn(string $column): string => "`{$column}`", $columns);
        $holders = array_map(static fn(string $column): string => ':' . $column, $columns);
        $updates = [
            '`id_cve_feed_run` = VALUES(`id_cve_feed_run`)',
            '`is_current` = 1',
            '`date_collected` = VALUES(`date_collected`)',
            '`date_last_seen` = VALUES(`date_last_seen`)',
        ];

        $insert = "INSERT INTO `{$table}` (" . implode(',', $quoted) . ") VALUES (" . implode(',', $holders) . ")"
            . " ON DUPLICATE KEY UPDATE " . implode(', ', $updates);
        $stmt = $this->pdo->prepare($insert);
        $stmt->execute($row);

        return $stmt->rowCount() === 1 ? 1 : 0;
    }

    private function startRun(string $sourceCode, string $mode, ?string $cursorStart = null, ?string $cursorEnd = null): int
    {
        $sourceId = $this->sourceId($sourceCode);
        $stmt = $this->pdo->prepare(
            "INSERT INTO `cve_feed_run` (`id_cve_feed_source`, `run_mode`, `status`, `started_at`, `cursor_start`, `cursor_end`)"
            . " VALUES (:source_id, :mode, 'running', NOW(), :cursor_start, :cursor_end)"
        );
        $stmt->execute([
            'source_id' => $sourceId,
            'mode' => $mode,
            'cursor_start' => $cursorStart,
            'cursor_end' => $cursorEnd,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    private function finishRun(
        int $runId,
        string $status,
        int $seen,
        int $inserted,
        int $updated,
        ?string $error = null
    ): void {
        $stmt = $this->pdo->prepare(
            "UPDATE `cve_feed_run` SET `status` = :status, `finished_at` = NOW(),"
            . " `records_seen` = :seen, `records_inserted` = :inserted, `records_updated` = :updated,"
            . " `error_message` = :error WHERE `id` = :id"
        );
        $stmt->execute([
            'status' => $status,
            'seen' => $seen,
            'inserted' => $inserted,
            'updated' => $updated,
            'error' => $error,
            'id' => $runId,
        ]);
    }

    private function sourceId(string $code): int
    {
        if (isset($this->sourceIds[$code])) {
            return $this->sourceIds[$code];
        }

        $stmt = $this->pdo->prepare("SELECT `id` FROM `cve_feed_source` WHERE `code` = :code");
        $stmt->execute(['code' => $code]);
        $id = $stmt->fetchColumn();
        if ($id === false) {
            throw new RuntimeException("Missing cve_feed_source row for {$code}");
        }

        return $this->sourceIds[$code] = (int)$id;
    }

    private function isDatabaseProductCve(array $cve): bool
    {
        $cpeMatches = $this->nvdCpeMatches($cve);
        foreach ($cpeMatches as $match) {
            $criteria = strtolower((string)($match['criteria'] ?? ''));
            foreach ($this->databaseCpeNeedles() as $needle) {
                if (str_contains($criteria, $needle)) {
                    return true;
                }
            }
        }

        $description = strtolower($this->nvdEnglishDescription($cve));
        foreach ($this->databaseDescriptionNeedles() as $needle) {
            if (preg_match($needle, $description) === 1) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    private function databaseCpeNeedles(): array
    {
        return [
            ':oracle:mysql',
            ':oracle:mysql_server',
            ':mysql:mysql',
            ':mariadb:mariadb',
            ':percona:percona_server',
            ':percona:percona_xtradb_cluster',
            ':proxysql:proxysql',
            ':haproxy:haproxy',
            ':mariadb:maxscale',
            ':vitess:vitess',
            ':pingcap:tidb',
            ':singlestore:singlestore',
            ':memsql:memsql',
            ':codership:galera_cluster',
            ':amazon:aurora_mysql',
            ':amazon:rds_mysql',
        ];
    }

    /** @return list<string> */
    private function databaseDescriptionNeedles(): array
    {
        return [
            '/\boracle mysql\b/',
            '/\bmysql (server|community|enterprise|cluster|router|workbench|connector|connectors)\b/',
            '/\bmariadb\b/',
            '/\bpercona\b/',
            '/\bproxysql\b/',
            '/\bhaproxy\b/',
            '/\bmaxscale\b/',
            '/\bvitess\b/',
            '/\btidb\b/',
            '/\bsinglestore\b/',
            '/\bmemsql\b/',
            '/\baurora mysql\b/',
            '/\brds for mysql\b/',
            '/\bgalera\b/',
        ];
    }

    private function mentions(array $cve, array $needles): bool
    {
        $haystack = strtolower($this->json($cve));
        foreach ($needles as $needle) {
            if (str_contains($haystack, strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    /** @return list<array<string,mixed>> */
    private function nvdCpeMatches(array $cve): array
    {
        $matches = [];
        foreach (($cve['configurations'] ?? []) as $configuration) {
            foreach (($configuration['nodes'] ?? []) as $node) {
                foreach (($node['cpeMatch'] ?? []) as $match) {
                    if (is_array($match)) {
                        $matches[] = $match;
                    }
                }
            }
        }

        return $matches;
    }

    private function nvdEnglishDescription(array $cve): string
    {
        foreach (($cve['descriptions'] ?? []) as $description) {
            if (($description['lang'] ?? '') === 'en') {
                return (string)($description['value'] ?? '');
            }
        }

        return '';
    }

    /** @return array{severity:string,v2:?float,v3:?float,v4:?float} */
    private function nvdSeverity(array $cve): array
    {
        $metrics = $cve['metrics'] ?? [];
        $v4 = $this->cvssScore($metrics['cvssMetricV40'][0] ?? null);
        $v3 = $this->cvssScore($metrics['cvssMetricV31'][0] ?? ($metrics['cvssMetricV30'][0] ?? null));
        $v2 = $this->cvssScore($metrics['cvssMetricV2'][0] ?? null);
        $severity = $this->cvssSeverity($metrics['cvssMetricV40'][0] ?? null)
            ?? $this->cvssSeverity($metrics['cvssMetricV31'][0] ?? null)
            ?? $this->cvssSeverity($metrics['cvssMetricV30'][0] ?? null)
            ?? $this->cvssSeverity($metrics['cvssMetricV2'][0] ?? null)
            ?? 'unknown';

        return ['severity' => $severity, 'v2' => $v2, 'v3' => $v3, 'v4' => $v4];
    }

    private function cvssScore(mixed $metric): ?float
    {
        if (!is_array($metric)) {
            return null;
        }

        $score = $metric['cvssData']['baseScore'] ?? null;
        return is_numeric($score) ? (float)$score : null;
    }

    private function cvssSeverity(mixed $metric): ?string
    {
        if (!is_array($metric)) {
            return null;
        }

        $severity = $metric['cvssData']['baseSeverity'] ?? ($metric['baseSeverity'] ?? null);
        return $this->severityEnum($severity, ['none', 'low', 'medium', 'high', 'critical', 'unknown']);
    }

    private function severityFromScore(?string $score): string
    {
        if ($score === null || !is_numeric($score)) {
            return 'unknown';
        }

        $value = (float)$score;
        if ($value >= 9.0) {
            return 'critical';
        }
        if ($value >= 7.0) {
            return 'high';
        }
        if ($value >= 4.0) {
            return 'medium';
        }
        if ($value > 0.0) {
            return 'low';
        }

        return 'none';
    }

    /** @param list<string> $allowed */
    private function severityEnum(mixed $value, array $allowed): string
    {
        $normalized = strtolower((string)$value);
        return in_array($normalized, $allowed, true) ? $normalized : 'unknown';
    }

    private function nvdAffectedVersions(array $cve): ?string
    {
        $ranges = [];
        foreach ($this->nvdCpeMatches($cve) as $match) {
            $criteria = (string)($match['criteria'] ?? '');
            if (stripos($criteria, 'percona') === false) {
                continue;
            }

            $parts = [$criteria];
            foreach (['versionStartIncluding', 'versionStartExcluding', 'versionEndIncluding', 'versionEndExcluding'] as $key) {
                if (isset($match[$key])) {
                    $parts[] = $key . '=' . $match[$key];
                }
            }
            $ranges[] = implode(' ', $parts);
        }

        return $ranges === [] ? null : implode("\n", array_unique($ranges));
    }

    /** @return list<array{ecosystem:string,name:string}> */
    private function osvPackages(): array
    {
        return [
            ['ecosystem' => 'Go', 'name' => 'vitess.io/vitess'],
            ['ecosystem' => 'Go', 'name' => 'github.com/pingcap/tidb'],
        ];
    }

    /** @return list<array{ecosystem:string,name:string,component:?string}> */
    private function ghsaPackages(): array
    {
        return [
            ['ecosystem' => 'go', 'name' => 'vitess.io/vitess', 'component' => 'vitess'],
            ['ecosystem' => 'go', 'name' => 'github.com/pingcap/tidb', 'component' => null],
        ];
    }

    /** @return list<array{cells:list<string>,hrefs:list<string>}> */
    private function htmlRows(string $html, string $sourceUrl): array
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//tr|//*[@role="row"]');
        $rows = [];

        foreach ($nodes as $node) {
            $cellNodes = $xpath->query('.//td|.//*[@role="cell"]', $node);
            if ($cellNodes === false || $cellNodes->length === 0) {
                continue;
            }

            $cells = [];
            $hrefs = [];
            foreach ($cellNodes as $cellNode) {
                $cells[] = trim(preg_replace('/\s+/', ' ', $cellNode->textContent) ?? '');
                foreach ($xpath->query('.//a[@href]', $cellNode) ?: [] as $link) {
                    $hrefs[] = $this->absoluteUrl($link->getAttribute('href'), $sourceUrl);
                }
            }

            if ($cells !== []) {
                $rows[] = ['cells' => $cells, 'hrefs' => array_values(array_unique($hrefs))];
            }
        }

        return $rows;
    }

    /** @return list<string> */
    private function oracleMysqlRiskMatrixUrls(): array
    {
        return [
            'https://www.oracle.com/security-alerts/cpujul2022.html#AppendixMSQL',
        ];
    }

    /**
     * @return list<array{
     *   cve_id:string,
     *   product:string,
     *   component:?string,
     *   protocol:?string,
     *   remote_exploit_without_auth:?string,
     *   base_score:?string,
     *   attack_vector:?string,
     *   attack_complexity:?string,
     *   privileges_required:?string,
     *   user_interaction:?string,
     *   scope:?string,
     *   confidentiality:?string,
     *   integrity:?string,
     *   availability:?string,
     *   affected_versions:?string,
     *   notes:?string,
     *   cpu_cycle:string,
     *   source_url:string,
     *   hrefs:list<string>
     * }>
     */
    private function oracleMysqlRiskMatrixRows(string $html, string $sourceUrl): array
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $table = $xpath->query('//*[@id="AppendixMSQL"]/following::table[1]')->item(0);
        if ($table === null) {
            return [];
        }

        $cycle = $this->oracleCpuCycleFromHtml($xpath, $sourceUrl);
        $rows = [];
        foreach ($xpath->query('.//tbody/tr', $table) ?: [] as $node) {
            $cellNodes = $xpath->query('./th|./td', $node);
            if ($cellNodes === false || $cellNodes->length < 16) {
                continue;
            }

            $cells = [];
            $hrefs = [];
            foreach ($cellNodes as $cellNode) {
                $cells[] = $this->htmlCellText($cellNode->textContent);
                foreach ($xpath->query('.//a[@href]', $cellNode) ?: [] as $link) {
                    $hrefs[] = $this->absoluteUrl($link->getAttribute('href'), $sourceUrl);
                }
            }

            if (!preg_match('/^CVE-\d{4}-\d+$/', $cells[0] ?? '')) {
                continue;
            }

            $rows[] = [
                'cve_id' => $cells[0],
                'product' => $cells[1],
                'component' => $this->nullable($cells[2] ?? null),
                'protocol' => $this->nullable($cells[3] ?? null),
                'remote_exploit_without_auth' => $this->nullable($cells[4] ?? null),
                'base_score' => $this->nullable($cells[5] ?? null),
                'attack_vector' => $this->nullable($cells[6] ?? null),
                'attack_complexity' => $this->nullable($cells[7] ?? null),
                'privileges_required' => $this->nullable($cells[8] ?? null),
                'user_interaction' => $this->nullable($cells[9] ?? null),
                'scope' => $this->nullable($cells[10] ?? null),
                'confidentiality' => $this->nullable($cells[11] ?? null),
                'integrity' => $this->nullable($cells[12] ?? null),
                'availability' => $this->nullable($cells[13] ?? null),
                'affected_versions' => $this->nullable($cells[14] ?? null),
                'notes' => $this->nullable($cells[15] ?? null),
                'cpu_cycle' => $cycle,
                'source_url' => $sourceUrl,
                'hrefs' => array_values(array_unique($hrefs)),
            ];
        }

        return $rows;
    }

    private function htmlCellText(string $value): string
    {
        $value = str_replace(["\xc2\xa0", "Un-\nchanged", "Un- changed"], [' ', 'Unchanged', 'Unchanged'], $value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    private function oracleCpuCycleFromHtml(DOMXPath $xpath, string $sourceUrl): string
    {
        foreach ($xpath->query('//h1|//h2|//h3') ?: [] as $node) {
            $text = $this->htmlCellText($node->textContent);
            if (preg_match('/Oracle Critical Patch Update Advisory\s*-\s*(.+)$/i', $text, $m) === 1) {
                return trim($m[1]);
            }
        }

        if (preg_match('/cpu([a-z]{3})(\d{4})\.html/i', $sourceUrl, $m) === 1) {
            $months = [
                'jan' => 'January',
                'apr' => 'April',
                'jul' => 'July',
                'oct' => 'October',
            ];
            $month = $months[strtolower($m[1])] ?? strtoupper($m[1]);
            return $month . ' ' . $m[2];
        }

        return 'unknown';
    }

    /** @return list<array<string,string|null>> */
    private function awsAuroraRows(): array
    {
        $url = 'https://docs.aws.amazon.com/AmazonRDS/latest/AuroraMySQLReleaseNotes/AuroraMySQL.CVE_list.html';
        $html = $this->httpGet($url);

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $rows = [];
        foreach ($xpath->query('//li[contains(@class, "listitem")]') ?: [] as $node) {
            $text = trim(preg_replace('/\s+/', ' ', $node->textContent) ?? '');
            if (!preg_match('/CVE-\d{4}-\d+/', $text, $m)) {
                continue;
            }

            $versions = [];
            foreach ($xpath->query('.//a', $node) ?: [] as $link) {
                $label = trim($link->textContent);
                if (preg_match('/^\d+\.\d+\.\d+$/', $label) === 1) {
                    $versions[] = $label;
                }
            }

            $rows[] = [
                'cve_id' => $m[0],
                'fixed_versions' => $versions === [] ? null : implode(', ', array_unique($versions)),
                'source_url' => $url,
                'text' => $text,
            ];
        }

        return $rows;
    }

    /** @return list<array<string,string|null>> */
    private function awsSecurityRssRows(): array
    {
        $feedUrl = 'https://aws.amazon.com/security/security-bulletins/rss/feed/';
        $feed = $this->httpGet($feedUrl);
        preg_match_all('#<link>([^<]+)</link>#', $feed, $matches);
        $links = array_values(array_filter(array_unique($matches[1] ?? []), static function (string $url): bool {
            return str_starts_with($url, 'https://aws.amazon.com/security/security-bulletins/rss/');
        }));

        $rows = [];
        foreach ($links as $link) {
            $html = $this->httpGet($link);
            $plain = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html))) ?? '');
            if (!preg_match_all('/CVE-\d{4}-\d+/', $plain, $cves)) {
                continue;
            }

            if (!preg_match('/\b(RDS|Aurora|MySQL|MariaDB|PostgreSQL|database|DB)\b/i', $plain)) {
                continue;
            }

            $bulletinId = trim(basename(parse_url($link, PHP_URL_PATH) ?: $link), '/');
            foreach (array_unique($cves[0]) as $cveId) {
                $rows[] = [
                    'bulletin_id' => $bulletinId,
                    'cve_id' => $cveId,
                    'service' => 'AWS',
                    'product' => str_contains(strtolower($plain), 'aurora') ? 'Amazon Aurora' : 'AWS database service',
                    'engine' => str_contains(strtolower($plain), 'mysql') ? 'mysql-compatible' : null,
                    'source_url' => $link,
                    'excerpt' => mb_substr($plain, 0, 2000),
                ];
            }

            $this->pause();
        }

        return $rows;
    }

    private function oracleCpuCycle(string $advisory): ?string
    {
        if (preg_match('/Critical Patch Update\s+(.+)$/i', $advisory, $m) === 1) {
            return trim($m[1]);
        }

        return $this->nullable($advisory);
    }

    private function mariadbBranch(string $version): ?string
    {
        if (preg_match('/^(\d+\.\d+)/', $version, $m) === 1) {
            return $m[1];
        }

        return null;
    }

    private function httpGet(string $url, array $headers = []): string
    {
        $ch = curl_init($url);
        $defaultHeaders = ['User-Agent: PmaControl-CVE-Backfill/1.0'];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => $this->httpTimeout,
            CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers),
        ]);

        $response = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (!is_string($response) || $code < 200 || $code >= 300) {
            throw new RuntimeException("HTTP GET failed {$code} for {$url}: {$error}");
        }

        return $response;
    }

    private function httpPostJson(string $url, array $payload): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => $this->httpTimeout,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $this->json($payload),
            CURLOPT_HTTPHEADER => [
                'User-Agent: PmaControl-CVE-Backfill/1.0',
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (!is_string($response) || $code < 200 || $code >= 300) {
            throw new RuntimeException("HTTP POST failed {$code} for {$url}: {$error}");
        }

        return $response;
    }

    private function download(string $url, string $name): string
    {
        $path = $this->cacheDir . '/' . $name;
        if (is_file($path) && filesize($path) > 0) {
            return $path;
        }

        $this->line("download {$url}");
        $body = $this->httpGet($url);
        file_put_contents($path, $body);
        return $path;
    }

    private function decodeJson(string $json, string $source): array
    {
        $payload = json_decode($json, true);
        if (!is_array($payload)) {
            throw new RuntimeException("Invalid JSON from {$source}: " . json_last_error_msg());
        }

        return $payload;
    }

    private function json(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function jsonOrNull(mixed $value): ?string
    {
        if ($value === null || $value === []) {
            return null;
        }

        return $this->json($value);
    }

    private function dateTime(mixed $value): ?string
    {
        if (!is_scalar($value) || trim((string)$value) === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable((string)$value))->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }

    private function dateOnly(mixed $value): ?string
    {
        $dateTime = $this->dateTime($value);
        return $dateTime === null ? null : substr($dateTime, 0, 10);
    }

    private function nullable(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function absoluteUrl(string $url, string $base): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            $parts = parse_url($base);
            return ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? 'localhost') . $url;
        }

        return rtrim($base, '/') . '/' . ltrim($url, '/');
    }

    private function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    private function ensureCacheDir(): void
    {
        if (!is_dir($this->cacheDir) && !mkdir($this->cacheDir, 0775, true) && !is_dir($this->cacheDir)) {
            throw new RuntimeException("Cannot create cache directory {$this->cacheDir}");
        }
    }

    private function pause(): void
    {
        if ($this->sleepMicroseconds > 0) {
            usleep($this->sleepMicroseconds);
        }
    }

    private function line(string $message): void
    {
        if ($this->verbose) {
            echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        }
    }

    public static function options(array $argv): array
    {
        $currentYear = (int)date('Y');
        $options = [
            'database' => getenv('PMACONTROL_DB_NAME') ?: 'pmacontrol',
            'host' => getenv('PMACONTROL_DB_HOST') ?: null,
            'socket' => getenv('PMACONTROL_DB_SOCKET') ?: null,
            'user' => getenv('PMACONTROL_DB_USER') ?: null,
            'password' => getenv('PMACONTROL_DB_PASSWORD') ?: null,
            'defaults-file' => null,
            'source' => self::SOURCE_ALL,
            'nvd-start-year' => 2004,
            'nvd-end-year' => $currentYear,
            'cache-dir' => __DIR__ . '/../tmp/cve-source-cache',
            'http-timeout' => 60,
            'sleep-us' => 200000,
            'verbose' => true,
        ];

        foreach (array_slice($argv, 1) as $arg) {
            if ($arg === '--quiet') {
                $options['verbose'] = false;
                continue;
            }

            if ($arg === '--help' || $arg === '-h') {
                self::usage();
                exit(0);
            }

            if (!str_starts_with($arg, '--') || !str_contains($arg, '=')) {
                throw new InvalidArgumentException("Invalid argument: {$arg}");
            }

            [$key, $value] = explode('=', substr($arg, 2), 2);
            if (!array_key_exists($key, $options)) {
                throw new InvalidArgumentException("Unknown option: {$key}");
            }

            $options[$key] = $value;
        }

        $defaultsFile = $options['defaults-file'] ?: (is_file(getenv('HOME') . '/.my.cnf') ? getenv('HOME') . '/.my.cnf' : null);
        if (is_string($defaultsFile) && $defaultsFile !== '') {
            $options = self::mergeMysqlDefaults($options, $defaultsFile);
        }

        $options['nvd-start-year'] = max(2002, (int)$options['nvd-start-year']);
        $options['nvd-end-year'] = min($currentYear, max((int)$options['nvd-start-year'], (int)$options['nvd-end-year']));

        return $options;
    }

    public static function pdo(array $options): PDO
    {
        $database = (string)$options['database'];
        $user = (string)($options['user'] ?? '');
        $password = (string)($options['password'] ?? '');
        if (!empty($options['socket'])) {
            $dsn = 'mysql:unix_socket=' . $options['socket'] . ';dbname=' . $database . ';charset=utf8mb4';
        } else {
            $host = (string)($options['host'] ?: 'localhost');
            $dsn = 'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4';
        }

        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    private static function mergeMysqlDefaults(array $options, string $path): array
    {
        if (!is_readable($path)) {
            return $options;
        }

        $parsed = parse_ini_file($path, true, INI_SCANNER_RAW);
        if (!is_array($parsed)) {
            return $options;
        }

        $client = is_array($parsed['client'] ?? null) ? $parsed['client'] : [];
        foreach (['user', 'password', 'host', 'socket'] as $key) {
            if (($options[$key] ?? null) === null && isset($client[$key]) && is_scalar($client[$key])) {
                $options[$key] = self::unquoteMysqlOption((string)$client[$key]);
            }
        }

        return $options;
    }

    private static function unquoteMysqlOption(string $value): string
    {
        $value = trim($value);
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === "'" && $last === "'") || ($first === '"' && $last === '"')) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }

    private static function usage(): void
    {
        echo "Usage: php script/cve_source_backfill.php [--database=pmacontrol] [--source=all|nvd,cisa_kev] [--defaults-file=/path/.my.cnf]\n";
    }
}

if (PHP_SAPI === 'cli' && realpath((string)($argv[0] ?? '')) === __FILE__) {
    try {
        $options = CveSourceBackfill::options($argv);
        $loader = new CveSourceBackfill(CveSourceBackfill::pdo($options), $options);
        exit($loader->run($options));
    } catch (Throwable $e) {
        fwrite(STDERR, '[ERROR] ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
