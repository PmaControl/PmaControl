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

<!-- ── Plugin catalog matrix ── -->
<div class="pl-card">
    <div class="pl-card-head" style="background:linear-gradient(135deg,#4c1d95,#7e22ce)">
        <?= __('Plugin catalog (MariaDB / MySQL matrix)') ?>
        <small style="opacity:0.85;font-weight:400;margin-left:8px">— <?= __('hot-installable rows have an Install button; the others require an OS package + service restart') ?></small>
    </div>
    <div class="pl-card-body">
        <table class="pl-table">
            <tr>
                <th><?= __('Name') ?></th>
                <th><?= __('Kind') ?></th>
                <th><?= __('Description') ?></th>
                <th>MariaDB</th>
                <th>MySQL</th>
                <th><?= __('Status on this server') ?></th>
                <th><?= __('Action') ?></th>
            </tr>
            <?php foreach ($catalog as $entry):
                $name = strtoupper($entry['name']);
                $kind = $entry['kind'];
                $isEngine = $kind === PluginCatalog::KIND_ENGINE;
                $installed = $isEngine
                    ? in_array(($enginesIndex[$name]['support'] ?? 'NO'), ['YES','DEFAULT'], true)
                    : (isset($pluginsIndex[$name]) && $pluginsIndex[$name]['status'] === 'ACTIVE');
                $spec = $entry[$family] ?? null;
                $availability = $spec['availability'] ?? PluginCatalog::AVAILABILITY_NA;
                $rowClass = 'pl-row-na';
                if ($installed) $rowClass = 'pl-row-installed';
                elseif ($availability === PluginCatalog::AVAILABILITY_CORE) $rowClass = 'pl-row-missing-hot';
                elseif ($availability === PluginCatalog::AVAILABILITY_PACKAGE) $rowClass = 'pl-row-missing-pkg';

                $renderColumn = function (array $entry, string $col) {
                    $spec = $entry[$col] ?? null;
                    if (!$spec || ($spec['availability'] ?? '') === PluginCatalog::AVAILABILITY_NA) {
                        return '<span style="color:#cbd5e1">—</span>'
                            . (!empty($spec['install_hint']) ? '<br><small style="color:#94a3b8">' . htmlspecialchars($spec['install_hint']) . '</small>' : '');
                    }
                    $hot = $spec['availability'] === PluginCatalog::AVAILABILITY_CORE;
                    $tag = $hot
                        ? '<span style="background:#16a34a;color:#fff;padding:1px 6px;border-radius:3px;font-size:10px">hot</span>'
                        : '<span style="background:#ea580c;color:#fff;padding:1px 6px;border-radius:3px;font-size:10px">apt + restart</span>';
                    $body = $tag
                        . ' <span class="pl-soname">' . htmlspecialchars($spec['soname']) . '</span>';
                    if (!empty($spec['package'])) {
                        $body .= '<br><span class="pl-pkg">' . htmlspecialchars($spec['package']) . '</span>';
                    }
                    if (!empty($spec['install_hint'])) {
                        $body .= '<br><small style="color:#94a3b8">' . htmlspecialchars($spec['install_hint']) . '</small>';
                    }
                    return $body;
                };
            ?>
                <tr class="<?= $rowClass ?>">
                    <td><strong><?= htmlspecialchars($name) ?></strong></td>
                    <td><small style="color:#64748b"><?= htmlspecialchars($kind) ?></small></td>
                    <td><small><?= htmlspecialchars($entry['description']) ?></small></td>
                    <td><?= $renderColumn($entry, 'mariadb') ?></td>
                    <td><?= $renderColumn($entry, 'mysql') ?></td>
                    <td>
                        <?php if ($installed): ?>
                            <span style="background:#16a34a;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px"><?= __('installed') ?></span>
                        <?php elseif ($availability === PluginCatalog::AVAILABILITY_CORE): ?>
                            <span style="background:#facc15;color:#1f2937;padding:2px 8px;border-radius:10px;font-size:11px"><?= __('not loaded — hot install possible') ?></span>
                        <?php elseif ($availability === PluginCatalog::AVAILABILITY_PACKAGE): ?>
                            <span style="background:#ea580c;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px"><?= __('package + restart required') ?></span>
                        <?php else: ?>
                            <span style="background:#cbd5e1;color:#1f2937;padding:2px 8px;border-radius:10px;font-size:11px"><?= __('n/a') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($installed): ?>
                            <small style="color:#94a3b8">—</small>
                        <?php elseif ($availability === PluginCatalog::AVAILABILITY_CORE && $isAdmin): ?>
                            <button class="pl-btn" data-plugin="<?= htmlspecialchars($name) ?>" data-soname="<?= htmlspecialchars($spec['soname']) ?>"><?= __('Install') ?></button>
                        <?php elseif ($availability === PluginCatalog::AVAILABILITY_CORE && !$isAdmin): ?>
                            <small style="color:#94a3b8"><?= __('SuperAdmin only') ?></small>
                        <?php elseif ($availability === PluginCatalog::AVAILABILITY_PACKAGE && $isAdmin): ?>
                            <small style="color:#475569">SSH <code><?= htmlspecialchars('apt install ' . ($spec['package'] ?? '')) ?></code></small>
                        <?php else: ?>
                            <small style="color:#94a3b8">—</small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p style="font-size:11px;color:#64748b;margin-top:10px;border-top:1px dashed #e2e8f0;padding-top:8px">
            <?= __('Legend:') ?>
            <span style="background:#16a34a;color:#fff;padding:1px 6px;border-radius:3px;font-size:10px">hot</span> <?= __('= INSTALL SONAME suffices, no restart.') ?>
            <span style="background:#ea580c;color:#fff;padding:1px 6px;border-radius:3px;font-size:10px">apt + restart</span> <?= __('= install the package on the host first, then restart the service before INSTALL SONAME.') ?>
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

    document.querySelectorAll('.pl-btn[data-plugin]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var plugin = btn.getAttribute('data-plugin');
            var soname = btn.getAttribute('data-soname');
            if (!confirm('Install plugin "' + plugin + '" via INSTALL SONAME \'' + soname + '\' on this server?')) return;
            btn.disabled = true;
            btn.textContent = '...';

            var fd = new FormData();
            fd.append(CSRF_FIELD, CSRF_TOKEN);
            fd.append('plugin', plugin);

            fetch(LINK + 'MysqlServer/installPlugin/' + SERVER_ID + '/ajax:true/', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.error) {
                        alert('Install failed: ' + d.error);
                        btn.disabled = false;
                        btn.textContent = 'Install';
                    } else {
                        alert('Plugin "' + plugin + '" installed (Support=' + (d.support || 'unknown') + '). Reloading…');
                        window.location.reload();
                    }
                })
                .catch(function (e) {
                    alert('Network error: ' + e.message);
                    btn.disabled = false;
                    btn.textContent = 'Install';
                });
        });
    });
})();
</script>
<?php endif; ?>
