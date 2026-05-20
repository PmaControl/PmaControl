<?php

declare(strict_types=1);

namespace App\Library\Security;

use Glial\Http\Request;
use InvalidArgumentException;

final class SafeRedirect
{
    public static function refererOrFallback(array $server, string $fallback, ?string $trustedOrigin = null): string
    {
        if ($fallback === '') {
            throw new InvalidArgumentException('Redirect fallback must not be empty.');
        }

        $referer = $server['HTTP_REFERER'] ?? null;
        if (!is_string($referer) || $referer === '') {
            return $fallback;
        }

        if (Request::isSameSiteUrl($referer, $server, CsrfGuard::trustedOrigin($trustedOrigin))) {
            return $referer;
        }

        return $fallback;
    }
}
