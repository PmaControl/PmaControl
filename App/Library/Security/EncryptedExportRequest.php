<?php

declare(strict_types=1);

namespace App\Library\Security;

use App\Library\Chiffrement;
use Glial\Security\Crypt\Crypt;

final class EncryptedExportRequest
{
    public const DEFAULT_MAX_BYTES = 5242880;

    public static function evaluateUploadPasswordPost(
        array $files,
        array $post,
        array $server,
        array $session,
        string $scope,
        string $invalidPayloadMessage,
        bool $requireUploadedFile = true,
        int $maxBytes = self::DEFAULT_MAX_BYTES,
        string $tooLargeMessage = 'Uploaded export file too large',
        string $group = 'export',
        string $fileField = 'file',
        string $passwordField = 'password'
    ): array {
        $guard = CsrfGuard::check($post, $server, $session, $scope);
        if (!$guard['allowed']) {
            return self::outcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $fileSize = self::extractFileSize($files, $group, $fileField);
        if ($fileSize !== null && $fileSize > $maxBytes) {
            return self::outcome(413, $tooLargeMessage);
        }

        $payload = self::normalizeUploadPasswordPayload($files, $post, $requireUploadedFile, $group, $fileField, $passwordField);
        if ($payload === null) {
            return self::outcome(422, $invalidPayloadMessage);
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'file' => $payload['file'],
            'password' => $payload['password'],
        ];
    }

    public static function normalizeUploadPasswordPayload(
        array $files,
        array $post,
        bool $requireUploadedFile = true,
        string $group = 'export',
        string $fileField = 'file',
        string $passwordField = 'password'
    ): ?array {
        $uploadError = $files[$group]['error'][$fileField] ?? UPLOAD_ERR_OK;
        if (!is_numeric($uploadError) || (int) $uploadError !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $files[$group]['tmp_name'][$fileField] ?? null;
        if (!is_scalar($file)) {
            return null;
        }

        $file = trim((string) $file);
        if ($file === '' || ($requireUploadedFile && !is_uploaded_file($file))) {
            return null;
        }

        $exportPost = $post[$group] ?? null;
        if (!is_array($exportPost) || !is_scalar($exportPost[$passwordField] ?? null)) {
            return null;
        }

        $password = trim((string) $exportPost[$passwordField]);
        if ($password === '') {
            return null;
        }

        return [
            'file' => $file,
            'password' => $password,
        ];
    }

    public static function extractFileSize(array $files, string $group = 'export', string $fileField = 'file'): ?int
    {
        $size = $files[$group]['size'][$fileField] ?? null;
        if (!is_numeric($size)) {
            return null;
        }

        return (int) $size;
    }

    public static function decryptFile(string $file, string $password): ?string
    {
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $crypted = file_get_contents($file);
            if ($crypted === false) {
                return null;
            }

            $decrypted = Chiffrement::decrypt($crypted, $password);
        } catch (\Throwable $exception) {
            return null;
        } finally {
            restore_error_handler();
            if (defined('CRYPT_KEY')) {
                Crypt::$key = CRYPT_KEY;
            }
        }

        return is_string($decrypted) ? $decrypted : null;
    }

    private static function outcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'file' => '',
            'password' => '',
        ];
    }
}
