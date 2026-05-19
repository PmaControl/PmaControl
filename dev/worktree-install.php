#!/usr/bin/env php
<?php
/**
 * Standalone bootstrap for /srv/www/pmacontrol-worktrees/<branch>/.
 *
 * Runs the six-step procedure documented in
 * docs/parallel_worktree_deployments.md in a single shot so a freshly
 * created `git worktree add` becomes Apache-servable and www-data-owned
 * without manual steps.
 *
 * Why a standalone script rather than a Glial controller? Because the
 * worktree we're trying to bootstrap typically has no configuration/,
 * no vendor/, and no symlinks yet — so the Glial bootstrap itself
 * cannot run. This script intentionally avoids loading anything from
 * the worktree (no autoload, no Sgbd, no constants from index.php).
 *
 * Usage (from inside the worktree):
 *
 *     sudo php dev/worktree-install.php
 *
 * Options:
 *
 *     --master=<path>          Source checkout (default /srv/www/pmacontrol)
 *     --www-prefix=<v>         WWW_ROOT prefix (default /pmacontrol-worktrees/)
 *     --plugin-cache=<v>       PLUGIN_STORAGE_DIR (default /srv/www/pmacontrol-plugin/.cache/)
 *     --plugin-src=<path>      Plugin sources root for trust-key discovery
 *                              (default /srv/www/pmacontrol-plugin)
 *     --no-trust-dev-keys      Do NOT inject the Ed25519 dev keys found at
 *                              <plugin-src>/*\/.keys/*.public.b64 into the
 *                              generated webroot.config.php. Set this on a
 *                              production checkout.
 *     --no-chown               Skip the recursive chown to www-data:www-data
 *     --no-glial               Skip the `glial administration all` regeneration
 *     --dry-run                Print every step without changing anything
 *
 * Idempotent: re-running on a fully bootstrapped worktree just confirms
 * the layout and re-runs the table-cache generator.
 */

set_error_handler(function ($severity, $message, $file, $line) {
    // Honour the @ operator (PHP 8 reports error_reporting()=0 inside @-suppressed calls).
    if (!(error_reporting() & $severity)) return false;
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

$opts = parse_argv($argv);
$root = realpath($opts['root'] ?? getcwd());
if ($root === false) {
    die_red("Cannot resolve worktree root (cwd={$opts['root']}).");
}
$master = rtrim($opts['master'] ?? '/srv/www/pmacontrol', '/');
$wwwPrefix = $opts['www-prefix'] ?? '/pmacontrol-worktrees/';
$pluginCache = $opts['plugin-cache'] ?? '/srv/www/pmacontrol-plugin/.cache/';
$pluginSrc   = rtrim($opts['plugin-src'] ?? dirname($pluginCache, 2).'/'.basename(dirname($pluginCache, 2)), '/');
// Default: /srv/www/pmacontrol-plugin/, sibling of the cache dir.
if (!is_dir($pluginSrc)) {
    $pluginSrc = '/srv/www/pmacontrol-plugin';
}
$dryRun = !empty($opts['dry-run']);
$skipChown = !empty($opts['no-chown']);
$skipGlial = !empty($opts['no-glial']);
$noTrust  = !empty($opts['no-trust-dev-keys']);
$branch = basename($root);

if ($root === $master) {
    die_red("Refusing to bootstrap the master checkout itself ({$root}).");
}
if (!is_dir($master.'/configuration') || !is_dir($master.'/vendor')) {
    die_red("Master {$master} is missing configuration/ or vendor/. Pass --master=<path>.");
}
if (!is_dir($root.'/App/Webroot')) {
    die_red("{$root} does not look like a pmacontrol worktree (no App/Webroot/).");
}

banner("Bootstrap worktree:\n  root   = {$root}\n  master = {$master}\n  branch = {$branch}".($dryRun ? "\n  mode   = DRY-RUN" : ''));

step_webroot_config($root, $branch, $wwwPrefix, $pluginCache, $pluginSrc, $noTrust, $dryRun);
step_configuration_symlinks($root, $master, $dryRun);
step_vendor($root, $master, $dryRun);
step_webroot_plugins_symlink($root, $dryRun);
step_app_model_dir($root, $dryRun);
step_chown($root, $skipChown, $dryRun);
step_plugin_cache_dir($pluginCache, $skipChown, $dryRun);
step_glial_admin($root, $skipGlial, $dryRun);

global $_REPORT;
$oks = count(array_filter($_REPORT, fn($r) => $r[0] === 'ok'));
$warns = count(array_filter($_REPORT, fn($r) => $r[0] === 'warn'));
echo "\nDone — {$oks} steps OK".($warns ? ", {$warns} warning(s)" : '').".\n";
echo "Browse:  http://<host>{$wwwPrefix}{$branch}/en/server/main\n";
foreach ($_REPORT as $r) {
    if ($r[0] === 'warn') echo "  ⚠  {$r[1]}\n";
}
exit(0);

// ---------------------------------------------------------------- helpers

$GLOBALS['_REPORT'] = array();

function parse_argv(array $argv)
{
    $out = array();
    foreach (array_slice($argv, 1) as $a) {
        if (preg_match('/^--([a-z\-]+)=(.+)$/', $a, $m)) {
            $out[$m[1]] = $m[2];
        } elseif (preg_match('/^--([a-z\-]+)$/', $a, $m)) {
            $out[$m[1]] = true;
        } elseif ($a[0] !== '-') {
            $out['root'] = $a;
        }
    }
    return $out;
}

function step_webroot_config($root, $branch, $wwwPrefix, $pluginCache, $pluginSrc, $noTrust, $dryRun)
{
    $target = $root.'/configuration/webroot.config.php';
    $wwwRoot = rtrim($wwwPrefix, '/').'/'.$branch.'/';

    $trustBlock = '';
    $trustSummary = '';
    if (!$noTrust) {
        $trusted = collect_dev_trust_anchors($pluginSrc);
        if (!empty($trusted)) {
            $json = json_encode($trusted, JSON_UNESCAPED_SLASHES);
            $trustBlock = "\n// Dev-only Ed25519 trust anchors collected from every\n"
                ."// {$pluginSrc}/*/.keys/*.public.b64. Skip with --no-trust-dev-keys\n"
                ."// (e.g. on a production checkout where real keys ship via env).\n"
                ."if (getenv('PMACONTROL_PLUGIN_SIGNATURE_PUBLIC_KEYS') === false) {\n"
                ."    putenv('PMACONTROL_PLUGIN_SIGNATURE_PUBLIC_KEYS='.".var_export($json, true).");\n"
                ."}\n";
            $trustSummary = ", trust anchors=".count($trusted)." (".implode(',', array_keys($trusted)).")";
        }
    }

    $contents = "<?php\n"
        ."// Generated by dev/worktree-install.php. Hand-edit if needed.\n"
        ."if (! defined('WWW_ROOT')) {\n"
        ."    define('WWW_ROOT', ".var_export($wwwRoot, true).");\n"
        ."}\n"
        ."if (! defined('PLUGIN_STORAGE_DIR')) {\n"
        ."    define('PLUGIN_STORAGE_DIR', ".var_export($pluginCache, true).");\n"
        ."}\n"
        .$trustBlock;

    if (is_file($target) && @file_get_contents($target) === $contents) {
        return ok("configuration/webroot.config.php (already current)");
    }
    if ($dryRun) return ok("[dry] write {$target}{$trustSummary}");
    @mkdir(dirname($target), 0755, true);
    if (file_put_contents($target, $contents) === false) {
        die_red("Cannot write {$target}");
    }
    ok("configuration/webroot.config.php → WWW_ROOT={$wwwRoot}, PLUGIN_STORAGE_DIR={$pluginCache}{$trustSummary}");
}

/**
 * Scan <pluginSrc>/*\/.keys/*.public.b64 and return an associative
 * array <keyId> => <base64 public key>. The keyId is the filename minus
 * the .public.b64 suffix, so `eol-dev.public.b64` registers `eol-dev`.
 * Empty / oversized / unreadable files are skipped silently.
 */
function collect_dev_trust_anchors($pluginSrc)
{
    $trusted = array();
    foreach (glob($pluginSrc.'/*/.keys/*.public.b64') ?: array() as $file) {
        if (!is_readable($file) || filesize($file) > 256) continue;
        $body = trim((string)@file_get_contents($file));
        if ($body === '') continue;
        $keyId = preg_replace('/\.public\.b64$/', '', basename($file));
        $trusted[$keyId] = $body;
    }
    return $trusted;
}

function step_configuration_symlinks($root, $master, $dryRun)
{
    $linked = 0;
    foreach (glob($master.'/configuration/*') as $src) {
        $base = basename($src);
        if ($base === 'webroot.config.php') continue;
        $dst = $root.'/configuration/'.$base;
        if (file_exists($dst) || is_link($dst)) continue;
        if ($dryRun) { $linked++; continue; }
        if (!@symlink($src, $dst)) {
            die_red("Cannot symlink {$src} -> {$dst}");
        }
        $linked++;
    }
    ok("configuration/* symlinks ({$linked} new, others kept)");
}

function step_vendor($root, $master, $dryRun)
{
    $vendorDir = $root.'/vendor';
    if (is_link($vendorDir)) {
        die_red("vendor/ is a symlink — that breaks Composer PSR-4 autoload (App\\\\… resolves to master). rm vendor/ and re-run.");
    }
    if (!is_dir($vendorDir)) {
        if ($dryRun) return ok("[dry] mkdir vendor + cp composer + symlinks");
        if (!mkdir($vendorDir, 0755)) die_red("Cannot mkdir {$vendorDir}");
    }

    $localized = 0;
    foreach (array('autoload.php', 'composer') as $entry) {
        $src = $master.'/vendor/'.$entry;
        $dst = $vendorDir.'/'.$entry;
        if (file_exists($dst)) continue;
        if (!file_exists($src)) die_red("Missing {$src} on master — run composer install on master first.");
        if ($dryRun) { $localized++; continue; }
        copy_recursive($src, $dst);
        $localized++;
    }

    $symlinked = 0;
    foreach (glob($master.'/vendor/*', GLOB_ONLYDIR) as $src) {
        $base = basename($src);
        if ($base === 'composer') continue;
        $dst = $vendorDir.'/'.$base;
        if (file_exists($dst) || is_link($dst)) continue;
        if ($dryRun) { $symlinked++; continue; }
        if (!@symlink($src, $dst)) die_red("Cannot symlink vendor/{$base}");
        $symlinked++;
    }
    ok("vendor/ ({$localized} files copied, {$symlinked} packages symlinked)");
}

function step_webroot_plugins_symlink($root, $dryRun)
{
    $link = $root.'/App/Webroot/plugins';
    if (is_link($link) || file_exists($link)) {
        return ok("App/Webroot/plugins (already present)");
    }
    if ($dryRun) return ok("[dry] symlink App/Webroot/plugins → ../../plugins");
    if (!@symlink('../../plugins', $link)) {
        die_red("Cannot symlink {$link}");
    }
    ok("App/Webroot/plugins → ../../plugins");
}

function step_app_model_dir($root, $dryRun)
{
    $dir = $root.'/App/model/IdentifierPmacontrol';
    if (is_dir($dir)) return ok("App/model/IdentifierPmacontrol (already present)");
    if ($dryRun) return ok("[dry] mkdir {$dir}");
    if (!mkdir($dir, 0755, true)) die_red("Cannot mkdir {$dir}");
    ok("App/model/IdentifierPmacontrol (created)");
}

/**
 * mkdir + chown the PLUGIN_STORAGE_DIR that step_webroot_config baked
 * into the worktree's configuration/webroot.config.php. Without this,
 * the first Plugin/install/ click in the freshly bootstrapped worktree
 * dies with a "Cannot create plugin storage directory" error and asks
 * the operator to drop to a root shell to mkdir+chown by hand.
 *
 * Idempotent: re-running on a populated cache only fixes ownership.
 * Runs *after* step_chown so the recursive chown of the worktree
 * cannot clobber this work (the cache usually lives outside the
 * worktree, but a user passing --plugin-cache=<inside-worktree-path>
 * would otherwise see this step land first and then get reverted).
 */
function step_plugin_cache_dir($pluginCache, $skipChown, $dryRun)
{
    $pluginCache = rtrim($pluginCache, '/');
    $extracted = $pluginCache.'/extracted';

    foreach (array($pluginCache, $extracted) as $dir) {
        if (is_dir($dir)) continue;
        if ($dryRun) { ok("[dry] mkdir -p {$dir}"); continue; }
        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            die_red("Cannot mkdir {$dir}");
        }
    }

    if ($skipChown) {
        return warn("plugin cache {$pluginCache} (chown skipped: --no-chown)");
    }
    if (posix_getuid() !== 0) {
        return warn("plugin cache {$pluginCache} (chown skipped: not running as root)");
    }
    if ($dryRun) {
        return ok("[dry] chown www-data:www-data {$pluginCache} {$extracted}");
    }

    foreach (array($pluginCache, $extracted) as $dir) {
        if (!is_dir($dir)) continue;
        exec('chown www-data:www-data '.escapeshellarg($dir), $out, $rc);
        if ($rc !== 0) die_red("chown failed on {$dir} (rc={$rc})");
    }
    ok("plugin cache {$pluginCache} (mkdir + chown www-data:www-data)");
}

function step_chown($root, $skip, $dryRun)
{
    if ($skip) return warn("chown -R www-data:www-data .  (skipped: --no-chown)");
    if (posix_getuid() !== 0) return warn("chown -R www-data:www-data .  (skipped: not running as root — sudo php dev/worktree-install.php)");
    if ($dryRun) return ok("[dry] chown -R www-data:www-data {$root}");
    exec('chown -R www-data:www-data '.escapeshellarg($root), $out, $rc);
    if ($rc !== 0) die_red("chown failed (rc={$rc})");
    ok("chown -R www-data:www-data .");
}

function step_glial_admin($root, $skip, $dryRun)
{
    if ($skip) return warn("./glial administration all  (skipped: --no-glial)");
    $glial = $root.'/glial';
    if (!is_file($glial)) return warn("./glial administration all  (skipped: ./glial not found)");
    if ($dryRun) return ok("[dry] sudo -u www-data php {$glial} administration all");
    $cmd = 'cd '.escapeshellarg($root).' && '
         .'sudo -u www-data php '.escapeshellarg($glial).' administration all 2>&1';
    exec($cmd, $out, $rc);
    if ($rc !== 0) {
        warn("./glial administration all failed (rc={$rc}):\n    "
            .implode("\n    ", array_slice($out, -6)));
        return;
    }
    ok("./glial administration all (tmp/database + App/model regenerated)");
}

function copy_recursive($src, $dst)
{
    if (is_dir($src)) {
        if (!is_dir($dst) && !mkdir($dst, 0755, true)) die_red("Cannot mkdir {$dst}");
        foreach (array_diff(scandir($src), array('.', '..')) as $entry) {
            copy_recursive($src.'/'.$entry, $dst.'/'.$entry);
        }
    } else {
        if (!copy($src, $dst)) die_red("Cannot copy {$src} → {$dst}");
    }
}

function ok($msg) { $GLOBALS['_REPORT'][] = array('ok', $msg); echo "  \e[32m✓\e[0m {$msg}\n"; }
function warn($msg) { $GLOBALS['_REPORT'][] = array('warn', $msg); echo "  \e[33m⚠\e[0m {$msg}\n"; }
function banner($msg) { echo "\n\e[1m{$msg}\e[0m\n\n"; }
function die_red($msg) { fwrite(STDERR, "\n\e[31m✗ {$msg}\e[0m\n"); exit(1); }
