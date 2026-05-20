<?php

declare(strict_types=1);

use App\Library\Sql\WhereBuilder;
use PHPUnit\Framework\TestCase;

final class WhereBuilderTest extends TestCase
{
    public function testWhereAndAndWhereReturnEmptyStringWithoutConditions(): void
    {
        $db = new WhereBuilderFakeDb();

        $this->assertSame('', WhereBuilder::where($db, []));
        $this->assertSame('', WhereBuilder::andWhere($db, []));
    }

    public function testWhereBuildsEqualityClausesWithSupportedLiteralTypes(): void
    {
        $db = new WhereBuilderFakeDb();

        $this->assertSame(
            "WHERE `id` = 12 AND `name` = 'O\\'Reilly' AND `active` = 1 AND `deleted_at` IS NULL",
            WhereBuilder::where($db, [
                'id' => 12,
                'name' => "O'Reilly",
                'active' => true,
                'deleted_at' => null,
            ])
        );
    }

    public function testAndWhereBuildsAdditiveClause(): void
    {
        $this->assertSame(
            " AND `type` = 'JSON'",
            WhereBuilder::andWhere(new WhereBuilderFakeDb(), ['type' => 'JSON'])
        );
    }

    public function testDottedIdentifiersQuoteEachSegment(): void
    {
        $this->assertSame(
            'WHERE `a`.`SCHEMA_NAME` = \'app\'',
            WhereBuilder::where(new WhereBuilderFakeDb(), ['a.SCHEMA_NAME' => 'app'])
        );
    }

    public function testInvalidIdentifierThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        WhereBuilder::where(new WhereBuilderFakeDb(), ['id;DROP' => 1]);
    }

    public function testIntegerArrayKeyThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        WhereBuilder::where(new WhereBuilderFakeDb(), [0 => 'bad']);
    }

    public function testUnsupportedLiteralTypeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        WhereBuilder::equals(new WhereBuilderFakeDb(), 'name', ['bad']);
    }

    public function testAdapterWithoutEscapeMethodThrowsForStrings(): void
    {
        $this->expectException(InvalidArgumentException::class);

        WhereBuilder::equals(new stdClass(), 'name', 'alice');
    }

    public function testFloatFormattingIsLocaleIndependent(): void
    {
        $previousLocale = setlocale(LC_NUMERIC, '0');
        setlocale(LC_NUMERIC, 'fr_FR.UTF-8', 'fr_FR', 'fr');

        try {
            $this->assertSame(
                'WHERE `ratio` = 1.5',
                WhereBuilder::where(new WhereBuilderFakeDb(), ['ratio' => 1.5])
            );
        } finally {
            setlocale(LC_NUMERIC, $previousLocale ?: 'C');
        }
    }

    public function testInBuildsListAndEmptyListPredicate(): void
    {
        $db = new WhereBuilderFakeDb();

        $this->assertSame('0=1', WhereBuilder::in($db, 'id', []));
        $this->assertSame("`id` IN (1, 2, 3)", WhereBuilder::in($db, 'id', [1, 2, 3]));
        $this->assertSame(
            "`name` IN ('alice', 'O\\'Reilly')",
            WhereBuilder::in($db, 'name', ['alice', "O'Reilly"])
        );
    }

    public function testLikeHelpersEscapeWildcardsByDefaultAndCanOptOut(): void
    {
        $db = new WhereBuilderFakeDb();

        $this->assertSame(
            "`name` LIKE '%50\\\\%\\\\_match%'",
            WhereBuilder::likeContains($db, 'name', '50%_match')
        );
        $this->assertSame(
            "`name` LIKE '%50%%'",
            WhereBuilder::likeContains($db, 'name', '50%', false)
        );
        $this->assertSame(
            "`name` LIKE 'admin\\\\_%'",
            WhereBuilder::likePrefix($db, 'name', 'admin_')
        );
    }
}

final class WhereBuilderFakeDb
{
    public function sql_real_escape_string(string $value): string
    {
        return addslashes($value);
    }
}
