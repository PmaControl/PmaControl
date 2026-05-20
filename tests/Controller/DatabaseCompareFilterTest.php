<?php

declare(strict_types=1);

use App\Controller\Database;
use PHPUnit\Framework\TestCase;

final class DatabaseCompareFilterTest extends TestCase
{
    public function testCompareRequestReusesGetOnlyDatabaseSelection(): void
    {
        $outcome = Database::evaluateDataRequest([
            'compare_main' => [
                'id_mysql_server__original' => '3',
                'id_mysql_server__compare' => '4',
                'database__original' => 'main_db',
                'database__compare' => 'main-db-copy',
            ],
        ], ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            'id_mysql_server__original' => 3,
            'id_mysql_server__compare' => 4,
            'database__original' => 'main_db',
            'database__compare' => 'main-db-copy',
        ], $outcome['selection']);
    }

    public function testCompareRequestRejectsResidualPost(): void
    {
        $outcome = Database::evaluateDataRequest([], ['REQUEST_METHOD' => 'POST']);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('Method Not Allowed', $outcome['body']);
        $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
        $this->assertNull($outcome['selection']);
    }

    public function testCompareControllerAndViewUseGetFlow(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Database.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Database/compare.view.php');
        $compareStart = strpos($controller, 'public function compare($param)');
        $analyseStart = strpos($controller, 'public function analyse($param)');

        $this->assertNotFalse($compareStart);
        $this->assertNotFalse($analyseStart);
        $compareBody = substr($controller, $compareStart, $analyseStart - $compareStart);

        $this->assertStringContainsString('evaluateDataRequest($_GET, $_SERVER)', $compareBody);
        $this->assertStringContainsString('$selection[\'database__original\']', $compareBody);
        $this->assertStringContainsString('$selection[\'database__compare\']', $compareBody);
        $this->assertStringNotContainsString('$_POST', $compareBody);
        $this->assertStringNotContainsString('header(\'location: \'.LINK.\'database/compare/', $compareBody);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }
}
