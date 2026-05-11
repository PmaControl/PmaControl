<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Pin the live-refresh contract for the binlog positions on
 * /slave/show/<id>/<conn>/. Lag is already polled every 5 s via
 * Slave::getLag(); this extends the same poll to refresh "Binlog read
 * (IO)" file/pos and "Relay exec (SQL)" file/pos so the operator sees
 * IO/SQL progress without reloading the page.
 */
final class SlaveShowLiveBinlogPosTest extends TestCase
{
    private string $view;
    private string $controller;

    protected function setUp(): void
    {
        $v = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        $c = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        self::assertNotFalse($v);
        self::assertNotFalse($c);
        $this->view = $v;
        $this->controller = $c;
    }

    public function testViewExposesBinlogPosSpansWithStableIds(): void
    {
        // The four DOM nodes the JS poll will update. IDs are part of
        // the contract — renaming any of them breaks the live refresh.
        self::assertStringContainsString('id="sv-live-master-log"', $this->view);
        self::assertStringContainsString('id="sv-live-read-pos"', $this->view);
        self::assertStringContainsString('id="sv-live-relay-log"', $this->view);
        self::assertStringContainsString('id="sv-live-exec-pos"', $this->view);
    }

    public function testGetLagReturnsBinlogPosFields(): void
    {
        // The JSON payload of slave/getLag must carry the four binlog
        // position fields so the poller can refresh them. Source of
        // truth is the SHOW SLAVE / REPLICA STATUS row — fall back from
        // Master_* (MariaDB / MySQL <8.0.22) to Source_* (MySQL 8.0.22+).
        self::assertStringContainsString(
            "\$result['master_log'] = \$slave['Master_Log_File']       ?? \$slave['Source_Log_File']       ?? '';",
            $this->controller
        );
        self::assertStringContainsString(
            "\$result['read_pos']   = \$slave['Read_Master_Log_Pos']   ?? \$slave['Read_Source_Log_Pos']   ?? '';",
            $this->controller
        );
        self::assertStringContainsString(
            "\$result['relay_log']  = \$slave['Relay_Master_Log_File'] ?? \$slave['Relay_Source_Log_File'] ?? '';",
            $this->controller
        );
        self::assertStringContainsString(
            "\$result['exec_pos']   = \$slave['Exec_Master_Log_Pos']   ?? \$slave['Exec_Source_Log_Pos']   ?? '';",
            $this->controller
        );
        // Defaults must be in the initial result array so the poller
        // never sees `undefined` when the server is unavailable.
        self::assertStringContainsString("'master_log' => '',", $this->controller);
        self::assertStringContainsString("'read_pos' => '',", $this->controller);
        self::assertStringContainsString("'relay_log' => '',", $this->controller);
        self::assertStringContainsString("'exec_pos' => '',", $this->controller);
    }

    public function testPollerUpdatesBinlogPosSpans(): void
    {
        // The setInterval poll loop must write into the four spans
        // when getLag returns the new fields.
        self::assertStringContainsString(
            '$("#sv-live-master-log").text(d.master_log',
            $this->controller
        );
        self::assertStringContainsString(
            '$("#sv-live-read-pos").text(d.read_pos)',
            $this->controller
        );
        self::assertStringContainsString(
            '$("#sv-live-relay-log").text(d.relay_log',
            $this->controller
        );
        self::assertStringContainsString(
            '$("#sv-live-exec-pos").text(d.exec_pos)',
            $this->controller
        );
    }
}
