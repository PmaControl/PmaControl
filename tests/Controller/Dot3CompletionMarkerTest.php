<?php

declare(strict_types=1);

use App\Controller\Dot3;
use PHPUnit\Framework\TestCase;

final class Dot3CompletionMarkerTest extends TestCase
{
    public function testBuildMarkSvgGeneratedSqlUsesSnapshotIdAsReadyMarker(): void
    {
        $this->assertSame(
            'UPDATE dot3_information SET is_svg_generated = 516776 WHERE id = 516776',
            self::buildMarkSvgGeneratedSql(516776)
        );
    }

    public function testShouldMarkSvgGeneratedRequiresPersistedGraphs(): void
    {
        $this->assertTrue(self::shouldMarkSvgGenerated(516776, 1));
        $this->assertFalse(self::shouldMarkSvgGenerated(516776, 0));
        $this->assertFalse(self::shouldMarkSvgGenerated(0, 1));
    }

    private static function buildMarkSvgGeneratedSql(int $idDot3Information): string
    {
        $reflection = new ReflectionClass(Dot3::class);
        $method = $reflection->getMethod('buildMarkSvgGeneratedSql');

        return $method->invoke(null, $idDot3Information);
    }

    private static function shouldMarkSvgGenerated(int $idDot3Information, int $savedGraphs): bool
    {
        $reflection = new ReflectionClass(Dot3::class);
        $method = $reflection->getMethod('shouldMarkSvgGenerated');

        return $method->invoke(null, $idDot3Information, $savedGraphs);
    }
}
