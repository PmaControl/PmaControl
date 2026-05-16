<?php

declare(strict_types=1);

namespace App\Library\Audit;

/**
 * Lightweight User-Agent parser for the audit drain (#1235).
 *
 * Deliberately minimal — covers the families we actually encounter
 * (Chrome / Firefox / Safari / Edge / Opera / curl / wget / common bots
 * and Windows / Linux / macOS / iOS / Android). Codex's note suggested
 * `matomo/device-detector` for better robot coverage; that lives behind
 * a feature flag in `AuditDrain` and is added as a Composer dep later.
 *
 * Hot path stays out of this — it's invoked only by the batch drain,
 * once per *unique* hash, results cached in `audit_user_agent`.
 */
final class UaParser
{
    /**
     * @return array{
     *   os_family:?string, os_version:?string,
     *   browser_family:?string, browser_version:?string,
     *   device_type:?string,
     *   is_robot:bool, robot_family:?string
     * }
     */
    public static function parse(string $ua): array
    {
        // Prefer Matomo DeviceDetector when available (post-MVP step 3,
        // #1235). It carries a much larger regex catalog for bots, devices
        // and brands than the inline fallback. We keep the fallback so the
        // library still works without the Composer dep (test fixtures,
        // forks, or environments where vendor/ is stale).
        if (class_exists(\DeviceDetector\DeviceDetector::class)) {
            return self::parseWithMatomo($ua);
        }

        $robot = self::detectRobot($ua);
        $os    = self::detectOs($ua);
        $br    = self::detectBrowser($ua);
        $dev   = self::detectDevice($ua, $os['family']);
        return [
            'os_family'       => $os['family'],
            'os_version'      => $os['version'],
            'browser_family'  => $br['family'],
            'browser_version' => $br['version'],
            'device_type'     => $dev,
            'is_robot'        => $robot !== null,
            'robot_family'    => $robot,
        ];
    }

    /**
     * Matomo DeviceDetector adapter. Maps its `os/client/device/bot`
     * shape onto the columns we already store in `audit_user_agent`.
     */
    private static function parseWithMatomo(string $ua): array
    {
        try {
            $d = new \DeviceDetector\DeviceDetector($ua);
            $d->parse();
        } catch (\Throwable $e) {
            // Library failure is non-fatal — fall back to the inline parser.
            $robot = self::detectRobot($ua);
            $os    = self::detectOs($ua);
            $br    = self::detectBrowser($ua);
            $dev   = self::detectDevice($ua, $os['family']);
            return [
                'os_family' => $os['family'], 'os_version' => $os['version'],
                'browser_family' => $br['family'], 'browser_version' => $br['version'],
                'device_type' => $dev,
                'is_robot' => $robot !== null, 'robot_family' => $robot,
            ];
        }

        if ($d->isBot()) {
            $bot = $d->getBot();
            $name = is_array($bot) ? (string) ($bot['name'] ?? 'unknown') : 'unknown';
            return [
                'os_family'       => null,
                'os_version'      => null,
                'browser_family'  => null,
                'browser_version' => null,
                'device_type'     => null,
                'is_robot'        => true,
                'robot_family'    => substr($name, 0, 64),
            ];
        }

        $os = $d->getOs();
        $client = $d->getClient();
        $browserName = is_array($client) ? (string) ($client['name'] ?? '') : '';

        // Matomo doesn't flag curl/wget/python-requests/etc. as bots — they
        // get a `Library` client type — but for an *audit* timeline we
        // really want to surface them as non-human traffic. Promote known
        // CLI / SDK clients to the is_robot bucket so they show up under
        // the robot icon and don't pollute the "human browsers" stats.
        $cliPromote = [
            'curl' => 'curl',
            'Wget' => 'wget',
            'python-requests' => 'python-requests',
            'Go-http-client'  => 'Go-http-client',
            'Java'            => 'Java',
        ];
        if ($browserName !== '' && isset($cliPromote[$browserName])) {
            return [
                'os_family'       => null,
                'os_version'      => null,
                'browser_family'  => null,
                'browser_version' => null,
                'device_type'     => null,
                'is_robot'        => true,
                'robot_family'    => $cliPromote[$browserName],
            ];
        }

        return [
            'os_family'       => is_array($os) ? self::normaliseOsName((string) ($os['name'] ?? '')) : null,
            'os_version'      => is_array($os) ? substr((string) ($os['version'] ?? ''), 0, 16) : null,
            'browser_family'  => is_array($client) ? substr($browserName, 0, 32) : null,
            'browser_version' => is_array($client) ? substr((string) ($client['version'] ?? ''), 0, 16) : null,
            'device_type'     => self::normaliseDeviceName($d->getDeviceName() ?: null),
            'is_robot'        => false,
            'robot_family'    => null,
        ];
    }

    /**
     * DeviceDetector reports "Mac" for macOS, "GNU/Linux" for Linux, etc.
     * Map onto the OS labels the rest of the UI uses (matches what the
     * inline parser returns, and what the UaIcon helper looks up).
     */
    private static function normaliseOsName(string $name): string
    {
        $map = [
            'Mac'              => 'macOS',
            'GNU/Linux'        => 'Linux',
            'Ubuntu'           => 'Linux',
            'Debian'           => 'Linux',
            'Fedora'           => 'Linux',
            'Arch Linux'       => 'Linux',
            'Windows'          => 'Windows',
            'iOS'              => 'iOS',
            'iPadOS'           => 'iPadOS',
            'Android'          => 'Android',
            'Chrome OS'        => 'ChromeOS',
            'FreeBSD'          => 'FreeBSD',
            'OpenBSD'          => 'OpenBSD',
        ];
        return $map[$name] ?? $name;
    }

    private static function normaliseDeviceName(?string $name): ?string
    {
        if ($name === null || $name === '') {
            return null;
        }
        $lower = strtolower($name);
        if (str_contains($lower, 'mobile') || str_contains($lower, 'phone')) {
            return 'mobile';
        }
        if (str_contains($lower, 'tablet')) {
            return 'tablet';
        }
        if (str_contains($lower, 'desktop')) {
            return 'desktop';
        }
        return $lower;
    }

    private static function detectRobot(string $ua): ?string
    {
        $bots = [
            '/Googlebot/i'      => 'Googlebot',
            '/Bingbot/i'        => 'Bingbot',
            '/DuckDuckBot/i'    => 'DuckDuckBot',
            '/Baiduspider/i'    => 'Baiduspider',
            '/YandexBot/i'      => 'YandexBot',
            '/Slurp/i'          => 'YahooSlurp',
            '/facebookexternalhit/i' => 'FacebookExternalHit',
            '/Twitterbot/i'     => 'Twitterbot',
            '/LinkedInBot/i'    => 'LinkedInBot',
            '/AhrefsBot/i'      => 'AhrefsBot',
            '/SemrushBot/i'     => 'SemrushBot',
            '/MJ12bot/i'        => 'MJ12bot',
            '/PetalBot/i'       => 'PetalBot',
            '/Applebot/i'       => 'Applebot',
            '/SeznamBot/i'      => 'SeznamBot',
            '/curl\//i'         => 'curl',
            '/wget\//i'         => 'wget',
            '/python-requests/i'=> 'python-requests',
            '/Go-http-client/i' => 'Go-http-client',
            '/Java\//i'         => 'Java',
            '/uptimerobot/i'    => 'UptimeRobot',
            '/StatusCake/i'     => 'StatusCake',
            '/Pingdom/i'        => 'Pingdom',
            '/HeadlessChrome/i' => 'HeadlessChrome',
            '/PhantomJS/i'      => 'PhantomJS',
            '/bot|crawler|spider/i' => 'GenericBot',
        ];
        foreach ($bots as $re => $name) {
            if (preg_match($re, $ua) === 1) {
                return $name;
            }
        }
        return null;
    }

    /**
     * @return array{family:?string,version:?string}
     */
    private static function detectOs(string $ua): array
    {
        $patterns = [
            // iOS first because iPhone/iPad UAs also include 'Mac OS X'.
            '/iPhone OS (\d+[_\.\d]*)/'         => 'iOS',
            '/iPad.*OS (\d+[_\.\d]*)/'          => 'iPadOS',
            '/Android (\d+[\.\d]*)/'            => 'Android',
            '/Windows NT (\d+\.\d+)/'           => 'Windows',
            '/Mac OS X (\d+[_\.\d]*)/'          => 'macOS',
            '/CrOS\s+\w+\s+(\d+[\.\d]*)/'       => 'ChromeOS',
            '/(?:Ubuntu|Debian|Fedora|CentOS|Arch|SUSE)/i' => 'Linux',
            '/Linux/i'                          => 'Linux',
            '/FreeBSD/i'                        => 'FreeBSD',
            '/OpenBSD/i'                        => 'OpenBSD',
        ];
        foreach ($patterns as $re => $family) {
            if (preg_match($re, $ua, $m) === 1) {
                $version = null;
                if (isset($m[1])) {
                    $version = str_replace('_', '.', $m[1]);
                    if ($family === 'Windows') {
                        $version = self::windowsNtToName($version);
                    }
                }
                return ['family' => $family, 'version' => $version];
            }
        }
        return ['family' => null, 'version' => null];
    }

    private static function windowsNtToName(string $nt): string
    {
        switch ($nt) {
            case '10.0': return '10/11';
            case '6.3':  return '8.1';
            case '6.2':  return '8';
            case '6.1':  return '7';
            case '6.0':  return 'Vista';
            case '5.1':  return 'XP';
            default:     return 'NT ' . $nt;
        }
    }

    /**
     * @return array{family:?string,version:?string}
     */
    private static function detectBrowser(string $ua): array
    {
        // Order matters — Edge/Opera embed Chrome's UA; check them first.
        $patterns = [
            '/Edg\/(\d+[\.\d]*)/'           => 'Edge',
            '/OPR\/(\d+[\.\d]*)/'           => 'Opera',
            '/Opera\/(\d+[\.\d]*)/'         => 'Opera',
            '/Vivaldi\/(\d+[\.\d]*)/'       => 'Vivaldi',
            '/Brave\/(\d+[\.\d]*)/'         => 'Brave',
            '/Chrome\/(\d+[\.\d]*)/'        => 'Chrome',
            '/Firefox\/(\d+[\.\d]*)/'       => 'Firefox',
            '/Version\/(\d+[\.\d]*)\s.*Safari/' => 'Safari',
            '/Safari\/(\d+[\.\d]*)/'        => 'Safari',
            '/curl\/(\d+[\.\d]*)/i'         => 'curl',
            '/wget\/(\d+[\.\d]*)/i'         => 'wget',
        ];
        foreach ($patterns as $re => $family) {
            if (preg_match($re, $ua, $m) === 1) {
                return ['family' => $family, 'version' => $m[1] ?? null];
            }
        }
        return ['family' => null, 'version' => null];
    }

    private static function detectDevice(string $ua, ?string $osFamily): ?string
    {
        if (preg_match('/iPad|Tablet|Kindle|Nexus 7|Nexus 10/i', $ua) === 1) {
            return 'tablet';
        }
        if ($osFamily === 'Android' || $osFamily === 'iOS' || preg_match('/Mobile|iPhone|iPod/i', $ua) === 1) {
            return 'mobile';
        }
        if ($osFamily === 'Linux' || $osFamily === 'Windows' || $osFamily === 'macOS' || $osFamily === 'ChromeOS') {
            return 'desktop';
        }
        return null;
    }

    /**
     * GPU `UNMASKED_RENDERER_WEBGL` → normalised family. Anti-fingerprinting
     * decision from Codex (#1235): never store the raw string, only a short
     * vendor/family classifier.
     */
    public static function normaliseGpu(?string $rawRenderer): ?string
    {
        if ($rawRenderer === null || $rawRenderer === '') {
            return null;
        }
        $r = $rawRenderer;
        $map = [
            '/Apple M\d|Apple GPU/i'                 => 'apple-silicon',
            '/NVIDIA.*(RTX|GTX|Quadro|Tesla)/i'      => 'nvidia-discrete',
            '/AMD|Radeon/i'                          => 'amd',
            '/Intel.*(?:Iris|UHD|HD Graphics|Xe)/i'  => 'intel-igpu',
            '/Mali-/i'                               => 'arm-mali',
            '/Adreno/i'                              => 'qcom-adreno',
            '/PowerVR/i'                             => 'powervr',
            '/SwiftShader|llvmpipe/i'                => 'software',
        ];
        foreach ($map as $re => $family) {
            if (preg_match($re, $r) === 1) {
                return $family;
            }
        }
        return 'unknown';
    }

    /**
     * navigator.deviceMemory (GB, integer) → coarse bucket.
     */
    public static function ramBucket(?float $gb): ?string
    {
        if ($gb === null) {
            return null;
        }
        if ($gb < 1)   return '<1GB';
        if ($gb <= 2)  return '1-2GB';
        if ($gb <= 4)  return '2-4GB';
        if ($gb <= 8)  return '4-8GB';
        if ($gb <= 16) return '8-16GB';
        return '>16GB';
    }

    /**
     * navigator.hardwareConcurrency → bucket.
     */
    public static function cpuBucket(?int $cores): ?string
    {
        if ($cores === null) {
            return null;
        }
        if ($cores <= 2)  return '1-2';
        if ($cores <= 4)  return '2-4';
        if ($cores <= 8)  return '4-8';
        if ($cores <= 16) return '8-16';
        return '>16';
    }
}
