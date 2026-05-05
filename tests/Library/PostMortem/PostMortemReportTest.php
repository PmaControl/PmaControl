<?php

declare(strict_types=1);

use App\Controller\PostMortem;
use App\Library\PostMortem\PostMortemReport;
use PHPUnit\Framework\TestCase;

final class PostMortemReportTest extends TestCase
{
    public function testNormalizeWindowUsesAroundAndRadiusAsLookback(): void
    {
        $window = PostMortemReport::normalizeWindow([
            'around' => '2026-04-28 12:00:00',
            'radius' => '30m',
        ]);

        $this->assertSame('2026-04-28 11:30:00', $window['start']);
        $this->assertSame('2026-04-28 12:00:00', $window['end']);
        $this->assertSame(1800, $window['radius_seconds']);
    }

    public function testNormalizeWindowPrefersValidFromToRange(): void
    {
        $window = PostMortemReport::normalizeWindow([
            'from' => '2026-04-28 09:00:00',
            'to' => '2026-04-28 10:00:00',
            'around' => '2026-04-28 12:00:00',
        ]);

        $this->assertSame('2026-04-28 09:00:00', $window['start']);
        $this->assertSame('2026-04-28 10:00:00', $window['end']);
        $this->assertNull($window['radius_seconds']);
    }

    public function testNormalizeWindowCapsExplicitRangeToSevenDays(): void
    {
        $window = PostMortemReport::normalizeWindow([
            'from' => '2026-04-01 00:00:00',
            'to' => '2026-04-28 10:00:00',
        ]);

        $this->assertSame('2026-04-21 10:00:00', $window['start']);
        $this->assertSame('2026-04-28 10:00:00', $window['end']);
    }

    public function testNormalizeServerIdRejectsNonPositiveScalarAndArrayValues(): void
    {
        $this->assertSame(42, PostMortemReport::normalizeServerId(['42']));
        $this->assertSame(0, PostMortemReport::normalizeServerId(['0']));
        $this->assertSame(0, PostMortemReport::normalizeServerId(['-1']));
        $this->assertSame(0, PostMortemReport::normalizeServerId(['12 ']));
        $this->assertSame(0, PostMortemReport::normalizeServerId([['12']]));
        $this->assertSame(0, PostMortemReport::normalizeServerId([]));
    }

    public function testNormalizeRadiusSecondsSupportsUnitsFallbackAndCaps(): void
    {
        $this->assertSame(300, PostMortemReport::normalizeRadiusSeconds('5m'));
        $this->assertSame(7200, PostMortemReport::normalizeRadiusSeconds('2h'));
        $this->assertSame(259200, PostMortemReport::normalizeRadiusSeconds('3d'));
        $this->assertSame(60, PostMortemReport::normalizeRadiusSeconds(30));
        $this->assertSame(PostMortemReport::MAX_RADIUS_SECONDS, PostMortemReport::normalizeRadiusSeconds('9999d'));
        $this->assertSame(PostMortemReport::DEFAULT_RADIUS_SECONDS, PostMortemReport::normalizeRadiusSeconds('abc'));
    }

    public function testBuildPayloadReusesServerKpiPayload(): void
    {
        $receivedOptions = [];
        $payload = PostMortemReport::buildPayload(
            42,
            ['around' => '2026-04-28 12:00:00', 'radius' => '1h'],
            static function (int $serverId, array $options) use (&$receivedOptions): array {
                $receivedOptions = $options;

                return [
                    'server' => ['id' => $serverId, 'name' => 'srv42', 'client' => 'Client', 'environment' => 'Prod'],
                    'status' => ['label' => 'UP', 'class' => 'up', 'since' => '2026-04-28 11:55:00', 'value' => 1],
                    'last_success_at' => '2026-04-28 11:59:00',
                    'last_error' => null,
                    'timeline' => ['summary' => ['up' => 60, 'readonly' => 0, 'down' => 0, 'unknown' => 0]],
                    'attempts' => [['started_at' => '2026-04-28 11:59:00', 'result_label' => 'UP']],
                    'variable_diff' => ['available' => true, 'rows' => [], 'has_more' => false, 'message' => ''],
                    'generated_at' => '2026-04-28 12:00:00',
                    'degraded' => false,
                    'warnings' => [],
                ];
            }
        );

        $this->assertSame('srv42', $payload['server']['name']);
        $this->assertSame('UP', $payload['status']['label']);
        $this->assertSame('2026-04-28 11:00:00', $payload['window']['start']);
        $this->assertSame('2026-04-28 12:00:00', $payload['window']['end']);
        $this->assertCount(1, $payload['attempts']);
        $this->assertSame('2026-04-28 11:00:00', $receivedOptions['window_start']);
        $this->assertSame('2026-04-28 12:00:00', $receivedOptions['now']);
    }

    public function testBuildPayloadReturnsDegradedPayloadWithSanitizedFactoryError(): void
    {
        $payload = PostMortemReport::buildPayload(
            42,
            ['around' => '2026-04-28 12:00:00'],
            static function (): array {
                throw new RuntimeException('password=secret token=abc mysql://user:pass@example/db');
            }
        );

        $this->assertTrue($payload['degraded']);
        $this->assertSame(
            'password=*** token=*** mysql://user:***@example/db',
            $payload['warnings'][0] ?? ''
        );
    }

    public function testPostMortemIndexActionAndMenuMigrationArePresent(): void
    {
        $this->assertTrue(method_exists(PostMortem::class, 'index'));

        $migration = (string)file_get_contents(__DIR__.'/../../../sql/incremental_v2/20260430_postmortem_menu.sql');
        $this->assertStringContainsString("'Post-mortem'", $migration);
        $this->assertStringContainsString("'{LINK}postmortem/index'", $migration);
        $this->assertStringContainsString("'PostMortem'", $migration);
    }

    public function testViewContainsReportSections(): void
    {
        $view = (string)file_get_contents(__DIR__.'/../../../App/view/PostMortem/index.view.php');

        $this->assertStringContainsString('Post-mortem incident', $view);
        $this->assertStringContainsString('Recent aspirateur attempts', $view);
        $this->assertStringContainsString('global_variable today vs yesterday', $view);
        $this->assertStringContainsString("function_exists('pm_h')", $view);
    }
}
