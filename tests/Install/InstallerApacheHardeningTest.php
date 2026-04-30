<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InstallerApacheHardeningTest extends TestCase
{
    private string $apacheConf;
    private string $helper;
    private string $debian12;
    private string $debian13;
    private string $ubuntu2604;
    private string $centos9;
    private string $rhel94;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 2);

        $this->apacheConf = (string) file_get_contents($root . '/install/apache/pmacontrol-docroot.conf');
        $this->helper = (string) file_get_contents($root . '/install/lib/harden_apache.sh');
        $this->debian12 = (string) file_get_contents($root . '/install/debian12.sh');
        $this->debian13 = (string) file_get_contents($root . '/install/debian13.sh');
        $this->ubuntu2604 = (string) file_get_contents($root . '/install/ubuntu26.04.sh');
        $this->centos9 = (string) file_get_contents($root . '/install/centos9.sh');
        $this->rhel94 = (string) file_get_contents($root . '/install/rhel9.4.sh');
    }

    public function testApacheConfDeniesSrvWwwByDefaultAndAllowsPmaControlOnly(): void
    {
        self::assertMatchesRegularExpression(
            '/<Directory \/srv\/www>\s+Require all denied\s+Options -Indexes\s+<\/Directory>/',
            $this->apacheConf
        );
        self::assertMatchesRegularExpression(
            '/<Directory \/srv\/www\/pmacontrol>\s+Require all granted\s+AllowOverride All\s+Options -Indexes \+FollowSymLinks\s+<\/Directory>/',
            $this->apacheConf
        );
    }

    public function testApacheConfDeniesInternalApplicationPathsAndDotfiles(): void
    {
        self::assertStringContainsString('App/(?!Webroot', $this->apacheConf);
        self::assertStringContainsString('configuration', $this->apacheConf);
        self::assertStringContainsString('documentation', $this->apacheConf);
        self::assertStringContainsString('install', $this->apacheConf);
        self::assertStringContainsString('tests?', $this->apacheConf);
        self::assertStringContainsString('<FilesMatch "^\\.">', $this->apacheConf);
        self::assertStringContainsString('Require all denied', $this->apacheConf);
    }

    public function testSharedHelperInstallsApacheAndHttpdHardeningConfigs(): void
    {
        self::assertStringContainsString('pmactrl_harden_apache_docroot()', $this->helper);
        self::assertStringContainsString('pmactrl_harden_httpd_docroot()', $this->helper);
        self::assertStringContainsString('/etc/apache2/conf-available/pmacontrol-docroot.conf', $this->helper);
        self::assertStringContainsString('/etc/httpd/conf.d/pmacontrol-docroot.conf', $this->helper);
        self::assertStringContainsString('a2enconf "pmacontrol-docroot"', $this->helper);
        self::assertStringContainsString('PMACTRL_HARDEN_APACHE_DOCROOT:-1', $this->helper);
        self::assertStringContainsString('PMACTRL_DRY_RUN:-0', $this->helper);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function debianUbuntuInstallers(): array
    {
        return [
            'debian12' => ['debian12'],
            'debian13' => ['debian13'],
            'ubuntu26.04' => ['ubuntu2604'],
        ];
    }

    #[DataProvider('debianUbuntuInstallers')]
    public function testDebianAndUbuntuInstallersEnableApacheHardening(string $scriptName): void
    {
        $script = $this->{$scriptName};

        self::assertStringContainsString('lib/harden_apache.sh', $script);
        self::assertStringContainsString('PMACTRL_HARDEN_APACHE_DOCROOT="${PMACTRL_HARDEN_APACHE_DOCROOT:-1}"', $script);
        self::assertStringContainsString('pmactrl_harden_apache_docroot', $script);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function rpmInstallers(): array
    {
        return [
            'centos9' => ['centos9'],
            'rhel9.4' => ['rhel94'],
        ];
    }

    #[DataProvider('rpmInstallers')]
    public function testRpmInstallersEnableHttpdHardening(string $scriptName): void
    {
        $script = $this->{$scriptName};

        self::assertStringContainsString('lib/harden_apache.sh', $script);
        self::assertStringContainsString('PMACTRL_HARDEN_APACHE_DOCROOT="${PMACTRL_HARDEN_APACHE_DOCROOT:-1}"', $script);
        self::assertStringContainsString('pmactrl_harden_httpd_docroot', $script);
        self::assertStringContainsString('Options -Indexes +FollowSymLinks', $script);
        self::assertStringNotContainsString('Options Indexes FollowSymLinks', $script);
    }

    public function testInstallScriptsDoNotEnableDirectoryIndexes(): void
    {
        foreach ([$this->debian12, $this->debian13, $this->ubuntu2604, $this->centos9, $this->rhel94] as $script) {
            self::assertStringNotContainsString('Options Indexes', $script);
        }
    }
}
