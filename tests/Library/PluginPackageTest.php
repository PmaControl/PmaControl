<?php

namespace Tests\Library;

require_once dirname(__DIR__, 2).'/App/Library/PluginPackage.php';

use App\Library\PluginPackage;
use PHPUnit\Framework\TestCase;

final class PluginPackageTest extends TestCase
{
    private $tmpRoot;

    protected function setUp(): void
    {
        $this->tmpRoot = sys_get_temp_dir().'/pmacontrol-plugin-test-'.uniqid();
        mkdir($this->tmpRoot.'/plugin/src/App/Controller', 0777, true);
        mkdir($this->tmpRoot.'/plugin/sql', 0777, true);
        file_put_contents($this->tmpRoot.'/plugin/src/App/Controller/Demo.php', '<?php echo "demo";');
        file_put_contents($this->tmpRoot.'/plugin/sql/install.sql', 'CREATE TABLE demo(id int);');
        file_put_contents($this->tmpRoot.'/plugin/sql/uninstall.sql', 'DROP TABLE demo;');
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->tmpRoot);
    }

    public function testLoadManifestNormalizesFilesAndPhaseSections(): void
    {
        file_put_contents($this->tmpRoot.'/plugin/plugin.json', json_encode(array(
            'files' => array(
                array(
                    'source' => 'src/App/Controller/Demo.php',
                    'destination' => 'App/Controller/Demo.php',
                ),
            ),
            'ddl' => array(
                'install' => array('sql/install.sql'),
                'uninstall' => array('sql/uninstall.sql'),
            ),
        )));

        $manifest = PluginPackage::load($this->tmpRoot.'/plugin');

        $this->assertSame('src/App/Controller/Demo.php', $manifest['files'][0]['source']);
        $this->assertSame('App/Controller/Demo.php', $manifest['files'][0]['destination']);
        $this->assertSame(array('sql/install.sql'), $manifest['ddl']['install']);
        $this->assertSame(array('sql/uninstall.sql'), $manifest['ddl']['uninstall']);
    }

    public function testInstallCopiesDeclaredFilesAndReturnsSqlPlan(): void
    {
        $manifest = PluginPackage::normalizeManifest(array(
            'files' => array(
                array(
                    'source' => 'src/App/Controller/Demo.php',
                    'destination' => 'App/Controller/Demo.php',
                ),
            ),
            'ddl' => array(
                'install' => array('sql/install.sql'),
            ),
        ));

        $plan = PluginPackage::install($manifest, $this->tmpRoot.'/plugin', $this->tmpRoot.'/project');

        $this->assertFileExists($this->tmpRoot.'/project/App/Controller/Demo.php');
        $this->assertSame($this->tmpRoot.'/plugin/sql/install.sql', $plan['sql'][0]);
        $this->assertSame($this->tmpRoot.'/project/App/Controller/Demo.php', $plan['files'][0]['destination']);
    }

    public function testUninstallRemovesDeclaredFilesAndReturnsUninstallSqlPlan(): void
    {
        mkdir($this->tmpRoot.'/project/App/Controller', 0777, true);
        file_put_contents($this->tmpRoot.'/project/App/Controller/Demo.php', 'demo');
        $manifest = PluginPackage::normalizeManifest(array(
            'files' => array(
                array(
                    'source' => 'src/App/Controller/Demo.php',
                    'destination' => 'App/Controller/Demo.php',
                ),
            ),
            'ddl' => array(
                'uninstall' => array('sql/uninstall.sql'),
            ),
        ));

        $plan = PluginPackage::uninstall($manifest, $this->tmpRoot.'/plugin', $this->tmpRoot.'/project');

        $this->assertFileDoesNotExist($this->tmpRoot.'/project/App/Controller/Demo.php');
        $this->assertSame($this->tmpRoot.'/plugin/sql/uninstall.sql', $plan['sql'][0]);
    }

    public function testRejectsPathTraversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PluginPackage::install(PluginPackage::normalizeManifest(array(
            'files' => array(
                array(
                    'source' => '../outside.php',
                    'destination' => 'App/Controller/Outside.php',
                ),
            ),
        )), $this->tmpRoot.'/plugin', $this->tmpRoot.'/project');
    }

    public function testRejectsExistingDestination(): void
    {
        mkdir($this->tmpRoot.'/project/App/Controller', 0777, true);
        file_put_contents($this->tmpRoot.'/project/App/Controller/Demo.php', 'existing');

        $this->expectException(\RuntimeException::class);

        PluginPackage::install(PluginPackage::normalizeManifest(array(
            'files' => array(
                array(
                    'source' => 'src/App/Controller/Demo.php',
                    'destination' => 'App/Controller/Demo.php',
                ),
            ),
        )), $this->tmpRoot.'/plugin', $this->tmpRoot.'/project');
    }

    public function testArchiveExtensionFromUrlRecognizesPmactrl(): void
    {
        $this->assertSame('pmactrl', PluginPackage::archiveExtensionFromUrl('https://example.test/eol.pmactrl?download=1'));
        $this->assertSame('zip', PluginPackage::archiveExtensionFromUrl('https://example.test/mysql-sys.zip'));
        $this->assertSame('zip', PluginPackage::archiveExtensionFromUrl('https://example.test/no-extension'));
    }

    public function testExtractPmactrlArchiveUsesDebLikeDataTar(): void
    {
        $archive = $this->tmpRoot.'/eol.pmactrl';
        $this->writePmactrlArchive($archive, array(
            'eol-1.0.0/plugin.json' => '{"name":"eol"}',
            'eol-1.0.0/src/App/Controller/Eol.php' => '<?php class DemoEol {}',
        ));

        PluginPackage::extractPmactrl($archive, $this->tmpRoot.'/extracted');

        $this->assertFileExists($this->tmpRoot.'/extracted/eol-1.0.0/plugin.json');
        $this->assertFileExists($this->tmpRoot.'/extracted/eol-1.0.0/src/App/Controller/Eol.php');
        $this->assertSame('{"name":"eol"}', file_get_contents($this->tmpRoot.'/extracted/eol-1.0.0/plugin.json'));
    }

    public function testMysqlSysExamplePackageDeclaresLogoAndDeployPlan(): void
    {
        $pluginDirectory = dirname(__DIR__, 2).'/plugins/extracted/mysql-sys-1.2';

        $manifest = PluginPackage::load($pluginDirectory);
        $destinations = array_column($manifest['files'], 'destination');

        $this->assertSame('mysql-sys', $manifest['name']);
        $this->assertSame('logo.svg', $manifest['picture']);
        $this->assertFileExists($pluginDirectory.'/'.$manifest['picture']);
        $this->assertContains('App/Controller/Mysqlsys.php', $destinations);
        $this->assertContains('App/view/Mysqlsys/index.view.php', $destinations);
        $this->assertContains('App/view/Mysqlsys/install.view.php', $destinations);
        $this->assertContains('App/Webroot/image/plugin/mysql-sys.svg', $destinations);
        $this->assertSame(array('sql/install.sql'), $manifest['sql']['install']);
        $this->assertSame(array('sql/uninstall.sql'), $manifest['sql']['uninstall']);
    }

    public function testMysqlSysExamplePackageInstallsAndUninstallsInSandbox(): void
    {
        $pluginDirectory = dirname(__DIR__, 2).'/plugins/extracted/mysql-sys-1.2';
        $manifest = PluginPackage::load($pluginDirectory);
        $projectRoot = $this->tmpRoot.'/project';

        $installPlan = PluginPackage::install($manifest, $pluginDirectory, $projectRoot);

        $this->assertFileExists($projectRoot.'/App/Controller/Mysqlsys.php');
        $this->assertFileExists($projectRoot.'/App/view/Mysqlsys/index.view.php');
        $this->assertFileExists($projectRoot.'/App/view/Mysqlsys/install.view.php');
        $this->assertFileExists($projectRoot.'/App/Webroot/image/plugin/mysql-sys.svg');
        $this->assertSame($pluginDirectory.'/sql/install.sql', $installPlan['sql'][0]);

        PluginPackage::uninstall($manifest, $pluginDirectory, $projectRoot);

        $this->assertFileDoesNotExist($projectRoot.'/App/Controller/Mysqlsys.php');
        $this->assertFileDoesNotExist($projectRoot.'/App/view/Mysqlsys/index.view.php');
        $this->assertFileDoesNotExist($projectRoot.'/App/view/Mysqlsys/install.view.php');
        $this->assertFileDoesNotExist($projectRoot.'/App/Webroot/image/plugin/mysql-sys.svg');
    }

    private function removeTree($path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (array_diff(scandir($path), array('.', '..')) as $entry) {
            $child = $path.DIRECTORY_SEPARATOR.$entry;

            if (is_dir($child)) {
                $this->removeTree($child);
            } else {
                unlink($child);
            }
        }

        rmdir($path);
    }

    private function writePmactrlArchive(string $path, array $dataFiles): void
    {
        $controlTarGz = gzencode($this->buildTar(array(
            'control' => "Package: eol\nVersion: 1.0.0\nArchitecture: all\n",
        )));
        $dataTarGz = gzencode($this->buildTar($dataFiles));

        file_put_contents(
            $path,
            "!<arch>\n"
            .$this->arEntry('debian-binary', "2.0\n")
            .$this->arEntry('control.tar.gz', $controlTarGz)
            .$this->arEntry('data.tar.gz', $dataTarGz)
        );
    }

    private function arEntry(string $name, string $contents): string
    {
        $header = str_pad($name.'/', 16)
            .str_pad((string)time(), 12)
            .str_pad('0', 6)
            .str_pad('0', 6)
            .str_pad('100644', 8)
            .str_pad((string)strlen($contents), 10)
            ."`\n";

        return $header.$contents.(strlen($contents) % 2 === 1 ? "\n" : '');
    }

    private function buildTar(array $files): string
    {
        $tar = '';
        foreach ($files as $name => $contents) {
            $tar .= $this->tarFileEntry((string)$name, (string)$contents);
        }

        return $tar.str_repeat("\0", 1024);
    }

    private function tarFileEntry(string $name, string $contents): string
    {
        $header = str_pad($name, 100, "\0")
            .str_pad(sprintf('%07o', 0644), 8, "\0", STR_PAD_LEFT)
            .str_pad(sprintf('%07o', 0), 8, "\0", STR_PAD_LEFT)
            .str_pad(sprintf('%07o', 0), 8, "\0", STR_PAD_LEFT)
            .str_pad(sprintf('%011o', strlen($contents)), 12, "\0", STR_PAD_LEFT)
            .str_pad(sprintf('%011o', time()), 12, "\0", STR_PAD_LEFT)
            .'        '
            .'0'
            .str_repeat("\0", 100)
            .'ustar'."\0"
            .'00'
            .str_repeat("\0", 255);

        $header = substr($header, 0, 512);
        $checksum = 0;
        for ($i = 0; $i < 512; $i++) {
            $checksum += ord($header[$i]);
        }

        $header = substr($header, 0, 148)
            .str_pad(sprintf('%06o', $checksum), 6, '0', STR_PAD_LEFT)."\0 "
            .substr($header, 156);

        return $header.$contents.str_repeat("\0", (512 - (strlen($contents) % 512)) % 512);
    }
}
