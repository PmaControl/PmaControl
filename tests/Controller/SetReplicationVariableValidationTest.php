<?php

declare(strict_types=1);

use App\Controller\Slave;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1198 — pin the validation contract of the generic
 * Slave::setReplicationVariable setter. Every dynamic variable on
 * /slave/show flows through this single endpoint, so the whitelist
 * + per-variable validation is the only thing standing between
 * arbitrary POST and `SET GLOBAL`.
 */
final class SetReplicationVariableValidationTest extends TestCase
{
    public static function happyPathProvider(): array
    {
        return [
            'enum sync_binlog' => [
                ['7'], ['variable' => 'sync_binlog', 'value' => '1'],
                ['id_mysql_server' => 7, 'variable' => 'sync_binlog', 'value' => '1', 'value_sql_literal' => '1'],
            ],
            'enum binlog_format mixed case' => [
                ['7'], ['variable' => 'binlog_format', 'value' => 'row'],
                ['id_mysql_server' => 7, 'variable' => 'binlog_format', 'value' => 'ROW', 'value_sql_literal' => "'ROW'"],
            ],
            // #1224 — MariaDB rejects `SET GLOBAL innodb_flush_log_at_trx_commit = '2'`
            // (quoted) with errno 1232. The literal MUST be a bare
            // integer for numeric-valued enums.
            'enum innodb_flush trx_commit' => [
                ['1'], ['variable' => 'innodb_flush_log_at_trx_commit', 'value' => '2'],
                ['id_mysql_server' => 1, 'variable' => 'innodb_flush_log_at_trx_commit', 'value' => '2', 'value_sql_literal' => '2'],
            ],
            'enum source_info_repository' => [
                ['5'], ['variable' => 'source_info_repository', 'value' => 'TABLE'],
                ['id_mysql_server' => 5, 'variable' => 'source_info_repository', 'value' => 'TABLE', 'value_sql_literal' => "'TABLE'"],
            ],
            'enum super_read_only with whitespace' => [
                ['5'], ['variable' => 'super_read_only', 'value' => '  on  '],
                ['id_mysql_server' => 5, 'variable' => 'super_read_only', 'value' => 'ON', 'value_sql_literal' => "'ON'"],
            ],
            'int binlog_commit_wait_usec' => [
                ['9'], ['variable' => 'binlog_commit_wait_usec', 'value' => '5000'],
                ['id_mysql_server' => 9, 'variable' => 'binlog_commit_wait_usec', 'value' => '5000', 'value_sql_literal' => '5000'],
            ],
            'int min 0' => [
                ['9'], ['variable' => 'binlog_commit_wait_count', 'value' => '0'],
                ['id_mysql_server' => 9, 'variable' => 'binlog_commit_wait_count', 'value' => '0', 'value_sql_literal' => '0'],
            ],
        ];
    }

    #[DataProvider('happyPathProvider')]
    public function testHappyPath(array $param, array $post, array $expected): void
    {
        $this->assertSame($expected, Slave::normalizeSetReplicationVariablePayload($param, $post));
    }

    public static function rejectProvider(): array
    {
        return [
            'variable not whitelisted' => [['7'], ['variable' => 'innodb_buffer_pool_size', 'value' => '4G']],
            'enum value not in list'   => [['7'], ['variable' => 'binlog_format', 'value' => 'YOLO']],
            'enum case ok value bad'   => [['7'], ['variable' => 'sync_binlog', 'value' => 'one']],
            'int negative below min'   => [['7'], ['variable' => 'sync_binlog', 'value' => '-1']],
            'int above max'            => [['7'], ['variable' => 'sync_binlog', 'value' => '999999999']],
            'int non-numeric'          => [['7'], ['variable' => 'sync_binlog', 'value' => 'abc']],
            'missing variable key'     => [['7'], ['value' => '1']],
            'missing value key'        => [['7'], ['variable' => 'sync_binlog']],
            'non-scalar value'         => [['7'], ['variable' => 'sync_binlog', 'value' => ['1']]],
            'missing id'               => [[],    ['variable' => 'sync_binlog', 'value' => '1']],
            'id zero'                  => [['0'], ['variable' => 'sync_binlog', 'value' => '1']],
            'id non-digit'             => [['abc'], ['variable' => 'sync_binlog', 'value' => '1']],
            'relay_log_recovery excluded' => [['7'], ['variable' => 'relay_log_recovery', 'value' => 'ON']],
        ];
    }

    #[DataProvider('rejectProvider')]
    public function testRejections(array $param, array $post): void
    {
        $this->assertNull(Slave::normalizeSetReplicationVariablePayload($param, $post));
    }

    public function testWhitelistShapeMatchesContract(): void
    {
        $wl = Slave::replicationVariableWhitelist();

        // Every entry must declare a type.
        foreach ($wl as $name => $spec) {
            $this->assertArrayHasKey('type', $spec, "$name must declare a type");
            $this->assertContains($spec['type'], ['enum', 'int'], "$name type must be enum or int");
            if ($spec['type'] === 'enum') {
                $this->assertNotEmpty($spec['values'] ?? [], "$name enum must declare values");
            } else {
                $this->assertArrayHasKey('min', $spec, "$name int must declare min");
                $this->assertArrayHasKey('max', $spec, "$name int must declare max");
            }
        }

        // The dynamic-mutation list must contain the operator-visible
        // variables — relay_log_recovery is intentionally absent
        // because it cannot be changed at runtime.
        $expectKeys = [
            'sync_binlog', 'innodb_flush_log_at_trx_commit',
            'binlog_format', 'binlog_row_image',
            'replica_preserve_commit_order', 'slave_preserve_commit_order',
            'source_info_repository', 'master_info_repository',
            'relay_log_info_repository',
            'super_read_only',
            'binlog_commit_wait_count', 'binlog_commit_wait_usec',
            'binlog_group_commit_sync_no_delay_count', 'binlog_group_commit_sync_delay',
        ];
        foreach ($expectKeys as $k) {
            $this->assertArrayHasKey($k, $wl, "$k must be editable");
        }
        $this->assertArrayNotHasKey('relay_log_recovery', $wl,
            'relay_log_recovery must NOT be in the runtime-editable whitelist (server-restart only)');
    }

    public function testCsrfFailureShortCircuits(): void
    {
        $out = Slave::evaluateSetReplicationVariableRequest(
            ['7'],
            ['variable' => 'sync_binlog', 'value' => '1'],
            [],
            []
        );
        $this->assertNotSame(200, $out['status'], 'no CSRF token = must reject');
        $this->assertGreaterThanOrEqual(400, $out['status']);
    }
}
