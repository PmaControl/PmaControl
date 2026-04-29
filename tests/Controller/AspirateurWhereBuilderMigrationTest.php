<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AspirateurWhereBuilderMigrationTest extends TestCase
{
    public function testRemoteInformationSchemaProbeUsesSharedWhereBuilder(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Aspirateur.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('use App\\Library\\Sql\\WhereBuilder;', $controller);
        $this->assertStringContainsString('$where = WhereBuilder::where($db, $filters);', $controller);
        $this->assertStringContainsString(
            '$sql = "SELECT 1 FROM information_schema.".$table." ".$where." LIMIT 1";',
            $controller
        );
        $this->assertStringNotContainsString('$where[] = $column."', $controller);
    }
}
