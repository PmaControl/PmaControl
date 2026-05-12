<?php
/**
 * /MysqlServer/plugins/<id>/<name>/ — Plugins + Storage Engines tab.
 * Read-only for everyone; SuperAdmin (id_group=4) gets the Install
 * buttons on hot-installable catalog rows.
 *
 * @var array $data
 */

use \Glial\Synapse\FactoryController;
use \App\Library\PluginCatalog;

FactoryController::addNode("MysqlServer", "menu", [
    $data['server']['id'] ?? 0,
    $data['server']['name'] ?? '',
]);

$srv      = $data['server'] ?? [];
$family   = (string) ($data['family'] ?? 'unknown');
$version  = (string) ($data['version'] ?? '');
$vcomment = (string) ($data['version_comment'] ?? '');
$enginesIndex = $data['engines_index'] ?? [];
$pluginsIndex = $data['plugins_index'] ?? [];
$catalog = $data['catalog'] ?? [];
$isAdmin = !empty($data['is_super_admin']);

$familyLabel = ['mariadb' => 'MariaDB', 'mysql' => 'MySQL', 'unknown' => '?'][$family] ?? '?';

$pillCssById = [
    'YES'      => 'background:#16a34a;color:#fff',
    'DEFAULT'  => 'background:#16a34a;color:#fff',
    'ACTIVE'   => 'background:#16a34a;color:#fff',
    'NO'       => 'background:#94a3b8;color:#fff',
    'DISABLED' => 'background:#dc2626;color:#fff',
    'INACTIVE' => 'background:#dc2626;color:#fff',
];
function pluginsTabPill(string $value, array $cssById): string {
    $css = $cssById[strtoupper($value)] ?? 'background:#cbd5e1;color:#1f2937';
    return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;' . $css . '">'
        . htmlspecialchars($value) . '</span>';
}
?>

<style>
.pl-card { border:1px solid #e2e8f0;border-radius:6px;margin-bottom:16px; }
.pl-card-head { background:linear-gradient(135deg,#0f172a,#1e3a8a);color:#fff;padding:8px 14px;font-weight:600;border-radius:6px 6px 0 0; }
.pl-card-body { padding:8px 14px; }
.pl-table { width:100%;border-collapse:collapse;font-size:13px; }
.pl-table th, .pl-table td { padding:6px 10px;border-bottom:1px solid #f1f5f9;vertical-align:top; }
.pl-table th { text-align:left;color:#64748b;font-weight:600;background:#f8fafc; }
.pl-row-installed { background:#f0fdf4; }
.pl-row-missing-hot { background:#fefce8; }
.pl-row-missing-pkg { background:#fff7ed; }
.pl-row-na { background:#f8fafc;color:#94a3b8; }
.pl-btn { background:#1e40af;color:#fff;border:none;padding:3px 8px;border-radius:4px;cursor:pointer;font-size:11px; }
.pl-btn:hover { background:#1d4ed8; }
.pl-btn:disabled { background:#cbd5e1;cursor:not-allowed; }
.pl-pkg { font-family:monospace;font-size:11px;background:#fef3c7;padding:1px 6px;border-radius:3px; }
.pl-soname { font-family:monospace;font-size:11px;color:#475569; }
</style>

<div class="pl-card">
    <div class="pl-card-head">
        <?= __('Server') ?> :
        <?= htmlspecialchars($srv['display_name'] ?: $srv['name']) ?>
        <small style="opacity:0.85;font-weight:400;margin-left:8px">
            <?= htmlspecialchars($version) ?>
            <?php if ($vcomment !== ''): ?> — <?= htmlspecialchars($vcomment) ?><?php endif; ?>
            — <?= __('detected family') ?> : <strong><?= htmlspecialchars($familyLabel) ?></strong>
            <?php if (!$isAdmin): ?>
                · <span style="background:#fbbf24;color:#1f2937;padding:1px 6px;border-radius:3px"><?= __('read-only — Super administrator only can install') ?></span>
            <?php endif; ?>
        </small>
    </div>
</div>

<!-- ── Storage engines (live SHOW ENGINES from Aspirateur cache) ── -->
<div class="pl-card">
    <div class="pl-card-head" style="background:linear-gradient(135deg,#0c4a6e,#0e7490)">
        <?= __('Storage engines') ?> (<?= count($enginesIndex) ?>) <small style="opacity:0.85;font-weight:400">— <?= __('source: Aspirateur cache `information_schema::engines`') ?></small>
    </div>
    <div class="pl-card-body">
        <?php if (empty($enginesIndex)): ?>
            <p style="color:#64748b;margin:0"><?= __('No engine row in the Aspirateur cache yet — the collector may not have run for this server.') ?></p>
        <?php else: ?>
            <table class="pl-table">
                <tr>
                    <th><?= __('Engine') ?></th>
                    <th><?= __('Support') ?></th>
                    <th><?= __('Transactions') ?></th>
                    <th>XA</th>
                    <th><?= __('Savepoints') ?></th>
                    <th><?= __('Comment') ?></th>
                </tr>
                <?php foreach ($enginesIndex as $name => $eng): ?>
                    <tr<?= in_array($eng['support'], ['YES','DEFAULT'], true) ? ' class="pl-row-installed"' : '' ?>>
                        <td><strong><?= htmlspecialchars($name) ?></strong></td>
                        <td><?= pluginsTabPill($eng['support'] ?: 'NO', $pillCssById) ?></td>
                        <td><?= htmlspecialchars($eng['transactions']) ?></td>
                        <td><?= htmlspecialchars($eng['xa']) ?></td>
                        <td><?= htmlspecialchars($eng['savepoints']) ?></td>
                        <td><small style="color:#475569"><?= htmlspecialchars($eng['comment']) ?></small></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- ── Installed plugins (information_schema.PLUGINS) ── -->
<div class="pl-card">
    <div class="pl-card-head" style="background:linear-gradient(135deg,#1e3a8a,#4338ca)">
        <?= __('Installed plugins') ?> (<?= count($pluginsIndex) ?>) <small style="opacity:0.85;font-weight:400">— <?= __('source: Aspirateur cache `information_schema::plugins`') ?></small>
    </div>
    <div class="pl-card-body">
        <?php if (empty($pluginsIndex)): ?>
            <p style="color:#64748b;margin:0"><?= __('No plugin row in the Aspirateur cache yet.') ?></p>
        <?php else: ?>
            <table class="pl-table">
                <tr>
                    <th><?= __('Plugin') ?></th>
                    <th><?= __('Status') ?></th>
                    <th><?= __('Type') ?></th>
                    <th><?= __('Version') ?></th>
                    <th><?= __('Library') ?></th>
                    <th><?= __('License') ?></th>
                </tr>
                <?php foreach ($pluginsIndex as $name => $p): ?>
                    <tr<?= $p['status'] === 'ACTIVE' ? ' class="pl-row-installed"' : '' ?>>
                        <td><code style="font-size:12px"><?= htmlspecialchars($name) ?></code></td>
                        <td><?= pluginsTabPill($p['status'] ?: '?', $pillCssById) ?></td>
                        <td><small style="color:#64748b"><?= htmlspecialchars($p['type']) ?></small></td>
                        <td><small><?= htmlspecialchars($p['version']) ?></small></td>
                        <td><small class="pl-soname"><?= htmlspecialchars($p['library']) ?></small></td>
                        <td><small style="color:#94a3b8"><?= htmlspecialchars($p['license']) ?></small></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- ── Plugin catalog matrix (filtered to detected family) ── -->
<div class="pl-card">
    <div class="pl-card-head" style="background:linear-gradient(135deg,#4c1d95,#7e22ce)">
        <?= __('Plugin catalog') ?> — <?= htmlspecialchars($familyLabel) ?>
        <small style="opacity:0.85;font-weight:400;margin-left:8px">— <?= __('only entries available on this server family; Install / Uninstall are split into two columns to avoid mistakes') ?></small>
    </div>
    <div class="pl-card-body">
        <table class="pl-table">
            <tr>
                <th><?= __('Name') ?></th>
                <th><?= __('Kind') ?></th>
                <th><?= __('Description') ?></th>
                <th><?= __('Package / SONAME') ?></th>
                <th><?= __('Status') ?></th>
                <th style="background:#ecfccb"><?= __('Install') ?></th>
                <th style="background:#fef2f2"><?= __('Uninstall') ?></th>
            </tr>
            <?php foreach ($catalog as $entry):
                $name = strtoupper($entry['name']);
                $kind = $entry['kind'];
                $isEngine = $kind === PluginCatalog::KIND_ENGINE;
                $spec = $entry[$family] ?? null;
                $availability = $spec['availability'] ?? PluginCatalog::AVAILABILITY_NA;
                $soname = (string) ($spec['soname'] ?? '');
                $package = (string) ($spec['package'] ?? '');
                $hint = (string) ($spec['install_hint'] ?? '');

                // Engines are checked against information_schema.engines
                // (the live SHOW ENGINES table); plugins are checked
                // against information_schema.plugins. Either one is
                // enough to count the row as installed.
                $installed = false;
                $library = '';
                if ($isEngine && in_array(($enginesIndex[$name]['support'] ?? 'NO'), ['YES','DEFAULT'], true)) {
                    $installed = true;
                    // Engine row also has a matching info_schema.plugins
                    // row; pull PLUGIN_LIBRARY from there to know if
                    // it's static-compiled.
                    $library = (string) ($pluginsIndex[$name]['library'] ?? '');
                }
                if (!$installed && isset($pluginsIndex[$name]) && $pluginsIndex[$name]['status'] === 'ACTIVE') {
                    $installed = true;
                    $library = (string) ($pluginsIndex[$name]['library'] ?? '');
                }
                // Static-compiled plugin = PLUGIN_LIBRARY is empty.
                // UNINSTALL SONAME would fail; we hide the button.
                $isStaticCompiled = $installed && $library === '';

                $rowClass = 'pl-row-na';
                if ($installed) $rowClass = 'pl-row-installed';
                elseif ($availability === PluginCatalog::AVAILABILITY_CORE) $rowClass = 'pl-row-missing-hot';
                elseif ($availability === PluginCatalog::AVAILABILITY_PACKAGE) $rowClass = 'pl-row-missing-pkg';

                $hot = $availability === PluginCatalog::AVAILABILITY_CORE;
                $packageBadge = $hot
                    ? '<span style="background:#16a34a;color:#fff;padding:1px 6px;border-radius:3px;font-size:10px">hot</span>'
                    : '<span style="background:#ea580c;color:#fff;padding:1px 6px;border-radius:3px;font-size:10px">apt + restart</span>';
            ?>
                <tr class="<?= $rowClass ?>">
                    <td><strong><?= htmlspecialchars($name) ?></strong></td>
                    <td><small style="color:#64748b"><?= htmlspecialchars($kind) ?></small></td>
                    <td><small><?= htmlspecialchars($entry['description']) ?></small></td>
                    <td>
                        <?= $packageBadge ?>
                        <?php if ($soname !== ''): ?> <span class="pl-soname"><?= htmlspecialchars($soname) ?></span><?php endif; ?>
                        <?php if ($package !== ''): ?><br><span class="pl-pkg"><?= htmlspecialchars($package) ?></span><?php endif; ?>
                        <?php if ($hint !== ''): ?><br><small style="color:#94a3b8"><?= htmlspecialchars($hint) ?></small><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($installed && $isStaticCompiled): ?>
                            <span style="background:#1e40af;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px"><?= __('built-in') ?></span>
                        <?php elseif ($installed): ?>
                            <span style="background:#16a34a;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px"><?= __('installed') ?></span>
                        <?php elseif ($hot): ?>
                            <span style="background:#facc15;color:#1f2937;padding:2px 8px;border-radius:10px;font-size:11px"><?= __('not loaded — hot install') ?></span>
                        <?php else: ?>
                            <span style="background:#ea580c;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px"><?= __('package required') ?></span>
                        <?php endif; ?>
                    </td>
                    <!-- Install column (left) -->
                    <td style="background:#f7fee7">
                        <?php if ($installed): ?>
                            <small style="color:#94a3b8">—</small>
                        <?php elseif (!$isAdmin): ?>
                            <small style="color:#94a3b8"><?= __('SuperAdmin only') ?></small>
                        <?php elseif ($hot): ?>
                            <button class="pl-btn pl-btn-install" data-plugin="<?= htmlspecialchars($name) ?>" data-soname="<?= htmlspecialchars($soname) ?>" style="background:#15803d"><?= __('Install') ?></button>
                        <?php else: ?>
                            <button class="pl-btn pl-btn-install" data-plugin="<?= htmlspecialchars($name) ?>" data-soname="<?= htmlspecialchars($soname) ?>" data-needs-package="<?= htmlspecialchars($package) ?>" style="background:#a16207" title="<?= __('Will fail until you run apt install on the host first') ?>"><?= __('Install (try)') ?></button>
                            <br><small style="color:#475569">apt: <code><?= htmlspecialchars($package) ?></code></small>
                        <?php endif; ?>
                    </td>
                    <!-- Uninstall column (right) -->
                    <td style="background:#fef2f2">
                        <?php if (!$installed): ?>
                            <small style="color:#94a3b8">—</small>
                        <?php elseif ($isStaticCompiled): ?>
                            <small style="color:#94a3b8" title="<?= __('Statically compiled — UNINSTALL SONAME would fail (no PLUGIN_LIBRARY)') ?>"><?= __('built-in') ?></small>
                        <?php elseif (!$isAdmin): ?>
                            <small style="color:#94a3b8"><?= __('SuperAdmin only') ?></small>
                        <?php else: ?>
                            <button class="pl-btn pl-btn-uninstall" data-plugin="<?= htmlspecialchars($name) ?>" data-soname="<?= htmlspecialchars($soname) ?>" data-library="<?= htmlspecialchars($library) ?>" style="background:#b91c1c"><?= __('Uninstall') ?></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p style="font-size:11px;color:#64748b;margin-top:10px;border-top:1px dashed #e2e8f0;padding-top:8px">
            <?= __('Legend:') ?>
            <span style="background:#16a34a;color:#fff;padding:1px 6px;border-radius:3px;font-size:10px">hot</span> = INSTALL SONAME suffices, no restart.
            <span style="background:#ea580c;color:#fff;padding:1px 6px;border-radius:3px;font-size:10px">apt + restart</span> = install the package on the host first, restart the service, then INSTALL SONAME.
            <span style="background:#1e40af;color:#fff;padding:1px 6px;border-radius:3px;font-size:10px">built-in</span> = statically compiled into the server binary; cannot be UNINSTALLed.
        </p>
    </div>
</div>

<?php if ($isAdmin): ?>
<script>
(function () {
    var CSRF_FIELD = <?= json_encode($data['csrf_field']) ?>;
    var CSRF_TOKEN = <?= json_encode($data['csrf_token']) ?>;
    var SERVER_ID  = <?= (int) ($srv['id'] ?? 0) ?>;
    var LINK = <?= json_encode(LINK) ?>;

    var CSRF_TOKEN_UNIN = <?= json_encode($data['csrf_token_unin'] ?? '') ?>;

    function pluginAjax(action, plugin, btn, originalLabel) {
        var endpoint = action === 'install' ? 'installPlugin' : 'uninstallPlugin';
        var token    = action === 'install' ? CSRF_TOKEN : CSRF_TOKEN_UNIN;
        btn.disabled = true;
        btn.textContent = '...';

        var fd = new FormData();
        fd.append(CSRF_FIELD, token);
        fd.append('plugin', plugin);

        fetch(LINK + 'MysqlServer/' + endpoint + '/' + SERVER_ID + '/ajax:true/', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.error) {
                    alert((action === 'install' ? 'Install' : 'Uninstall') + ' failed: ' + d.error);
                    btn.disabled = false;
                    btn.textContent = originalLabel;
                } else {
                    alert('Plugin "' + plugin + '" ' + (action === 'install' ? ('installed (Support=' + (d.support || 'unknown') + ')') : 'uninstalled') + '. Reloading…');
                    window.location.reload();
                }
            })
            .catch(function (e) {
                alert('Network error: ' + e.message);
                btn.disabled = false;
                btn.textContent = originalLabel;
            });
    }

    document.querySelectorAll('.pl-btn-install').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var plugin = btn.getAttribute('data-plugin');
            var soname = btn.getAttribute('data-soname');
            var pkg    = btn.getAttribute('data-needs-package') || '';
            var msg = pkg
                ? 'INSTALL SONAME \'' + soname + '\' for "' + plugin + '"?\n\nThis plugin normally requires `apt install ' + pkg + '` on the host first. The query will fail with "can\'t open shared library" if the package is missing.'
                : 'Install plugin "' + plugin + '" via INSTALL SONAME \'' + soname + '\' on this server?';
            if (!confirm(msg)) return;
            pluginAjax('install', plugin, btn, btn.textContent);
        });
    });

    document.querySelectorAll('.pl-btn-uninstall').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var plugin = btn.getAttribute('data-plugin');
            var library = btn.getAttribute('data-library') || btn.getAttribute('data-soname') || '';
            if (!confirm('UNINSTALL plugin "' + plugin + '" (library=' + library + ') on this server?\n\nThis disables the plugin live; tables that use the engine will become inaccessible until it is re-installed.')) return;
            pluginAjax('uninstall', plugin, btn, btn.textContent);
        });
    });
})();
</script>
<?php endif; ?>
