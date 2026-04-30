<?php

namespace App\Controller;

use App\Library\Security\CsrfGuard;
use Glial\Security\Csrf;
use Glial\Synapse\Controller;

/**
 * Class responsible for site workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * Public marketing route classification is declared in
 * App\Library\PublicRouteCatalog for issue #513. This controller is
 * intentionally outside the admin menu while issue #522 decides the canonical
 * website front.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Site extends Controller
{
    private const SITE_DEMO_CSRF_SCOPE = 'site.demo';
    private const SITE_DEMO_FIELD_LIMITS = [
        'name' => 120,
        'email' => 254,
        'mode' => 64,
        'servers' => 120,
        'context' => 2000,
    ];

    private array $siteCommon = [];

/**
 * Prepare site state through `before`.
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
 * @example /fr/site/before
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
        $this->layout_name = 'site';
        $this->siteCommon  = $this->buildCommon();
        $this->set('site_common', $this->siteCommon);
    }

/**
 * Handle site state through `buildCommon`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return array Returned value for buildCommon.
 * @phpstan-return array
 * @psalm-return array
 * @see self::buildCommon()
 * @example /fr/site/buildCommon
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
                'route'    => 'User/connection/',
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

/**
 * Handle site state through `setActive`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $id Input value for `id`.
 * @phpstan-param string $id
 * @psalm-param string $id
 * @return void Returned value for setActive.
 * @phpstan-return void
 * @psalm-return void
 * @see self::setActive()
 * @example /fr/site/setActive
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function setActive(string $id): void
    {
        $this->set('site_active', $id);
    }

/**
 * Render site state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/site/index
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
        $this->title = "PmaControl – Observabilité SQL augmentée";
        $this->setActive('home');
    }

/**
 * Handle site state through `saas`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for saas.
 * @phpstan-return void
 * @psalm-return void
 * @see self::saas()
 * @example /fr/site/saas
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function saas()
    {
        $this->title = "PmaControl SaaS & DBaaS";
        $this->setActive('saas');
    }

/**
 * Handle site state through `onpremise`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for onpremise.
 * @phpstan-return void
 * @psalm-return void
 * @see self::onpremise()
 * @example /fr/site/onpremise
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function onpremise()
    {
        $this->title = "PmaControl On-Premise";
        $this->setActive('onpremise');
    }

/**
 * Handle site state through `agents`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for agents.
 * @phpstan-return void
 * @psalm-return void
 * @see self::agents()
 * @example /fr/site/agents
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function agents()
    {
        $this->title = "Agents IA PmaControl";
        $this->setActive('agents');
    }

/**
 * Handle site state through `max`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for max.
 * @phpstan-return void
 * @psalm-return void
 * @see self::max()
 * @example /fr/site/max
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function max()
    {
        $this->title = "Max+ Platform";
        $this->setActive('max');
    }

/**
 * Handle site state through `offers`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for offers.
 * @phpstan-return void
 * @psalm-return void
 * @see self::offers()
 * @example /fr/site/offers
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function offers()
    {
        $this->title = "Offres & plans PmaControl";
        $this->setActive('offers');
    }

/**
 * Handle site state through `integrations`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for integrations.
 * @phpstan-return void
 * @psalm-return void
 * @see self::integrations()
 * @example /fr/site/integrations
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
        $this->title = "Intégrations PmaControl";
        $this->setActive('integrations');
    }

/**
 * Handle site state through `documentation`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for documentation.
 * @phpstan-return void
 * @psalm-return void
 * @see self::documentation()
 * @example /fr/site/documentation
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function documentation()
    {
        $this->title = "Documentation & support";
        $this->setActive('documentation');
    }

/**
 * Handle site state through `faq`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for faq.
 * @phpstan-return void
 * @psalm-return void
 * @see self::faq()
 * @example /fr/site/faq
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function faq()
    {
        $this->title = "FAQ PmaControl";
        $this->setActive('faq');
    }

/**
 * Handle site state through `process`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for process.
 * @phpstan-return void
 * @psalm-return void
 * @see self::process()
 * @example /fr/site/process
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function process()
    {
        $this->title = "Processus d’accompagnement";
        $this->setActive('process');
    }

/**
 * Handle site state through `demo`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for demo.
 * @phpstan-return void
 * @psalm-return void
 * @see self::demo()
 * @example /fr/site/demo
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function demo()
    {
        $this->title = "Réserver une démo";
        $this->setActive('demo');

        $data = [
            'site_demo_csrf_field' => Csrf::DEFAULT_FIELD,
            'site_demo_csrf_token' => Csrf::issueToken($_SESSION, self::SITE_DEMO_CSRF_SCOPE),
            'site_demo_submitted' => false,
        ];

        if (CsrfGuard::isPost($_SERVER)) {
            $outcome = self::evaluateDemoRequest($_POST, $_SERVER, $_SESSION);
            if ($outcome['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendDemoError($outcome['status'], $outcome['body'], $outcome['headers']);
                return;
            }

            $data['site_demo_submitted'] = true;
            $data['site_demo_request'] = $outcome['demo_request'];
        }

        $this->set('data', $data);
    }

    public static function evaluateDemoRequest(array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::SITE_DEMO_CSRF_SCOPE)) {
            return self::buildDemoOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $demoRequest = self::normalizeDemoPayload($post);
        if ($demoRequest === null) {
            return self::buildDemoOutcome(400, 'Invalid demo request payload');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'demo_request' => $demoRequest,
        ];
    }

    public static function normalizeDemoPayload(array $post): ?array
    {
        $payload = [];
        foreach (self::SITE_DEMO_FIELD_LIMITS as $field => $limit) {
            $value = $post[$field] ?? '';
            if (!is_scalar($value)) {
                return null;
            }

            $value = trim((string) $value);
            if (strlen($value) > $limit) {
                return null;
            }

            $payload[$field] = $value;
        }

        if ($payload['email'] !== '' && filter_var($payload['email'], FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        return $payload;
    }

    private static function buildDemoOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'demo_request' => null,
        ];
    }

    private static function sendDemoError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Handle site state through `resources`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for resources.
 * @phpstan-return void
 * @psalm-return void
 * @see self::resources()
 * @example /fr/site/resources
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
        $this->title = "Ressources & contenus";
        $this->setActive('resources');
    }

/**
 * Handle site state through `blog`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for blog.
 * @phpstan-return void
 * @psalm-return void
 * @see self::blog()
 * @example /fr/site/blog
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
        $this->title = "Blog & Insights";
        $this->setActive('blog');
    }

/**
 * Handle site state through `support`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for support.
 * @phpstan-return void
 * @psalm-return void
 * @see self::support()
 * @example /fr/site/support
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function support()
    {
        $this->title = "Centre de support";
        $this->setActive('support');
    }

/**
 * Handle site state through `contact`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for contact.
 * @phpstan-return void
 * @psalm-return void
 * @see self::contact()
 * @example /fr/site/contact
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
        $this->title = "Contact PmaControl";
        $this->setActive('contact');
    }

/**
 * Handle site state through `roadmap`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for roadmap.
 * @phpstan-return void
 * @psalm-return void
 * @see self::roadmap()
 * @example /fr/site/roadmap
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
        $this->title = "Roadmap publique";
        $this->setActive('roadmap');
    }

/**
 * Handle site state through `success`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for success.
 * @phpstan-return void
 * @psalm-return void
 * @see self::success()
 * @example /fr/site/success
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function success()
    {
        $this->title = "Success Stories";
        $this->setActive('success');
    }

/**
 * Handle site state through `lab`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for lab.
 * @phpstan-return void
 * @psalm-return void
 * @see self::lab()
 * @example /fr/site/lab
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function lab()
    {
        $this->title = "Internal Lab Showcase";
        $this->setActive('lab');
    }

/**
 * Handle site state through `incidents`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for incidents.
 * @phpstan-return void
 * @psalm-return void
 * @see self::incidents()
 * @example /fr/site/incidents
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function incidents()
    {
        $this->title = "Incidents Hall of Fame";
        $this->setActive('incidents');
    }
}
