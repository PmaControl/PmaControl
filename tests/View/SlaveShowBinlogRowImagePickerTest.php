<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1190 — pin the wiring of the editable binlog_row_image picker
 * on the slave/show Durability card.
 */
final class SlaveShowBinlogRowImagePickerTest extends TestCase
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

    public function testControllerIssuesCsrfTokenForPicker(): void
    {
        $this->assertStringContainsString(
            "SLAVE_BINLOG_ROW_IMAGE_SET_CSRF_SCOPE = 'slave.binlog_row_image.set'",
            $this->controller,
            'CSRF scope constant must be defined'
        );
        $this->assertStringContainsString(
            "\$data['slave_binlog_row_image_set_csrf_token'] = Csrf::issueToken(\$_SESSION, self::SLAVE_BINLOG_ROW_IMAGE_SET_CSRF_SCOPE);",
            $this->controller,
            'Slave::show must issue the CSRF token for the picker'
        );
        $this->assertStringContainsString(
            "\$data['binlog_row_image_values'] = self::BINLOG_ROW_IMAGE_VALUES;",
            $this->controller,
            'controller must pass the allow-list to the view'
        );
    }

    public function testControllerSetterActionIsDefined(): void
    {
        $this->assertStringContainsString(
            'public function setBinlogRowImage($param)',
            $this->controller,
            'Slave::setBinlogRowImage($param) must be defined'
        );
        $this->assertStringContainsString(
            "header('Content-Type: application/json; charset=UTF-8');",
            $this->controller,
            'setter must declare JSON content-type'
        );
        $this->assertStringContainsString(
            'CsrfGuard::ensureOrFail',
            $this->controller,
            'setter must run through CsrfGuard'
        );
        $this->assertStringContainsString(
            "SET GLOBAL binlog_row_image = '",
            $this->controller,
            'setter must execute SET GLOBAL on the chosen value'
        );
    }

    public function testViewRendersPickerOnlyForBinlogRowImage(): void
    {
        // The pencil button + picker are emitted only for the
        // binlog_row_image row, gated by `$row['name'] === 'binlog_row_image'`.
        $this->assertStringContainsString(
            "\$isEditable = (\$row['name'] === 'binlog_row_image');",
            $this->view,
            'picker must be gated on the binlog_row_image row'
        );
        $this->assertStringContainsString('sv-durability-edit', $this->view);
        $this->assertStringContainsString('sv-durability-picker', $this->view);
        $this->assertStringContainsString('sv-durability-picker-apply', $this->view);
        $this->assertStringContainsString('sv-durability-picker-cancel', $this->view);
        $this->assertStringContainsString('slave/setBinlogRowImage/', $this->view);
    }

    public function testApplyButtonCarriesCsrfToken(): void
    {
        $this->assertMatchesRegularExpression(
            '/data-csrf-field="<\?= htmlspecialchars\(\$slaveBinlogRowImageSetCsrfField/',
            $this->view,
            'apply button must carry the CSRF field name'
        );
        $this->assertMatchesRegularExpression(
            '/data-csrf-token="<\?= htmlspecialchars\(\$slaveBinlogRowImageSetCsrfToken/',
            $this->view,
            'apply button must carry the CSRF token value'
        );
    }

    public function testJsConfirmsDowngradeFromFull(): void
    {
        // FULL → MINIMAL/NOBLOB breaks pt-table-checksum and CDC; the
        // double-confirm must be present so an operator does not
        // downgrade by accident.
        $this->assertStringContainsString("current === 'FULL' && newValue !== 'FULL'", $this->view);
        $this->assertStringContainsString('pt-table-checksum', $this->view);
        $this->assertStringContainsString('CDC', $this->view);
        $this->assertStringContainsString('confirm(', $this->view);
    }

    public function testApplyFetchUsesSvParseJsonResponse(): void
    {
        // Reuses the helper introduced by #1187 so a session-expired
        // mid-edit still routes to /user/connection cleanly.
        $this->assertStringContainsString('.then(svParseJsonResponse)', $this->view);
    }

    public function testSuccessFlowGivesVisibleConfirmationBeforeReload(): void
    {
        // Without a visible confirmation the silent reload made it
        // ambiguous whether the SET GLOBAL had taken effect — assert
        // the JS now flips the Apply button into a "previous → applied"
        // green tag and only THEN reloads the page.
        $this->assertStringContainsString("(prev === applied ? 'No change' : (prev + ' → ' + applied))", $this->view);
        $this->assertStringContainsString("btn.style.background = '#16a34a'", $this->view);
        $this->assertStringContainsString('setTimeout(function()', $this->view);
        $this->assertStringContainsString('window.location.reload()', $this->view);
        // Loading state too — without it the operator sees no
        // feedback during the round-trip.
        $this->assertStringContainsString("fa-spinner fa-spin", $this->view);
        $this->assertStringContainsString("Saving", $this->view);
    }
}
