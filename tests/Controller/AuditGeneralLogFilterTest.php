<?php

declare(strict_types=1);

use App\Controller\Audit;
use PHPUnit\Framework\TestCase;

final class AuditGeneralLogFilterTest extends TestCase
{
    public function testGeneralLogAcceptsEmptyGetSelection(): void
    {
        $outcome = Audit::evaluateGeneralLogRequest([], ['REQUEST_METHOD' => 'GET']);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['id' => null], $outcome['selection']);
    }

    public function testGeneralLogAcceptsSingleServerGetSelection(): void
    {
        $outcome = Audit::evaluateGeneralLogRequest(
            ['mysql_server' => ['id' => '104']],
            ['REQUEST_METHOD' => 'HEAD']
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(['id' => 104], $outcome['selection']);
    }

    public function testGeneralLogTreatsEmptySubmittedSelectionAsInitialRender(): void
    {
        foreach ([['mysql_server' => ['id' => '']], ['mysql_server' => ['id' => []]]] as $get) {
            $outcome = Audit::evaluateGeneralLogRequest($get, ['REQUEST_METHOD' => 'GET']);

            $this->assertSame(200, $outcome['status']);
            $this->assertSame(['id' => null], $outcome['selection']);
        }
    }

    public function testGeneralLogRejectsResidualPostAndOtherUnsafeMethods(): void
    {
        foreach (['POST', 'PUT', 'DELETE'] as $method) {
            $outcome = Audit::evaluateGeneralLogRequest(
                ['mysql_server' => ['id' => '104']],
                ['REQUEST_METHOD' => $method]
            );

            $this->assertSame(405, $outcome['status']);
            $this->assertSame('Method Not Allowed', $outcome['body']);
            $this->assertSame('GET, HEAD', $outcome['headers']['Allow']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testGeneralLogRejectsInvalidSelections(): void
    {
        foreach ($this->invalidGets() as $get) {
            $outcome = Audit::evaluateGeneralLogRequest($get, ['REQUEST_METHOD' => 'GET']);

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid audit general log selection', $outcome['body']);
            $this->assertNull($outcome['selection']);
        }
    }

    public function testGeneralLogUsesGetOnlyAndNormalizedServerId(): void
    {
        $controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Audit.php');
        $view = (string) file_get_contents(__DIR__ . '/../../App/view/Audit/general_log.view.php');

        $generalLogStart = strpos($controller, 'public function general_log($param)');
        $scpStart = strpos($controller, 'public function scp($param)', $generalLogStart);
        $generalLogBody = substr($controller, $generalLogStart, $scpStart - $generalLogStart);

        $this->assertStringContainsString('use App\\Library\\Security\\ServerIdSelection;', $controller);
        $this->assertStringContainsString('self::evaluateGeneralLogRequest($_GET, $_SERVER)', $generalLogBody);
        $this->assertStringContainsString('ServerIdSelection::normalizeList($get[\'mysql_server\'][\'id\'], 1)', $controller);
        $this->assertStringNotContainsString('$_POST', $generalLogBody);
        $this->assertStringNotContainsString('REQUEST_METHOD\'] == "POST"', $generalLogBody);
        $this->assertStringNotContainsString('Post::getToPost()', $generalLogBody);

        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringNotContainsString('method="POST"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
        $this->assertStringNotContainsString('general_log', $view);
    }

    private function invalidGets(): array
    {
        return [
            ['mysql_server' => '104'],
            ['mysql_server' => ['id' => 'abc']],
            ['mysql_server' => ['id' => '0']],
            ['mysql_server' => ['id' => '-1']],
            ['mysql_server' => ['id' => ['104', '105']]],
            ['mysql_server' => ['id' => '104', 'extra' => '1']],
            ['mysql_server' => ['id' => ['104', ['105']]]],
        ];
    }
}
