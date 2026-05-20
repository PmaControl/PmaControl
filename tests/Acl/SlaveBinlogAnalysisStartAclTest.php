<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Issue #1187 — Slave/startBinlogAnalysis was missing from the ACL
 * allow-list, so the framework's gate redirected the AJAX POST to
 * /user/connection (HTML 302) and the JS fetch barfed
 * `Unexpected token '<', "<!DOCTYPE"... is not valid JSON`.
 *
 * Pin every binlog-analysis web action that the slave/show view
 * fetches against, so a future ACL-omission cannot reach production
 * unnoticed.
 */
final class SlaveBinlogAnalysisStartAclTest extends TestCase
{
    private string $aclConfig;

    protected function setUp(): void
    {
        // Source of truth is the sample shipped in the repo (the live
        // `configuration/acl.config.ini` is gitignored and copied from
        // `config_sample/` at install time — see install.sh:94).
        $contents = file_get_contents(__DIR__ . '/../../config_sample/acl.config.ini');
        $this->assertNotFalse($contents, 'config_sample/acl.config.ini must exist');
        $this->aclConfig = $contents;
    }

    /**
     * Every action the slave/show view fetch()es as JSON must be in
     * the ACL allow-list, otherwise the framework returns a HTML 302
     * to the login page that the JS cannot parse.
     */
    public function testEveryBinlogAnalysisAjaxActionIsAclRegistered(): void
    {
        $required = [
            'Slave/startBinlogAnalysis',
            'Slave/binlogAnalysisList',
            'Slave/binlogAnalysisResult',
        ];

        foreach ($required as $action) {
            $this->assertMatchesRegularExpression(
                '/^\s*[A-Za-z]+\[\]\s*=\s*"' . preg_quote($action, '/') . '"\s*$/m',
                $this->aclConfig,
                $action . ' must be allow-listed in configuration/acl.config.ini'
            );
        }
    }

    public function testSlaveShowViewOnlyFetchesAllowListedActions(): void
    {
        // Cross-check: every `fetch(LINK + 'slave/<action>/...')` in the
        // view points at a Slave/<action> entry in the ACL allow-list.
        // Catches future regressions of the same shape on a different
        // action.
        $view = file_get_contents(__DIR__ . '/../../App/view/Slave/show.view.php');
        $this->assertNotFalse($view);

        preg_match_all("#fetch\\(LINK \\+ 'slave/([a-zA-Z]+)/#", $view, $m);
        $fetched = array_values(array_unique($m[1] ?? []));
        $this->assertNotEmpty($fetched, 'expected at least one fetch() in the view');

        foreach ($fetched as $action) {
            $resource = 'Slave/' . $action;
            $this->assertMatchesRegularExpression(
                '/^\s*[A-Za-z]+\[\]\s*=\s*"' . preg_quote($resource, '/') . '"\s*$/m',
                $this->aclConfig,
                "fetch(LINK + 'slave/$action/...') in show.view.php has no ACL entry — JSON call will redirect to /user/connection"
            );
        }
    }
}
