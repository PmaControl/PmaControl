<?php
/**
 * /<lang>/Plugin/[index|installed|toupdate]/
 *
 * Catalogue of plugins. Each entry shows the latest known version, lists
 * older known versions, and exposes install/update/remove actions tied to
 * the row currently in `plugin_main`.
 *
 * Data shape (built by Plugin::index):
 *   $data[<plugin nom>][<version>] = plugin_main row
 *
 * The legacy view re-derived a flat $newArray with computed Version /
 * OldVersion / CurrentVersion fields. We keep the same derivation in a
 * small helper closure to preserve the public contract — only the
 * rendering is rewritten to match the server-main design language.
 */

$activeFilter = isset($param[0]) ? (string)$param[0] : 'all';

$plugins = array();
if (!is_null($data)) {
    foreach ($data as $name => $versions) {
        // Order versions with version_compare() so two-digit minors
        // (v1.10.0, v1.11.0) sit after v1.9.0 — not before, as the
        // previous ksort() string sort would have it (#1314).
        uksort($versions, 'version_compare');
        $row = array();
        $oldVersions = array();
        $currentVersion = '';
        foreach ($versions as $version => $vrow) {
            $row = $vrow;
            $oldVersions[] = $version;
            if ((int)($vrow['est_actif'] ?? 0) === 1) {
                $currentVersion = $version;
            }
        }
        $latestVersion = end($oldVersions);
        reset($oldVersions);

        $row['_name'] = (string)$name;
        $row['_latestVersion'] = (string)$latestVersion;
        $row['_oldVersions'] = $oldVersions;
        $row['_currentVersion'] = (string)$currentVersion;
        $row['_installed'] = $currentVersion !== '';
        // version_compare() — string < would say 'v1.9.0' is not less
        // than 'v1.11.0' (because '9' > '1'), hiding the Update button
        // (#1314).
        $row['_updateAvailable'] = $currentVersion !== ''
            && version_compare($currentVersion, $latestVersion, '<');
        $plugins[$name] = $row;
    }
}
?>
<style>
/* ================================================================
   PLUGIN INDEX — Catalog layout aligned with /server/main tokens
   ================================================================ */
:root { --pi-grad: linear-gradient(135deg, #0f172a, #1e3a8a); --pi-border: #e2e8f0;
        --pi-ok: #10b981; --pi-warn: #f59e0b; --pi-crit: #ef4444; --pi-info: #3b82f6;
        --pi-muted: #94a3b8; --pi-surface: #fff; --pi-r: 6px; --pi-text: #1e293b; }

.pi-toolbar { background: var(--pi-surface); border: 1px solid var(--pi-border); border-radius: var(--pi-r);
              padding: 8px 14px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
              box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.pi-toolbar .btn { font-size: 11px; padding: 4px 10px; }
.pi-toolbar .btn i { margin-right: 4px; }
.pi-toolbar .pi-count { font-size: 10px; color: var(--pi-muted);
                        text-transform: uppercase; letter-spacing: .4px; font-weight: 600; }
.pi-search-wrap { position: relative; margin-left: auto; }
.pi-search-wrap .fa-search { position: absolute; left: 9px; top: 50%; transform: translateY(-50%);
                             color: var(--pi-muted); font-size: 11px; pointer-events: none; }
.pi-search { border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 10px 4px 26px;
             font-size: 11px; width: 200px; transition: width .2s, border-color .15s;
             color: var(--pi-text); background: #fff; }
.pi-search:focus { outline: none; border-color: #3b82f6; width: 260px; }
.pi-search::placeholder { color: var(--pi-muted); }

.pi-hidden { display: none !important; }
.pi-no-match { padding: 18px 22px; color: var(--pi-muted); font-size: 12px; font-style: italic; text-align: center; }

.pi-card { background: var(--pi-surface); border: 1px solid var(--pi-border); border-radius: var(--pi-r);
           overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 14px; }
.pi-head { background: var(--pi-grad); color: #fff; padding: 10px 16px;
           display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.pi-head-logo { width: 36px; height: 36px; flex: 0 0 auto; background: #fff; border-radius: 6px;
                display: flex; align-items: center; justify-content: center; overflow: hidden; }
.pi-head-logo img { max-width: 32px; max-height: 32px; display: block; }
.pi-head-name { font-size: 14px; font-weight: 700; line-height: 1.3; }
.pi-head-name a { color: #fff; text-decoration: none; }
.pi-head-name a:hover { text-decoration: underline; }
.pi-head-version { font-size: 11px; color: #cbd5e1; font-weight: 400; }
.pi-head-spacer { flex: 1; }
.pi-head .pi-pill { display: inline-flex; align-items: center; gap: 4px; padding: 2px 9px; border-radius: 10px;
                    font-size: 11px; font-weight: 700; }
.pi-pill.installed { background: rgba(16,185,129,.25); color: #a7f3d0; }
.pi-pill.available { background: rgba(59,130,246,.2); color: #93c5fd; }
.pi-pill.update    { background: rgba(245,158,11,.25); color: #fde68a; }

.pi-body { display: grid; grid-template-columns: 130px 1fr; gap: 0; padding: 0; }
.pi-body-logo { padding: 16px; display: flex; align-items: center; justify-content: center;
                background: #f8fafc; border-right: 1px solid var(--pi-border); }
.pi-body-logo img { max-width: 96px; max-height: 96px; display: block; }
.pi-body-main { padding: 14px 18px; min-width: 0; }
.pi-desc { color: var(--pi-text); font-size: 12.5px; line-height: 1.5; margin-bottom: 12px; }

.pi-meta { display: flex; flex-wrap: wrap; gap: 14px 22px; margin-bottom: 10px; }
.pi-meta-item { display: flex; flex-direction: column; gap: 1px; }
.pi-meta-lbl { font-size: 9px; text-transform: uppercase; letter-spacing: .4px;
               color: var(--pi-muted); font-weight: 700; }
.pi-meta-val { font-size: 12px; color: var(--pi-text); font-weight: 500; }
.pi-old-version { display: inline-block; font-size: 10px; padding: 1px 5px; margin-right: 4px;
                  border-radius: 3px; background: #f1f5f9; color: var(--pi-muted); font-weight: 600; }
.pi-old-version.current { background: var(--pi-ok); color: #fff; }

.pi-actions { display: flex; flex-wrap: wrap; gap: 6px; padding-top: 10px;
              border-top: 1px dashed var(--pi-border); }
.pi-actions .btn { font-size: 11px; padding: 5px 12px; }
.pi-actions .btn i { margin-right: 4px; }

.pi-empty { text-align: center; padding: 40px 20px; color: var(--pi-muted); }
.pi-empty i { font-size: 32px; opacity: .6; display: block; margin-bottom: 8px; }
.pi-empty-title { font-size: 13px; font-weight: 600; color: var(--pi-text); margin-bottom: 4px; }

@media (max-width: 720px) {
    .pi-body { grid-template-columns: 1fr; }
    .pi-body-logo { border-right: none; border-bottom: 1px solid var(--pi-border); padding: 12px; }
}
</style>

<div class="pi-toolbar">
    <a href="<?= LINK ?>plugin/index" class="btn btn-primary<?= $activeFilter === 'all' ? ' active' : '' ?>">
        <i class="fa fa-th-large"></i><?= __('All') ?>
    </a>
    <a href="<?= LINK ?>plugin/index/installed" class="btn btn-primary<?= $activeFilter === 'installed' ? ' active' : '' ?>">
        <i class="fa fa-check-circle"></i><?= __('Installed') ?>
    </a>
    <a href="<?= LINK ?>plugin/index/toupdate" class="btn btn-primary<?= $activeFilter === 'toupdate' ? ' active' : '' ?>">
        <i class="fa fa-arrow-circle-up"></i><?= __('ToUpdate') ?>
    </a>
    <div class="pi-count" id="pi-count" data-total="<?= count($plugins) ?>">
        <?php
        $count = count($plugins);
        echo $count.' '.($count === 1 ? __('plugin') : __('plugins'));
        ?>
    </div>
    <div class="pi-search-wrap">
        <i class="fa fa-search"></i>
        <input type="text" class="pi-search" id="pi-filter"
               placeholder="<?= htmlspecialchars(__('Filter plugins...'), ENT_QUOTES, 'UTF-8') ?>"
               autocomplete="off">
    </div>
</div>

<?php if (empty($plugins)): ?>
    <div class="pi-card">
        <div class="pi-empty">
            <i class="fa fa-puzzle-piece"></i>
            <div class="pi-empty-title"><?= __('No plugin matches this filter') ?></div>
            <div><?= __('Refresh the catalogue or switch to All to see every available plugin.') ?></div>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($plugins as $name => $p):
        $logo = strtr((string)($p['image'] ?? ''), array('{WWW}' => WWW_ROOT, '{LINK}' => WWW_ROOT));
        $displayName = trim((string)($p['title'] ?? ''));
        if ($displayName === '') $displayName = (string)$name;
        $description = (string)($p['description'] ?? '');
        $author = (string)($p['auteur'] ?? '');
        $licence = (string)($p['type_licence'] ?? '');
        $installedAt = (string)($p['date_installation'] ?? '');
        $latestVersion = (string)$p['_latestVersion'];
        $currentVersion = (string)$p['_currentVersion'];
        $oldVersions = (array)$p['_oldVersions'];
        $sourceUrl = (string)($p['fichier'] ?? '');
        $pluginId = (int)($p['id'] ?? 0);

        if ($p['_updateAvailable']) {
            $pillClass = 'update'; $pillIcon = 'fa-arrow-circle-up'; $pillLabel = __('Update available');
        } elseif ($p['_installed']) {
            $pillClass = 'installed'; $pillIcon = 'fa-check-circle'; $pillLabel = __('Installed');
        } else {
            $pillClass = 'available'; $pillIcon = 'fa-cloud-download'; $pillLabel = __('Available');
        }

        $searchHaystack = strtolower(trim(
            $name.' '.$displayName.' '.$latestVersion.' '.$author.' '.$licence.' '.$pillLabel.' '
            .implode(' ', $oldVersions).' '.strip_tags($description)
        ));
    ?>
    <div class="pi-card" data-search="<?= htmlspecialchars($searchHaystack, ENT_QUOTES, 'UTF-8') ?>">
        <div class="pi-head">
            <?php if ($logo !== ''): ?>
                <div class="pi-head-logo"><img src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') ?>" alt=""></div>
            <?php endif; ?>
            <div>
                <div class="pi-head-name">
                    <?php if ($sourceUrl !== ''): ?>
                        <a href="<?= htmlspecialchars($sourceUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>
                        <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </div>
                <div class="pi-head-version"><?= htmlspecialchars($latestVersion, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="pi-head-spacer"></div>
            <span class="pi-pill <?= $pillClass ?>"><i class="fa <?= $pillIcon ?>"></i> <?= htmlspecialchars($pillLabel, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="pi-body">
            <div class="pi-body-logo">
                <?php if ($logo !== ''): ?>
                    <img src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <i class="fa fa-puzzle-piece" style="font-size:48px;color:var(--pi-muted);"></i>
                <?php endif; ?>
            </div>
            <div class="pi-body-main">
                <?php if ($description !== ''): ?>
                    <div class="pi-desc"><?= $description /* allows authored HTML, as before */ ?></div>
                <?php endif; ?>

                <div class="pi-meta">
                    <?php if ($author !== ''): ?>
                    <div class="pi-meta-item">
                        <span class="pi-meta-lbl"><?= __('Author') ?></span>
                        <span class="pi-meta-val"><?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($licence !== ''): ?>
                    <div class="pi-meta-item">
                        <span class="pi-meta-lbl"><?= __('Licence') ?></span>
                        <span class="pi-meta-val"><?= htmlspecialchars($licence, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($installedAt !== ''): ?>
                    <div class="pi-meta-item">
                        <span class="pi-meta-lbl"><?= __('Released') ?></span>
                        <span class="pi-meta-val"><?= htmlspecialchars($installedAt, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($oldVersions)): ?>
                    <div class="pi-meta-item">
                        <span class="pi-meta-lbl"><?= __('Versions') ?></span>
                        <span class="pi-meta-val">
                            <?php foreach ($oldVersions as $v):
                                $cls = ($v === $currentVersion) ? 'pi-old-version current' : 'pi-old-version';
                            ?>
                            <span class="<?= $cls ?>"><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="pi-actions">
                    <?php if (!$p['_installed']): ?>
                        <a href="<?= LINK ?>plugin/install/<?= $pluginId ?>" class="btn btn-success">
                            <i class="fa fa-cloud-download"></i> <?= __('Install') ?>
                        </a>
                    <?php else: ?>
                        <?php if ($p['_updateAvailable']): ?>
                            <a href="<?= LINK ?>plugin/update/<?= $pluginId ?>" class="btn btn-warning">
                                <i class="fa fa-wrench"></i> <?= __('Update') ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?= LINK ?>plugin/remove/<?= $pluginId ?>" class="btn btn-danger"
                           onclick="return confirm('<?= htmlspecialchars(__('Remove this plugin?'), ENT_QUOTES, 'UTF-8') ?>');">
                            <i class="fa fa-trash"></i> <?= __('Remove') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="pi-card pi-no-match pi-hidden" id="pi-no-match">
        <i class="fa fa-search" style="margin-right:6px;"></i>
        <?= htmlspecialchars(__('No plugin matches your search.'), ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<script>
(function() {
    var input = document.getElementById("pi-filter");
    if (!input) return;
    var counter = document.getElementById("pi-count");
    var noMatch = document.getElementById("pi-no-match");
    var cards = document.querySelectorAll(".pi-card[data-search]");
    var total = counter ? parseInt(counter.getAttribute("data-total"), 10) || cards.length : cards.length;
    var labelOne = <?= json_encode(__('plugin')) ?>;
    var labelMany = <?= json_encode(__('plugins')) ?>;
    var timer = null;

    function apply() {
        var q = input.value.toLowerCase().trim();
        var visible = 0;
        cards.forEach(function(card) {
            var hay = card.getAttribute("data-search") || "";
            var match = q === "" || hay.indexOf(q) !== -1;
            card.classList.toggle("pi-hidden", !match);
            if (match) visible++;
        });
        if (counter) counter.textContent = visible + " " + (visible === 1 ? labelOne : labelMany)
                                         + (q !== "" && visible !== total ? " / " + total : "");
        if (noMatch) noMatch.classList.toggle("pi-hidden", visible !== 0 || q === "");
    }

    input.addEventListener("keyup", function() {
        clearTimeout(timer);
        timer = setTimeout(apply, 120);
    });
    input.addEventListener("search", apply);

    // Optional: ?q=foo prefills the filter.
    var params = new URLSearchParams(window.location.search);
    if (params.has("q")) {
        input.value = params.get("q");
        apply();
    }
})();
</script>
