<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RouteExposureBootstrapTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';

    public function testBootstrapRunsRouteExposurePolicyBeforeAclDispatch(): void
    {
        $bootstrap = (string) file_get_contents(self::ROOT . '/App/Webroot/Bootstrap.php');

        $this->assertStringContainsString('use App\\Library\\Security\\RouteExposurePolicy;', $bootstrap);

        $guardPosition = strpos($bootstrap, 'RouteExposurePolicy::denialReason');
        $aclPosition = strpos($bootstrap, '$acl = new Acl(CONFIG."acl.config.ini");');
        $dispatchPosition = strpos($bootstrap, 'FactoryController::rootNode($_SYSTEM');
        $apacheModePosition = strpos($bootstrap, '} else {  //mode with apache');

        $this->assertIsInt($guardPosition);
        $this->assertIsInt($aclPosition);
        $this->assertIsInt($dispatchPosition);
        $this->assertIsInt($apacheModePosition);
        $this->assertGreaterThan($apacheModePosition, $guardPosition, 'Route exposure must only run in the HTTP branch, not CLI dispatch.');
        $this->assertLessThan($aclPosition, $guardPosition, 'Route exposure must be checked before ACL wildcard permissions.');
        $this->assertLessThan($dispatchPosition, $guardPosition, 'Route exposure must be checked before controller dispatch.');
        $this->assertStringContainsString("Blocked non-exposed controller route", $bootstrap);
    }

    public function testIssue510DangerousRoutesAreVersionedInSharedPolicy(): void
    {
        $policy = strtolower((string) file_get_contents(self::ROOT . '/App/Library/Security/RouteExposurePolicy.php'));

        foreach ([
            'alter/dropsp',
            'alter/slave',
            'alter/droproot',
            'alter/user',
            'webservice/decrypt',
            'mysql/passwd',
            'server/passwd',
        ] as $route) {
            $this->assertStringContainsString("'".$route."'", $policy);
        }

        $this->assertStringNotContainsString("'server/password'", $policy);
    }

    public function testIssue511CliOnlyControllersAreVersionedInSharedPolicy(): void
    {
        $policy = strtolower((string) file_get_contents(self::ROOT . '/App/Library/Security/RouteExposurePolicy.php'));

        foreach ([
            "'aspirateur'",
            "'ventilateur'",
            "'control'",
            "'integrate'",
            "'integratelog'",
            "'aggregatemetric'",
        ] as $controller) {
            $this->assertStringContainsString($controller, $policy);
        }

        $this->assertStringContainsString("'listener/checkall'", $policy);
        $this->assertStringNotContainsString("'listener/status'", $policy);
    }
}
