<?php

use PHPUnit\Framework\TestCase;
use App\Controller\Partition;
use \Glial\Sgbd\Sql\Mysql;
use \Glial\Sgbd\Sgbd;

class PartitionTest extends TestCase
{
    private $partition;

    protected function setUp(): void
    {
        // Instancier la classe Partition avec les arguments nécessaires
        $this->partition = $this->getMockBuilder(Partition::class)
                                 ->setConstructorArgs(['Controller', 'View', []]) // Fournir les arguments requis
                                 ->onlyMethods(['saveSQLToFile'])
                                 ->getMock();

        // Stub pour éviter la sauvegarde réelle des fichiers
        $this->partition->method('saveSQLToFile')->willReturn(null);
    }
    
    public function testExtractParametersValid()
    {
        $params = [1, 'test_db', 'test_table', 'id', 4, 0.5];
        $result = $this->invokeMethod($this->partition, 'extractParameters', [$params]);

        $this->assertIsArray($result);
        $this->assertEquals('test_db', $result['database']);
        $this->assertEquals('test_table', $result['table_target']);
        $this->assertEquals('id', $result['field']);
        $this->assertEquals(4, $result['nb_partitions']);
        $this->assertEquals(0.5, $result['sample_ratio']);
    }

    public function testExtractParametersInvalid()
    {
        $params = [1, null, null];
        $result = $this->invokeMethod($this->partition, 'extractParameters', [$params]);

        $this->assertNull($result);
    }

    /**
     * Méthode utilitaire pour appeler des méthodes privées/protégées
     */
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Remplace la connexion DB dans Mysql::getDbLink
     */
    private function replaceDbLink($mockDb)
    {
        // Stub pour remplacer la méthode statique Mysql::getDbLink
        $stub = $this->getMockBuilder(\App\Library\Mysql::class)
                     ->disableOriginalConstructor()
                     ->onlyMethods(['getDbLink'])
                     ->getMock();

        $stub->method('getDbLink')->willReturn($mockDb);

        // Remplacer la méthode statique par le stub
        \App\Library\Mysql::class::$getDbLink = function () use ($mockDb) {
            return $mockDb;
        };
    }
}