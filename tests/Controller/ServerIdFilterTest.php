<?php

declare(strict_types=1);

use App\Controller\Server;
use PHPUnit\Framework\TestCase;

final class ServerIdFilterTest extends TestCase
{
    public function testIdFilterPayloadAcceptsValidValues(): void
    {
        $filter = Server::normalizeIdFilterPayload($this->validGet());

        $this->assertSame(
            [
                'id_mysql_server' => 7,
                'ts_variable_name' => 'handler_read_next',
                'ts_variable_date' => '1-hour',
                'ts_variable_derivate' => '1',
            ],
            $filter
        );
    }

    public function testIdFilterPayloadNormalizesSpaceSeparatedInterval(): void
    {
        $get = $this->validGet();
        $get['ts_variable']['date'] = '1 hour';

        $filter = Server::normalizeIdFilterPayload($get);

        $this->assertSame('1-hour', $filter['ts_variable_date']);
    }

    public function testIdFilterPayloadRejectsInvalidValues(): void
    {
        $get = $this->validGet();

        $this->assertNull(Server::normalizeIdFilterPayload([]));
        $this->assertNull(Server::normalizeIdFilterPayload($this->withServerId($get, '0')));
        $this->assertNull(Server::normalizeIdFilterPayload($this->withServerId($get, '7 OR 1=1')));
        $this->assertNull(Server::normalizeIdFilterPayload($this->withServerId($get, ['7'])));
        $this->assertNull(Server::normalizeIdFilterPayload($this->withVariableField($get, 'name', 'handler;alert(1)')));
        $this->assertNull(Server::normalizeIdFilterPayload($this->withVariableField($get, 'name', ['handler_read_next'])));
        $this->assertNull(Server::normalizeIdFilterPayload($this->withVariableField($get, 'name', str_repeat('a', 129))));
        $this->assertNull(Server::normalizeIdFilterPayload($this->withVariableField($get, 'date', '3-year')));
        $this->assertNull(Server::normalizeIdFilterPayload($this->withVariableField($get, 'date', ['1-hour'])));
        $this->assertNull(Server::normalizeIdFilterPayload($this->withVariableField($get, 'derivate', '3')));
        $this->assertNull(Server::normalizeIdFilterPayload($this->withVariableField($get, 'derivate', ['1'])));
    }

    public function testViewUsesGetFilterMarkerInsteadOfPost(): void
    {
        $view = file_get_contents(__DIR__ . '/../../App/view/Server/id.view.php');

        $this->assertIsString($view);
        $this->assertStringContainsString('method="get"', $view);
        $this->assertStringContainsString('name="server_id_filter" value="1"', $view);
        $this->assertStringNotContainsString('method="post"', $view);
    }

    public function testControllerNoLongerUsesPostOrSqlRedirect(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Server.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('normalizeIdFilterPayload($_GET)', $controller);
        $this->assertStringContainsString("private const SERVER_ID_FILTER_INTERVALS = [", $controller);
        $this->assertStringContainsString('label: \'.json_encode($name).\',', $controller);
        $this->assertStringNotContainsString("SELECT * FROM mysql_server where id='", $controller);
        $this->assertStringNotContainsString('$_POST[\'mysql_server\']', $controller);
    }

    private function validGet(): array
    {
        return [
            'server_id_filter' => '1',
            'mysql_server' => [
                'id' => '7',
            ],
            'ts_variable' => [
                'name' => 'handler_read_next',
                'date' => '1-hour',
                'derivate' => '1',
            ],
        ];
    }

    private function withServerId(array $get, $value): array
    {
        $get['mysql_server']['id'] = $value;
        return $get;
    }

    private function withVariableField(array $get, string $field, $value): array
    {
        $get['ts_variable'][$field] = $value;
        return $get;
    }
}
