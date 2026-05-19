#!/usr/bin/env php
<?php
/**
 * PmaControl plugin translations generator (reference implementation).
 *
 * Walks a plugin's `src/` tree for every `__('…')` / `__("…")` call,
 * captures the source string plus the file/line where it appears,
 * looks each up in a hand-maintained `$T` dictionary, and emits
 * `sql/translations.sql` with one `INSERT IGNORE INTO translation_main`
 * row per (key, target_language) pair. Drop the SQL into the plugin's
 * manifest under `data.install` so PluginPackage's standard install
 * pipeline runs it.
 *
 * Why this lives in `dev/` of the main PmaControl repo:
 *
 *   • The pattern is the same for every plugin — only the dictionary
 *     contents change. Keeping the canonical generator here means
 *     plugins copy it once into their own `bin/` and only maintain
 *     the translation table.
 *   • The Glial I18n key convention (sha1('en-' + source_text)) lives
 *     in `vendor/glial/glial/Glial/I18n/I18n.php:466`. Centralising the
 *     generator next to that contract avoids drift if Glial ever
 *     changes how it derives keys.
 *
 * =============================================================
 *  PROCESS — end-to-end, the same for every PmaControl plugin
 * =============================================================
 *
 *  1. INVENTORY
 *     • The generator regexes `__('…')` and `__("…")` from every
 *       `.php` file under `src/`. Unescapes single-quote / double-quote
 *       backslash sequences. Captures the first file/line where each
 *       unique source string appears.
 *     • `--stdout` prints to stdout instead of writing the file.
 *     • `--check` exits non-zero if any source string is missing a
 *       translation — wire it into the plugin's pre-commit / CI gate.
 *
 *  2. CONTEXT-AWARE TRANSLATION
 *     • `$T` is the heart of the file: one entry per unique source
 *       string, with five target translations.
 *     • Always check the context where the string appears before
 *       picking the French rendering. Examples:
 *         "Latest"    in a version column → "Dernière"
 *         "Latest"    in a release timeline → "Récente"
 *         "Server"    column header → "Serveur" (singular)
 *         "Servers"   summary count → "serveurs" (lowercase plural)
 *     • The French rendering is the reference. Translate the other
 *       four languages (ar, ru, pl, zh-cn) FROM that French so meaning
 *       stays consistent across all five renderings.
 *     • Comments alongside each entry are recommended — they make the
 *       intent obvious during reviews and dictate which French variant
 *       was chosen.
 *     • Brand names and acronyms NEVER get translated: EOL, KPI,
 *       TL;DR, LTS, MariaDB, MySQL, Percona, ProxySQL, MaxScale,
 *       PXC, Org × Env, Δ. The Δ in particular should be a UTF-8
 *       literal in the source file, not the HTML entity.
 *     • Punctuation: French uses non-breaking spaces before « : » and
 *       « ; ». Use `\u{00A0}` if you need the literal character in a
 *       PHP single-quoted string (or just keep the regular space — the
 *       browser will not punish you for it).
 *     • Numbers and dates with separators: the en-dash (–) and em-dash
 *       (—) are kept literal in every language. The "×" in
 *       "Org × Env matrix" is U+00D7 (multiplication sign) — copy/paste
 *       from the source rather than retyping with a lowercase x.
 *
 *  3. OUTPUT
 *     • Generates `sql/translations.sql` with one `INSERT IGNORE`
 *       row per (key, destination). Source language is hard-coded
 *       to 'en' because PmaControl assumes English source strings.
 *     • Header comment in the SQL records: timestamp, source-string
 *       count, target-language list. Useful for `git diff` review
 *       (the file is auto-generated, but human-readable).
 *     • `INSERT IGNORE` (not `ON DUPLICATE KEY UPDATE`) — re-running
 *       the install never overwrites a hand-corrected row that an
 *       operator might have edited in the DB after the fact. To force
 *       a refresh, the operator deletes the rows manually.
 *
 *  4. WIRE INTO THE MANIFEST
 *     • Add an entry under `data.install` in `plugin.json`:
 *         "data": { "install": ["sql/translations.sql"], "uninstall": [] }
 *     • `PluginPackage::phaseFiles()` reads `data` alongside `ddl`
 *       and `sql`, runs them in `Plugin::install` after the file
 *       copies, before the menu setup. The translations are in DB
 *       before the operator hits any plugin URL — no first-render
 *       gap.
 *     • Leave `data.uninstall` empty by default. Removing the
 *       translation_main rows on uninstall is not worth the risk of
 *       deleting a row that another plugin still references via the
 *       same sha1 key.
 *
 *  5. CI / PRE-COMMIT
 *     • `php bin/gen-translations.php --check` exits non-zero if any
 *       source string is missing from `$T`. Wire it into the plugin's
 *       pre-commit hook or CI so a missing translation is caught at
 *       commit time rather than at install time.
 *     • Re-run the generator after every `__()`-touching change. The
 *       SQL is committed alongside the source, so the install always
 *       ships the right set.
 *
 *  6. RUNTIME RENDERING
 *     • Glial's `__($text)` resolves the active language from the URL
 *       prefix (`/<lang>/Eol/kpi/`), computes `sha1('en-'.$text)`,
 *       looks the key up in `translation_main`, falls back to the
 *       English source if no row matches. So a missing translation is
 *       a silent degradation, not an error — but worth catching with
 *       `--check`.
 *
 *  7. KEY COLLISIONS (extremely rare)
 *     • The sha1 keyspace is 2^160. The chance of two different
 *       English source strings landing on the same key is mathematically
 *       negligible. If it ever happens, Glial picks the row with the
 *       earliest `id` from translation_main — non-deterministic and a
 *       footgun. Mitigation: never name two `__()` strings with such
 *       similar text that a collision could be intentional.
 *
 * =============================================================
 *  Adopting in YOUR plugin
 * =============================================================
 *
 *  $ cp dev/gen-plugin-translations.php
 *       /path/to/myplugin/bin/gen-translations.php
 *  $ vim   /path/to/myplugin/bin/gen-translations.php
 *      # 1. update the $T dictionary
 *      # 2. tweak the `--check` exit-code policy if needed
 *  $ php   /path/to/myplugin/bin/gen-translations.php
 *  $ vim   /path/to/myplugin/plugin.json
 *      # add "data": { "install": ["sql/translations.sql"], "uninstall": [] }
 *  $ git add bin/gen-translations.php sql/translations.sql plugin.json
 *
 *  Re-run the generator every time you touch a `__()` call. Commit
 *  the SQL alongside the source so reviewers see the translation
 *  diff at the same time as the UI diff.
 *
 *  The dictionary below is the eol-plugin reference. Replace it with
 *  your own strings. Keep this header intact so future maintainers
 *  know where to find the canonical pattern (PmaControl
 *  `dev/gen-plugin-translations.php`).
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Must be run from CLI\n");
    exit(1);
}

$root = realpath($argv[1] ?? getcwd());
if ($root === false || !is_dir($root.'/src')) {
    fwrite(STDERR, "Usage: php dev/gen-plugin-translations.php [<plugin-root>]\n");
    fwrite(STDERR, "       <plugin-root> defaults to cwd; must contain `src/`.\n");
    exit(1);
}

$LANGUAGES = ['fr', 'ar', 'ru', 'pl', 'zh-cn'];

// -----------------------------------------------------------------
// $T — translation dictionary. REPLACE THIS BLOCK in your plugin.
// -----------------------------------------------------------------
// One entry per source string. Five translations per entry. Comment
// the intended French context where it isn't obvious — that anchors
// the meaning for the other four languages.
$T = [
    // Example only. The eol plugin's own dictionary lives in its
    // bin/gen-translations.php — generally ~150-200 entries.
    'Hello' => ['fr' => 'Bonjour', 'ar' => 'مرحباً', 'ru' => 'Привет', 'pl' => 'Cześć', 'zh-cn' => '你好'],
];

// -----------------------------------------------------------------
// Below this point: regular generator machinery — usually no edits.
// -----------------------------------------------------------------

function key_of($source) {
    return sha1('en-'.$source);
}

function sql_escape($s) {
    return str_replace(['\\', "'"], ['\\\\', "\\'"], $s);
}

$strings = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/src'));
foreach ($rii as $file) {
    if (!$file->isFile() || substr($file->getFilename(), -4) !== '.php') continue;
    $path = $file->getRealPath();
    $relative = ltrim(substr($path, strlen($root)), '/');
    $installed = preg_replace('#^src/#', '', $relative);
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (preg_match_all("/__\\(\\s*'((?:[^'\\\\]|\\\\.)*)'\\s*\\)/", $line, $m)) {
            foreach ($m[1] as $hit) {
                $unescaped = stripcslashes($hit);
                $strings[$unescaped] = $strings[$unescaped] ?? ['file' => $installed, 'line' => $i + 1];
            }
        }
        if (preg_match_all('/__\(\s*"((?:[^"\\\\]|\\\\.)*)"\s*\)/', $line, $m)) {
            foreach ($m[1] as $hit) {
                $unescaped = stripcslashes($hit);
                $strings[$unescaped] = $strings[$unescaped] ?? ['file' => $installed, 'line' => $i + 1];
            }
        }
    }
}
ksort($strings);

$stdout = in_array('--stdout', $argv, true);
$check  = in_array('--check', $argv, true);
$outPath = $root.'/sql/translations.sql';
$out = $stdout ? STDOUT : fopen($outPath, 'w');
if ($out === false) {
    fwrite(STDERR, "Cannot open {$outPath} for write\n");
    exit(1);
}

fwrite($out,
    "-- Auto-generated by " . basename(__FILE__) . " — DO NOT HAND-EDIT.\n"
  . "-- Source strings: every __('…') and __(\"…\") call in src/.\n"
  . "-- Key: sha1('en-' + source_text)  (Glial I18n.php line 466).\n"
  . "-- Generated: ".date('Y-m-d H:i:s')." UTC".sprintf('%+d', date('Z') / 3600)."\n"
  . "-- Strings:  ".count($strings)."\n"
  . "-- Targets:  ".implode(', ', $LANGUAGES)."\n\n"
);

$missing = [];
$emitted = 0;
foreach ($strings as $source => $meta) {
    if (!isset($T[$source])) {
        $missing[] = $source;
        continue;
    }
    $key = key_of($source);
    $fileFound = sql_escape($meta['file']);
    foreach ($LANGUAGES as $lang) {
        $tr = $T[$source][$lang] ?? null;
        if ($tr === null) { $missing[] = $source.'  ['.$lang.']'; continue; }
        fwrite($out, sprintf(
            "INSERT IGNORE INTO `translation_main` (`key`, `source`, `destination`, `text`, `file_found`, `line_found`) "
          . "VALUES ('%s', 'en', '%s', '%s', '%s', %d);\n",
            $key, $lang, sql_escape($tr), $fileFound, $meta['line']
        ));
        $emitted++;
    }
}
if (!$stdout) fclose($out);

fwrite(STDERR, "Sources scanned:  ".count($strings)."\n");
fwrite(STDERR, "Rows emitted:     {$emitted}\n");
if ($missing) {
    fwrite(STDERR, "MISSING translations (".count($missing)."):\n");
    foreach (array_unique($missing) as $m) {
        fwrite(STDERR, "  - {$m}\n");
    }
    if ($check) exit(2);
}
fwrite(STDERR, "Wrote: ".($stdout ? '(stdout)' : $outPath)."\n");
exit(0);
