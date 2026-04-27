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
}
