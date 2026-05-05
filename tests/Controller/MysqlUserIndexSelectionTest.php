<?php

declare(strict_types=1);

use App\Controller\MysqlUser;
use PHPUnit\Framework\TestCase;

final class MysqlUserIndexSelectionTest extends TestCase
{
    public function testIndexSelectionRejectsPostRequests(): void
    {
        $outcome = MysqlUser::evaluateIndexSelectionRequest([], ['REQUEST_METHOD' => 'POST'], [], '/', 'MysqlUser');

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('GET', $outcome['headers']['Allow']);
        $this->assertFalse($outcome['allowed']);
        $this->assertNull($outcome['redirect']);
        $this->assertSame([], $outcome['ids']);
    }

    public function testIndexSelectionRedirectsGetSelectionToCanonicalRoute(): void
    {
        $outcome = MysqlUser::evaluateIndexSelectionRequest(
            ['mysql_server' => ['id' => ['2', '1', '2', '0', 'bad']]],
            ['REQUEST_METHOD' => 'GET'],
            [],
            '/pmacontrol/fr/',
            'MysqlUser'
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(303, $outcome['status']);
        $this->assertSame('/pmacontrol/fr/MysqlUser/index/mysql_server:id:[2,1]', $outcome['redirect']);
        $this->assertSame([], $outcome['ids']);
    }

    public function testIndexSelectionUsesNormalizedRouteParameterIds(): void
    {
        $outcome = MysqlUser::evaluateIndexSelectionRequest(
            [],
            ['REQUEST_METHOD' => 'GET'],
            ['[3,4,4,2 OR 1=1,0]'],
            '/',
            'MysqlUser'
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertNull($outcome['redirect']);
        $this->assertSame([3, 4], $outcome['ids']);
    }

    public function testIndexSelectionIgnoresInvalidGetSelectionWithoutRedirect(): void
    {
        $outcome = MysqlUser::evaluateIndexSelectionRequest(
            ['mysql_server' => ['id' => ['0', 'bad', '-1']]],
            ['REQUEST_METHOD' => 'GET'],
            [],
            '/',
            'MysqlUser'
        );

        $this->assertTrue($outcome['allowed']);
        $this->assertSame(200, $outcome['status']);
        $this->assertNull($outcome['redirect']);
        $this->assertSame([], $outcome['ids']);
    }

    public function testSelectedServerIdsArePositiveIntegersDedupedAndBounded(): void
    {
        $selection = range(1, 205);
        $selection[] = '1';
        $selection[] = 'not-an-id';

        $ids = MysqlUser::normalizeSelectedServerIds($selection);

        $this->assertCount(200, $ids);
        $this->assertSame(1, $ids[0]);
        $this->assertSame(200, $ids[199]);
    }

    public function testSelectedServerIdsAcceptNestedGetArraysAndBracketRouteSyntax(): void
    {
        $this->assertSame([7, 8, 9], MysqlUser::normalizeSelectedServerIds([['7', '8'], '[8,9]']));
    }

    public function testMysqlUserIndexViewUsesGetInsteadOfPost(): void
    {
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/MysqlUser/index.view.php');

        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }

    public function testIndexControllerNoLongerReadsSelectionFromPost(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../App/Controller/MysqlUser.php');

        $this->assertStringContainsString('evaluateIndexSelectionRequest($_GET, $_SERVER, $param, LINK, $this->getClass())', $source);
        $this->assertStringContainsString('header(\'location: \'.$selection[\'redirect\'], true, 303);', $source);
        $this->assertStringContainsString('$id_servers = $selection[\'ids\'];', $source);
        $this->assertStringNotContainsString('$_POST[\'mysql_server\'][\'id\']', $source);
    }
}
