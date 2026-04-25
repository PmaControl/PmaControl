<?php

declare(strict_types=1);

use App\Controller\MysqlServer;
use PHPUnit\Framework\TestCase;

final class MysqlServerAtomicCacheTest extends TestCase
{
    public function testWriteJsonFileAtomicallyWritesCompleteJsonAndCleansTempFile(): void
    {
        $dir = sys_get_temp_dir().'/pmacontrol-atomic-cache-'.bin2hex(random_bytes(6));
        mkdir($dir);
        $path = $dir.'/chart.day.json';

        try {
            MysqlServer::writeJsonFileAtomically($path, ['date' => '2026-04-15', 'counts' => ['ERROR' => 1]], JSON_PRETTY_PRINT);

            $decoded = json_decode((string) file_get_contents($path), true);
            $this->assertSame(['date' => '2026-04-15', 'counts' => ['ERROR' => 1]], $decoded);
            $this->assertSame([], glob($dir.'/.chart.day.json.tmp.*') ?: []);
        } finally {
            foreach (glob($dir.'/*') ?: [] as $file) {
                @unlink($file);
            }
            foreach (glob($dir.'/.*.tmp.*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($dir);
        }
    }

    public function testWriteJsonFileAtomicallyReplacesExistingFile(): void
    {
        $dir = sys_get_temp_dir().'/pmacontrol-atomic-cache-'.bin2hex(random_bytes(6));
        mkdir($dir);
        $path = $dir.'/.mysql-error.log.part.parsed.json';
        file_put_contents($path, '{"old":true}');

        try {
            MysqlServer::writeJsonFileAtomically($path, [['event_time' => '2026-04-15 10:00:00']], JSON_UNESCAPED_SLASHES);

            $decoded = json_decode((string) file_get_contents($path), true);
            $this->assertSame([['event_time' => '2026-04-15 10:00:00']], $decoded);
            $this->assertSame([], glob($dir.'/.'.basename($path).'.tmp.*') ?: []);
        } finally {
            foreach (glob($dir.'/*') ?: [] as $file) {
                @unlink($file);
            }
            foreach (glob($dir.'/.*') ?: [] as $file) {
                if (basename($file) !== '.' && basename($file) !== '..') {
                    @unlink($file);
                }
            }
            @rmdir($dir);
        }
    }

    public function testWriteFileAtomicallyKeepsExistingPermissions(): void
    {
        $dir = sys_get_temp_dir().'/pmacontrol-atomic-cache-'.bin2hex(random_bytes(6));
        mkdir($dir);
        $path = $dir.'/chart.hour.json';
        file_put_contents($path, '{"old":true}');
        chmod($path, 0600);

        try {
            MysqlServer::writeFileAtomically($path, '{"new":true}');

            clearstatcache(true, $path);
            $this->assertSame('{"new":true}', file_get_contents($path));
            $this->assertSame(0600, fileperms($path) & 0777);
        } finally {
            foreach (glob($dir.'/*') ?: [] as $file) {
                @unlink($file);
            }
            foreach (glob($dir.'/.*') ?: [] as $file) {
                if (basename($file) !== '.' && basename($file) !== '..') {
                    @unlink($file);
                }
            }
            @rmdir($dir);
        }
    }
}
