<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InstallerNetworkHardeningTest extends TestCase
{
    private string $helper;
    private string $ubuntu2604;
    private string $debian13;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 2);

        $this->helper = (string) file_get_contents($root . '/install/lib/harden_network.sh');
        $this->ubuntu2604 = (string) file_get_contents($root . '/install/ubuntu26.04.sh');
        $this->debian13 = (string) file_get_contents($root . '/install/debian13.sh');
    }

    public function testSharedHelperDefinesMariaDbBindAddressHardening(): void
    {
        self::assertStringContainsString('pmactrl_harden_mariadb_bind()', $this->helper);
        self::assertStringContainsString('/etc/mysql/mariadb.conf.d/90-pmacontrol-network.cnf', $this->helper);
        self::assertStringContainsString('bind-address = ${bind_address}', $this->helper);
        self::assertStringContainsString('127.0.0.1,::1', $this->helper);
        self::assertStringContainsString('cmp -s "${tmp_file}" "${target}"', $this->helper);
        self::assertStringContainsString('systemctl restart mariadb', $this->helper);
    }

    public function testSharedHelperHandlesLocalAndRemoteDatabaseHosts(): void
    {
        self::assertStringContainsString('pmactrl_network_is_local_db_host()', $this->helper);
        self::assertStringContainsString('"127.0.0.1"|"localhost"|"::1"|"[::1]"', $this->helper);
        self::assertStringContainsString('PMACTRL_HARDEN_DB_BIND:-1', $this->helper);
        self::assertStringContainsString('"auto"|"")', $this->helper);
        self::assertStringContainsString('skipping MariaDB bind-address hardening', $this->helper);
    }

    public function testSharedHelperDefinesRpcbindPoliciesAndNfsBypass(): void
    {
        self::assertStringContainsString('pmactrl_harden_rpcbind()', $this->helper);
        self::assertStringContainsString('"disable"|"mask"', $this->helper);
        self::assertStringContainsString('"leave"', $this->helper);
        self::assertStringContainsString('systemctl disable --now', $this->helper);
        self::assertStringContainsString('systemctl mask --now', $this->helper);
        self::assertStringContainsString('rpcbind.socket rpcbind.service', $this->helper);
        self::assertStringContainsString('dpkg-query -W', $this->helper);
        self::assertStringContainsString('nfs-common', $this->helper);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function installerScripts(): array
    {
        return [
            'ubuntu26.04' => ['ubuntu2604'],
            'debian13' => ['debian13'],
        ];
    }

    #[DataProvider('installerScripts')]
    public function testInstallersSourceSharedNetworkHardeningHelper(string $scriptName): void
    {
        $script = $this->{$scriptName};

        self::assertStringContainsString('lib/harden_network.sh', $script);
        self::assertStringContainsString('PMACTRL_HARDEN_DB_BIND="${PMACTRL_HARDEN_DB_BIND:-1}"', $script);
        self::assertStringContainsString('PMACTRL_DB_BIND_ADDRESS="${PMACTRL_DB_BIND_ADDRESS:-127.0.0.1,::1}"', $script);
        self::assertStringContainsString('PMACTRL_RPCBIND_POLICY="${PMACTRL_RPCBIND_POLICY:-disable}"', $script);
        self::assertStringContainsString('pmactrl_harden_mariadb_bind', $script);
        self::assertStringContainsString('pmactrl_harden_rpcbind', $script);
        self::assertStringNotContainsString('apt-get install -y rpcbind', $script);
        self::assertStringNotContainsString('apt-get install -y nfs-common', $script);
    }

    public function testUbuntuInstallerHardensNetworkAfterMariaDbConfiguration(): void
    {
        self::assertMatchesRegularExpression(
            '/configure_mariadb\s+pmactrl_harden_mariadb_bind\s+pmactrl_harden_rpcbind\s+configure_php/s',
            $this->ubuntu2604
        );
    }

    public function testDebian13InstallerHardensNetworkAfterMariaDbInstall(): void
    {
        self::assertMatchesRegularExpression(
            '/install_mariadb\s+pmactrl_harden_mariadb_bind\s+pmactrl_harden_rpcbind\s+install_php/s',
            $this->debian13
        );
    }
}
