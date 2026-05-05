<?php

declare(strict_types=1);

use App\Library\Sql\QueryGraphExtractor;
use PHPUnit\Framework\TestCase;

final class QueryGraphExtractorTest extends TestCase
{
    public function testExtractsTablesAliasesAndJoinConditionsFromSelect(): void
    {
        $sql = 'SELECT u.id, o.total AS total_order FROM crm.users u LEFT JOIN orders o ON u.id = o.user_id WHERE o.total > 10';

        $graph = QueryGraphExtractor::extract($sql);

        $this->assertSame('SELECT', $graph['statement_type']);
        $this->assertSame('users', $graph['tables'][0]['table']);
        $this->assertSame('u', $graph['tables'][0]['alias']);
        $this->assertSame('crm', $graph['tables'][0]['database']);
        $this->assertSame('orders', $graph['tables'][1]['table']);
        $this->assertSame('o', $graph['tables'][1]['alias']);
        $this->assertSame('LEFT', $graph['tables'][1]['join_type']);
        $this->assertSame('u', $graph['joins'][0]['from_table']);
        $this->assertSame('id', $graph['joins'][0]['from_field']);
        $this->assertSame('o', $graph['joins'][0]['to_table']);
        $this->assertSame('user_id', $graph['joins'][0]['to_field']);
        $this->assertSame('o.total > 10', $graph['where_fields'][0]['expression']);
    }

    public function testTableWrappersPreserveOffsetsWithoutRegexParsing(): void
    {
        $sql = "SELECT *\nFROM crm.users u\nJOIN orders o ON u.id = o.user_id";

        $tables = QueryGraphExtractor::extractTablesWithOffsets($sql);

        $this->assertSame(
            [
                [
                    'database' => 'crm',
                    'table' => 'users',
                    'alias' => 'u',
                    'offset' => 14,
                    'line' => 2,
                ],
                [
                    'database' => null,
                    'table' => 'orders',
                    'alias' => 'o',
                    'offset' => 31,
                    'line' => 3,
                ],
            ],
            $tables
        );
    }

    public function testExtractsUpdateTargetAndJoinedTable(): void
    {
        $graph = QueryGraphExtractor::extract('UPDATE app.user u JOIN company c ON u.id_company = c.id SET u.enabled = 1');

        $this->assertSame('UPDATE', $graph['statement_type']);
        $this->assertSame('user', $graph['tables'][0]['table']);
        $this->assertSame('u', $graph['tables'][0]['alias']);
        $this->assertSame('company', $graph['tables'][1]['table']);
        $this->assertSame('join', $graph['joins'][0]['type']);
    }
}
