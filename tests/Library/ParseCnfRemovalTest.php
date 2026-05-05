<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ParseCnfRemovalTest extends TestCase
{
    public function testLegacyParseCnfLibraryAndGeneratedSheetAreRemoved(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'App/Library/ParseCnf.php',
            'documentation/Controller~Library/ParseCnf.md',
        ] as $path) {
            $this->assertFileDoesNotExist($root . '/' . $path);
        }
    }

    public function testMysqlControllerNoLongerExposesRawMyCnfDumpRoute(): void
    {
        $root = dirname(__DIR__, 2);
        $mysql = (string) file_get_contents($root . '/App/Controller/Mysql.php');

        $this->assertStringNotContainsString('public function parsecnf', $mysql);
        $this->assertStringNotContainsString('file("/etc/mysql/my.cnf")', $mysql);
        $this->assertStringNotContainsString('/fr/mysql/parsecnf', $mysql);
    }

    public function testActiveGeneratedDocumentationDoesNotReferenceRemovedParseCnfRoute(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'documentation/Controller~Library/README.md',
            'documentation/pmacontrol_tables_documentation.md',
            'documentation/pmacontrol_documentation_master.md',
            'documentation/pmacontrol_documentation.html',
        ] as $path) {
            $content = (string) file_get_contents($root . '/' . $path);

            $this->assertStringNotContainsString('App/Library/ParseCnf.php', $content, $path);
            $this->assertStringNotContainsString('documentation/Controller~Library/ParseCnf.md', $content, $path);
            $this->assertStringNotContainsString('/Mysql/parsecnf', $content, $path);
        }
    }
}
