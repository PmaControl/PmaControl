<?php

declare(strict_types=1);

use App\Controller\Environment;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class EnvironmentAddSecurityTest extends TestCase
{
    public function testEnvironmentAddAcceptsValidPost(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'environment.add');

        $outcome = Environment::evaluateAddRequest(
            [
                Csrf::DEFAULT_FIELD => $token,
                'environment' => $this->validEnvironmentPayload(),
            ],
            $this->sameSitePostServer(),
            $session
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame($this->validEnvironmentPayload(), $outcome['environment']);
    }

    public function testEnvironmentAddRejectsNonPost(): void
    {
        $outcome = Environment::evaluateAddRequest([], ['REQUEST_METHOD' => 'GET'], []);

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['environment']);
    }

    public function testEnvironmentAddRejectsExternalOriginBeforeToken(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'environment.add');

        $outcome = Environment::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $token, 'environment' => $this->validEnvironmentPayload()],
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
        $this->assertNull($outcome['environment']);
    }

    public function testEnvironmentAddRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'environment.update');
        $server = $this->sameSitePostServer();

        $missingToken = Environment::evaluateAddRequest(
            ['environment' => $this->validEnvironmentPayload()],
            $server,
            $session
        );
        $foreignScope = Environment::evaluateAddRequest(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'environment' => $this->validEnvironmentPayload()],
            $server,
            $session
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testEnvironmentAddPayloadRejectsMalformedUnexpectedOrOutOfSchemaValues(): void
    {
        $this->assertNull(Environment::normalizeAddPayload([]));
        $this->assertNull(Environment::normalizeAddPayload(['environment' => 'Preprod']));
        $this->assertNull(Environment::normalizeAddPayload(['environment' => []]));
        $this->assertNull(Environment::normalizeAddPayload(['environment' => $this->validEnvironmentPayload(['id' => '7'])]));
        $this->assertNull(Environment::normalizeAddPayload(['environment' => $this->validEnvironmentPayload(['libelle' => ['Preprod']])]));
        $this->assertNull(Environment::normalizeAddPayload(['environment' => $this->validEnvironmentPayload(['libelle' => str_repeat('a', 21)])]));
        $this->assertNull(Environment::normalizeAddPayload(['environment' => $this->validEnvironmentPayload(['key' => str_repeat('a', 14)])]));
        $this->assertNull(Environment::normalizeAddPayload(['environment' => $this->validEnvironmentPayload(['class' => 'owned'])]));
        $this->assertNull(Environment::normalizeAddPayload(['environment' => $this->validEnvironmentPayload(['letter' => 'PR'])]));
        $this->assertNull(Environment::normalizeAddPayload(['environment' => $this->validEnvironmentPayload(['letter' => ''])]));

        $this->assertSame(
            $this->validEnvironmentPayload(['libelle' => 'Preprod']),
            Environment::normalizeAddPayload(['environment' => $this->validEnvironmentPayload(['libelle' => ' Preprod '])])
        );
    }

    public function testExternalPostWouldHaveReachedLegacyMutationButIsRejected(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'environment.add');
        $post = [Csrf::DEFAULT_FIELD => $token, 'environment' => $this->validEnvironmentPayload(['libelle' => 'Owned'])];

        $this->assertSame(
            $this->validEnvironmentPayload(['libelle' => 'Owned']),
            Environment::normalizeAddPayload($post)
        );

        $outcome = Environment::evaluateAddRequest(
            $post,
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertNull($outcome['environment']);
    }

    public function testEnvironmentAddUsesSharedCsrfLibraryAndViewSendsToken(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/Environment.php');
        $view = file_get_contents(__DIR__ . '/../../App/view/Environment/add.view.php');

        $this->assertIsString($controller);
        $this->assertIsString($view);

        $this->assertStringContainsString('use App\\Library\\Security\\GroupedFormRequest;', $controller);
        $this->assertStringContainsString("private const ENVIRONMENT_ADD_CSRF_SCOPE = 'environment.add'", $controller);
        $this->assertStringContainsString('Csrf::issueToken($_SESSION, self::ENVIRONMENT_ADD_CSRF_SCOPE)', $controller);
        $this->assertStringContainsString('GroupedFormRequest::evaluate(', $controller);
        $this->assertStringContainsString('CsrfGuard::isPost($_SERVER)', $controller);

        $this->assertStringContainsString('$environmentAddCsrfField', $view);
        $this->assertStringContainsString('$environmentAddCsrfToken', $view);
        $this->assertStringContainsString('type="hidden"', $view);
        $this->assertStringContainsString('method="post"', $view);
    }

    private function validEnvironmentPayload(array $override = []): array
    {
        return array_replace([
            'libelle' => 'Preprod',
            'key' => 'preprod',
            'class' => 'warning',
            'letter' => 'P',
        ], $override);
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
