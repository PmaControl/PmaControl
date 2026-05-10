<?php

declare(strict_types=1);

use App\Controller\Slave;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1190 — pin the input-validation contract for the
 * binlog_row_image setter, so a regression in
 * Slave::normalizeSetBinlogRowImagePayload cannot let an arbitrary
 * value through to `SET GLOBAL`.
 */
final class SetBinlogRowImageValidationTest extends TestCase
{
    /** @return array<string,array{0:array<int,mixed>,1:array<string,mixed>,2:?array<string,mixed>}> */
    public static function payloadProvider(): array
    {
        return [
            'happy path FULL'   => [['7'], ['value' => 'FULL'],   ['id_mysql_server' => 7, 'value' => 'FULL']],
            'happy path lower'  => [['7'], ['value' => 'minimal'], ['id_mysql_server' => 7, 'value' => 'MINIMAL']],
            'happy path NOBLOB' => [['7'], ['value' => 'NOBLOB'], ['id_mysql_server' => 7, 'value' => 'NOBLOB']],
            'whitespace OK'     => [['7'], ['value' => '  FULL  '], ['id_mysql_server' => 7, 'value' => 'FULL']],
            'unknown value'     => [['7'], ['value' => 'COMPRESSED'], null],
            'empty value'       => [['7'], ['value' => ''], null],
            'no value key'      => [['7'], [], null],
            'value not scalar'  => [['7'], ['value' => ['FULL']], null],
            'no id'             => [[], ['value' => 'FULL'], null],
            'id zero'           => [['0'], ['value' => 'FULL'], null],
            'id negative'       => [['-1'], ['value' => 'FULL'], null],
            'id non numeric'    => [['abc'], ['value' => 'FULL'], null],
        ];
    }

    #[DataProvider('payloadProvider')]
    public function testNormalize(array $param, array $post, ?array $expected): void
    {
        $this->assertSame($expected, Slave::normalizeSetBinlogRowImagePayload($param, $post));
    }

    public function testCsrfFailureShortCircuits(): void
    {
        // Empty post → no CSRF token → CsrfGuard returns failure outcome.
        $out = Slave::evaluateSetBinlogRowImageRequest(['7'], [], [], []);
        $this->assertNotSame(200, $out['status'], 'CSRF must reject empty post');
        $this->assertGreaterThanOrEqual(400, $out['status']);
    }

    public function testWhitelistedValuesAreExactlyThree(): void
    {
        // Reflection guard against accidentally widening the allow-list
        // (e.g. someone adds 'STATEMENT' or 'COMPRESSED' which the engine
        // doesn't accept and would surface as a SQL error).
        $r = new ReflectionClass(Slave::class);
        $values = $r->getConstant('BINLOG_ROW_IMAGE_VALUES');
        $this->assertSame(['FULL', 'MINIMAL', 'NOBLOB'], $values);
    }
}
