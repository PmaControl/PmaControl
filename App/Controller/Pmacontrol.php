<?php

namespace App\Controller;

use \Glial\Synapse\Controller;

class Pmacontrol extends Controller
{
    public function before($param)
    {
        $this->layout_name = 'pmacontrol';
        $this->di['js']->addJavascript(array('pmacontrol-marketing.js'));
    }

    private function setPageMeta($title, $ariane, array $meta)
    {
        $this->title = $title;
        $this->ariane = $ariane;
        $this->set('meta', $meta);
    }

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
