<?php

namespace App\Library;

class PluginPackage
{
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

        return $manifest;
    }

    public static function install(array $manifest, $pluginDirectory, $projectRoot)
    {
        $copied = self::copyFiles($manifest, $pluginDirectory, $projectRoot);

        return array(
            'files' => $copied,
            'sql' => self::phaseFiles($manifest, $pluginDirectory, 'install'),
            'scripts' => self::phaseScripts($manifest, $pluginDirectory, 'install'),
        );
    }

    public static function uninstall(array $manifest, $pluginDirectory, $projectRoot)
    {
        return array(
            'files' => self::removeFiles($manifest, $projectRoot),
            'sql' => self::phaseFiles($manifest, $pluginDirectory, 'uninstall'),
            'scripts' => self::phaseScripts($manifest, $pluginDirectory, 'uninstall'),
        );
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
}
