<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1194 — pin the view contract for the GTID action group when
 * slave & master are in incompatible MySQL families.
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
        $this->assertStringContainsString(
            "self::evaluateGtidActivationCompatibility(",
            $this->controller,
            'Slave::show must call the compat helper'
        );
        $this->assertStringContainsString(
            "\$data['gtid_compatible']    = \$gtidCompat['compatible'];",
            $this->controller,
            'Slave::show must hand the flag to the view'
        );
        $this->assertStringContainsString(
            "\$data['gtid_compat_reason'] = \$gtidCompat['reason'];",
            $this->controller,
            'Slave::show must hand the reason to the view'
        );
    }

    public function testViewGreysActivateAndDeactivateOnMixedTopology(): void
    {
        // Both Activate and Deactivate must be disabled in the mixed
        // branch — leaving Deactivate live would let the operator
        // partially break replication on the other half.
        $this->assertStringContainsString('$gtidIncompat       = !($data[\'gtid_compatible\'] ?? true);', $this->view);
        $this->assertStringContainsString('$gtidIncompatReason = (string) ($data[\'gtid_compat_reason\'] ?? \'\');', $this->view);
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
