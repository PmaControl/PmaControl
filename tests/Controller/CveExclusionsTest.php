<?php

declare(strict_types=1);

use App\Controller\Cve;
use PHPUnit\Framework\TestCase;

final class CveExclusionsTest extends TestCase
{
    public function testNormalizeSearchQueryTrimsControlsAndLimitsLength(): void
    {
        $this->assertSame('CVE-2022-22965 proxysql', Cve::normalizeSearchQuery("  CVE-2022-22965\n proxysql  "));
        $this->assertSame('', Cve::normalizeSearchQuery(['bad']));
        $this->assertSame(120, strlen(Cve::normalizeSearchQuery(str_repeat('a', 160))));
    }

    public function testNormalizeCveExclusionsPayloadNormalizesVisibleRows(): void
    {
        $rows = Cve::normalizeCveExclusionsPayload([
            'visible_cve_ids' => ['cve-2022-22965', 'CVE-2024-12345'],
            'disabled_cve_ids' => ['CVE-2022-22965'],
        ]);

        $this->assertSame([
            'CVE-2022-22965' => true,
            'CVE-2024-12345' => false,
        ], $rows);
    }

    public function testNormalizeCveExclusionsPayloadRejectsInvalidInput(): void
    {
        $this->assertNull(Cve::normalizeCveExclusionsPayload([]));
        $this->assertNull(Cve::normalizeCveExclusionsPayload([
            'visible_cve_ids' => ['CVE-2022-22965'],
            'disabled_cve_ids' => 'CVE-2022-22965',
        ]));
        $this->assertNull(Cve::normalizeCveExclusionsPayload([
            'visible_cve_ids' => ['CVE-2022-22965'],
            'disabled_cve_ids' => ['CVE-2022-0001'],
        ]));
        $this->assertNull(Cve::normalizeCveExclusionsPayload([
            'visible_cve_ids' => ['NOT-A-CVE'],
            'disabled_cve_ids' => [],
        ]));
        $this->assertNull(Cve::normalizeCveExclusionsPayload([
            'visible_cve_ids' => array_fill(0, 501, 'CVE-2022-22965'),
            'disabled_cve_ids' => [],
        ]));
    }
}
