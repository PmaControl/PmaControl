<?php

declare(strict_types=1);

use App\Controller\Color;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class ColorIndexSecurityTest extends TestCase
{
    public function testIndexAcceptsValidTokenAndNormalizesRows(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'color.index');

        $outcome = Color::evaluateIndexRequest($this->validPost($token), $this->sameSitePostServer(), $session);

        $this->assertSame(200, $outcome['status']);
        $this->assertSame([
            7 => [
                'font' => '#FFFFFF',
                'color' => '#00AA00',
                'background' => '#101010',
                'style' => 'filled',
            ],
        ], $outcome['rows']);
    }

    public function testIndexRejectsNonPost(): void
    {
        $outcome = Color::evaluateIndexRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['rows']);
    }

    public function testIndexRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'color.index');

        $outcome = Color::evaluateIndexRequest(
            $this->validPost($token),
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
        $this->assertNull($outcome['rows']);
    }

    public function testIndexRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'format.index');
        $server = $this->sameSitePostServer();

        $missingToken = Color::evaluateIndexRequest($this->validPost(null), $server, $session);
        $foreignScope = Color::evaluateIndexRequest($this->validPost($foreignToken), $server, $session);

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testIndexRejectsInvalidPayloads(): void
    {
        foreach ($this->invalidPosts() as $post) {
            $this->assertNull(Color::normalizeIndexPayload($post));
        }
    }

    public function testIndexControllerAndViewUseCsrfGuard(): void
    {
        $controller = (string)file_get_contents(__DIR__ . '/../../App/Controller/Color.php');
        $view = (string)file_get_contents(__DIR__ . '/../../App/view/Color/index.view.php');
        $indexStart = strpos($controller, 'public function index($param)');
        $normalizeStart = strpos($controller, 'public static function evaluateIndexRequest');

        $this->assertNotFalse($indexStart);
        $this->assertNotFalse($normalizeStart);
        $indexBody = substr($controller, $indexStart, $normalizeStart - $indexStart);

        $this->assertStringContainsString("private const COLOR_INDEX_CSRF_SCOPE = 'color.index'", $controller);
        $this->assertStringContainsString('self::evaluateIndexRequest($_POST, $_SERVER, $_SESSION)', $indexBody);
        $this->assertStringContainsString('CsrfGuard::check($post, $server, $session, self::COLOR_INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::COLOR_INDEX_CSRF_SCOPE)', $controller);
        $this->assertStringNotContainsString('foreach ($_POST[\'dot3_legend\']', $indexBody);
        $this->assertStringContainsString('$colorIndexCsrfField', $view);
        $this->assertStringContainsString('$colorIndexCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('method="post"', $view);
    }

    private function validPost(?string $token): array
    {
        $post = [
            'dot3_legend' => [
                '7' => [
                    'font' => ' #ffffff ',
                    'color' => '#00aa00',
                    'background' => '#101010',
                    'style' => 'filled',
                ],
            ],
        ];

        if ($token !== null) {
            $post[Csrf::DEFAULT_FIELD] = $token;
        }

        return $post;
    }

    private function invalidPosts(): array
    {
        $base = $this->validPost('token');
        $missingGroup = [];
        $nonArrayGroup = ['dot3_legend' => 'invalid'];
        $badId = $base;
        $badId['dot3_legend'] = ['7 OR 1=1' => $base['dot3_legend']['7']];
        $missingStyle = $base;
        unset($missingStyle['dot3_legend']['7']['style']);
        $unknownField = $base;
        $unknownField['dot3_legend']['7']['unexpected'] = '1';
        $newlineColor = $base;
        $newlineColor['dot3_legend']['7']['color'] = "#fff\n#000";
        $longFont = $base;
        $longFont['dot3_legend']['7']['font'] = str_repeat('a', 33);
        $arrayBackground = $base;
        $arrayBackground['dot3_legend']['7']['background'] = ['#000000'];
        $invalidStyle = $base;
        $invalidStyle['dot3_legend']['7']['style'] = 'onclick';

        return [
            $missingGroup,
            $nonArrayGroup,
            $badId,
            $missingStyle,
            $unknownField,
            $newlineColor,
            $longFont,
            $arrayBackground,
            $invalidStyle,
        ];
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
