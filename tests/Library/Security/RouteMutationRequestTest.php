<?php

declare(strict_types=1);

use App\Library\Security\RouteMutationRequest;
use Glial\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class RouteMutationRequestTest extends TestCase
{
    public function testAcceptsMatchingRouteAndPostId(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'server.acknowledge');

        $outcome = RouteMutationRequest::evaluate(
            [Csrf::DEFAULT_FIELD => $token, 'id_server' => '7'],
            $this->sameSitePostServer(),
            $session,
            ['7'],
            'server.acknowledge',
            'Invalid server id'
        );

        $this->assertSame(200, $outcome['status']);
        $this->assertSame(7, $outcome['id']);
    }

    public function testRejectsNonPostBeforeIdValidation(): void
    {
        $outcome = RouteMutationRequest::evaluate(
            ['id_server' => '7 OR 1=1'],
            ['REQUEST_METHOD' => 'GET'],
            [],
            ['7 OR 1=1'],
            'server.acknowledge',
            'Invalid server id'
        );

        $this->assertSame(405, $outcome['status']);
        $this->assertSame('POST', $outcome['headers']['Allow']);
        $this->assertNull($outcome['id']);
    }

    public function testRejectsExternalOriginBeforeIdValidation(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'server.acknowledge');

        $outcome = RouteMutationRequest::evaluate(
            [Csrf::DEFAULT_FIELD => $token, 'id_server' => '7 OR 1=1'],
            [
                'REQUEST_METHOD' => 'POST',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'pmacontrol.test',
                'HTTP_ORIGIN' => 'https://attacker.test',
            ],
            $session,
            ['7 OR 1=1'],
            'server.acknowledge',
            'Invalid server id'
        );

        $this->assertSame(403, $outcome['status']);
        $this->assertSame('Invalid request origin', $outcome['body']);
        $this->assertNull($outcome['id']);
    }

    public function testRejectsMissingOrForeignToken(): void
    {
        $session = [];
        $foreignToken = Csrf::issueToken($session, 'server.retract');
        $server = $this->sameSitePostServer();

        $missingToken = RouteMutationRequest::evaluate(
            ['id_server' => '7'],
            $server,
            $session,
            ['7'],
            'server.acknowledge',
            'Invalid server id'
        );
        $foreignScope = RouteMutationRequest::evaluate(
            [Csrf::DEFAULT_FIELD => $foreignToken, 'id_server' => '7'],
            $server,
            $session,
            ['7'],
            'server.acknowledge',
            'Invalid server id'
        );

        $this->assertSame(403, $missingToken['status']);
        $this->assertSame('Invalid CSRF token', $missingToken['body']);
        $this->assertSame(403, $foreignScope['status']);
        $this->assertSame('Invalid CSRF token', $foreignScope['body']);
    }

    public function testRejectsInvalidRouteOrPostIdsAfterValidCsrf(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'server.acknowledge');
        $server = $this->sameSitePostServer();

        foreach ([['0'], ['7 OR 1=1'], [['7']], ['-1'], [(string) PHP_INT_MAX . '0']] as $param) {
            $outcome = RouteMutationRequest::evaluate(
                [Csrf::DEFAULT_FIELD => $token],
                $server,
                $session,
                $param,
                'server.acknowledge',
                'Invalid server id'
            );

            $this->assertSame(400, $outcome['status']);
            $this->assertSame('Invalid server id', $outcome['body']);
            $this->assertNull($outcome['id']);
        }

        $postOutcome = RouteMutationRequest::evaluate(
            [Csrf::DEFAULT_FIELD => $token, 'id_server' => '7 OR 1=1'],
            $server,
            $session,
            [],
            'server.acknowledge',
            'Invalid server id'
        );

        $this->assertSame(400, $postOutcome['status']);
        $this->assertNull($postOutcome['id']);
    }

    public function testRejectsRouteAndPostMismatch(): void
    {
        $session = [];
        $token = Csrf::issueToken($session, 'server.acknowledge');

        $outcome = RouteMutationRequest::evaluate(
            [Csrf::DEFAULT_FIELD => $token, 'id_server' => '7'],
            $this->sameSitePostServer(),
            $session,
            ['42'],
            'server.acknowledge',
            'Invalid server id'
        );

        $this->assertSame(400, $outcome['status']);
        $this->assertNull($outcome['id']);
    }

    public function testInvalidRouteIsRejectedEvenWhenPostIdIsValid(): void
    {
        $this->assertNull(RouteMutationRequest::normalizeRequestedId(['id_server' => '7'], ['7 OR 1=1']));
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
