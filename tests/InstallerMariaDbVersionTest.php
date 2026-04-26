<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InstallerMariaDbVersionTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        $this->script = file_get_contents(__DIR__ . '/../install/debian13.sh');
    }

    public function testDebian13InstallerConfiguresMariaDbRepositoryWithRequestedVersion(): void
    {
        $this->assertStringContainsString(
            '--mariadb-server-version="mariadb-${VERSION_MARIADB}"',
            $this->script
        );
    }

    public function testDebian13InstallerDownloadsRepositorySetupBeforeExecutingIt(): void
    {
        $this->assertStringContainsString('repo_setup_script=$(mktemp)', $this->script);
        $this->assertStringContainsString(
            'curl -fsSL https://r.mariadb.com/downloads/mariadb_repo_setup -o "${repo_setup_script}"',
            $this->script
        );
        $this->assertStringContainsString(
            'bash "${repo_setup_script}" --mariadb-server-version="mariadb-${VERSION_MARIADB}"',
            $this->script
        );
        $this->assertStringNotContainsString('| bash -s -- --mariadb-server-version', $this->script);
    }

    public function testDebian13InstallerPinsMariaDbPackagesToResolvedVersion(): void
    {
        $this->assertMatchesRegularExpression(
            '/resolve_mariadb_package_version\(\).*apt-cache madison mariadb-server/s',
            $this->script
        );

        $this->assertStringContainsString('"mariadb-server=${mariadb_package_version}"', $this->script);
        $this->assertStringContainsString('"mariadb-client=${mariadb_package_version}"', $this->script);
        $this->assertStringContainsString('"mariadb-plugin-rocksdb=${mariadb_package_version}"', $this->script);
        $this->assertStringNotContainsString(
            'apt-get install -y mariadb-server mariadb-client mariadb-plugin-rocksdb',
            $this->script
        );
    }

    public function testDebian13InstallerFailsWhenRequestedMariaDbVersionIsUnavailable(): void
    {
        $this->assertStringContainsString(
            'MariaDB ${VERSION_MARIADB} is not available in the configured APT repositories.',
            $this->script
        );
    }
}
