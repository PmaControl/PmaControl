<?php

declare(strict_types=1);

namespace App\Library;

/**
 * Catalog of public marketing routes that are intentionally outside the admin menu.
 */
final class PublicRouteCatalog
{
    private const TRACKED_BY_ISSUE = 513;
    private const DECISION_ISSUE = 522;

    /**
     * @var array<string,array<string,mixed>>
     */
    private const CONTROLLERS = [
        'site' => [
            'controller' => 'Site',
            'layout' => 'site',
            'view_directory' => 'App/view/Site',
            'kind' => 'internal-public-front',
            'status' => 'candidate-retained-pending-decision',
            'admin_menu_reachable' => false,
            'tracked_by_issue' => self::TRACKED_BY_ISSUE,
            'decision_issue' => self::DECISION_ISSUE,
            'external_candidate' => 'pmacontrol/www.pmacontrol.com',
            'strengths' => [
                'Central public navigation built by Site::buildCommon().',
                'Dedicated site layout with menu, breadcrumb and footer.',
                'Demo POST flow already protected by the shared CSRF guard.',
            ],
            'risks' => [
                'Smaller content and SEO surface than Pmacontrol.',
                'Final vhost ownership still has to be decided in issue 522.',
            ],
            'routes' => [
                'agents' => ['path' => 'site/agents/', 'view' => 'App/view/Site/agents.view.php'],
                'blog' => ['path' => 'site/blog/', 'view' => 'App/view/Site/blog.view.php'],
                'contact' => ['path' => 'site/contact/', 'view' => 'App/view/Site/contact.view.php'],
                'demo' => [
                    'path' => 'site/demo/',
                    'view' => 'App/view/Site/demo.view.php',
                    'method' => 'GET+POST',
                    'csrf_scope' => 'site.demo',
                ],
                'documentation' => ['path' => 'site/documentation/', 'view' => 'App/view/Site/documentation.view.php'],
                'faq' => ['path' => 'site/faq/', 'view' => 'App/view/Site/faq.view.php'],
                'incidents' => ['path' => 'site/incidents/', 'view' => 'App/view/Site/incidents.view.php'],
                'index' => ['path' => 'site/index/', 'view' => 'App/view/Site/index.view.php'],
                'integrations' => ['path' => 'site/integrations/', 'view' => 'App/view/Site/integrations.view.php'],
                'lab' => ['path' => 'site/lab/', 'view' => 'App/view/Site/lab.view.php'],
                'max' => ['path' => 'site/max/', 'view' => 'App/view/Site/max.view.php'],
                'offers' => ['path' => 'site/offers/', 'view' => 'App/view/Site/offers.view.php'],
                'onpremise' => ['path' => 'site/onpremise/', 'view' => 'App/view/Site/onpremise.view.php'],
                'process' => ['path' => 'site/process/', 'view' => 'App/view/Site/process.view.php'],
                'resources' => ['path' => 'site/resources/', 'view' => 'App/view/Site/resources.view.php'],
                'roadmap' => ['path' => 'site/roadmap/', 'view' => 'App/view/Site/roadmap.view.php'],
                'saas' => ['path' => 'site/saas/', 'view' => 'App/view/Site/saas.view.php'],
                'success' => ['path' => 'site/success/', 'view' => 'App/view/Site/success.view.php'],
                'support' => ['path' => 'site/support/', 'view' => 'App/view/Site/support.view.php'],
            ],
        ],
        'pmacontrol' => [
            'controller' => 'Pmacontrol',
            'layout' => 'pmacontrol',
            'view_directory' => 'App/view/Pmacontrol',
            'kind' => 'internal-public-front',
            'status' => 'candidate-retained-pending-decision',
            'admin_menu_reachable' => false,
            'tracked_by_issue' => self::TRACKED_BY_ISSUE,
            'decision_issue' => self::DECISION_ISSUE,
            'external_candidate' => 'pmacontrol/www.pmacontrol.com',
            'strengths' => [
                'Wider marketing and SEO route surface.',
                'Per-page meta payload through Pmacontrol::setPageMeta().',
                'Product, feature, resource and legal pages already enumerated.',
            ],
            'risks' => [
                'Several views still expose wireframe/specification text.',
                'Contact view is not a POST endpoint and has no CSRF-protected submit flow.',
                'Navigation is spread in views instead of a shared public menu catalog.',
            ],
            'routes' => [
                'ai' => ['path' => 'pmacontrol/ai/', 'view' => 'App/view/Pmacontrol/ai.view.php'],
                'automation' => ['path' => 'pmacontrol/automation/', 'view' => 'App/view/Pmacontrol/automation.view.php'],
                'backups' => ['path' => 'pmacontrol/backups/', 'view' => 'App/view/Pmacontrol/backups.view.php'],
                'blog' => ['path' => 'pmacontrol/blog/', 'view' => 'App/view/Pmacontrol/blog.view.php'],
                'blog_article' => ['path' => 'pmacontrol/blog_article/', 'view' => 'App/view/Pmacontrol/blog_article.view.php'],
                'case_studies' => ['path' => 'pmacontrol/case_studies/', 'view' => 'App/view/Pmacontrol/case_studies.view.php'],
                'company' => ['path' => 'pmacontrol/company/', 'view' => 'App/view/Pmacontrol/company.view.php'],
                'contact' => [
                    'path' => 'pmacontrol/contact/',
                    'view' => 'App/view/Pmacontrol/contact.view.php',
                    'method' => 'GET',
                    'status' => 'wireframe-contact-no-post',
                ],
                'cookies' => ['path' => 'pmacontrol/cookies/', 'view' => 'App/view/Pmacontrol/cookies.view.php'],
                'docs' => ['path' => 'pmacontrol/docs/', 'view' => 'App/view/Pmacontrol/docs.view.php'],
                'galera' => ['path' => 'pmacontrol/galera/', 'view' => 'App/view/Pmacontrol/galera.view.php'],
                'index' => ['path' => 'pmacontrol/index/', 'view' => 'App/view/Pmacontrol/index.view.php'],
                'integrations' => ['path' => 'pmacontrol/integrations/', 'view' => 'App/view/Pmacontrol/integrations.view.php'],
                'monitoring' => ['path' => 'pmacontrol/monitoring/', 'view' => 'App/view/Pmacontrol/monitoring.view.php'],
                'performance' => ['path' => 'pmacontrol/performance/', 'view' => 'App/view/Pmacontrol/performance.view.php'],
                'pricing' => ['path' => 'pmacontrol/pricing/', 'view' => 'App/view/Pmacontrol/pricing.view.php'],
                'privacy' => ['path' => 'pmacontrol/privacy/', 'view' => 'App/view/Pmacontrol/privacy.view.php'],
                'product' => ['path' => 'pmacontrol/product/', 'view' => 'App/view/Pmacontrol/product.view.php'],
                'proxysql' => ['path' => 'pmacontrol/proxysql/', 'view' => 'App/view/Pmacontrol/proxysql.view.php'],
                'resources' => ['path' => 'pmacontrol/resources/', 'view' => 'App/view/Pmacontrol/resources.view.php'],
                'roadmap' => ['path' => 'pmacontrol/roadmap/', 'view' => 'App/view/Pmacontrol/roadmap.view.php'],
                'schema' => ['path' => 'pmacontrol/schema/', 'view' => 'App/view/Pmacontrol/schema.view.php'],
                'security' => ['path' => 'pmacontrol/security/', 'view' => 'App/view/Pmacontrol/security.view.php'],
                'security_page' => ['path' => 'pmacontrol/security_page/', 'view' => 'App/view/Pmacontrol/security_page.view.php'],
                'solutions' => ['path' => 'pmacontrol/solutions/', 'view' => 'App/view/Pmacontrol/solutions.view.php'],
                'terms' => ['path' => 'pmacontrol/terms/', 'view' => 'App/view/Pmacontrol/terms.view.php'],
                'webinars' => ['path' => 'pmacontrol/webinars/', 'view' => 'App/view/Pmacontrol/webinars.view.php'],
                'whitepapers' => ['path' => 'pmacontrol/whitepapers/', 'view' => 'App/view/Pmacontrol/whitepapers.view.php'],
            ],
        ],
    ];

    /**
     * @return array<string,array<string,mixed>>
     */
    public static function controllers(): array
    {
        return self::CONTROLLERS;
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function controller(string $controller): ?array
    {
        return self::CONTROLLERS[self::controllerKey($controller)] ?? null;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public static function routes(string $controller): array
    {
        $metadata = self::controller($controller);

        return is_array($metadata) && isset($metadata['routes']) && is_array($metadata['routes'])
            ? $metadata['routes']
            : [];
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function route(string $controller, string $action): ?array
    {
        return self::routes($controller)[self::actionKey($action)] ?? null;
    }

    public static function isPublicMarketingRoute(string $controller, string $action): bool
    {
        return self::route($controller, $action) !== null;
    }

    private static function controllerKey(string $controller): string
    {
        return strtolower(trim($controller));
    }

    private static function actionKey(string $action): string
    {
        return strtolower(trim($action));
    }
}
