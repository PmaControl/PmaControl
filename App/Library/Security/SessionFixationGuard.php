<?php

declare(strict_types=1);

namespace App\Library\Security;

use RuntimeException;

final class SessionFixationGuard
{
    public static function enforceRegenerationAfterSuccessfulAuthentication(
        bool $authenticated,
        ?callable $sessionStatusProvider = null,
        ?callable $headersSentChecker = null,
        ?callable $sessionRegenerator = null
    ): bool {
        if (!$authenticated) {
            return false;
        }

        if (!self::regenerateActiveSession($sessionStatusProvider, $headersSentChecker, $sessionRegenerator)) {
            throw new RuntimeException('Unable to regenerate session ID after successful authentication.');
        }

        return true;
    }

    public static function regenerateActiveSession(
        ?callable $sessionStatusProvider = null,
        ?callable $headersSentChecker = null,
        ?callable $sessionRegenerator = null
    ): bool {
        $sessionStatusProvider = $sessionStatusProvider ?? 'session_status';
        $headersSentChecker = $headersSentChecker ?? 'headers_sent';
        $sessionRegenerator = $sessionRegenerator ?? 'session_regenerate_id';

        if ($sessionStatusProvider() !== PHP_SESSION_ACTIVE || $headersSentChecker()) {
            return false;
        }

        return (bool) $sessionRegenerator(true);
    }
}
