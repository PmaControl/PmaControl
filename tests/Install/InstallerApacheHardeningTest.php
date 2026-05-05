<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InstallerApacheHardeningTest extends TestCase
{
    private string $apacheConf;
    private string $helper;
    private string $debian10;
    private string $debian11;
    private string $debian12;
    private string $debian13;
    private string $ubuntu2204;
    private string $ubuntu2604;
    private string $centos9;
    private string $rhel94;
    private string $remoteInstall;
    private string $webExposureCheck;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 2);

        $this->apacheConf = (string) file_get_contents($root . '/install/apache/pmacontrol-docroot.conf');
        $this->helper = (string) file_get_contents($root . '/install/lib/harden_apache.sh');
        $this->debian10 = (string) file_get_contents($root . '/install/debian10.sh');
        $this->debian11 = (string) file_get_contents($root . '/install/debian11.sh');
        $this->debian12 = (string) file_get_contents($root . '/install/debian12.sh');
        $this->debian13 = (string) file_get_contents($root . '/install/debian13.sh');
        $this->ubuntu2204 = (string) file_get_contents($root . '/install/ubuntu22.04.sh');
        $this->ubuntu2604 = (string) file_get_contents($root . '/install/ubuntu26.04.sh');
        $this->centos9 = (string) file_get_contents($root . '/install/centos9.sh');
        $this->rhel94 = (string) file_get_contents($root . '/install/rhel9.4.sh');
        $this->remoteInstall = (string) file_get_contents($root . '/ci/remote-install-and-test.sh');
        $this->webExposureCheck = (string) file_get_contents($root . '/ci/check-web-exposure.sh');
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
        self::assertMatchesRegularExpression(
            '/<Directory \/srv\/www\/site>\s+Require all granted\s+AllowOverride All\s+Options -Indexes \+FollowSymLinks\s+DirectoryIndex App\/Webroot\/index\.php\s+<\/Directory>/',
            $this->apacheConf
        );
        self::assertMatchesRegularExpression(
            '/<Directory \/srv\/www\/site\/App\/Webroot>\s+Require all granted\s+AllowOverride All\s+Options -Indexes \+FollowSymLinks\s+DirectoryIndex index\.php\s+<\/Directory>/',
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
        self::assertStringContainsString('/srv/www/site/', $this->apacheConf);
        self::assertStringContainsString('vendor', $this->apacheConf);
        self::assertStringContainsString('<FilesMatch "^\\.">', $this->apacheConf);
        self::assertStringContainsString('Require all denied', $this->apacheConf);
    }

    public function testApacheConfDeniesVcsMetadataAndDependencyManifests(): void
    {
        self::assertStringContainsString('(?:\\.git|\\.svn|\\.hg|\\.bzr)', $this->apacheConf);
        self::assertStringContainsString('composer\\.(?:json|lock)', $this->apacheConf);
        self::assertStringContainsString('package(?:-lock)?\\.json', $this->apacheConf);
        self::assertStringContainsString('yarn\\.lock', $this->apacheConf);
        self::assertStringContainsString('pnpm-lock\\.yaml', $this->apacheConf);
        self::assertStringContainsString('Gemfile(?:\\.lock)?', $this->apacheConf);
        self::assertStringContainsString('Pipfile(?:\\.lock)?', $this->apacheConf);
        self::assertStringContainsString('\\.env(?:\\..*)?', $this->apacheConf);
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
            'debian10' => ['debian10'],
            'debian11' => ['debian11'],
            'debian12' => ['debian12'],
            'debian13' => ['debian13'],
            'ubuntu22.04' => ['ubuntu2204'],
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
        foreach ([$this->debian10, $this->debian11, $this->debian12, $this->debian13, $this->ubuntu2204, $this->ubuntu2604, $this->centos9, $this->rhel94, $this->remoteInstall] as $script) {
            self::assertStringNotContainsString('Options Indexes', $script);
        }
    }

    public function testRemoteInstallEnablesApacheHardeningAndExposureSmokeCheck(): void
    {
        self::assertStringContainsString('install/lib/harden_apache.sh', $this->remoteInstall);
        self::assertStringContainsString('PMACTRL_HARDEN_APACHE_DOCROOT="${PMACTRL_HARDEN_APACHE_DOCROOT:-1}"', $this->remoteInstall);
        self::assertStringContainsString('pmactrl_harden_apache_docroot', $this->remoteInstall);
        self::assertSame(2, substr_count($this->remoteInstall, 'ci/check-web-exposure.sh http://127.0.0.1 /pmacontrol/'));
    }

    public function testExposureSmokeCheckBlocksInternalPathsButDoesNotBlockSite(): void
    {
        self::assertStringContainsString('"/"', $this->webExposureCheck);
        self::assertStringContainsString('"/?C=N;O=D"', $this->webExposureCheck);
        self::assertStringContainsString('"/infra/"', $this->webExposureCheck);
        self::assertStringContainsString('"/glial/"', $this->webExposureCheck);
        self::assertStringContainsString('"${APP_PATH}.git/config"', $this->webExposureCheck);
        self::assertStringContainsString('"${APP_PATH}configuration/"', $this->webExposureCheck);
        self::assertStringNotContainsString('"/site/"', $this->webExposureCheck);
        self::assertStringContainsString('expect_app_reachable "${APP_PATH}"', $this->webExposureCheck);
    }
}
