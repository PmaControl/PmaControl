<?php

declare(strict_types=1);

use App\Library\Tree as TreeInterval;
use PHPUnit\Framework\TestCase;

final class TreeLibraryIntegerGuardTest extends TestCase
{
    public function testTreeLibraryRejectsUnsafeNodeIdsBeforeQuerying(): void
    {
        $db = new TreeLibraryIntegerGuardFakeDb();
        $tree = new TreeInterval($db, 'menu', [], ['group_id' => 1]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid tree id');

        $tree->delete('1 OR 1=1');
    }

    public function testTreeLibraryRejectsUnsafeParentIdsBeforeQuerying(): void
    {
        $db = new TreeLibraryIntegerGuardFakeDb();
        $tree = new TreeInterval($db, 'menu', [], ['group_id' => 1]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid tree parent id');

        $tree->add(['title' => 'Owned'], '1 UNION SELECT password FROM user');
    }

    public function testTreeLibraryRejectsUnsafeExtraWhereOptionsBeforeMutation(): void
    {
        $db = new TreeLibraryIntegerGuardFakeDb();
        $tree = new TreeInterval($db, 'menu', [], ['group_id' => "1' OR '1'='1"]);

        try {
            $tree->delete(7);
            $this->fail('Expected invalid tree option exception');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Invalid tree option value', $exception->getMessage());
        }

        $this->assertSame([
            'SELECT * FROM `menu` WHERE `id`=7',
        ], $db->queries);
    }

    public function testTreeLibraryUsesSharedPositiveIntegerSelection(): void
    {
        $library = (string) file_get_contents(__DIR__ . '/../../App/Library/Tree.php');

        $this->assertStringContainsString('use App\\Library\\Security\\PositiveIntegerSelection;', $library);
        $this->assertStringContainsString('PositiveIntegerSelection::normalizeSingle($value)', $library);
        $this->assertStringContainsString("throw new \\InvalidArgumentException('Invalid '.\$label);", $library);
        $this->assertStringContainsString('`".$key."` = ".self::normalizePositiveInteger($val, \'tree option value\')', $library);
        $this->assertStringNotContainsString('`".$key."` = \'".$val."\'', $library);
    }
}

final class TreeLibraryIntegerGuardFakeDb
{
    public array $queries = [];

    public function sql_query(string $sql)
    {
        $this->queries[] = $sql;

        return $sql;
    }

    public function sql_fetch_object($result): object
    {
        return (object) [
            'id' => 7,
            'bg' => 1,
            'bd' => 2,
        ];
    }

    public function sql_save(array $payload): int
    {
        return 1;
    }
}
