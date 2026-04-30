<?php

declare(strict_types=1);

use App\Library\Security\ServerFilterWhere;
use PHPUnit\Framework\TestCase;

final class ServerFilterWhereTest extends TestCase
{
    public function testBuildsWhereFromGetAndSessionFallback(): void
    {
        $get = [
            'environment' => ['libelle' => '[1,2]'],
        ];
        $session = [
            'client' => ['libelle' => '[5]'],
        ];

        $sql = ServerFilterWhere::build($get, $session, [9, 10], 'srv');

        $this->assertSame(' AND `srv`.id_environment IN (1,2) AND `srv`.id_client IN (5) AND `srv`.id IN (9,10) ', $sql);
        $this->assertSame('[5]', $get['client']['libelle']);
    }

    public function testAbsentFiltersDoNotFailClosed(): void
    {
        $get = [];
        $session = [];

        $this->assertSame('', ServerFilterWhere::build($get, $session, [], 'a'));
    }

    public function testInvalidEnvironmentFailsClosed(): void
    {
        $get = [
            'environment' => ['libelle' => '["1) OR 1=1 -- "]'],
        ];
        $session = [];

        $sql = ServerFilterWhere::build($get, $session, [], 'a');

        $this->assertSame(' AND 0=1', $sql);
        $this->assertStringNotContainsString('OR 1=1', $sql);
    }

    public function testInvalidClientFailsClosed(): void
    {
        $get = [
            'client' => ['libelle' => '["5) UNION SELECT password -- "]'],
        ];
        $session = [];

        $sql = ServerFilterWhere::build($get, $session, [], 'a');

        $this->assertSame(' AND 0=1', $sql);
        $this->assertStringNotContainsString('UNION', $sql);
    }

    public function testInvalidServerIdsFailClosed(): void
    {
        $get = [];
        $session = [];

        $sql = ServerFilterWhere::build($get, $session, ['9) OR 1=1 -- '], 'a');

        $this->assertSame(' AND 0=1', $sql);
        $this->assertStringNotContainsString('OR 1=1', $sql);
    }

    public function testInvalidAliasIsRejected(): void
    {
        $get = [];
        $session = [];

        $this->expectException(\InvalidArgumentException::class);

        ServerFilterWhere::build($get, $session, [], 'a` OR 1=1 --');
    }

    public function testUsesSharedPositiveIntegerSelection(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../../App/Library/Security/ServerFilterWhere.php');

        $this->assertStringContainsString('PositiveIntegerSelection::normalizeList', $source);
        $this->assertStringContainsString('PositiveIntegerSelection::toCsv', $source);
    }
}
