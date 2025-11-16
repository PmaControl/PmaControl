<?php

use PHPUnit\Framework\TestCase;
use App\Controller\Docker;
use App\Library\Ssh;

require_once __DIR__ . '/../FakeSsh.php';

class DockerTest extends TestCase
{
    protected function setUp(): void
    {
        Docker::$logger = new \Monolog\Logger('Test'); // évite erreurs logger null
    }

    public function testPushConfig_NoChangeNeeded()
    {
        // Simule : BIND_ADDRESS déjà correct, READ_ONLY NO
        $fakeSsh = new FakeSsh([
            '/mysql -uroot -Nse/' => "BIND_ADDRESS 0.0.0.0 NO",
        ]);

        // Mock Ssh::ssh() pour retourner FakeSsh
        Ssh::setMockInstance($fakeSsh);

        $res = Docker::pushConfig([1, 'docker', 'bind_address', '0.0.0.0']);
        $this->assertTrue($res, "Expected no change needed case to return true");
    }

    public function testPushConfig_SetGlobal_LiveChange()
    {
        // Simule : variable est modifiable (READ_ONLY NO), mais différente
        $fakeSsh = new FakeSsh([
            '/mysql -uroot -Nse/' => "BIND_ADDRESS 127.0.0.1 NO",
            '/SET GLOBAL/'        => "",
        ]);

        Ssh::setMockInstance($fakeSsh);

        $res = Docker::pushConfig([1, 'docker', 'bind_address', '0.0.0.0']);
        $this->assertTrue($res, "Expected SET GLOBAL rewrite to succeed");
        $this->assertStringContainsString("SET GLOBAL bind_address", implode("\n", $fakeSsh->executed));
    }

    public function testPushConfig_ReadOnly_ConfigFileCreation()
    {
        // Simule : READ_ONLY YES -> on doit créer le fichier + restart mariadb
        $fakeSsh = new FakeSsh([
            '/mysql -uroot -Nse/'  => "BIND_ADDRESS 127.0.0.1 YES",
            '/base64 -d/'          => "",
            '/systemctl restart/'  => "",
        ]);
        
        Ssh::setMockInstance($fakeSsh);

        $res = Docker::pushConfig([1, 'docker', 'bind-address', '0.0.0.0']);
        $this->assertTrue($res, "Expected read-only config push to succeed");
        $this->assertStringContainsString("99-pmacontrol-bind-address.cnf", implode("\n", $fakeSsh->executed));
    }
}