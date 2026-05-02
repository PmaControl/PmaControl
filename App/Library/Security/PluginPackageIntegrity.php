<?php

namespace App\Library\Security;

final class PluginPackageIntegrity
{
    const MODE_LEGACY_MD5_ONLY = 'legacy-md5-only';
    const MODE_SIGNED_SHA256 = 'signed-sha256';

    /**
     * @param array<string,mixed> $metadata
     * @param array<string,string>|array<int,string> $trustedPublicKeys
     * @return array<string,mixed>
     */
    public static function ensureZipTrusted($zipPath, array $metadata, array $trustedPublicKeys, $allowLegacyMd5 = true)
    {
        $result = self::evaluate($zipPath, $metadata, $trustedPublicKeys, $allowLegacyMd5);

        if (!$result['allowed']) {
            throw new \RuntimeException($result['error']);
        }

        return $result;
    }

    /**
     * @param array<string,mixed> $metadata
     * @param array<string,string>|array<int,string> $trustedPublicKeys
     * @return array<string,mixed>
     */
    public static function evaluate($zipPath, array $metadata, array $trustedPublicKeys, $allowLegacyMd5 = true)
    {
        if (!is_string($zipPath) || !is_file($zipPath) || !is_readable($zipPath)) {
            return self::blocked('Plugin ZIP file is not readable');
        }

        try {
            $expectedMd5 = self::normalizeChecksum(self::metadataValue($metadata, array('md5', 'md5_zip')), 32, 'Plugin ZIP MD5 checksum');
            $expectedSha256 = self::normalizeChecksum(self::metadataValue($metadata, array('sha256', 'sha256_zip', 'SHA256')), 64, 'Plugin ZIP SHA-256 checksum');
        } catch (\InvalidArgumentException $exception) {
            return self::blocked($exception->getMessage());
        }

        if ($expectedMd5 !== '' && hash_file('md5', $zipPath) !== $expectedMd5) {
            return self::blocked('Plugin ZIP MD5 checksum mismatch');
        }

        if ($expectedSha256 === '') {
            if ($expectedMd5 !== '' && $allowLegacyMd5) {
                return array(
                    'allowed' => true,
                    'mode' => self::MODE_LEGACY_MD5_ONLY,
                    'warnings' => array('Plugin ZIP uses legacy MD5-only integrity metadata'),
                    'error' => '',
                );
            }

            return self::blocked('Plugin ZIP SHA-256 checksum is required');
        }

        if (hash_file('sha256', $zipPath) !== $expectedSha256) {
            return self::blocked('Plugin ZIP SHA-256 checksum mismatch');
        }

        $signature = trim((string) self::metadataValue($metadata, array('signature', 'signature_zip', 'Signature')));
        if ($signature === '') {
            return self::blocked('Plugin ZIP signature is required with SHA-256 metadata');
        }

        $signatureKeyId = trim((string) self::metadataValue($metadata, array('signature_key_id', 'key_id', 'KeyId')));
        $signatureResult = self::verifySignature($zipPath, $signature, $signatureKeyId, $trustedPublicKeys);
        if ($signatureResult !== '') {
            return self::blocked($signatureResult);
        }

        return array(
            'allowed' => true,
            'mode' => self::MODE_SIGNED_SHA256,
            'warnings' => array(),
            'error' => '',
        );
    }

    /**
     * @param array<string,mixed> $metadata
     * @param array<int,string> $names
     * @return mixed
     */
    private static function metadataValue(array $metadata, array $names)
    {
        foreach ($names as $name) {
            if (array_key_exists($name, $metadata)) {
                return $metadata[$name];
            }
        }

        return '';
    }

    private static function normalizeChecksum($value, $length, $label)
    {
        $checksum = strtolower(trim((string) $value));
        if ($checksum === '') {
            return '';
        }

        if (!preg_match('/\A[a-f0-9]{' . (int) $length . '}\z/', $checksum)) {
            throw new \InvalidArgumentException($label . ' must be ' . (int) $length . ' hexadecimal characters');
        }

        return $checksum;
    }

    /**
     * @param array<string,string>|array<int,string> $trustedPublicKeys
     */
    private static function verifySignature($zipPath, $signature, $signatureKeyId, array $trustedPublicKeys)
    {
        if (!extension_loaded('sodium') || !function_exists('sodium_crypto_sign_verify_detached')) {
            return 'Sodium extension is required to verify plugin signatures';
        }

        if ($trustedPublicKeys === array()) {
            return 'No trusted plugin signature public key configured';
        }

        $rawSignature = base64_decode($signature, true);
        if (!is_string($rawSignature) || strlen($rawSignature) !== SODIUM_CRYPTO_SIGN_BYTES) {
            return 'Plugin ZIP signature must be a base64 Ed25519 detached signature';
        }

        $candidateKeys = self::candidateKeys($trustedPublicKeys, $signatureKeyId);
        if ($candidateKeys === array()) {
            return 'No trusted plugin signature public key matches the requested key id';
        }

        $contents = file_get_contents($zipPath);
        if (!is_string($contents)) {
            return 'Plugin ZIP file is not readable';
        }

        foreach ($candidateKeys as $keyId => $encodedPublicKey) {
            $publicKey = base64_decode((string) $encodedPublicKey, true);
            if (!is_string($publicKey) || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
                continue;
            }

            if (sodium_crypto_sign_verify_detached($rawSignature, $contents, $publicKey)) {
                return '';
            }
        }

        return 'Plugin ZIP signature verification failed';
    }

    /**
     * @param array<string,string>|array<int,string> $trustedPublicKeys
     * @return array<string,string>|array<int,string>
     */
    private static function candidateKeys(array $trustedPublicKeys, $signatureKeyId)
    {
        if ($signatureKeyId === '') {
            return $trustedPublicKeys;
        }

        if (!array_key_exists($signatureKeyId, $trustedPublicKeys)) {
            return array();
        }

        return array($signatureKeyId => $trustedPublicKeys[$signatureKeyId]);
    }

    /**
     * @return array<string,mixed>
     */
    private static function blocked($error)
    {
        return array(
            'allowed' => false,
            'mode' => '',
            'warnings' => array(),
            'error' => $error,
        );
    }
}
