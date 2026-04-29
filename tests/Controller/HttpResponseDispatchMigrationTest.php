<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HttpResponseDispatchMigrationTest extends TestCase
{
    public function testDirectOutcomeDispatchesUseSharedHttpResponseHelper(): void
    {
        $controllers = [
            'Mysqlsys.php' => 3,
            'Monitoring.php' => 1,
            'PhpLiveRegex.php' => 1,
            'ProxySQL.php' => 1,
            'Mysql.php' => 2,
            'MysqlRouter.php' => 1,
            'Kpi.php' => 1,
        ];

        foreach ($controllers as $file => $expectedCount) {
            $controller = file_get_contents(__DIR__ . '/../../App/Controller/' . $file);

            $this->assertIsString($controller, $file);
            $this->assertStringContainsString('use App\\Library\\Http\\HttpResponse;', $controller, $file);
            $this->assertSame(
                $expectedCount,
                substr_count($controller, 'HttpResponse::sendOutcome($outcome, null);'),
                $file
            );
            $this->assertStringNotContainsString("http_response_code(\$outcome['status']);", $controller, $file);
            $this->assertDoesNotMatchRegularExpression(
                "/foreach \\(\\\$outcome\\['headers'\\] as \\\$name => \\\$value\\) \\{\\s*header\\(\\\$name \\. ': ' \\. \\\$value\\);\\s*\\}\\s*echo \\\$outcome\\['body'\\];/s",
                $controller,
                $file
            );
        }
    }
}
