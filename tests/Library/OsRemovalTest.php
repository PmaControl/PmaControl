<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OsRemovalTest extends TestCase
{
    public function testLegacyOsTraitAndGeneratedSheetAreRemoved(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'App/Library/Os.php',
            'documentation/Controller~Library/Os.md',
        ] as $path) {
            $this->assertFileDoesNotExist($root . '/' . $path);
        }
    }

    public function testActiveGeneratedDocumentationDoesNotReferenceRemovedOsTrait(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'documentation/Controller~Library/README.md',
            'documentation/pmacontrol_tables_documentation.md',
            'documentation/pmacontrol_documentation_master.md',
            'documentation/pmacontrol_documentation.html',
        ] as $path) {
            $content = (string) file_get_contents($root . '/' . $path);

            $this->assertStringNotContainsString('App/Library/Os.php', $content, $path);
            $this->assertStringNotContainsString('documentation/Controller~Library/Os.md', $content, $path);
        }
    }

    public function testApplicationCodeDoesNotReferenceLegacyOsTrait(): void
    {
        $root = dirname(__DIR__, 2);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root . '/App', FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = (string) file_get_contents($file->getPathname());

            $this->assertStringNotContainsString('App\\Library\\Os', $content, $file->getPathname());
            $this->assertStringNotContainsString('use Os;', $content, $file->getPathname());
            $this->assertStringNotContainsString('uaDetection', $content, $file->getPathname());
        }
    }
}
