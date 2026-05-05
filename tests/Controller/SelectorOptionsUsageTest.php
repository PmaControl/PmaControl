<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SelectorOptionsUsageTest extends TestCase
{
    public function testAjaxSelectorControllersDelegateToSelectorOptions(): void
    {
        foreach ($this->selectorControllerFiles() as $file) {
            $source = (string) file_get_contents(__DIR__ . '/../../' . $file);

            $this->assertStringContainsString(
                'SelectorOptions::',
                $source,
                $file . ' must delegate AJAX selector option building to App\\Library\\SelectorOptions.'
            );
        }
    }

    public function testCheckDataOnClusterNormalizesServerIdsBeforeClusterSelector(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../App/Controller/CheckDataOnCluster.php');

        $this->assertStringContainsString('ServerIdSelection::normalizeList($param[0] ?? null)', $source);
        $this->assertStringNotContainsString('explode(",", $param[0])', $source);
    }

    public function testDuplicateSelectorViewsRequireSharedPartials(): void
    {
        $expectations = [
            'App/view/Backup/getDatabaseByServer.view.php' => '../Common/_basic_database_select_partial.view.php',
            'App/view/Cleaner/getDatabaseByServer.view.php' => '../Common/_basic_database_select_partial.view.php',
            'App/view/CheckConfig/getDatabasesByServers.view.php' => '../Common/_cluster_database_select_partial.view.php',
            'App/view/CheckDataOnCluster/getDatabasesByServers.view.php' => '../Common/_cluster_database_select_partial.view.php',
            'App/view/Common/getTsVariables.view.php' => '_ts_variable_select_partial.view.php',
            'App/view/Common/getTsVariableJson.view.php' => '_ts_variable_select_partial.view.php',
        ];

        foreach ($expectations as $file => $partial) {
            $source = (string) file_get_contents(__DIR__ . '/../../' . $file);

            $this->assertStringContainsString('require __DIR__', $source, $file);
            $this->assertStringContainsString($partial, $source, $file);
            $this->assertStringNotContainsString('Form::select', $source, $file);
            $this->assertStringNotContainsString('Form::Select', $source, $file);
        }

        $this->assertFileDoesNotExist(__DIR__ . '/../../App/view/Backup/getServerByName.view.php');
    }

    private function selectorControllerFiles(): array
    {
        return [
            'App/Controller/Common.php',
            'App/Controller/Database.php',
            'App/Controller/Compare.php',
            'App/Controller/Backup.php',
            'App/Controller/Cleaner.php',
            'App/Controller/MysqlDatabase.php',
            'App/Controller/CheckConfig.php',
            'App/Controller/CheckDataOnCluster.php',
        ];
    }
}
