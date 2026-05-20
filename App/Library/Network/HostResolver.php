<?php

declare(strict_types=1);

namespace App\Library\Network;

/**
 * DNS resolution helpers shared by controllers and libraries.
 */
final class HostResolver
{
    /**
     * Resolve a host to unique IPv4 addresses without invoking a shell.
     *
     * @param mixed $host
     * @param callable|null $resolver Optional resolver for tests.
     * @return array<int,string>
     */
    public static function resolveIpv4Addresses($host, ?callable $resolver = null): array
    {
        if (!is_scalar($host)) {
            return array();
        }

        $host = trim((string) $host);
        if ($host === '') {
            return array();
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return array($host);
        }

        $resolver = $resolver ?? static function (string $host) {
            return @gethostbynamel($host);
        };

        $resolved = $resolver($host);
        if (!is_array($resolved)) {
            return array();
        }

        $ips = array();
        foreach ($resolved as $ip) {
            if (!is_scalar($ip)) {
                continue;
            }

            $ip = trim((string) $ip);
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                continue;
            }

            if (!in_array($ip, $ips, true)) {
                $ips[] = $ip;
            }
        }

        return $ips;
    }
}
