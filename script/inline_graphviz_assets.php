#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Library\Graphviz;

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

if (!defined('ROOT')) {
    define('ROOT', $root);
}

if (!defined('WWW_ROOT')) {
    define('WWW_ROOT', '/pmacontrol/');
}

require_once $root . '/App/Library/Graphviz.php';

if ($argc < 2) {
    fwrite(STDERR, "Usage: php script/inline_graphviz_assets.php <file1.svg|file2.svg|file.png> [...]\n");
    exit(1);
}

$exitCode = 0;

for ($i = 1; $i < $argc; $i++) {
    $file = $argv[$i];

    if (!is_file($file)) {
        fwrite(STDERR, "[ERROR] File not found: {$file}\n");
        $exitCode = 1;
        continue;
    }

    $extension = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));

    if ($extension === 'svg') {
        $svg = file_get_contents($file);
        if ($svg === false) {
            fwrite(STDERR, "[ERROR] Unable to read SVG: {$file}\n");
            $exitCode = 1;
            continue;
        }

        $processed = Graphviz::postProcessSvgMarkup($svg);
        if (file_put_contents($file, $processed) === false) {
            fwrite(STDERR, "[ERROR] Unable to write SVG: {$file}\n");
            $exitCode = 1;
            continue;
        }

        fwrite(STDOUT, "[OK] Embedded assets into SVG: {$file}\n");
        continue;
    }

    if ($extension === 'png') {
        fwrite(STDOUT, "[INFO] PNG already contains rasterized output, no post-processing applied: {$file}\n");
        continue;
    }

    fwrite(STDERR, "[WARN] Unsupported extension, skipped: {$file}\n");
}

exit($exitCode);
