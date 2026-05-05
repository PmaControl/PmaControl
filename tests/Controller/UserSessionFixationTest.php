<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UserSessionFixationTest extends TestCase
{
    private string $controller;

    protected function setUp(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../App/Controller/User.php');
        $this->assertIsString($controller);
        $this->controller = $controller;
    }

    public function testUserControllerUsesSharedSessionFixationGuard(): void
    {
        $this->assertStringContainsString(
            'use App\\Library\\Security\\SessionFixationGuard;',
            $this->controller
        );
    }

    public function testConnectionRegeneratesSessionBetweenAuthenticationAndSuccessRedirect(): void
    {
        $method = $this->extractMethod('connection');

        $this->assertContainsInOrder(
            $method,
            [
                '$authenticated = (bool) $this->di[\'auth\']->authenticate();',
                'SessionFixationGuard::enforceRegenerationAfterSuccessfulAuthentication($authenticated);',
                'if ($authenticated) {',
                'header("Location: " . LINK . ROUTE_DEFAULT);',
            ]
        );
    }

    public function testEstablishSessionRegeneratesOnlyAfterSuccessfulAuthentication(): void
    {
        $method = $this->extractMethod('establishSession');

        $this->assertContainsInOrder(
            $method,
            [
                '$ret = $this->di[\'auth\']->authenticate();',
                'SessionFixationGuard::enforceRegenerationAfterSuccessfulAuthentication((bool) $ret);',
                '$id_user = $this->di[\'auth\']->getIdUserTriingLogin();',
            ]
        );
    }

    public function testLogoutRegeneratesSessionBeforeRedirectingToLogin(): void
    {
        $method = $this->extractMethod('logout');

        $this->assertContainsInOrder(
            $method,
            [
                '$this->di[\'auth\']->logout();',
                'SessionFixationGuard::regenerateActiveSession();',
                'header("Location: " . LINK . "user/connection/");',
            ]
        );
    }

    private function extractMethod(string $method): string
    {
        $start = strpos($this->controller, 'function ' . $method . '(');
        $this->assertNotFalse($start, 'Method not found: ' . $method);

        $brace = strpos($this->controller, '{', $start);
        $this->assertNotFalse($brace, 'Method opening brace not found: ' . $method);

        $depth = 0;
        $length = strlen($this->controller);
        for ($offset = $brace; $offset < $length; $offset++) {
            if ($this->controller[$offset] === '{') {
                $depth++;
                continue;
            }

            if ($this->controller[$offset] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($this->controller, $start, $offset - $start + 1);
                }
            }
        }

        $this->fail('Unable to extract method: ' . $method);
    }

    private function assertContainsInOrder(string $haystack, array $needles): void
    {
        $position = -1;
        foreach ($needles as $needle) {
            $nextPosition = strpos($haystack, $needle, $position + 1);
            $this->assertNotFalse($nextPosition, 'Needle not found: ' . $needle);
            $this->assertGreaterThan($position, $nextPosition, 'Needle is out of order: ' . $needle);
            $position = $nextPosition;
        }
    }
}
