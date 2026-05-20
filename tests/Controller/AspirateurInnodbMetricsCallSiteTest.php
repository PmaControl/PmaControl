<?php

declare(strict_types=1);

use App\Controller\Aspirateur;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AspirateurInnodbMetricsCallSiteTest extends TestCase
{
    #[DataProvider('decisionProvider')]
    public function testShouldCollectInnodbMetricsUsesRealCallSiteDecision(
        ?string $detectedVersion,
        bool $isSingleStore,
        bool $expected
    ): void {
        $reflection = new ReflectionClass(Aspirateur::class);
        $method = $reflection->getMethod('shouldCollectInnodbMetrics');
        $controller = $reflection->newInstanceWithoutConstructor();

        $this->assertSame($expected, $method->invoke($controller, $detectedVersion, $isSingleStore));
    }

    public static function decisionProvider(): array
    {
        return [
            'mysql 8 collects' => ['8.0.32-log', false, true],
            'mysql 5.5 skips' => ['5.5.62-log', false, false],
            'singlestore skips' => ['8.0.32-log', true, false],
            'empty version skips' => ['', false, false],
            'missing version skips' => [null, false, false],
        ];
    }

    public function testTryMysqlConnectionNoLongerUsesUndefinedNumVerForInnodbMetrics(): void
    {
        $source = (string) file_get_contents(__DIR__.'/../../App/Controller/Aspirateur.php');

        $this->assertStringContainsString(
            '$this->shouldCollectInnodbMetrics($detectedVersion, $isSingleStore)',
            $source
        );
        $this->assertStringNotContainsString(
            "version_compare(\$numVer, '5.6.0', '>=')",
            $source
        );
    }
}
