<?php

declare(strict_types=1);

use App\Controller\Variable;
use PHPUnit\Framework\TestCase;

if (!function_exists('__')) {
    function __($text)
    {
        return $text;
    }
}

final class VariableIndexInputValidationTest extends TestCase
{
    private string $controller;
    private string $view;

    protected function setUp(): void
    {
        $this->controller = (string) file_get_contents(__DIR__ . '/../../App/Controller/Variable.php');
        $this->view = (string) file_get_contents(__DIR__ . '/../../App/view/Variable/index.view.php');
    }

    public function testEvaluateIndexFiltersAcceptsValidVariableAndServer(): void
    {
        $filters = Variable::evaluateIndexFilters(
            ['id_mysql_server' => '42', 'variable' => ' query_cache_size '],
            static fn(string $value): string => 'escaped-'.$value
        );

        $this->assertSame(42, $filters['filter_id_mysql_server']);
        $this->assertSame('42', $filters['list_server']);
        $this->assertSame('query_cache_size', $filters['filter_variable']);
        $this->assertSame(' AND `variable_name` ="escaped-query_cache_size" ', $filters['variable_where']);
    }

    public function testEvaluateIndexFiltersDropsInvalidVariablePayloads(): void
    {
        foreach ($this->invalidVariablePayloads() as $payload) {
            $filters = Variable::evaluateIndexFilters(
                ['id_mysql_server' => '7', 'variable' => $payload],
                static fn(string $value): string => $value
            );

            $this->assertSame(7, $filters['filter_id_mysql_server']);
            $this->assertSame('7', $filters['list_server']);
            $this->assertNull($filters['filter_variable']);
            $this->assertSame('', $filters['variable_where']);
        }
    }

    public function testEvaluateIndexFiltersIgnoresNonScalarServerId(): void
    {
        $filters = Variable::evaluateIndexFilters(
            ['id_mysql_server' => ['7'], 'variable' => 'query_cache_size'],
            static fn(string $value): string => $value
        );

        $this->assertNull($filters['filter_id_mysql_server']);
        $this->assertSame('SELECT distinct ID FROM mysql_server WHERE is_proxy=0', $filters['list_server']);
        $this->assertSame('query_cache_size', $filters['filter_variable']);
        $this->assertSame(' AND `variable_name` ="query_cache_size" ', $filters['variable_where']);
    }

    public function testControllerUsesSharedIdentifierAndNoDirectVariableConcatenation(): void
    {
        $this->assertStringContainsString('use App\\Library\\Security\\Identifier;', $this->controller);
        $this->assertStringContainsString(
            'Identifier::normalizeMysqlVariableName($get[\'variable\'] ?? null)',
            $this->controller
        );
        $this->assertStringContainsString('self::evaluateIndexFilters($_GET, [$db, \'sql_real_escape_string\'])', $this->controller);
        $this->assertStringContainsString('$filters[\'variable_where\']', $this->controller);
        $this->assertStringNotContainsString('$variable = \' AND `variable_name` ="', $this->controller);
        $this->assertStringNotContainsString('.$_GET[\'variable\'].', $this->controller);
    }

    public function testViewNoLongerReadsGetAndEscapesFilterAndRows(): void
    {
        $this->assertStringNotContainsString('$_GET', $this->view);
        $this->assertStringContainsString('use App\\Library\\Html;', $this->view);
        $this->assertStringContainsString('Html::escape($filterVariable)', $this->view);
        $this->assertStringContainsString('rawurlencode($filterVariable)', $this->view);
        $this->assertStringContainsString('rawurlencode($variableName)', $this->view);
        $this->assertStringContainsString('Html::escape($variableName)', $this->view);
        $this->assertStringContainsString('Html::escape($value)', $this->view);
        $this->assertStringContainsString('Html::escape(__($day))', $this->view);
        $this->assertStringContainsString('Html::escape($date)', $this->view);
        $this->assertStringContainsString('Html::escape($time)', $this->view);
    }

    public function testViewEscapesMaliciousFilterVariableWhenRendered(): void
    {
        if (!defined('LINK')) {
            define('LINK', '/fr/');
        }

        $data = [
            'filter_id_mysql_server' => null,
            'filter_variable' => '<script>alert(1)</script>" onclick="owned',
            'variable' => [],
        ];

        ob_start();
        require __DIR__ . '/../../App/view/Variable/index.view.php';
        $html = (string) ob_get_clean();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('onclick="owned', $html);
        $this->assertStringContainsString(
            '&lt;script&gt;alert(1)&lt;/script&gt;&quot; onclick=&quot;owned',
            $html
        );
    }

    private function invalidVariablePayloads(): array
    {
        return [
            '<script>alert(1)</script>',
            '" OR 1=1 -- ',
            'query cache size',
            "query_cache_size\0owned",
            str_repeat('a', 129),
            ['query_cache_size'],
        ];
    }
}
