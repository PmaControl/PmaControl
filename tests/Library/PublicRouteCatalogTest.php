<?php

declare(strict_types=1);

use App\Library\PublicRouteCatalog;
use PHPUnit\Framework\TestCase;

final class PublicRouteCatalogTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';

    public function testPublicMarketingControllersAreClassifiedOutsideAdminMenu(): void
    {
        $controllers = PublicRouteCatalog::controllers();

        $this->assertCount(2, $controllers);
        $this->assertArrayHasKey('site', $controllers);
        $this->assertArrayHasKey('pmacontrol', $controllers);

        foreach ($controllers as $controller => $metadata) {
            $this->assertSame(false, $metadata['admin_menu_reachable'], $controller);
            $this->assertSame(513, $metadata['tracked_by_issue'], $controller);
            $this->assertSame(522, $metadata['decision_issue'], $controller);
            $this->assertSame('pmacontrol/www.pmacontrol.com', $metadata['external_candidate'], $controller);
            $this->assertSame('candidate-retained-pending-decision', $metadata['status'], $controller);
            $this->assertDirectoryExists(self::ROOT.'/'.$metadata['view_directory'], $controller.' view directory missing');
            $this->assertNotSame('', $metadata['layout'], $controller.' layout must be declared');
        }
    }

    public function testManifestMatchesViewBackedActions(): void
    {
        foreach (PublicRouteCatalog::controllers() as $controller => $metadata) {
            $declaredActions = array_keys($metadata['routes']);
            sort($declaredActions);

            $viewActions = $this->viewBackedActions((string) $metadata['controller']);
            sort($viewActions);

            $this->assertSame($viewActions, $declaredActions, $controller.' manifest must match view-backed actions');

            $reflection = new ReflectionClass('App\\Controller\\'.$metadata['controller']);
            foreach ($metadata['routes'] as $action => $route) {
                $this->assertTrue($reflection->hasMethod($action), $metadata['controller'].'::'.$action.'() missing');
                $this->assertTrue($reflection->getMethod($action)->isPublic(), $metadata['controller'].'::'.$action.'() must remain public');
                $this->assertFileExists(self::ROOT.'/'.$route['view'], $controller.'/'.$action.' view missing');
                $this->assertSame($controller.'/'.$action.'/', $route['path'], $controller.'/'.$action.' path must match the Glial route');
            }
        }
    }

    public function testRouteLookupNormalizesControllerAndActionCase(): void
    {
        $this->assertTrue(PublicRouteCatalog::isPublicMarketingRoute('Site', 'Demo'));
        $this->assertTrue(PublicRouteCatalog::isPublicMarketingRoute('pmacontrol', 'CONTACT'));
        $this->assertFalse(PublicRouteCatalog::isPublicMarketingRoute('unknown', 'index'));
        $this->assertNull(PublicRouteCatalog::route('site', 'missing'));
    }

    public function testDecisionSpecificDebtIsVersioned(): void
    {
        $siteDemo = PublicRouteCatalog::route('site', 'demo');
        $pmacontrolContact = PublicRouteCatalog::route('pmacontrol', 'contact');
        $pmacontrol = PublicRouteCatalog::controller('pmacontrol');

        $this->assertIsArray($siteDemo);
        $this->assertSame('GET+POST', $siteDemo['method']);
        $this->assertSame('site.demo', $siteDemo['csrf_scope']);

        $this->assertIsArray($pmacontrolContact);
        $this->assertSame('GET', $pmacontrolContact['method']);
        $this->assertSame('wireframe-contact-no-post', $pmacontrolContact['status']);

        $this->assertIsArray($pmacontrol);
        $this->assertContains('Several views still expose wireframe/specification text.', $pmacontrol['risks']);
        $this->assertContains('Contact view is not a POST endpoint and has no CSRF-protected submit flow.', $pmacontrol['risks']);
    }

    public function testDocumentationCoversThreeCandidatesAndFollowUp(): void
    {
        $doc = (string) file_get_contents(self::ROOT.'/docs/public_marketing_routes.md');

        $this->assertStringContainsString('App/Controller/Site.php', $doc);
        $this->assertStringContainsString('App/Controller/Pmacontrol.php', $doc);
        $this->assertStringContainsString('pmacontrol/www.pmacontrol.com', $doc);
        $this->assertStringContainsString('/srv/www/site', $doc);
        $this->assertStringContainsString('Issue #522', $doc);
        $this->assertStringContainsString('PublicRouteCatalog', $doc);
    }

    /**
     * @return list<string>
     */
    private function viewBackedActions(string $controller): array
    {
        $viewDirectory = self::ROOT.'/App/view/'.$controller;
        $actions = [];

        foreach (glob($viewDirectory.'/*.view.php') ?: [] as $viewFile) {
            $actions[] = substr(basename($viewFile), 0, -strlen('.view.php'));
        }

        return $actions;
    }
}
