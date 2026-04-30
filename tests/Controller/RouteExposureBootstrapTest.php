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

        $this->assertIsInt($guardPosition);
        $this->assertIsInt($aclPosition);
        $this->assertIsInt($dispatchPosition);
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
}
