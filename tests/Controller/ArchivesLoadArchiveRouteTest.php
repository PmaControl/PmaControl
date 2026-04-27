<?php

declare(strict_types=1);

use App\Controller\Archives;
use PHPUnit\Framework\TestCase;

final class ArchivesLoadArchiveRouteTest extends TestCase
{
    public function testLoadArchiveIsNoLongerRoutableControllerAction(): void
    {
        $this->assertFalse(method_exists(Archives::class, 'load_archive'));
    }

    public function testArchiveRestoreUsesInternalArchiveLoaderDispatcher(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Archives.php');
        $loader = (string) file_get_contents(__DIR__ . '/../../App/Library/Archive/ArchiveLoader.php');

        $this->assertStringContainsString('use App\\Library\\Archive\\ArchiveLoader;', $controller);
        $this->assertStringContainsString('ArchiveLoader::dispatch($this, $restore);', $controller);
        $this->assertStringNotContainsString('public function load_archive', $controller);

        $this->assertStringContainsString('final class ArchiveLoader', $loader);
        $this->assertStringContainsString('public static function dispatch(Archives $controller, array $payload): void', $loader);
        $this->assertStringContainsString('$controller->load(array($idArchiveLoad));', $loader);
        $this->assertStringContainsString('Archives load ".$idArchiveLoad', $loader);
    }
}
