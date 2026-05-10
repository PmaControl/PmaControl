<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1198 — pin the contract for the generic editor wired in
 * the slave/show Durability + Binlog group-commit cards.
 */
final class SlaveShowGenericEditorTest extends TestCase
{
    private string $view;
    private string $controller;

    protected function setUp(): void
    {
        $v = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        $c = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        $this->assertNotFalse($v);
        $this->assertNotFalse($c);
        $this->view = $v;
        $this->controller = $c;
    }

    public function testControllerExposesWhitelistAndCsrfTokenToView(): void
    {
        $this->assertStringContainsString(
            "SLAVE_REPLICATION_VARIABLE_SET_CSRF_SCOPE = 'slave.replication_variable.set'",
            $this->controller
        );
        $this->assertStringContainsString(
            "\$data['slave_replication_variable_set_csrf_token'] = Csrf::issueToken(\$_SESSION, self::SLAVE_REPLICATION_VARIABLE_SET_CSRF_SCOPE);",
            $this->controller
        );
        $this->assertStringContainsString(
            "\$data['replication_variable_whitelist'] = self::replicationVariableWhitelist();",
            $this->controller
        );
    }

    public function testControllerSetterActionIsDefined(): void
    {
        $this->assertStringContainsString(
            'public function setReplicationVariable($param)',
            $this->controller
        );
        $this->assertStringContainsString(
            'CsrfGuard::ensureOrFail',
            $this->controller
        );
        $this->assertStringContainsString(
            'SET GLOBAL `" . $request[\'variable\'] . "` = " . $request[\'value_sql_literal\']',
            $this->controller,
            'setter must build SET GLOBAL with whitelisted variable + already-quoted literal'
        );
    }

    public function testRenderEditableCellHelperIsDefinedInView(): void
    {
        $this->assertStringContainsString('$renderEditableCell = function (', $this->view);
        $this->assertStringContainsString('sv-rv-edit', $this->view);
        $this->assertStringContainsString('sv-rv-picker', $this->view);
        $this->assertStringContainsString('sv-rv-picker-apply', $this->view);
        $this->assertStringContainsString('sv-rv-picker-cancel', $this->view);
    }

    public function testEditableCellRenderedForBothMasterAndSlaveColumns(): void
    {
        // Each row in the durability table calls the helper twice:
        // once with 'master', once with 'slave'.
        $this->assertStringContainsString("'master',", $this->view);
        $this->assertStringContainsString("'slave',", $this->view);
        $this->assertStringContainsString('$masterTargetId', $this->view);
        $this->assertStringContainsString('$slaveTargetId', $this->view);
        // The Durability section calls the helper twice per row
        // (master + slave) so for ≥1 row there are ≥2 calls.
        $start = strpos($this->view, 'sv-action-group sv-durability');
        $this->assertNotFalse($start);
        $end = strpos($this->view, '</table>', $start);
        $section = substr($this->view, $start, $end - $start);
        $this->assertGreaterThanOrEqual(
            2,
            substr_count($section, '$renderEditableCell('),
            'durability table must render an editable cell for both master and slave'
        );
    }

    public function testGroupCommitTableUsesTheSameHelper(): void
    {
        // The same renderEditableCell is invoked for binlog group-commit
        // rows so master + slave editing is uniform.
        $start = strpos($this->view, 'sv-action-group sv-group-commit');
        $this->assertNotFalse($start);
        $end = strpos($this->view, '</table>', $start);
        $section = substr($this->view, $start, $end - $start);

        $this->assertStringContainsString('$renderEditableCell(', $section,
            'group-commit table must reuse the editable-cell helper');
        $this->assertSame(
            2,
            substr_count($section, '$renderEditableCell('),
            'group-commit row must render master + slave editable cells'
        );
    }

    public function testColumnsAreEqualWidth(): void
    {
        // Operator asked for aligned columns. Both master and slave
        // <th> AND <td> declare the same width:140px on both tables
        // (durability + binlog group-commit) → 4 cells × 2 tables = 8.
        $this->assertGreaterThanOrEqual(
            8,
            substr_count($this->view, "width:140px"),
            'master and slave columns must share width:140px in both tables'
        );
    }

    public function testGenericPickerJsExists(): void
    {
        $this->assertStringContainsString('document.querySelectorAll(\'.sv-rv-edit\')', $this->view);
        $this->assertStringContainsString('document.querySelectorAll(\'.sv-rv-picker-apply\')', $this->view);
        $this->assertStringContainsString('document.querySelectorAll(\'.sv-rv-picker-cancel\')', $this->view);
        // closeAllPickers() guards against two pickers being open at once.
        $this->assertStringContainsString('function closeAllPickers()', $this->view);
        // RISKY_DOWNGRADES table covers the variables where a downgrade
        // hides a real cost.
        $this->assertStringContainsString('RISKY_DOWNGRADES', $this->view);
        foreach (['sync_binlog', 'innodb_flush_log_at_trx_commit', 'binlog_format', 'binlog_row_image', 'super_read_only'] as $v) {
            $this->assertStringContainsString($v, $this->view, "downgrade-confirm must cover $v");
        }
        // Reuses svParseJsonResponse from #1187.
        $this->assertStringContainsString('.then(svParseJsonResponse)', $this->view);
    }
}
