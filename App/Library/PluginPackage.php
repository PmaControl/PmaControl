<?php

namespace App\Library;

class PluginPackage
{
    private const PMACTRL_MAGIC = "!<arch>\n";
    private const MAX_ARCHIVE_UNCOMPRESSED_SIZE = 104857600;

    public static function load($pluginDirectory)
    {
        $manifestFile = rtrim((string)$pluginDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'plugin.json';

        if (!is_file($manifestFile)) {
            throw new \InvalidArgumentException('Missing plugin manifest: '.$manifestFile);
        }

        $manifest = json_decode((string)file_get_contents($manifestFile), true);

        if (!is_array($manifest)) {
            throw new \InvalidArgumentException('Invalid plugin manifest JSON: '.$manifestFile);
        }

        return self::normalizeManifest($manifest);
    }

    public static function normalizeManifest(array $manifest)
    {
        $manifest['files'] = self::normalizeFiles($manifest['files'] ?? array());
        $manifest['sql'] = self::normalizePhaseMap($manifest['sql'] ?? array());
        $manifest['ddl'] = self::normalizePhaseMap($manifest['ddl'] ?? array());
        $manifest['data'] = self::normalizePhaseMap($manifest['data'] ?? array());
        $manifest['scripts'] = self::normalizePhaseMap($manifest['scripts'] ?? array());
        $manifest['extensions'] = self::normalizeExtensions($manifest['extensions'] ?? array());

        return $manifest;
    }

    public static function install(array $manifest, $pluginDirectory, $projectRoot)
    {
        $copied = self::copyFiles($manifest, $pluginDirectory, $projectRoot);
        $extensions = self::copyExtensionPartials($manifest, $pluginDirectory, $projectRoot);

        return array(
            'files' => $copied,
            'sql' => self::phaseFiles($manifest, $pluginDirectory, 'install'),
            'scripts' => self::phaseScripts($manifest, $pluginDirectory, 'install'),
            'extensions' => $extensions,
        );
    }

    public static function uninstall(array $manifest, $pluginDirectory, $projectRoot)
    {
        return array(
            'files' => self::removeFiles($manifest, $projectRoot),
            'sql' => self::phaseFiles($manifest, $pluginDirectory, 'uninstall'),
            'scripts' => self::phaseScripts($manifest, $pluginDirectory, 'uninstall'),
            'extensions' => self::removeExtensionPartials($manifest, $projectRoot),
        );
    }

    /**
     * Compute the per-extension entries to push into the plugin_extension
     * registry. For "partial" entries the source file is copied to its
     * declared destination (under App/view/_partials/<plugin>/...). For
     * "callback" entries only the payload is returned.
     *
     * @return array<int, array{slot:string,kind:string,payload:string,order:int}>
     */
    public static function copyExtensionPartials(array $manifest, $pluginDirectory, $projectRoot)
    {
        $registered = array();

        foreach ($manifest['extensions'] ?? array() as $extension) {
            $slot = (string)$extension['slot'];
            $kind = (string)$extension['kind'];
            $order = (int)$extension['order'];

            if ($kind === 'partial') {
                $source = self::resolveExistingPath($pluginDirectory, $extension['source']);
                $destination = self::resolveTargetPath($projectRoot, $extension['destination']);
                $destinationDirectory = dirname($destination);

                if (file_exists($destination)) {
                    throw new \RuntimeException('Plugin partial already exists: '.$destination);
                }

                if (!is_dir($destinationDirectory) && !mkdir($destinationDirectory, 0755, true) && !is_dir($destinationDirectory)) {
                    throw new \RuntimeException('Cannot create partial directory: '.$destinationDirectory);
                }

                if (!copy($source, $destination)) {
                    throw new \RuntimeException('Cannot copy plugin partial to: '.$destination);
                }

                $registered[] = array(
                    'slot' => $slot,
                    'kind' => 'partial',
                    'payload' => self::normalizeRelativePath($extension['destination']),
                    'order' => $order,
                );
            } else {
                $registered[] = array(
                    'slot' => $slot,
                    'kind' => 'callback',
                    'payload' => (string)$extension['payload'],
                    'order' => $order,
                );
            }
        }

        return $registered;
    }

    /**
     * @return array<int, string> filesystem paths of partials removed
     */
    public static function removeExtensionPartials(array $manifest, $projectRoot)
    {
        $removed = array();

        foreach ($manifest['extensions'] ?? array() as $extension) {
            if ($extension['kind'] !== 'partial') {
                continue;
            }
            $destination = self::resolveTargetPath($projectRoot, $extension['destination']);
            if (is_file($destination) || is_link($destination)) {
                unlink($destination);
                $removed[] = $destination;
            }
        }

        return $removed;
    }

    public static function copyFiles(array $manifest, $pluginDirectory, $projectRoot)
    {
        $copied = array();

        foreach ($manifest['files'] ?? array() as $file) {
            $source = self::resolveExistingPath($pluginDirectory, $file['source']);
            $destination = self::resolveTargetPath($projectRoot, $file['destination']);
            $destinationDirectory = dirname($destination);

            if (file_exists($destination)) {
                throw new \RuntimeException('Plugin destination already exists: '.$destination);
            }

            if (!is_dir($destinationDirectory) && !mkdir($destinationDirectory, 0755, true) && !is_dir($destinationDirectory)) {
                throw new \RuntimeException('Cannot create destination directory: '.$destinationDirectory);
            }

            if (is_dir($source)) {
                self::copyDirectory($source, $destination);
            } elseif (!copy($source, $destination)) {
                throw new \RuntimeException('Cannot copy plugin file to: '.$destination);
            }

            $copied[] = array(
                'source' => $source,
                'destination' => $destination,
            );
        }

        return $copied;
    }

    public static function removeFiles(array $manifest, $projectRoot)
    {
        $removed = array();
        $files = array_reverse($manifest['files'] ?? array());

        foreach ($files as $file) {
            $destination = self::resolveTargetPath($projectRoot, $file['destination']);

            if (is_file($destination) || is_link($destination)) {
                unlink($destination);
                $removed[] = $destination;
            } elseif (is_dir($destination)) {
                self::removeDirectory($destination);
                $removed[] = $destination;
            }
        }

        return $removed;
    }

    public static function phaseFiles(array $manifest, $pluginDirectory, $phase)
    {
        $files = array();

        foreach (array('ddl', 'data', 'sql') as $section) {
            foreach ($manifest[$section][$phase] ?? array() as $relativePath) {
                $files[] = self::resolveExistingPath($pluginDirectory, $relativePath);
            }
        }

        return $files;
    }

    public static function phaseScripts(array $manifest, $pluginDirectory, $phase)
    {
        $scripts = array();

        foreach ($manifest['scripts'][$phase] ?? array() as $relativePath) {
            $scripts[] = self::resolveExistingPath($pluginDirectory, $relativePath);
        }

        return $scripts;
    }

    public static function runScript($scriptPath)
    {
        $scriptPath = (string)$scriptPath;
        $command = preg_match('/\.php$/i', $scriptPath)
            ? PHP_BINARY.' '.escapeshellarg($scriptPath)
            : escapeshellarg($scriptPath);

        $output = array();
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Plugin script failed: '.$scriptPath);
        }

        return $output;
    }

    public static function archiveExtensionFromUrl($url)
    {
        $path = parse_url((string)$url, PHP_URL_PATH);
        $extension = strtolower((string)pathinfo((string)$path, PATHINFO_EXTENSION));

        return in_array($extension, array('zip', 'pmactrl'), true) ? $extension : 'zip';
    }

    public static function extractPmactrl($archivePath, $targetDirectory)
    {
        $entries = self::readArArchive((string)$archivePath);

        if (empty($entries['data.tar.gz'])) {
            throw new \RuntimeException('PmaControl plugin archive is missing data.tar.gz');
        }

        self::extractTarGzBytes($entries['data.tar.gz'], (string)$targetDirectory);
    }

    private static function normalizeFiles($files)
    {
        $normalized = array();

        foreach ((array)$files as $key => $file) {
            if (is_string($file)) {
                $normalized[] = array(
                    'source' => $file,
                    'destination' => is_string($key) ? $key : $file,
                );
                continue;
            }

            if (is_array($file) && !empty($file['source']) && !empty($file['destination'])) {
                $normalized[] = array(
                    'source' => (string)$file['source'],
                    'destination' => (string)$file['destination'],
                );
            }
        }

        return $normalized;
    }

    private static function normalizePhaseMap($map)
    {
        if (is_array($map) && (isset($map['install']) || isset($map['uninstall']))) {
            return array(
                'install' => array_values(array_map('strval', (array)($map['install'] ?? array()))),
                'uninstall' => array_values(array_map('strval', (array)($map['uninstall'] ?? array()))),
            );
        }

        return array(
            'install' => array_values(array_map('strval', (array)$map)),
            'uninstall' => array(),
        );
    }

    private static function normalizeExtensions($extensions)
    {
        $normalized = array();

        foreach ((array)$extensions as $extension) {
            if (!is_array($extension) || empty($extension['slot']) || empty($extension['kind'])) {
                continue;
            }

            $slot = (string)$extension['slot'];
            $kind = (string)$extension['kind'];
            $order = isset($extension['order']) ? (int)$extension['order'] : 100;

            if ($kind === 'partial') {
                if (empty($extension['source']) || empty($extension['destination'])) {
                    throw new \InvalidArgumentException('Partial extension requires source and destination: '.$slot);
                }
                $normalized[] = array(
                    'slot' => $slot,
                    'kind' => 'partial',
                    'source' => (string)$extension['source'],
                    'destination' => (string)$extension['destination'],
                    'order' => $order,
                );
            } elseif ($kind === 'callback') {
                if (empty($extension['payload'])) {
                    throw new \InvalidArgumentException('Callback extension requires payload: '.$slot);
                }
                $normalized[] = array(
                    'slot' => $slot,
                    'kind' => 'callback',
                    'payload' => (string)$extension['payload'],
                    'order' => $order,
                );
            } else {
                throw new \InvalidArgumentException('Unknown extension kind: '.$kind);
            }
        }

        return $normalized;
    }

    private static function normalizeRelativePath($relativePath)
    {
        $relativePath = str_replace('\\', '/', trim((string)$relativePath));

        if ($relativePath === '' || $relativePath[0] === '/' || preg_match('#(^|/)\.\.(/|$)#', $relativePath)) {
            throw new \InvalidArgumentException('Invalid plugin relative path: '.$relativePath);
        }

        return $relativePath;
    }

    private static function resolveExistingPath($root, $relativePath)
    {
        $path = rtrim((string)$root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.self::normalizeRelativePath($relativePath);
        $real = realpath($path);

        if ($real === false) {
            throw new \InvalidArgumentException('Missing plugin path: '.$path);
        }

        return $real;
    }

    private static function resolveTargetPath($root, $relativePath)
    {
        return rtrim((string)$root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.self::normalizeRelativePath($relativePath);
    }

    private static function copyDirectory($source, $destination)
    {
        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            throw new \RuntimeException('Cannot create destination directory: '.$destination);
        }

        foreach (array_diff(scandir($source), array('.', '..')) as $entry) {
            $entrySource = $source.DIRECTORY_SEPARATOR.$entry;
            $entryDestination = $destination.DIRECTORY_SEPARATOR.$entry;

            if (is_dir($entrySource)) {
                self::copyDirectory($entrySource, $entryDestination);
            } elseif (!copy($entrySource, $entryDestination)) {
                throw new \RuntimeException('Cannot copy plugin file to: '.$entryDestination);
            }
        }
    }

    private static function removeDirectory($directory)
    {
        foreach (array_diff(scandir($directory), array('.', '..')) as $entry) {
            $path = $directory.DIRECTORY_SEPARATOR.$entry;

            if (is_dir($path)) {
                self::removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }

    private static function readArArchive($archivePath)
    {
        if (!is_file($archivePath) || !is_readable($archivePath)) {
            throw new \RuntimeException('PmaControl plugin archive is not readable: '.$archivePath);
        }

        $handle = fopen($archivePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Cannot open PmaControl plugin archive: '.$archivePath);
        }

        try {
            $magic = fread($handle, strlen(self::PMACTRL_MAGIC));
            if ($magic !== self::PMACTRL_MAGIC) {
                throw new \RuntimeException('Invalid PmaControl plugin archive magic');
            }

            $entries = array();
            while (!feof($handle)) {
                $header = fread($handle, 60);
                if ($header === '' || $header === false) {
                    break;
                }
                if (strlen($header) !== 60) {
                    throw new \RuntimeException('Truncated PmaControl plugin archive header');
                }
                if (substr($header, 58, 2) !== "`\n") {
                    throw new \RuntimeException('Invalid PmaControl plugin archive member header');
                }

                $name = rtrim(trim(substr($header, 0, 16)), '/');
                $sizeText = trim(substr($header, 48, 10));
                if ($name === '' || !ctype_digit($sizeText)) {
                    throw new \RuntimeException('Invalid PmaControl plugin archive member metadata');
                }

                $size = (int)$sizeText;
                $contents = $size > 0 ? fread($handle, $size) : '';
                if (!is_string($contents) || strlen($contents) !== $size) {
                    throw new \RuntimeException('Truncated PmaControl plugin archive member: '.$name);
                }

                if (($size % 2) === 1) {
                    fread($handle, 1);
                }

                $entries[$name] = $contents;
            }
        } finally {
            fclose($handle);
        }

        if (($entries['debian-binary'] ?? '') !== "2.0\n") {
            throw new \RuntimeException('Unsupported PmaControl plugin archive version');
        }

        return $entries;
    }

    private static function extractTarGzBytes($gzipBytes, $targetDirectory)
    {
        $tarBytes = gzdecode($gzipBytes);
        if (!is_string($tarBytes)) {
            throw new \RuntimeException('Invalid compressed data.tar.gz in PmaControl plugin archive');
        }
        if (strlen($tarBytes) > self::MAX_ARCHIVE_UNCOMPRESSED_SIZE) {
            throw new \RuntimeException('PmaControl plugin archive is too large after extraction');
        }

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new \RuntimeException('Cannot create plugin extraction directory: '.$targetDirectory);
        }

        $offset = 0;
        $length = strlen($tarBytes);
        while ($offset + 512 <= $length) {
            $header = substr($tarBytes, $offset, 512);
            $offset += 512;

            if ($header === str_repeat("\0", 512)) {
                break;
            }

            $name = rtrim(substr($header, 0, 100), "\0 ");
            $prefix = rtrim(substr($header, 345, 155), "\0 ");
            if ($prefix !== '') {
                $name = $prefix.'/'.$name;
            }

            $name = self::normalizeRelativePath($name);
            $type = substr($header, 156, 1);
            $sizeText = trim(rtrim(substr($header, 124, 12), "\0 "));
            $size = $sizeText === '' ? 0 : octdec($sizeText);
            $target = self::resolveTargetPath($targetDirectory, $name);

            if ($type === '5') {
                if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                    throw new \RuntimeException('Cannot create plugin archive directory: '.$target);
                }
            } elseif ($type === '0' || $type === "\0" || $type === '') {
                $directory = dirname($target);
                if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                    throw new \RuntimeException('Cannot create plugin archive directory: '.$directory);
                }

                $contents = substr($tarBytes, $offset, $size);
                if (file_put_contents($target, $contents) === false) {
                    throw new \RuntimeException('Cannot extract plugin archive file: '.$target);
                }
            } else {
                throw new \RuntimeException('Unsupported plugin archive tar entry type: '.$type);
            }

            $offset += (int)(ceil($size / 512) * 512);
        }
    }
}
