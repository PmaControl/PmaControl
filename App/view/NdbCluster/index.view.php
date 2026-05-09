<?php
// Epic #799 / lot 6 — single-cluster NDB dashboard.

$cluster = $data['cluster'] ?? null;
$nodes   = $data['nodes']   ?? ['mgmd' => [], 'data' => [], 'sql' => []];
$totals  = $data['totals']  ?? ['mgmd' => 0, 'mgmd_up' => 0, 'data' => 0, 'data_up' => 0, 'sql' => 0, 'sql_up' => 0];
$sqlServers = $data['sql_servers'] ?? [];
$errors  = $data['errors']  ?? [];

$dotClass = static function (string $status): string {
    $s = strtoupper($status);
    if ($s === 'STARTED' || $s === 'CONNECTED') return 'ok';
    if ($s === 'STARTING') return 'warn';
    if ($s === 'NOT_STARTED' || $s === 'DISCONNECTED') return 'crit';
    return 'muted';
};

$fmtUptime = static function ($sec): string {
    if ($sec === null || $sec === '') return '—';
    $sec = (int) $sec;
    if ($sec < 60) return $sec . 's';
    if ($sec < 3600) return (int)floor($sec / 60) . 'm';
    if ($sec < 86400) return (int)floor($sec / 3600) . 'h';
    return (int)floor($sec / 86400) . 'd';
};

$fmtMem = static function ($used, $total): string {
    if ($used === null || $total === null) return '—';
    $u = (int)$used; $t = (int)$total;
    if ($t === 0) return $u . ' MB';
    $pct = (int) round($u * 100 / $t);
    return $u . ' / ' . $t . ' MB (' . $pct . '%)';
};
?>
<style>
.ndb { --border: #e2e8f0; --surface: #fff; --muted: #94a3b8; --txt: #1e293b;
       --ok: #2e7d32; --warn: #f9a825; --crit: #c62828; --info: #1565c0;
       --mgmd: #e65100; --data: #2e7d32; --sql: #1565c0;
       --radius: 8px; }

.ndb-head { background: linear-gradient(135deg,#0f172a,#1e3a8a); color:#fff;
            padding: 14px 18px; border-radius: var(--radius) var(--radius) 0 0;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px; }
.ndb-head h2 { margin: 0; font-size: 16px; font-weight: 700; }
.ndb-head .meta { font-size: 12px; opacity: 0.85; }

.ndb-summary { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr));
               gap: 12px; padding: 14px 18px; background: #f8fafc;
               border-bottom: 1px solid var(--border); }
.ndb-tile { background: #fff; border: 1px solid var(--border); border-radius: 6px;
            padding: 10px 12px; }
.ndb-tile.mgmd { border-left: 4px solid var(--mgmd); }
.ndb-tile.data { border-left: 4px solid var(--data); }
.ndb-tile.sql  { border-left: 4px solid var(--sql); }
.ndb-tile-title { font-size: 11px; text-transform: uppercase; letter-spacing: .5px;
                  color: var(--muted); font-weight: 700; }
.ndb-tile-value { font-size: 22px; font-weight: 700; color: var(--txt); margin-top: 4px; }
.ndb-tile-value small { font-size: 13px; color: var(--muted); font-weight: 500; }

.ndb-band { background: #fff; border: 1px solid var(--border); border-top: 0; }
.ndb-band:last-child { border-radius: 0 0 var(--radius) var(--radius); }
.ndb-band-head { padding: 10px 16px; font-weight: 700; font-size: 13px;
                 text-transform: uppercase; letter-spacing: .5px;
                 border-bottom: 1px solid var(--border); }
.ndb-band-head.mgmd { background: #fff3e0; color: #bf360c; }
.ndb-band-head.data { background: #e8f5e9; color: #1b5e20; }
.ndb-band-head.sql  { background: #e3f2fd; color: #0d47a1; }

.ndb-nodes { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); }
.ndb-node { padding: 12px 16px; border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border); }
.ndb-node:hover { background: #f8fafc; }
.ndb-node-head { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.ndb-node-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.ndb-node-dot.ok { background: var(--ok); box-shadow: 0 0 6px rgba(46,125,50,.4); }
.ndb-node-dot.warn { background: var(--warn); }
.ndb-node-dot.crit { background: var(--crit); box-shadow: 0 0 6px rgba(198,40,40,.4); }
.ndb-node-dot.muted { background: var(--muted); }
.ndb-node-name { font-size: 14px; font-weight: 700; color: var(--txt); }
.ndb-node-name a { color: inherit; text-decoration: none; }
.ndb-node-name a:hover { color: var(--info); }
.ndb-node-status { font-size: 10px; font-weight: 700; text-transform: uppercase;
                   letter-spacing: .5px; padding: 2px 8px; border-radius: 8px;
                   margin-left: auto; }
.ndb-node-status.ok { background: rgba(46,125,50,.15); color: var(--ok); }
.ndb-node-status.warn { background: rgba(249,168,37,.2); color: #b8860b; }
.ndb-node-status.crit { background: rgba(198,40,40,.15); color: var(--crit); }
.ndb-node-status.muted { background: rgba(148,163,184,.2); color: var(--muted); }

.ndb-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 12px; font-size: 11px; }
.ndb-meta dt { color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
.ndb-meta dd { color: #334155; margin: 0; font-family: monospace; word-break: break-all; }

.ndb-empty { padding: 32px 16px; text-align: center; color: var(--muted); font-style: italic; }
.ndb-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
             padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; }
</style>

<div class="ndb">

<?php foreach ($errors as $e): ?>
    <div class="ndb-error"><i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars((string)$e) ?></div>
<?php endforeach; ?>

<?php if ($cluster === null): ?>
    <p><?= __('No NDB cluster to display.') ?></p>
<?php else: ?>

    <div class="ndb-head">
        <div>
            <h2><i class="fa fa-cubes"></i> <?= htmlspecialchars((string)$cluster['display_name']) ?></h2>
            <div class="meta">
                <?php if (!empty($cluster['ndb_version'])): ?>
                    version <?= htmlspecialchars((string)$cluster['ndb_version']) ?>
                <?php endif; ?>
                <?php if ($cluster['no_of_replicas'] !== null): ?>
                    &middot; <?= (int)$cluster['no_of_replicas'] ?> replicas
                <?php endif; ?>
                &middot; mgmd <?= htmlspecialchars((string)$cluster['mgmd_host']) ?>:<?= (int)$cluster['mgmd_port'] ?>
            </div>
        </div>
        <div style="font-size:12px;opacity:.85">
            <?= __('Last update') ?>: <?= htmlspecialchars((string)$cluster['date_updated']) ?>
        </div>
    </div>

    <div class="ndb-summary">
        <div class="ndb-tile mgmd">
            <div class="ndb-tile-title">Management nodes</div>
            <div class="ndb-tile-value"><?= (int)$totals['mgmd_up'] ?><small> / <?= (int)$totals['mgmd'] ?></small></div>
        </div>
        <div class="ndb-tile data">
            <div class="ndb-tile-title">Data nodes</div>
            <div class="ndb-tile-value"><?= (int)$totals['data_up'] ?><small> / <?= (int)$totals['data'] ?></small></div>
        </div>
        <div class="ndb-tile sql">
            <div class="ndb-tile-title">SQL / API nodes</div>
            <div class="ndb-tile-value"><?= (int)$totals['sql_up'] ?><small> / <?= (int)$totals['sql'] ?></small></div>
        </div>
    </div>

    <?php foreach (['mgmd' => 'Management', 'data' => 'Data nodes', 'sql' => 'SQL / API'] as $role => $label): ?>
        <div class="ndb-band">
            <div class="ndb-band-head <?= $role ?>"><?= $label ?> (<?= count($nodes[$role]) ?>)</div>
            <?php if (empty($nodes[$role])): ?>
                <div class="ndb-empty"><?= __('No node in this band.') ?></div>
            <?php else: ?>
                <div class="ndb-nodes">
                <?php foreach ($nodes[$role] as $node):
                    $cls = $dotClass((string)$node['status']);
                    $statusUp = strtoupper((string)$node['status']);
                    $linkedServerId = null;
                    if ($role === 'sql' && !empty($sqlServers)) {
                        // Best-effort: pick the mysql_server matching this node's IP.
                        foreach ($sqlServers as $sid => $row) {
                            if ((string)$row['ip'] === (string)$node['ip']) {
                                $linkedServerId = $sid;
                                break;
                            }
                        }
                        if ($linkedServerId === null) {
                            $linkedServerId = (int) array_key_first($sqlServers);
                        }
                    }
                ?>
                    <div class="ndb-node">
                        <div class="ndb-node-head">
                            <span class="ndb-node-dot <?= $cls ?>"></span>
                            <span class="ndb-node-name">
                                <?php if ($linkedServerId): ?>
                                    <a href="<?= LINK ?>MysqlServer/main/<?= (int)$linkedServerId ?>/pmacontrol">
                                        <?= htmlspecialchars($node['hostname'] ?: $node['ip']) ?>
                                    </a>
                                <?php else: ?>
                                    <?= htmlspecialchars($node['hostname'] ?: $node['ip']) ?>
                                <?php endif; ?>
                            </span>
                            <span class="ndb-node-status <?= $cls ?>"><?= htmlspecialchars($statusUp) ?></span>
                        </div>
                        <dl class="ndb-meta">
                            <dt>Node id</dt><dd><?= (int)$node['ndb_node_id'] ?><?= !empty($node['is_primary']) ? ' *' : '' ?></dd>
                            <dt>Address</dt><dd><?= htmlspecialchars((string)$node['ip']) ?><?= $node['port'] ? ':'.(int)$node['port'] : '' ?></dd>
                            <?php if ($node['node_group'] !== null): ?>
                                <dt>Node group</dt><dd><?= (int)$node['node_group'] ?></dd>
                            <?php endif; ?>
                            <?php if (!empty($node['ndb_version'])): ?>
                                <dt>Version</dt><dd><?= htmlspecialchars((string)$node['ndb_version']) ?></dd>
                            <?php endif; ?>
                            <dt>Uptime</dt><dd><?= htmlspecialchars($fmtUptime($node['uptime_sec'])) ?></dd>
                            <?php if ($role === 'data'): ?>
                                <dt>Memory</dt><dd><?= htmlspecialchars($fmtMem($node['memory_used_mb'], $node['memory_total_mb'])) ?></dd>
                                <?php if ($node['data_memory_used_mb'] !== null): ?>
                                    <dt>Data mem</dt><dd><?= (int)$node['data_memory_used_mb'] ?> MB</dd>
                                <?php endif; ?>
                                <?php if ($node['index_memory_used_mb'] !== null): ?>
                                    <dt>Index mem</dt><dd><?= (int)$node['index_memory_used_mb'] ?> MB</dd>
                                <?php endif; ?>
                            <?php endif; ?>
                            <dt>Last seen</dt><dd><?= htmlspecialchars((string)$node['date_seen']) ?></dd>
                        </dl>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

</div>
