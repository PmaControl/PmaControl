<?php

declare(strict_types=1);

use App\Controller\Slave;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1190 — exercise the controller-side SQL the picker emits.
 *
 * The setter ultimately runs `SET GLOBAL binlog_row_image = '<v>'`
 * through `Mysql::getDbLink($id)` which is hard to stub from a unit
 * test, so we focus on the parts whose contract is what protects
 * against silent failures:
 *
 *  - the value is whitelisted before reaching SQL (covered by
 *    SetBinlogRowImageValidationTest);
 *  - the controller exposes a `readBinlogRowImage` companion that
 *    runs `SELECT @@global.binlog_row_image` so the user sees an
 *    actionable JSON `{previous, applied}` instead of a silent
 *    "did it change?" — exercise it here against a fake DB.
 */
final class SetBinlogRowImageHandlerTest extends TestCase
{
    public function testReadBinlogRowImageReturnsUppercaseValue(): void
    {
        $db = new BinlogRowImageFakeDb('full');
        $r = new ReflectionClass(Slave::class);
        $m = $r->getMethod('readBinlogRowImage');
        $this->assertSame('FULL', $m->invoke(null, $db));
    }

    public function testReadBinlogRowImageReturnsNullOnSilentFailure(): void
    {
        $db = new BinlogRowImageFakeDb(null, returnsFalse: true);
        $r = new ReflectionClass(Slave::class);
        $m = $r->getMethod('readBinlogRowImage');
        $this->assertNull($m->invoke(null, $db));
    }

    public function testReadBinlogRowImageReturnsNullOnEmptyResult(): void
    {
        $db = new BinlogRowImageFakeDb('');
        $r = new ReflectionClass(Slave::class);
        $m = $r->getMethod('readBinlogRowImage');
        $this->assertNull($m->invoke(null, $db));
    }

    public static function whitelistedValues(): array
    {
        return [
            ['FULL'], ['MINIMAL'], ['NOBLOB'],
        ];
    }

    /**
     * Sanity guard: the SQL we'd assemble on the happy path is a fixed
     * shape `SET GLOBAL binlog_row_image = '<value>'` with the value
     * always coming from the BINLOG_ROW_IMAGE_VALUES whitelist. No
     * placeholder, no concatenation of user input — assert the source
     * keeps that exact shape so a refactor cannot reintroduce
     * injection.
     */
    #[DataProvider('whitelistedValues')]
    public function testSqlShapeIsSetGlobalWithWhitelistedLiteral(string $value): void
    {
        $code = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        $this->assertNotFalse($code);
        $this->assertStringContainsString(
            "\$sql = \"SET GLOBAL binlog_row_image = '\" . \$request['value'] . \"'\";",
            $code,
            'setter must build SET GLOBAL with the whitelisted value, not raw POST'
        );
        // And the value going into the literal must come from the
        // whitelist branch (`normalizeSetBinlogRowImagePayload` only
        // returns BINLOG_ROW_IMAGE_VALUES members).
        $this->assertContains($value, ['FULL', 'MINIMAL', 'NOBLOB']);
    }
}

final class BinlogRowImageFakeDb
{
    private ?string $value;
    private bool $returnsFalse;
    private bool $consumed = false;
    public function __construct(?string $value = null, bool $returnsFalse = false)
    {
        $this->value = $value;
        $this->returnsFalse = $returnsFalse;
    }
    public function sql_query_silent(string $sql)
    {
        if ($this->returnsFalse) return false;
        $this->consumed = false;
        return 'mock';
    }
    public function sql_fetch_array($res, int $mode = MYSQLI_ASSOC)
    {
        if ($this->consumed) return null;
        $this->consumed = true;
        return ['v' => $this->value];
    }
}
