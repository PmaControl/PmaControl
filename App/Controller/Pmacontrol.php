<?php

namespace App\Controller;

use \Glial\Synapse\Controller;

/**
 * Class responsible for pmacontrol workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Pmacontrol extends Controller
{
/**
 * Prepare pmacontrol state through `before`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for before.
 * @phpstan-return void
 * @psalm-return void
 * @see self::before()
 * @example /fr/pmacontrol/before
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function before($param)
    {
        $this->layout_name = 'pmacontrol';
        $this->di['js']->addJavascript(array('pmacontrol-marketing.js'));
    }

/**
 * Handle pmacontrol state through `setPageMeta`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $title Input value for `title`.
 * @phpstan-param mixed $title
 * @psalm-param mixed $title
 * @param mixed $ariane Input value for `ariane`.
 * @phpstan-param mixed $ariane
 * @psalm-param mixed $ariane
 * @param array $meta Input value for `meta`.
 * @phpstan-param array $meta
 * @psalm-param array $meta
 * @return void Returned value for setPageMeta.
 * @phpstan-return void
 * @psalm-return void
 * @see self::setPageMeta()
 * @example /fr/pmacontrol/setPageMeta
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function setPageMeta($title, $ariane, array $meta)
    {
        $this->title = $title;
        $this->ariane = $ariane;
        $this->set('meta', $meta);
    }

/**
 * Render pmacontrol state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/pmacontrol/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index()
    {
        $this->setPageMeta(__('PmaControl marketing'), __('PmaControl marketing'), array(
            'title' => 'PmaControl | Production-grade MySQL/MariaDB cockpit',
            'description' => 'Monitor, diagnose, and automate MySQL/MariaDB + ProxySQL + Galera operations with PmaControl.',
            'keywords' => 'MySQL monitoring, MariaDB administration, ProxySQL, Galera, DBA automation',
            'slug' => '/pmacontrol',
            'schema' => 'Product, Organization, WebSite'
        ));
    }

/**
 * Handle pmacontrol state through `product`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for product.
 * @phpstan-return void
 * @psalm-return void
 * @see self::product()
 * @example /fr/pmacontrol/product
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function product()
    {
        $this->setPageMeta(__('Product overview'), __('Product overview'), array(
            'title' => 'PmaControl Product Overview',
            'description' => 'Explore the PmaControl cockpit for MySQL/MariaDB, ProxySQL, and Galera.',
            'keywords' => 'database control plane, MySQL cockpit, MariaDB platform',
            'slug' => '/pmacontrol/product',
            'schema' => 'Product, SoftwareApplication'
        ));
    }

/**
 * Handle pmacontrol state through `monitoring`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for monitoring.
 * @phpstan-return void
 * @psalm-return void
 * @see self::monitoring()
 * @example /fr/pmacontrol/monitoring
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function monitoring()
    {
        $this->setPageMeta(__('Monitoring'), __('Monitoring'), array(
            'title' => 'Monitoring | PmaControl',
            'description' => 'Dashboards for latency, replication, Galera health, and ProxySQL routing.',
            'keywords' => 'MySQL monitoring, Galera monitoring, ProxySQL metrics',
            'slug' => '/pmacontrol/monitoring',
            'schema' => 'SoftwareApplication'
        ));
    }

/**
 * Handle pmacontrol state through `performance`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for performance.
 * @phpstan-return void
 * @psalm-return void
 * @see self::performance()
 * @example /fr/pmacontrol/performance
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function performance()
    {
        $this->setPageMeta(__('Performance'), __('Performance'), array(
            'title' => 'Performance Toolkit | PmaControl',
            'description' => 'Slow log analysis, index advisor, buffer pool insights, and query intelligence.',
            'keywords' => 'slow query analysis, index advisor, MySQL tuning',
            'slug' => '/pmacontrol/performance',
            'schema' => 'SoftwareApplication'
        ));
    }

/**
 * Handle pmacontrol state through `backups`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for backups.
 * @phpstan-return void
 * @psalm-return void
 * @see self::backups()
 * @example /fr/pmacontrol/backups
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function backups()
    {
        $this->setPageMeta(__('Backups & recovery'), __('Backups & recovery'), array(
            'title' => 'Backups & Recovery | PmaControl',
            'description' => 'Orchestrate mydumper/myloader backups, validation, and recovery runbooks.',
            'keywords' => 'MySQL backup, MariaDB restore, PITR, runbooks',
            'slug' => '/pmacontrol/backups',
            'schema' => 'SoftwareApplication'
        ));
    }

/**
 * Handle pmacontrol state through `galera`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for galera.
 * @phpstan-return void
 * @psalm-return void
 * @see self::galera()
 * @example /fr/pmacontrol/galera
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function galera()
    {
        $this->setPageMeta(__('Galera operations'), __('Galera operations'), array(
            'title' => 'Galera Operations | PmaControl',
            'description' => 'Safely bootstrap, rejoin, and analyze flow control across Galera clusters.',
            'keywords' => 'Galera operations, SST, IST, flow control',
            'slug' => '/pmacontrol/galera',
            'schema' => 'SoftwareApplication'
        ));
    }

/**
 * Handle pmacontrol state through `proxysql`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for proxysql.
 * @phpstan-return void
 * @psalm-return void
 * @see self::proxysql()
 * @example /fr/pmacontrol/proxysql
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function proxysql()
    {
        $this->setPageMeta(__('ProxySQL operations'), __('ProxySQL operations'), array(
            'title' => 'ProxySQL Operations | PmaControl',
            'description' => 'Visualize hostgroups, routing rules, and healthchecks.',
            'keywords' => 'ProxySQL governance, query routing, hostgroups',
            'slug' => '/pmacontrol/proxysql',
            'schema' => 'SoftwareApplication'
        ));
    }

/**
 * Handle pmacontrol state through `schema`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for schema.
 * @phpstan-return void
 * @psalm-return void
 * @see self::schema()
 * @example /fr/pmacontrol/schema
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function schema()
    {
        $this->setPageMeta(__('Schema drift & diff'), __('Schema drift & diff'), array(
            'title' => 'Schema Drift & Diff | PmaControl',
            'description' => 'Detect schema divergence and apply standardization across environments.',
            'keywords' => 'schema diff, drift detection, MySQL schema',
            'slug' => '/pmacontrol/schema',
            'schema' => 'SoftwareApplication'
        ));
    }

/**
 * Handle pmacontrol state through `security`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for security.
 * @phpstan-return void
 * @psalm-return void
 * @see self::security()
 * @example /fr/pmacontrol/security
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function security()
    {
        $this->setPageMeta(__('Security & RBAC'), __('Security & RBAC'), array(
            'title' => 'Security & RBAC | PmaControl',
            'description' => 'Granular access control, audit logging, TLS, and secrets management.',
            'keywords' => 'RBAC, audit logs, secrets, TLS',
            'slug' => '/pmacontrol/security',
            'schema' => 'SoftwareApplication'
        ));
    }

/**
 * Handle pmacontrol state through `automation`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for automation.
 * @phpstan-return void
 * @psalm-return void
 * @see self::automation()
 * @example /fr/pmacontrol/automation
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function automation()
    {
        $this->setPageMeta(__('Automation & runbooks'), __('Automation & runbooks'), array(
            'title' => 'Automation & Runbooks | PmaControl',
            'description' => 'Schedule maintenance, automate DBA tasks, and enforce approvals.',
            'keywords' => 'DBA automation, runbooks, scheduled jobs',
            'slug' => '/pmacontrol/automation',
            'schema' => 'SoftwareApplication'
        ));
    }

/**
 * Handle pmacontrol state through `solutions`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for solutions.
 * @phpstan-return void
 * @psalm-return void
 * @see self::solutions()
 * @example /fr/pmacontrol/solutions
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function solutions()
    {
        $this->setPageMeta(__('Solutions'), __('Solutions'), array(
            'title' => 'Solutions | PmaControl',
            'description' => 'Reduce incidents, scale Galera safely, standardize DBA operations.',
            'keywords' => 'MTTR reduction, Galera scale, DBA standardization',
            'slug' => '/pmacontrol/solutions',
            'schema' => 'CollectionPage'
        ));
    }

/**
 * Handle pmacontrol state through `integrations`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for integrations.
 * @phpstan-return void
 * @psalm-return void
 * @see self::integrations()
 * @example /fr/pmacontrol/integrations
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function integrations()
    {
        $this->setPageMeta(__('Integrations'), __('Integrations'), array(
            'title' => 'Integrations | PmaControl',
            'description' => 'Prometheus, Grafana, Alertmanager, Slack, Teams, SSO, Vault.',
            'keywords' => 'Prometheus integration, Grafana dashboards, SSO',
            'slug' => '/pmacontrol/integrations',
            'schema' => 'CollectionPage'
        ));
    }

/**
 * Handle pmacontrol state through `pricing`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for pricing.
 * @phpstan-return void
 * @psalm-return void
 * @see self::pricing()
 * @example /fr/pmacontrol/pricing
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function pricing()
    {
        $this->setPageMeta(__('Pricing'), __('Pricing'), array(
            'title' => 'Pricing | PmaControl',
            'description' => 'Compare SaaS and self-hosted plans for PmaControl.',
            'keywords' => 'PmaControl pricing, SaaS, on-prem',
            'slug' => '/pmacontrol/pricing',
            'schema' => 'OfferCatalog'
        ));
    }

/**
 * Handle pmacontrol state through `docs`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for docs.
 * @phpstan-return void
 * @psalm-return void
 * @see self::docs()
 * @example /fr/pmacontrol/docs
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function docs()
    {
        $this->setPageMeta(__('Docs'), __('Docs'), array(
            'title' => 'Docs | PmaControl',
            'description' => 'Installation, permissions, backups, and troubleshooting guides.',
            'keywords' => 'PmaControl docs, installation, MySQL permissions',
            'slug' => '/pmacontrol/docs',
            'schema' => 'TechArticle'
        ));
    }

/**
 * Handle pmacontrol state through `resources`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for resources.
 * @phpstan-return void
 * @psalm-return void
 * @see self::resources()
 * @example /fr/pmacontrol/resources
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function resources()
    {
        $this->setPageMeta(__('Resources'), __('Resources'), array(
            'title' => 'Resources | PmaControl',
            'description' => 'Blog, whitepapers, case studies, and workshops.',
            'keywords' => 'MySQL resources, DBA guides, Galera workshop',
            'slug' => '/pmacontrol/resources',
            'schema' => 'CollectionPage'
        ));
    }

/**
 * Handle pmacontrol state through `blog`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for blog.
 * @phpstan-return void
 * @psalm-return void
 * @see self::blog()
 * @example /fr/pmacontrol/blog
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function blog()
    {
        $this->setPageMeta(__('Blog'), __('Blog'), array(
            'title' => 'Blog | PmaControl',
            'description' => 'Production-grade MySQL/MariaDB insights.',
            'keywords' => 'MySQL blog, MariaDB, ProxySQL',
            'slug' => '/pmacontrol/blog',
            'schema' => 'Blog'
        ));
    }

/**
 * Handle pmacontrol state through `blog_article`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for blog_article.
 * @phpstan-return void
 * @psalm-return void
 * @see self::blog_article()
 * @example /fr/pmacontrol/blog_article
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function blog_article()
    {
        $this->setPageMeta(__('Blog article'), __('Blog article'), array(
            'title' => 'Blog Article Template | PmaControl',
            'description' => 'Template for production-grade MySQL/MariaDB articles.',
            'keywords' => 'blog template, database article',
            'slug' => '/pmacontrol/blog/article',
            'schema' => 'BlogPosting'
        ));
    }

/**
 * Handle pmacontrol state through `case_studies`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for case_studies.
 * @phpstan-return void
 * @psalm-return void
 * @see self::case_studies()
 * @example /fr/pmacontrol/case_studies
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function case_studies()
    {
        $this->setPageMeta(__('Case studies'), __('Case studies'), array(
            'title' => 'Case Studies | PmaControl',
            'description' => 'Before/after stories with measurable DBA outcomes.',
            'keywords' => 'case study, database operations',
            'slug' => '/pmacontrol/case-studies',
            'schema' => 'CollectionPage'
        ));
    }

/**
 * Handle pmacontrol state through `whitepapers`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for whitepapers.
 * @phpstan-return void
 * @psalm-return void
 * @see self::whitepapers()
 * @example /fr/pmacontrol/whitepapers
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function whitepapers()
    {
        $this->setPageMeta(__('Whitepapers'), __('Whitepapers'), array(
            'title' => 'Whitepapers | PmaControl',
            'description' => 'Technical playbooks for MySQL/MariaDB fleets.',
            'keywords' => 'whitepaper, DBA playbook',
            'slug' => '/pmacontrol/whitepapers',
            'schema' => 'CollectionPage'
        ));
    }

/**
 * Handle pmacontrol state through `webinars`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for webinars.
 * @phpstan-return void
 * @psalm-return void
 * @see self::webinars()
 * @example /fr/pmacontrol/webinars
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function webinars()
    {
        $this->setPageMeta(__('Webinars & workshops'), __('Webinars & workshops'), array(
            'title' => 'Webinars & Workshops | PmaControl',
            'description' => 'Hands-on Galera and ProxySQL training.',
            'keywords' => 'workshop, Galera training, ProxySQL',
            'slug' => '/pmacontrol/webinars',
            'schema' => 'Event'
        ));
    }

/**
 * Handle pmacontrol state through `company`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for company.
 * @phpstan-return void
 * @psalm-return void
 * @see self::company()
 * @example /fr/pmacontrol/company
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function company()
    {
        $this->setPageMeta(__('Company'), __('Company'), array(
            'title' => 'Company | PmaControl',
            'description' => 'Built by a production DBA with 12+ years of experience.',
            'keywords' => 'company, DBA, MySQL architect',
            'slug' => '/pmacontrol/company',
            'schema' => 'Organization'
        ));
    }

/**
 * Handle pmacontrol state through `roadmap`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for roadmap.
 * @phpstan-return void
 * @psalm-return void
 * @see self::roadmap()
 * @example /fr/pmacontrol/roadmap
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function roadmap()
    {
        $this->setPageMeta(__('Public roadmap'), __('Public roadmap'), array(
            'title' => 'Public Roadmap | PmaControl',
            'description' => 'Now, next, later product milestones.',
            'keywords' => 'roadmap, product roadmap',
            'slug' => '/pmacontrol/roadmap',
            'schema' => 'ItemList'
        ));
    }

/**
 * Handle pmacontrol state through `security_page`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for security_page.
 * @phpstan-return void
 * @psalm-return void
 * @see self::security_page()
 * @example /fr/pmacontrol/security_page
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function security_page()
    {
        $this->setPageMeta(__('Security'), __('Security'), array(
            'title' => 'Security | PmaControl',
            'description' => 'Responsible disclosure and data handling.',
            'keywords' => 'security, disclosure, data handling',
            'slug' => '/pmacontrol/security',
            'schema' => 'WebPage'
        ));
    }

/**
 * Handle pmacontrol state through `contact`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for contact.
 * @phpstan-return void
 * @psalm-return void
 * @see self::contact()
 * @example /fr/pmacontrol/contact
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function contact()
    {
        $this->setPageMeta(__('Contact'), __('Contact'), array(
            'title' => 'Contact | PmaControl',
            'description' => 'Request a demo, book a call, or ask for a quote.',
            'keywords' => 'contact, request demo, quote',
            'slug' => '/pmacontrol/contact',
            'schema' => 'ContactPage'
        ));
    }

/**
 * Handle pmacontrol state through `privacy`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for privacy.
 * @phpstan-return void
 * @psalm-return void
 * @see self::privacy()
 * @example /fr/pmacontrol/privacy
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function privacy()
    {
        $this->setPageMeta(__('Privacy policy'), __('Privacy policy'), array(
            'title' => 'Privacy Policy | PmaControl',
            'description' => 'How we handle data and privacy.',
            'keywords' => 'privacy policy, data privacy',
            'slug' => '/pmacontrol/privacy',
            'schema' => 'WebPage'
        ));
    }

/**
 * Handle pmacontrol state through `terms`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for terms.
 * @phpstan-return void
 * @psalm-return void
 * @see self::terms()
 * @example /fr/pmacontrol/terms
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function terms()
    {
        $this->setPageMeta(__('Terms of service'), __('Terms of service'), array(
            'title' => 'Terms of Service | PmaControl',
            'description' => 'Terms for using PmaControl.',
            'keywords' => 'terms of service',
            'slug' => '/pmacontrol/terms',
            'schema' => 'WebPage'
        ));
    }

/**
 * Handle pmacontrol state through `cookies`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for cookies.
 * @phpstan-return void
 * @psalm-return void
 * @see self::cookies()
 * @example /fr/pmacontrol/cookies
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function cookies()
    {
        $this->setPageMeta(__('Cookie policy'), __('Cookie policy'), array(
            'title' => 'Cookie Policy | PmaControl',
            'description' => 'Cookie usage for PmaControl.',
            'keywords' => 'cookie policy',
            'slug' => '/pmacontrol/cookies',
            'schema' => 'WebPage'
        ));
    }

/**
 * Handle pmacontrol state through `ai`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for ai.
 * @phpstan-return void
 * @psalm-return void
 * @see self::ai()
 * @example /fr/pmacontrol/ai
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function ai()
    {
        $this->setPageMeta(__('AI features'), __('AI features'), array(
            'title' => 'AI Features | PmaControl',
            'description' => 'AI-assisted index suggestions and query insights with human-in-control.',
            'keywords' => 'AI DBA, index suggestions, query insights',
            'slug' => '/pmacontrol/ai',
            'schema' => 'WebPage'
        ));
    }
}

