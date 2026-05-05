<?php

declare(strict_types=1);

namespace App\Library\Digest;

final class DigestReportMailer
{
    public static function send(string $recipient, string $subject, string $htmlBody, string $textBody): bool
    {
        $boundary = 'pmacontrol-digest-' . bin2hex(random_bytes(12));
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'X-Mailer: PmaControl DigestReport',
        ];

        $body = [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $textBody,
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $htmlBody,
            '--' . $boundary . '--',
            '',
        ];

        return mail($recipient, $subject, implode("\r\n", $body), implode("\r\n", $headers));
    }
}
