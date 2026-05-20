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

    /**
     * Regression for #732: Util::filterServerList → Extraction2 (Dot3 daemon path)
     * legitimately passes the full monitored-server list. Capping it at 64 makes
     * the filter fall to ' AND 0=1', which empties dot3_information.information.servers
     * of all `version` / `mysql_available` data and renders the architecture page
     * cluster as an empty 11×11 SVG.
     */
    public function testInternalIdMysqlServerListAbove64IsAccepted(): void
    {
        $get = [];
        $session = [];
        $ids = range(1, 200);

        $sql = ServerFilterWhere::build($get, $session, $ids, 'a');

        $this->assertStringNotContainsString(' AND 0=1', $sql);
        $this->assertMatchesRegularExpression('/^\s*AND `a`\.id IN \(1,2,3,.*,200\)\s*$/', $sql);
    }

    public function testInternalIdMysqlServerStillRejectsNonNumericPayload(): void
    {
        $get = [];
        $session = [];

        $sql = ServerFilterWhere::build($get, $session, array_merge(range(1, 200), ['9) OR 1=1 -- ']), 'a');

        $this->assertSame(' AND 0=1', $sql);
        $this->assertStringNotContainsString('OR 1=1', $sql);
    }

    /**
     * The cap on $_GET / $_SESSION (environment / client) selections must remain —
     * those come from user input and the 64-id ceiling is the anti-abuse intent
     * of #490. Only the internal $idMysqlServer branch is uncapped (#732).
     */
    public function testEnvironmentSelectionAbove64IdsStillRejected(): void
    {
        $get = ['environment' => ['libelle' => '['.implode(',', range(1, 65)).']']];
        $session = [];

        $sql = ServerFilterWhere::build($get, $session, [], 'a');

        $this->assertSame(' AND 0=1', $sql);
    }

    public function testClientSelectionAbove64IdsStillRejected(): void
    {
        $get = ['client' => ['libelle' => '['.implode(',', range(1, 65)).']']];
        $session = [];

        $sql = ServerFilterWhere::build($get, $session, [], 'a');

        $this->assertSame(' AND 0=1', $sql);
    }
}
