<?php

declare(strict_types=1);

use App\Controller\Site;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class SiteDemoSecurityTest extends TestCase
{
    public function testDemoRequestAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'site.demo');

        $outcome = Site::evaluateDemoRequest(
            $this->validPost($token),
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(
            [
                'name' => 'Jane Doe / Acme',
                'email' => 'team@example.com',
                'mode' => 'SaaS / DBaaS',
                'servers' => '6 MySQL',
                'context' => 'Need a demo',
            ],
            $outcome['demo_request']
        );
    }

    public function testDemoRequestRejectsNonPost(): void
    {
        $outcome = Site::evaluateDemoRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['demo_request']);
    }

    public function testDemoRequestRejectsExternalOriginBeforePayload(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'site.demo');

        $outcome = Site::evaluateDemoRequest(
            [Csrf::DEFAULT_FIELD => $token, 'email' => ['invalid']],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['demo_request']);
    }

    public function testDemoRequestRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'user.connection');
        $server = $this->sameSitePostServer();

        $missingToken = Site::evaluateDemoRequest($this->validPost(null), $server, $session);
        $foreignScope = Site::evaluateDemoRequest($this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testDemoPayloadRejectsInvalidValues(): void
    {
        $post = $this->validPost('token');

        $this->assertNull(Site::normalizeDemoPayload(array_replace($post, ['name' => ['Jane']])));
        $this->assertNull(Site::normalizeDemoPayload(array_replace($post, ['email' => 'not-an-email'])));
        $this->assertNull(Site::normalizeDemoPayload(array_replace($post, ['email' => str_repeat('a', 255) . '@example.com'])));
        $this->assertNull(Site::normalizeDemoPayload(array_replace($post, ['mode' => str_repeat('a', 65)])));
        $this->assertNull(Site::normalizeDemoPayload(array_replace($post, ['servers' => str_repeat('a', 121)])));
        $this->assertNull(Site::normalizeDemoPayload(array_replace($post, ['context' => str_repeat('a', 2001)])));
    }

    public function testDemoPayloadAllowsEmptyOptionalFieldsAndTrimsScalars(): void
    {
        $this->assertSame(
            [
                'name' => 'Jane',
                'email' => '',
                'mode' => '',
                'servers' => '',
                'context' => 'Need a demo',
            ],
            Site::normalizeDemoPayload([
                'name' => '  Jane  ',
                'context' => '  Need a demo  ',
            ])
        );
    }

    public function testExternalPostWouldPassLegacyMethodGateButIsRejectedBeforeFutureHandling(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'site.demo');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://attacker.test',
        ];

        $this->assertTrue(strtoupper($server['REQUEST_METHOD']) === 'POST');

        $outcome = Site::evaluateDemoRequest($this->validPost($token), $server, $session);

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['demo_request']);
    }

    public function testDemoUsesSharedCsrfGuardAndFormCarriesToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Site.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Site/demo.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('use App\\Library\\Security\\CsrfGuard;', $controller);
        $this->assertStringContainsString('use Glial\\Security\\Csrf;', $controller);
        $this->assertStringContainsString("private const SITE_DEMO_CSRF_SCOPE = 'site.demo'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::SITE_DEMO_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('CsrfGuard::ensureOrFail($post, $server, $session, self::SITE_DEMO_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('method="post"', $view);
        $this->assertStringContainsString('$siteDemoCsrfField', $view);
        $this->assertStringContainsString('$siteDemoCsrfToken', $view);
        $this->assertStringContainsString('<input type="hidden" name="<?= $siteDemoCsrfField ?>" value="<?= $siteDemoCsrfToken ?>">', $view);
    }

    private function sameSitePostServer(): array
    {
        return [
            'REQUEST_METHOD' => 'POST',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'pmacontrol.test',
            'HTTP_ORIGIN' => 'https://pmacontrol.test',
        ];
    }

    private function validPost(?string $token): array
    {
        $post = [
            'name' => 'Jane Doe / Acme',
            'email' => 'team@example.com',
            'mode' => 'SaaS / DBaaS',
            'servers' => '6 MySQL',
            'context' => 'Need a demo',
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }
}
