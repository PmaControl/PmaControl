<?php

namespace App\Library;

use Fuz\Component\SharedMemory\Entity\StoredEntity;

/**
 * Read php-shared-memory pivot files without triggering vendor warnings.
 */
class SharedMemoryReader
{
    public static function read(string $file, ?string &$reason = null)
    {
        $reason = null;

        if (!is_file($file)) {
            $reason = 'missing';
            return null;
        }

        if (!is_readable($file)) {
            $reason = 'permission denied (owner '.self::describeOwner($file).')';
            return null;
        }

        $handle = @fopen($file, 'rb');
        if ($handle === false) {
            $reason = 'open failed';
            return null;
        }

        $locked = false;
        try {
            if (flock($handle, LOCK_SH) === false) {
                $reason = 'lock failed';
                return null;
            }

            $locked = true;
            $contents = stream_get_contents($handle);
        } finally {
            if ($locked) {
                flock($handle, LOCK_UN);
            }
            fclose($handle);
        }

        if ($contents === false || $contents === '') {
            $reason = 'empty file';
            return null;
        }

        $unserialize_error = null;
        set_error_handler(static function (int $severity, string $message) use (&$unserialize_error): bool {
            $unserialize_error = $message;
            return true;
        });

        try {
            $stored_entity = unserialize($contents);
        } finally {
            restore_error_handler();
        }

        if (!$stored_entity instanceof StoredEntity) {
            $reason = 'invalid stored entity';
            if ($unserialize_error !== null) {
                $reason .= ': '.$unserialize_error;
            }

            return null;
        }

        return $stored_entity->getData();
    }

    private static function describeOwner(string $file): string
    {
        $uid = @fileowner($file);
        if ($uid === false) {
            return 'unknown';
        }

        if (function_exists('posix_getpwuid')) {
            $info = @posix_getpwuid($uid);
            if (is_array($info) && isset($info['name'])) {
                return $info['name'].'/'.$uid;
            }
        }

        return (string) $uid;
    }
}
