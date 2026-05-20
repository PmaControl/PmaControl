<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1194 — pin the view contract for the GTID action group when
 * slave & master are in incompatible MySQL families.
 *
 * Smoke-test coverage map (PR #1209 follow-up). The PR body's manual
 * test plan delegates each operator-visible behaviour to one of the
 * methods below, so a phpunit-only run gives full regression coverage
 * for cross-family GTID guard. The mapping is intentional — if you
 * rename or remove a method here, update the PR body checklist too.
 *
 *   PR body item 1 (Activate disabled + tooltip)         → testViewGreysActivateOnMixedTopology
 *   PR body item 2 (Deactivate live in mid-state)        → testViewKeepsDeactivateLiveWhenMixedAndGtidActive
 *   PR body item 3 (server-side guard on activateGtid)   → testActivateGtidActionEnforcesCompatibilityServerSide
 *   PR body item 4 (no regression on compatible pair)    → testCompatibleBranchKeepsExistingActivateLink
 *
 * Sibling test file: tests/Controller/SlaveDetectMysqlFamilyTest.php
 * pins the helpers as non-routable (private static) — guards the
 * "controller helpers don't leak as /slave/<helper>/ endpoints"
 * concern raised in the review (claude HIGH #3, codex P2 #3).
 */
final class SlaveShowGtidMixedTopologyTest extends TestCase
{
    private string $view;
    private string $controller;

    protected function setUp(): void
    {
        $v = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        $c = file_get_contents(__DIR__ . '/../../App/Controller/Slave.php');
        $this->assertNotFalse($v);
        $this->assertNotFalse($c);
        $this->view = $v;
        $this->controller = $c;
    }

    public function testControllerComputesGtidCompatibilityFlag(): void
    {
        // Loose regex matching so cs-fixer alignment changes do not
        // break the contract test.
        $this->assertMatchesRegularExpression(
            '/self::evaluateGtidActivationCompatibility\s*\(/',
            $this->controller,
            'Slave::show must call the compat helper'
        );
        $this->assertMatchesRegularExpression(
            '/\$data\[\s*\'gtid_compatible\'\s*\]\s*=\s*\$gtidCompat\[\s*\'compatible\'\s*\]/',
            $this->controller,
            'Slave::show must hand the flag to the view'
        );
        $this->assertMatchesRegularExpression(
            '/\$data\[\s*\'gtid_compat_reason\'\s*\]\s*=\s*\$gtidCompat\[\s*\'reason\'\s*\]/',
            $this->controller,
            'Slave::show must hand the reason to the view'
        );
    }

    public function testActivateGtidActionEnforcesCompatibilityServerSide(): void
    {
        // The UI grey-out is purely cosmetic (`disabled` on an <a> is
        // a no-op per HTML spec) — the protective check has to live
        // in the action itself or a bookmarked URL bypasses it.
        $this->assertMatchesRegularExpression(
            '/public function activateGtid\(.*?\$gtidCompat\s*=\s*self::evaluateGtidActivationCompatibility\(/s',
            $this->controller,
            'activateGtid() must call evaluateGtidActivationCompatibility() before mutating'
        );
        $this->assertMatchesRegularExpression(
            '/activateGtid\(.*?\$gtidCompat\[\s*\'compatible\'\s*\]\s*===\s*false/s',
            $this->controller,
            'activateGtid() must refuse cross-family activation before any STOP/CHANGE'
        );
    }

    public function testViewGreysActivateOnMixedTopology(): void
    {
        // The Activate button is always disabled in the mixed branch,
        // regardless of $gtid_active — activating GTID across families
        // is what the patch is trying to prevent.
        $this->assertMatchesRegularExpression(
            '/\$gtidIncompat\s*=\s*!\(\s*\$data\[\s*\'gtid_compatible\'\s*\]/',
            $this->view
        );
        $this->assertMatchesRegularExpression(
            '/\$gtidIncompatReason\s*=\s*\(string\)\s*\(\s*\$data\[\s*\'gtid_compat_reason\'\s*\]/',
            $this->view
        );
        $this->assertStringContainsString('data-gtid-compat="<?= $gtidIncompat ? \'mixed\' : \'ok\' ?>"', $this->view);
        $this->assertStringContainsString('cursor:not-allowed', $this->view);
        $this->assertStringContainsString('fa-ban', $this->view);
        // Tooltip wiring with htmlspecialchars on the reason — XSS-safe
        // and Bootstrap-tooltip-armed.
        $this->assertStringContainsString(
            'title="<?= htmlspecialchars($gtidIncompatReason, ENT_QUOTES, \'UTF-8\') ?>"',
            $this->view
        );
        $this->assertStringContainsString('data-toggle="tooltip"', $this->view);
        // Keyboard a11y: disabled <a> must not stay focusable.
        $this->assertStringContainsString('aria-disabled="true"', $this->view);
        $this->assertStringContainsString('tabindex="-1"', $this->view);
    }

    public function testViewKeepsDeactivateLiveWhenMixedAndGtidActive(): void
    {
        // When the topology is already half-configured for GTID
        // (gtid_active = true on the slave) the operator MUST keep a
        // working Deactivate link to recover — otherwise they are
        // trapped in the broken state with no UI path back to
        // file+position replication.
        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*\$gtidIncompat\s*\).*?if\s*\(\s*\$gtid_active\s*\).*?\/deactivateGtid\//s',
            $this->view,
            'Deactivate link must be live in the mixed branch when $gtid_active is true'
        );
    }

    public function testCompatibleBranchKeepsExistingActivateLink(): void
    {
        // Make sure the elseif chain that handles the happy paths
        // (gtid_active, !gtid_active) is still there — we did not
        // accidentally drop the working code while wrapping the
        // greyed-out branch around it.
        $this->assertStringContainsString(
            'href="<?= LINK ?><?= $data[\'class\'] ?>/activateGtid/',
            $this->view,
            'Activate link must still exist for compatible topologies'
        );
        $this->assertStringContainsString(
            'href="<?= LINK ?><?= $data[\'class\'] ?>/deactivateGtid/',
            $this->view,
            'Deactivate link must still exist for compatible topologies'
        );
    }
}
