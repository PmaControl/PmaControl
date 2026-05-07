<?php

declare(strict_types=1);

use App\Controller\Dot3;
use PHPUnit\Framework\TestCase;

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}

final class Dot3GraphAssetFingerprintTest extends TestCase
{
    public function testGraphCacheMd5ChangesWhenReferencedIconContentChanges(): void
    {
        $asset = tempnam(sys_get_temp_dir(), 'pmac-dot-icon-');
        $this->assertIsString($asset);

        try {
            file_put_contents($asset, '<svg><circle fill="red"/></svg>');
            $dot = self::dotWithImage($asset);
            $before = self::buildGraphCacheMd5($dot);

            file_put_contents($asset, '<svg><circle fill="blue"/></svg>');
            $after = self::buildGraphCacheMd5($dot);

            $this->assertNotSame(
                $before,
                $after,
                'Dot3 graph cache keys must change when an embedded icon asset changes; otherwise architecture/index reuses stale SVG stored in dot3_graph.'
            );
        } finally {
            @unlink($asset);
        }
    }

    public function testDotAssetFingerprintIsStableRegardlessOfImageReferenceOrder(): void
    {
        $first = tempnam(sys_get_temp_dir(), 'pmac-dot-icon-a-');
        $second = tempnam(sys_get_temp_dir(), 'pmac-dot-icon-b-');
        $this->assertIsString($first);
        $this->assertIsString($second);

        try {
            file_put_contents($first, '<svg><path id="a"/></svg>');
            file_put_contents($second, '<svg><path id="b"/></svg>');

            $left = self::buildDotAssetFingerprint(self::dotWithImages([$first, $second]));
            $right = self::buildDotAssetFingerprint(self::dotWithImages([$second, $first]));

            $this->assertSame($left, $right);
        } finally {
            @unlink($first);
            @unlink($second);
        }
    }

    private static function dotWithImage(string $asset): string
    {
        return self::dotWithImages([$asset]);
    }

    /**
     * @param array<int,string> $assets
     */
    private static function dotWithImages(array $assets): string
    {
        $cells = '';
        foreach ($assets as $asset) {
            $cells .= '<td><IMG SCALE="TRUE" SRC="' . $asset . '" /></td>';
        }

        return 'digraph G { a [label=<<table><tr>' . $cells . '</tr></table>>]; }';
    }

    private static function buildGraphCacheMd5(string $dot): string
    {
        $reflection = new ReflectionClass(Dot3::class);
        $method = $reflection->getMethod('buildGraphCacheMd5');

        return $method->invoke(null, $dot);
    }

    private static function buildDotAssetFingerprint(string $dot): string
    {
        $reflection = new ReflectionClass(Dot3::class);
        $method = $reflection->getMethod('buildDotAssetFingerprint');

        return $method->invoke(null, $dot);
    }
}
