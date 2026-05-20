<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RenameIndexOrphanedViewTest extends TestCase
{
    public function testRenameIndexOrphanedPostViewIsRemoved(): void
    {
        $this->assertFileDoesNotExist(__DIR__ . '/../../App/Controller/Rename.php');
        $this->assertFileDoesNotExist(__DIR__ . '/../../App/view/Rename/index.view.php');
    }

    public function testRealDatabaseRenameFlowRemainsInDatabaseController(): void
    {
        $databaseRenameView = __DIR__ . '/../../App/view/Database/rename.view.php';
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Database.php');
        $view = file_get_contents($databaseRenameView);

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertFileExists($databaseRenameView);
        $this->assertStringContainsString('public function rename($param)', $controller);
        $this->assertStringContainsString('method="POST"', $view);
    }
}
