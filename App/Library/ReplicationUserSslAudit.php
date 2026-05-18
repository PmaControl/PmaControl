<?php

declare(strict_types=1);

namespace App\Library;

final class ReplicationUserSslAudit
{
    /**
     * @return array{user:string,ssl:string,healthy:bool,severity:string,cert_expires_at:string,message:string}
     */
    public static function audit(array $status, ?int $now = null, int $warnDays = 30): array
    {
        $now ??= time();
        $user = (string)($status['Master_User'] ?? $status['Source_User'] ?? $status['USER'] ?? '');
        $ssl = (string)($status['Master_SSL_Allowed'] ?? $status['Source_SSL_Allowed'] ?? $status['SSL_ALLOWED'] ?? '');
        $cipher = (string)($status['Master_SSL_Cipher'] ?? $status['Source_SSL_Cipher'] ?? $status['TLS_CIPHERSUITES'] ?? '');
        $expires = trim((string)($status['Master_SSL_Cert_Expires'] ?? $status['SSL_CERT_EXPIRES_AT'] ?? ''));

        $sslOn = in_array(strtoupper($ssl), ['YES', '1', 'ON'], true) || $cipher !== '';
        $severity = $sslOn ? 'ok' : 'warning';
        $message = $sslOn ? 'Replication connection uses SSL/TLS.' : 'Replication connection does not advertise SSL/TLS.';

        if ($expires !== '') {
            $expiresTs = strtotime($expires);
            if ($expiresTs !== false && $expiresTs - $now <= $warnDays * 86400) {
                $severity = 'warning';
                $message = 'Replication SSL certificate expires soon.';
            }
        }

        return [
            'user' => $user,
            'ssl' => $sslOn ? 'on' : 'off',
            'healthy' => $severity === 'ok',
            'severity' => $severity,
            'cert_expires_at' => $expires,
            'message' => $message,
        ];
    }
}
