<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ControlProxySqlMd5InvalidationTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(dirname(__DIR__, 2) . '/App/Controller/Control.php');
    }

    public function testRefreshVariableAlsoInvalidatesProxySqlStructuralMd5Cache(): void
    {
        self::assertStringContainsString('EngineV4::cleanProxySqlStructuralMd5()', $this->source);
        self::assertStringContainsString('ProxySQL structural MD5 files deleted', $this->source);
    }
}
