<?php

declare(strict_types=1);

use App\Library\Graphviz;
use PHPUnit\Framework\TestCase;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}

/**
 * Issue #772: Graphviz fallback used to overwrite a perfectly-readable
 * `dot -Tsvg` output with the `dot -Tsvg:cairo` output as soon as `dot`
 * exited non-zero (typically a missing icon asset). Cairo strips ALL
 * `<image>` tags and renders text as glyph paths, so the user-visible
 * effect was: every graph that shared a group with one missing icon
 * (eg ProxySQL) lost every icon (mysql, vip, gr, router…).
 *
 * This test pins:
 *   - the `isUsableSvgFile()` heuristic that lets generateDot keep the
 *     primary output when the file looks like SVG even on exit !=0,
 *   - the `resolveDotIconAsset()` helper that prefers `.svg` and falls
 *     back to `.png` when the SVG isn't on disk (so a missing
 *     proxysql.svg doesn't break the entire group).
 */
final class GraphvizFallbackPreservationTest extends TestCase
{
    public function testIsUsableSvgFileTrueOnRealSvgPayload(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pmac-svg-');
        file_put_contents($path, "<?xml version=\"1.0\"?>\n<svg xmlns=\"http://www.w3.org/2000/svg\"><g/></svg>");
        try {
            $this->assertTrue(Graphviz::isUsableSvgFile($path));
        } finally {
            @unlink($path);
        }
    }

    public function testIsUsableSvgFileTrueWithoutXmlProlog(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pmac-svg-');
        file_put_contents($path, '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        try {
            $this->assertTrue(Graphviz::isUsableSvgFile($path));
        } finally {
            @unlink($path);
        }
    }

    public function testIsUsableSvgFileFalseWhenMissingOrEmpty(): void
    {
        $missing = sys_get_temp_dir().'/pmac-no-such-svg-'.bin2hex(random_bytes(6));
        $this->assertFalse(Graphviz::isUsableSvgFile($missing));

        $empty = tempnam(sys_get_temp_dir(), 'pmac-empty-');
        file_put_contents($empty, '');
        try {
            $this->assertFalse(Graphviz::isUsableSvgFile($empty));
        } finally {
            @unlink($empty);
        }
    }

    public function testIsUsableSvgFileFalseOnPngHeader(): void
    {
        // Sometimes Graphviz fallback writes a PNG into the .svg path —
        // that case must not fool the cascade into thinking the file is
        // a usable SVG.
        $path = tempnam(sys_get_temp_dir(), 'pmac-png-');
        file_put_contents($path, "\x89PNG\r\n\x1a\nfake-payload");
        try {
            $this->assertFalse(Graphviz::isUsableSvgFile($path));
        } finally {
            @unlink($path);
        }
    }

    public function testResolveDotIconAssetPrefersSvgWhenPresent(): void
    {
        // mysql.svg is shipped in the repo; mysql.png is not (per the
        // current asset inventory). resolveDotIconAsset must prefer the
        // SVG.
        $picked = Graphviz::resolveDotIconAsset('mysql', 'mysql.svg');
        $this->assertSame('mysql.svg', $picked);
    }

    public function testResolveDotIconAssetFallsBackToPngWhenSvgMissing(): void
    {
        // maxscale: only `maxscale.png` is on disk currently — there is no
        // `maxscale.svg` (issue #772 second half). Helper must return the
        // png so the DOT references something Graphviz can actually read.
        $svg = ROOT.DS.'App'.DS.'Webroot'.DS.'image'.DS.'dot'.DS.'maxscale.svg';
        $png = ROOT.DS.'App'.DS.'Webroot'.DS.'image'.DS.'dot'.DS.'maxscale.png';
        if (file_exists($svg) || !file_exists($png)) {
            $this->markTestSkipped('Asset inventory changed: this test pins the maxscale-only-png case.');
        }

        $this->assertSame('maxscale.png', Graphviz::resolveDotIconAsset('maxscale', 'maxscale.png'));
    }

    public function testResolveDotIconAssetReturnsFallbackWhenNeitherExists(): void
    {
        $this->assertSame(
            'mysql.svg',
            Graphviz::resolveDotIconAsset('definitely-not-a-real-asset-'.bin2hex(random_bytes(4)), 'mysql.svg')
        );
    }

    public function testProxySqlSvgKeepsTransparentBackdropPaths(): void
    {
        $dotSvg = ROOT.DS.'App'.DS.'Webroot'.DS.'image'.DS.'dot'.DS.'proxysql.svg';
        $iconSvg = ROOT.DS.'App'.DS.'Webroot'.DS.'image'.DS.'icon'.DS.'proxysql.svg';

        $this->assertFileExists($dotSvg);
        $this->assertFileExists($iconSvg);
        $this->assertSame(hash_file('sha256', $dotSvg), hash_file('sha256', $iconSvg));

        $svg = (string) file_get_contents($dotSvg);
        $this->assertStringNotContainsString('fill="#000000"', $svg);

        preg_match_all('/<path\s+fill="([^"]+)"/i', $svg, $matches);
        $this->assertGreaterThanOrEqual(4, count($matches[1]));
        $this->assertSame(['none', 'none', 'none', 'none'], array_slice($matches[1], 0, 4));
    }
}
