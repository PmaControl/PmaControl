<?php
use PHPUnit\Framework\TestCase;
use App\Controller\Tunnel;

require_once __DIR__ . '/../../configuration/db.config.php';

class TunnelMysqlServerTest extends TestCase
{
    protected function setUp(): void
    {
        // Réinitialiser le cache avant chaque test
        $reflection = new \ReflectionClass(Tunnel::class);
        $prop = $reflection->getProperty('mysql_server_cache');
        $prop->setAccessible(true);
        $prop->setValue([]);
    }

    public function testReturnsCorrectIdForExistingServer(): void
    {
        // Préparer manuellement le cache pour éviter DB
        $reflection = new \ReflectionClass(Tunnel::class);
        $prop = $reflection->getProperty('mysql_server_cache');
        $prop->setAccessible(true);
        $prop->setValue([
            '127.0.0.1:3306' => 42
        ]);

        $result = Tunnel::getIdMysqlServerByIpPort(['127.0.0.1', 3306]);
        $this->assertEquals(42, $result, 'Should return the cached server ID');
    }

    public function testCacheIsUsedAfterFirstCall(): void
    {
        // Préparer une méthode factice pour preload
        $reflection = new \ReflectionClass(Tunnel::class);
        $method = $reflection->getMethod('preloadMysqlServerCache');
        $method->setAccessible(true);

        // Simuler le cache via preload
        Tunnel::$mysql_server_cache = [
            '10.0.0.1:3306' => 99
        ];

        $result = Tunnel::getIdMysqlServerByIpPort(['10.0.0.1', 3306]);
        $this->assertEquals(99, $result);
    }
}