<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DecoupageRemovalTest extends TestCase
{
    public function testLegacyDecoupageArtifactsAreRemoved(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'App/Library/Decoupage.php',
            'App/model/IdentifierPmacontrol/sharding.php',
            'documentation/Controller~Library/Decoupage.md',
        ] as $path) {
            $this->assertFileDoesNotExist($root . '/' . $path);
        }
    }

    public function testAgentNoLongerComposesDecoupageTrait(): void
    {
        $root = dirname(__DIR__, 2);
        $agent = (string) file_get_contents($root . '/App/Controller/Agent.php');

        $this->assertStringNotContainsString('App\\Library\\Decoupage', $agent);
        $this->assertStringNotContainsString('OnAddServer', $agent);
    }

    public function testExportWillNotRegenerateShardingDump(): void
    {
        $root = dirname(__DIR__, 2);
        $export = (string) file_get_contents($root . '/App/Controller/Export.php');
        $fullDump = (string) file_get_contents($root . '/sql/full/pmacontrol.sql');

        $this->assertStringNotContainsString('"daemon_main", "sharding"', $export);
        $this->assertMatchesRegularExpression('/exlude_table\\s*=\\s*array\\([^;]*"sharding"/s', $export);
        $this->assertStringNotContainsString('CREATE TABLE `sharding`', $fullDump);
        $this->assertStringNotContainsString('INSERT INTO `sharding`', $fullDump);
    }

    public function testActiveGeneratedDocumentationDoesNotReferenceRemovedDecoupageOrShardingArtifacts(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'documentation/Controller~Library/README.md',
            'documentation/pmacontrol_tables_documentation.md',
            'documentation/pmacontrol_documentation_master.md',
            'documentation/pmacontrol_documentation.html',
        ] as $path) {
            $content = (string) file_get_contents($root . '/' . $path);

            $this->assertStringNotContainsString('App/Library/Decoupage.php', $content, $path);
            $this->assertStringNotContainsString('documentation/Controller~Library/Decoupage.md', $content, $path);
            $this->assertStringNotContainsString('App/model/IdentifierPmacontrol/sharding.php', $content, $path);
            $this->assertStringNotContainsString('Table `sharding`', $content, $path);
            $this->assertStringNotContainsString('Table <code>sharding</code>', $content, $path);
        }
    }
}
