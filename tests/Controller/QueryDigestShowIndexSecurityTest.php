<?php

declare(strict_types=1);

use App\Controller\Query;
use PHPUnit\Framework\TestCase;

final class QueryDigestShowIndexSecurityTest extends TestCase
{
    public function testDigestShowIndexSqlQuotesStrictIdentifier(): void
    {
        $this->assertSame('SHOW INDEX FROM `orders_2024`', $this->buildDigestShowIndexSql('orders_2024'));
    }

    public function testDigestShowIndexSqlRejectsUnsafeIdentifier(): void
    {
        $this->assertNull($this->buildDigestShowIndexSql('orders`2024'));
        $this->assertNull($this->buildDigestShowIndexSql('orders-2024'));
        $this->assertNull($this->buildDigestShowIndexSql('1orders'));
    }

    private function buildDigestShowIndexSql(string $table): ?string
    {
        $method = new ReflectionMethod(Query::class, 'buildDigestShowIndexSql');

        return $method->invoke(null, $table);
    }
}
