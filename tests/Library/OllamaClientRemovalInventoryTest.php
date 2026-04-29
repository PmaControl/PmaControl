<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @see https://git.istosia.com/pmacontrol/pmacontrol/issues/483
 */
final class OllamaClientRemovalInventoryTest extends TestCase
{
    public function testDeadOllamaClientLibraryAndGeneratedSheetAreRemoved(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'App/Library/Ollama.php',
            'documentation/Controller~Library/OllamaClient.md',
        ] as $path) {
            $this->assertFileDoesNotExist($root . '/' . $path);
        }
    }

    public function testActiveGeneratedDocumentationDoesNotReferenceRemovedOllamaClient(): void
    {
        $root = dirname(__DIR__, 2);

        $content = (string) file_get_contents($root . '/documentation/Controller~Library/README.md');

        $this->assertStringNotContainsString('OllamaClient', $content);
        $this->assertStringNotContainsString('App/Library/Ollama.php', $content);
        $this->assertStringNotContainsString('documentation/Controller~Library/OllamaClient.md', $content);
    }

    public function testApplicationCodeDoesNotReferenceRemovedOllamaClient(): void
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

            $this->assertStringNotContainsString('OllamaClient', $content, $file->getPathname());
            $this->assertStringNotContainsString('App/Library/Ollama.php', $content, $file->getPathname());
        }
    }
}
