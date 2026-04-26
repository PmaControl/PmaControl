<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SlaveBinlogAnalysisHistoryTest extends TestCase
{
    private string $viewSource;
    private string $controllerSource;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 2);

        $this->viewSource = (string) file_get_contents($root . '/App/view/Slave/show.view.php');
        $this->controllerSource = (string) file_get_contents($root . '/App/Controller/Slave.php');
    }

    public function testHistoryRequestCarriesCurrentReplicationConnectionName(): void
    {
        self::assertStringContainsString(
            "LINK + 'slave/binlogAnalysisList/' + serverId + '/ajax:true/?connection_name='",
            $this->viewSource
        );
        self::assertStringContainsString(
            "encodeURIComponent(replicationName || '')",
            $this->viewSource
        );
    }

    public function testHistoryEndpointFiltersByServerAndConnectionName(): void
    {
        self::assertStringContainsString(
            "\$_GET['connection_name'] ?? (\$param[1] ?? '')",
            $this->controllerSource
        );
        self::assertStringContainsString(
            'self::sanitizeConnectionName',
            $this->controllerSource
        );
        self::assertStringContainsString(
            'WHERE id_mysql_server = $id_mysql_server',
            $this->controllerSource
        );
        self::assertStringContainsString(
            "AND connection_name = '\$connection_name'",
            $this->controllerSource
        );
    }
}
