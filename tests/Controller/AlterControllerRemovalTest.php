<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @see https://git.istosia.com/pmacontrol/pmacontrol/issues/516
 */
final class AlterControllerRemovalTest extends TestCase
{
    public function testDangerousDeadAlterControllerAndGeneratedSheetsAreRemoved(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'App/Controller/Alter.php',
            'documentation/controller/Alter.md',
            'documentation/Controller~Library/Alter.md',
        ] as $path) {
            $this->assertFileDoesNotExist($root . '/' . $path);
        }
    }

    public function testActiveGeneratedDocumentationDoesNotReferenceRemovedAlterController(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'documentation/controller/README.md',
            'documentation/controller/controllers_master.md',
            'documentation/controller/controllers_documentation.html',
            'documentation/Controller~Library/README.md',
        ] as $path) {
            $content = (string) file_get_contents($root . '/' . $path);

            $this->assertStringNotContainsString('App/Controller/Alter.php', $content, $path);
            $this->assertStringNotContainsString('documentation/Controller~Library/Alter.md', $content, $path);
            $this->assertStringNotContainsString('href="Alter.md"', $content, $path);
            $this->assertDoesNotMatchRegularExpression('/href="#alter(?:-\d+)?"/', $content, $path);
            $this->assertStringNotContainsString('[Alter](Alter.md)', $content, $path);
        }
    }
}
