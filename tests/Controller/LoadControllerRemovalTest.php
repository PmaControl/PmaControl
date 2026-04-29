<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LoadControllerRemovalTest extends TestCase
{
    public function testLegacyLoadControllersAndGeneratedSheetsAreRemoved(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'App/Controller/Load.php',
            'App/Controller/Load2.php',
            'documentation/controller/Load.md',
            'documentation/controller/Load2.md',
            'documentation/Controller~Library/Load.md',
            'documentation/Controller~Library/Load2.md',
        ] as $path) {
            $this->assertFileDoesNotExist($root . '/' . $path);
        }
    }

    public function testActiveGeneratedDocumentationDoesNotReferenceRemovedLoadRoutes(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'documentation/controller/README.md',
            'documentation/controller/controllers_master.md',
            'documentation/controller/controllers_documentation.html',
            'documentation/Controller~Library/README.md',
            'documentation/audit_securite.md',
            'documentation/audit_securite.html',
            'documentation/reverse_engineering_complete.md',
            'documentation/reverse_engineering_complete.html',
            'documentation/pmacontrol_tables_documentation.md',
            'documentation/pmacontrol_documentation_master.md',
            'documentation/pmacontrol_documentation.html',
        ] as $path) {
            $content = (string) file_get_contents($root . '/' . $path);

            $this->assertStringNotContainsString('App/Controller/Load.php', $content, $path);
            $this->assertStringNotContainsString('App/Controller/Load2.php', $content, $path);
            $this->assertStringNotContainsString('/Load/', $content, $path);
            $this->assertStringNotContainsString('/Load2/', $content, $path);
        }
    }
}
