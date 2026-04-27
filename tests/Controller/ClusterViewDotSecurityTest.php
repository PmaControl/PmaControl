<?php

declare(strict_types=1);

use App\Controller\Cluster;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ClusterViewDotSecurityTest extends TestCase
{
    public function testViewDotAcceptsValidPostToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cluster.view_dot');

        $outcome = Cluster::evaluateViewDotPostRequest(
            [Csrf::DEFAULT_FIELD => $token, 'dot_preview' => ['dot' => 'digraph G {}']],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
    }

    public function testViewDotRejectsNonPost(): void
    {
        $outcome = Cluster::evaluateViewDotPostRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
    }

    public function testViewDotRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cluster.view_dot');

        $outcome = Cluster::evaluateViewDotPostRequest(
            [Csrf::DEFAULT_FIELD => $token, 'dot_import' => ['payload' => '{}']],
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
    }

    public function testViewDotRejectsExternalRefererWhenOriginIsMissing(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'cluster.view_dot');

        $outcome = Cluster::evaluateViewDotPostRequest(
            [Csrf::DEFAULT_FIELD => $token, 'dot_preview' => ['dot' => 'digraph G {}']],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_REFERER' => 'https://attacker.test/post',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
    }

    public function testViewDotRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'cluster.history');
        $server = $this->sameSitePostServer();

        $missingToken = Cluster::evaluateViewDotPostRequest(['dot_preview' => ['dot' => 'digraph G {}']], $server, $session);
        $foreignScope = Cluster::evaluateViewDotPostRequest([Csrf::DEFAULT_FIELD => $foreignToken, 'dot_preview' => ['dot' => 'digraph G {}']], $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testViewDotControllerAndFormsUseCsrfGuard(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Cluster.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Cluster/viewDot.view.php');
        $viewDotStart = strpos($controller, 'public function viewDot($param)');
        $svgPayloadStart = strpos($controller, 'private static function isSvgPreviewPayload');

        $this->assertNotFalse($viewDotStart);
        $this->assertNotFalse($svgPayloadStart);
        $viewDotBody = substr($controller, $viewDotStart, $svgPayloadStart - $viewDotStart);

        $guardPosition = strpos($viewDotBody, 'self::evaluateViewDotPostRequest($_POST, $_SERVER, $_SESSION)');
        $importPosition = strpos($viewDotBody, 'isset($_POST[\'dot_import\'])');
        $previewPosition = strpos($viewDotBody, 'isset($_POST[\'dot_preview\'][\'dot\'])');

        $this->assertIsInt($guardPosition);
        $this->assertIsInt($importPosition);
        $this->assertIsInt($previewPosition);
        $this->assertLessThan($importPosition, $guardPosition);
        $this->assertLessThan($previewPosition, $guardPosition);
        $this->assertStringContainsString("private const VIEW_DOT_CSRF_SCOPE = 'cluster.view_dot'", $controller);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::VIEW_DOT_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::VIEW_DOT_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('$viewDotCsrfField', $view);
        $this->assertStringContainsString('$viewDotCsrfToken', $view);
        $this->assertStringContainsString('id="dot-import-form"', $view);
        $this->assertStringContainsString('id="dot-online-form"', $view);
        $this->assertGreaterThanOrEqual(2, substr_count($view, 'type="hidden" name="<?= $viewDotCsrfField ?>"'));
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
}
