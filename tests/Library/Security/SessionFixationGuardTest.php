<?php

declare(strict_types=1);

use App\Library\Security\SessionFixationGuard;
use PHPUnit\Framework\TestCase;

final class SessionFixationGuardTest extends TestCase
{
    public function testAuthenticationFailureDoesNotInspectOrRegenerateSession(): void
    {
        $this->assertFalse(SessionFixationGuard::enforceRegenerationAfterSuccessfulAuthentication(
            false,
            fn (): int => $this->fail('Session status must not be read after failed authentication.'),
            fn (): bool => $this->fail('Header state must not be read after failed authentication.'),
            fn (): bool => $this->fail('Session must not be regenerated after failed authentication.')
        ));
    }

    public function testSuccessfulAuthenticationRegeneratesActiveSessionWithServerSideDeletion(): void
    {
        $deleteOldSession = null;

        $result = SessionFixationGuard::enforceRegenerationAfterSuccessfulAuthentication(
            true,
            fn (): int => PHP_SESSION_ACTIVE,
            fn (): bool => false,
            function (bool $deleteOldSessionArgument) use (&$deleteOldSession): bool {
                $deleteOldSession = $deleteOldSessionArgument;

                return true;
            }
        );

        $this->assertTrue($result);
        $this->assertTrue($deleteOldSession);
    }

    public function testInactiveSessionThrowsWhenStrictRegenerationIsRequired(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to regenerate session ID after successful authentication.');

        SessionFixationGuard::enforceRegenerationAfterSuccessfulAuthentication(
            true,
            fn (): int => PHP_SESSION_NONE,
            fn (): bool => $this->fail('Header state must not be read without an active session.'),
            fn (): bool => $this->fail('Inactive session must not be regenerated.')
        );
    }

    public function testSentHeadersThrowWhenStrictRegenerationIsRequired(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to regenerate session ID after successful authentication.');

        SessionFixationGuard::enforceRegenerationAfterSuccessfulAuthentication(
            true,
            fn (): int => PHP_SESSION_ACTIVE,
            fn (): bool => true,
            fn (): bool => $this->fail('Session must not be regenerated after headers were sent.')
        );
    }

    public function testLogoutSoftRegenerationReturnsFalseWhenHeadersWereSent(): void
    {
        $this->assertFalse(SessionFixationGuard::regenerateActiveSession(
            fn (): int => PHP_SESSION_ACTIVE,
            fn (): bool => true,
            fn (): bool => $this->fail('Session must not be regenerated after headers were sent.')
        ));
    }

    public function testLogoutUsesTheSameActiveSessionRegenerationPrimitive(): void
    {
        $deleteOldSession = null;

        $result = SessionFixationGuard::regenerateActiveSession(
            fn (): int => PHP_SESSION_ACTIVE,
            fn (): bool => false,
            function (bool $deleteOldSessionArgument) use (&$deleteOldSession): bool {
                $deleteOldSession = $deleteOldSessionArgument;

                return true;
            }
        );

        $this->assertTrue($result);
        $this->assertTrue($deleteOldSession);
    }
}
