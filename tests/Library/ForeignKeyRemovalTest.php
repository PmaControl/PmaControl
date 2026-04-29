<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @see https://git.istosia.com/pmacontrol/pmacontrol/issues/479
 */
final class ForeignKeyRemovalTest extends TestCase
{
    public function testDeadForeignKeyLibraryIsRemoved(): void
    {
        $root = dirname(__DIR__, 2);

        $this->assertFileDoesNotExist($root . '/App/Library/ForeignKey.php');
    }

    public function testGeneratedDocumentationNowPointsToForeignKeyController(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'documentation/Controller~Library/ForeignKey.md',
            'documentation/controller/ForeignKey.md',
        ] as $path) {
            $content = (string) file_get_contents($root . '/' . $path);

            $this->assertStringContainsString('- Namespace: `App\\Controller`', $content, $path);
            $this->assertStringContainsString('- Source: `App/Controller/ForeignKey.php`', $content, $path);
            $this->assertStringContainsString('`settingPrefix($param)`', $content, $path);
            $this->assertStringNotContainsString('App/Library/ForeignKey.php', $content, $path);
            $this->assertStringNotContainsString('`getPath($table_a, $table_b)`', $content, $path);
        }
    }

    public function testControllerLibraryIndexNoLongerHasForeignKeyDuplicate(): void
    {
        $root = dirname(__DIR__, 2);
        $content = (string) file_get_contents($root . '/documentation/Controller~Library/README.md');

        $this->assertSame(1, substr_count($content, '## ForeignKey'));
        $this->assertMatchesRegularExpression(
            '/## ForeignKey\\R\\R- Kind: class\\R- Summary: `documentation\\/Controller~Library\\/ForeignKey\\.md`\\R- Methods: 31\\R/',
            $content
        );
    }

    public function testActiveDocumentationDoesNotReferenceRemovedForeignKeyLibrary(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'documentation/Controller~Library/README.md',
            'documentation/controller/controllers_master.md',
            'documentation/controller/controllers_documentation.html',
        ] as $path) {
            $content = (string) file_get_contents($root . '/' . $path);

            $this->assertStringNotContainsString('App/Library/ForeignKey.php', $content, $path);
            $this->assertStringNotContainsString('`getPath($table_a, $table_b)`', $content, $path);
            $this->assertStringNotContainsString('<code>getPath($table_a, $table_b)</code>', $content, $path);
        }
    }

    public function testApplicationCodeDoesNotReferenceRemovedForeignKeyLibrary(): void
    {
        $root = dirname(__DIR__, 2);
        $removedClass = 'App\\Library' . '\\ForeignKey';
        $removedPath = 'App/Library/ForeignKey.php';
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root . '/App', FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = (string) file_get_contents($file->getPathname());

            $this->assertStringNotContainsString($removedClass, $content, $file->getPathname());
            $this->assertStringNotContainsString($removedPath, $content, $file->getPathname());
        }
    }
}
