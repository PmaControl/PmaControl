<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AspirateurServerCapabilitiesCallSiteTest extends TestCase
{
    public function testDatabaseTemporaryColumnGuardUsesServerCapabilities(): void
    {
        $source = file_get_contents(__DIR__.'/../../App/Controller/Aspirateur.php');

        $this->assertStringContainsString(
            "ServerCapabilities::supports(\$mysql_tested, 'information_schema_tables_temporary_column')",
            $source
        );
        $this->assertStringNotContainsString('checkVersion(array("MariaDB", "10.3")', $source);
    }
}
