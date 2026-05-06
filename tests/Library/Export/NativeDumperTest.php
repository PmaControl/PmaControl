<?php

declare(strict_types=1);

use App\Library\Export\NativeDumper;
use PHPUnit\Framework\TestCase;

/**
 * Issue #777 / N3: NativeDumper replaces the legacy
 * `mysqldump --skip-dump-date | sed > file` shell pipeline with a pure
 * mysqli dumper. The end-to-end output is exercised by the live
 * `pmacontrol export generateDump` smoke test; here we pin the
 * row-formatting contract because that's where escaping bugs and
 * type-mismatch dumps would silently corrupt the file.
 */
final class NativeDumperTest extends TestCase
{
    public function testFormatRowEmitsNullForNullAndUnquotesNumericTypes(): void
    {
        $fields = [
            $this->field(3 /* LONG */),
            $this->field(5 /* DOUBLE */),
            $this->field(246 /* NEWDECIMAL */),
            $this->field(13 /* YEAR */),
            $this->field(8 /* LONGLONG */),
        ];
        $row = ['42', '3.14', '12345.6789', '2026', null];

        $sql = NativeDumper::formatRow(fn(string $v): string => $v, $fields, $row);

        $this->assertSame("42,3.14,12345.6789,2026,NULL", $sql);
    }

    public function testFormatRowQuotesAndEscapesStringTypes(): void
    {
        $fields = [
            $this->field(253 /* VARCHAR/STRING */),
            $this->field(252 /* BLOB */, /*binary*/ false),  // TEXT == non-binary BLOB
        ];
        // The escape callable replaces backslashes and single quotes the
        // same way mysqli::real_escape_string does — the test stub does
        // a minimal version to keep the assertion readable.
        $escape = static fn(string $v): string => str_replace(
            ["\\", "'"],
            ['\\\\', "\\'"],
            $v
        );

        $sql = NativeDumper::formatRow(
            $escape,
            $fields,
            ["o'brien", "line1\\\nline2"]
        );

        $this->assertSame("'o\\'brien','line1\\\\\nline2'", $sql);
    }

    public function testFormatRowHexEncodesBinaryBlobsForLosslessReplay(): void
    {
        $fields = [$this->field(252 /* BLOB */, /*binary*/ true)];
        $row    = ["\x00\xff\x10ABC"];

        $sql = NativeDumper::formatRow(
            fn(string $v): string => $v, // unused in the binary branch
            $fields,
            $row
        );

        $this->assertSame('0x00ff10414243', $sql);
    }

    public function testIsBinaryFieldUsesTheBinaryFlag(): void
    {
        $this->assertTrue(NativeDumper::isBinaryField($this->field(252, true)));
        $this->assertFalse(NativeDumper::isBinaryField($this->field(252, false)));
        $this->assertFalse(NativeDumper::isBinaryField(null));
    }

    /**
     * Build a minimal stand-in for what mysqli_result::fetch_fields()
     * returns: an object with `type` (one of the MYSQLI_TYPE_* constants)
     * and `flags` (bitfield containing MYSQLI_BINARY_FLAG=128 when the
     * column is BINARY/VARBINARY/BLOB-not-TEXT).
     */
    private function field(int $type, bool $binary = false): object
    {
        $f = new stdClass();
        $f->type = $type;
        $f->flags = $binary ? 128 : 0;
        return $f;
    }
}
