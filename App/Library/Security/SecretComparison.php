<?php

declare(strict_types=1);

namespace App\Library\Security;

final class SecretComparison
{
    public static function equals(string $knownSecret, string $providedSecret): bool
    {
        return hash_equals($knownSecret, $providedSecret);
    }
}
