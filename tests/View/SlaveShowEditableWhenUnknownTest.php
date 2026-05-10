<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1199 — when a whitelisted variable does not yet have a
 * value collected from the time-series (`level === 'unknown'`), the
 * pencil ✏ must still appear so the operator can SET GLOBAL it
 * directly. Hiding the pencil left no path to set the value when
 * the metric was not collected yet, even though the SQL would
 * succeed.
 */
final class SlaveShowEditableWhenUnknownTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $v = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        $this->assertNotFalse($v);
        $this->view = $v;
    }

    public function testEditableGuardDoesNotCheckLevel(): void
    {
        // Pin the simplified guard: editable as soon as the variable
        // is whitelisted AND we have a target id. We never check
        // `level !== 'unknown'` anymore.
        $this->assertStringContainsString(
            "\$editable = (\$spec !== null && \$targetServerId > 0);",
            $this->view,
            'editable guard must drop the unknown-level check'
        );
        $this->assertStringNotContainsString(
            "\$level !== 'unknown'",
            $this->view,
            'no remaining unknown-level guard on editable'
        );
    }

    public function testPickerPrefillsSafeDefaultWhenValueMissing(): void
    {
        // When level=unknown, the value field is empty; the picker
        // should still open with a sensible default — first allowed
        // enum value, or the int min.
        $this->assertStringContainsString('$effectiveValue = $value;', $this->view);
        $this->assertStringContainsString("\$spec['values'][0]", $this->view,
            'enum default must be the first allowed value');
        $this->assertStringContainsString("(int) (\$spec['min'] ?? 0)", $this->view,
            'int default must be the whitelist min');
    }

    public function testPickerInputsUseEffectiveValue(): void
    {
        // The select / input pre-population uses $effectiveValue, not
        // the raw value — otherwise the picker would always start at
        // the first option even when a real value is present.
        $this->assertStringContainsString('htmlspecialchars($effectiveValue', $this->view);
    }

    public function testPickerJsDefersToDomContentLoaded(): void
    {
        // The picker JS sits inside the Durability section's PHP if
        // block, which is rendered BEFORE the Binlog group-commit table.
        // Without DOMContentLoaded the IIFE runs at parse time —
        // querySelectorAll('.sv-rv-edit') only sees the durability
        // buttons; the group-commit buttons exist later in the document
        // and never get a click listener. Pin the deferral so the bug
        // cannot reappear in a refactor.
        $this->assertStringContainsString(
            "document.addEventListener('DOMContentLoaded', function() {",
            $this->view,
            'picker JS must defer to DOMContentLoaded so group-commit buttons get listeners too'
        );
    }
}
