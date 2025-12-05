<?php

namespace App\Controller;

use Glial\Synapse\Controller;

class Site extends Controller
{
    private array $siteCommon = [];

    public function before($param)
    {
        $this->layout_name = 'site';
        $this->siteCommon  = $this->buildCommon();
        $this->set('site_common', $this->siteCommon);
    }

    private function buildCommon(): array
    {
        $menu = [
            [
                'id'     => 'home',
                'route'  => 'site/index/',
                'labels' => [
                    'fr' => 'Accueil',
                    'en' => 'Home',
                ],
            ],
            [
                'id'     => 'agents',
                'route'  => 'site/agents/',
                'labels' => [
                    'fr' => 'Agent IA',
                    'en' => 'AI Agents',
                ],
            ],
            [
                'id'     => 'max',
                'route'  => 'site/max/',
                'labels' => [
                    'fr' => 'Max+',
                    'en' => 'Max+',
                ],
            ],
            [
                'id'     => 'offers',
                'route'  => 'site/offers/',
                'labels' => [
                    'fr' => 'Nos offres',
                    'en' => 'Plans',
                ],
                'children' => [
                    [
                        'id'     => 'saas',
                        'route'  => 'site/saas/',
                        'labels' => [
                            'fr' => 'SaaS & DBaaS',
                            'en' => 'SaaS & DBaaS',
                        ],
                    ],
                    [
                        'id'     => 'onpremise',
                        'route'  => 'site/onpremise/',
                        'labels' => [
                            'fr' => 'On-Premise',
                            'en' => 'On-Premise',
                        ],
                    ],
                ],
            ],
            [
                'id'     => 'documentation',
                'route'  => 'site/documentation/',
                'labels' => [
                    'fr' => 'Documentation',
                    'en' => 'Documentation',
                ],
            ],
            [
                'id'     => 'faq',
                'route'  => 'site/faq/',
                'labels' => [
                    'fr' => 'FAQ',
                    'en' => 'FAQ',
                ],
            ],
            [
                'id'     => 'process',
                'route'  => 'site/process/',
                'labels' => [
                    'fr' => 'Processus',
                    'en' => 'Process',
                ],
            ],
            [
                'id'     => 'integrations',
                'route'  => 'site/integrations/',
                'labels' => [
                    'fr' => 'Intégrations',
                    'en' => 'Integrations',
                ],
            ],
            [
                'id'     => 'demo',
                'route'  => 'site/demo/',
                'labels' => [
                    'fr' => 'Réserver une démo',
                    'en' => 'Book a demo',
                ],
                'is_cta' => true,
            ],
            [
                'id'       => 'login',
                'route'    => 'User/login/',
                'labels'   => [
                    'fr' => 'Se connecter',
                    'en' => 'Sign in',
                ],
                'external' => true,
            ],
        ];

        return [
            'phone' => '+33 6 63 28 27 47',
            'menu'  => $menu,
        ];
    }

    private function setActive(string $id): void
    {
        $this->set('site_active', $id);
    }

    public function index()
    {
        $this->title = "PmaControl – Observabilité SQL augmentée";
        $this->setActive('home');
    }

    public function saas()
    {
        $this->title = "PmaControl SaaS & DBaaS";
        $this->setActive('saas');
    }

    public function onpremise()
    {
        $this->title = "PmaControl On-Premise";
        $this->setActive('onpremise');
    }

    public function agents()
    {
        $this->title = "Agents IA PmaControl";
        $this->setActive('agents');
    }

    public function max()
    {
        $this->title = "Max+ Platform";
        $this->setActive('max');
    }

    public function offers()
    {
        $this->title = "Offres & plans PmaControl";
        $this->setActive('offers');
    }

    public function integrations()
    {
        $this->title = "Intégrations PmaControl";
        $this->setActive('integrations');
    }

    public function documentation()
    {
        $this->title = "Documentation & support";
        $this->setActive('documentation');
    }

    public function faq()
    {
        $this->title = "FAQ PmaControl";
        $this->setActive('faq');
    }

    public function process()
    {
        $this->title = "Processus d’accompagnement";
        $this->setActive('process');
    }

    public function demo()
    {
        $this->title = "Réserver une démo";
        $this->setActive('demo');
    }

    public function resources()
    {
        $this->title = "Ressources & contenus";
        $this->setActive('resources');
    }

    public function blog()
    {
        $this->title = "Blog & Insights";
        $this->setActive('blog');
    }

    public function support()
    {
        $this->title = "Centre de support";
        $this->setActive('support');
    }

    public function contact()
    {
        $this->title = "Contact PmaControl";
        $this->setActive('contact');
    }

    public function roadmap()
    {
        $this->title = "Roadmap publique";
        $this->setActive('roadmap');
    }

    public function success()
    {
        $this->title = "Success Stories";
        $this->setActive('success');
    }

    public function lab()
    {
        $this->title = "Internal Lab Showcase";
        $this->setActive('lab');
    }

    public function incidents()
    {
        $this->title = "Incidents Hall of Fame";
        $this->setActive('incidents');
    }
}
