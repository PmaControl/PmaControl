<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FilterTraitTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
        $_SESSION = [];
    }

    public function testFilterTraitBuildsWhereClauseFromSessionFallback(): void
    {
        $_SESSION['environment']['libelle'] = json_encode([7], JSON_THROW_ON_ERROR);
        $_SESSION['client']['libelle'] = json_encode([8, 9], JSON_THROW_ON_ERROR);

        $object = new class {
            use \App\Library\Filter;

            public function expose(array $ids, string $alias): string
            {
                return self::getFilter($ids, $alias);
            }
        };

        $sql = $object->expose([12], 'ms');

        $this->assertStringContainsString('`ms`.id_environment IN (7)', $sql);
        $this->assertStringContainsString('`ms`.id_client IN (8,9)', $sql);
        $this->assertStringContainsString('`ms`.id IN (12)', $sql);
        $this->assertSame($_SESSION['environment']['libelle'], $_GET['environment']['libelle']);
    }
}
