<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CveSourceBackfillScriptTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        $this->script = (string)file_get_contents(__DIR__ . '/../../script/cve_source_backfill.php');
        require_once __DIR__ . '/../../script/cve_source_backfill.php';
    }

    public function testScriptReferencesAllRawSourceTables(): void
    {
        foreach ([
            'cve_source_nvd',
            'cve_source_osv',
            'cve_source_ghsa',
            'cve_source_cisa_kev',
            'cve_source_oracle_cpu',
            'cve_source_mariadb_security',
            'cve_source_percona_advisory',
            'cve_source_component_ghsa',
            'cve_source_aws_security_bulletin',
        ] as $table) {
            self::assertStringContainsString($table, $this->script);
        }
    }

    public function testMysqlDefaultsFileRemovesQuotedPassword(): void
    {
        $defaults = tempnam(sys_get_temp_dir(), 'pmacontrol-my-cnf-');
        self::assertIsString($defaults);

        file_put_contents($defaults, "[client]\nuser=backfill\npassword='quoted-secret'\nhost=127.0.0.1\n");

        try {
            $options = CveSourceBackfill::options([
                'script/cve_source_backfill.php',
                '--defaults-file=' . $defaults,
                '--database=pmacontrol_test',
                '--source=cisa_kev',
                '--nvd-start-year=2004',
                '--nvd-end-year=2004',
            ]);
        } finally {
            unlink($defaults);
        }

        self::assertSame('backfill', $options['user']);
        self::assertSame('quoted-secret', $options['password']);
        self::assertSame('127.0.0.1', $options['host']);
        self::assertSame('pmacontrol_test', $options['database']);
        self::assertSame('cisa_kev', $options['source']);
        self::assertSame(2004, $options['nvd-start-year']);
        self::assertSame(2004, $options['nvd-end-year']);
    }

    public function testScriptDocumentsOfficialSourceEndpoints(): void
    {
        foreach ([
            'https://nvd.nist.gov/feeds/json/cve/2.0/',
            'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json',
            'https://api.osv.dev/v1/query',
            'https://api.github.com/advisories',
            'https://www.oracle.com/security-alerts/public-vuln-to-advisory-mapping.html',
            'https://mariadb.com/docs/server/security/cve/community-server',
            'https://docs.aws.amazon.com/AmazonRDS/latest/AuroraMySQLReleaseNotes/AuroraMySQL.CVE_list.html',
        ] as $endpoint) {
            self::assertStringContainsString($endpoint, $this->script);
        }
    }
}
