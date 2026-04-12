<?php
use App\Library\Display;
?>
<style>
.gr { --border: #e2e8f0; --surface: #fff; --head: linear-gradient(135deg,#0f172a,#1e3a8a);
      --ok: #10b981; --warn: #f59e0b; --crit: #ef4444; --muted: #94a3b8; --txt: #1e293b; --radius: 8px; }

.gr-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
           overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 20px; }
.gr-card-head { background: var(--head); color: #fff; padding: 12px 16px; font-size: 14px; font-weight: 600;
                display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.gr-card-head i { margin-right: 8px; opacity: .7; }
.gr-badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
.gr-badge-ok { background: rgba(16,185,129,.2); color: #059669; }
.gr-badge-crit { background: rgba(239,68,68,.2); color: #dc2626; }
.gr-badge-warn { background: rgba(245,158,11,.2); color: #92400e; }
.gr-badge-info { background: rgba(59,130,246,.2); color: #1e40af; }

/* Node grid */
.gr-nodes { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 0;
            border-top: 1px solid var(--border); }
.gr-node { border-right: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 14px 16px;
           position: relative; }
.gr-node:hover { background: #f8fafc; }
.gr-node-head { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.gr-node-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.gr-node-dot.ok { background: var(--ok); box-shadow: 0 0 6px rgba(16,185,129,.4); }
.gr-node-dot.crit { background: var(--crit); box-shadow: 0 0 6px rgba(239,68,68,.4); }
.gr-node-dot.warn { background: var(--warn); }
.gr-node-name { font-size: 14px; font-weight: 700; color: var(--txt); }
.gr-node-name a { color: inherit; text-decoration: none; }
.gr-node-name a:hover { color: #1e3a8a; }
.gr-node-role { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
                padding: 2px 8px; border-radius: 8px; margin-left: auto; }
.gr-node-role.primary { background: #dbeafe; color: #1e40af; }
.gr-node-role.secondary { background: #f1f5f9; color: #64748b; }

.gr-node-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 12px; font-size: 11px; }
.gr-node-meta dt { color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
.gr-node-meta dd { color: #334155; margin: 0; font-family: monospace; font-size: 11px; word-break: break-all; }

.gr-node-error { background: #fef2f2; border-left: 3px solid var(--crit); padding: 6px 10px; margin-top: 8px;
                 border-radius: 0 4px 4px 0; font-size: 11px; color: #991b1b; }

/* Config section */
.gr-config { padding: 12px 16px; border-top: 1px solid var(--border); background: #fafbfc; }
.gr-config-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
                    color: var(--muted); margin-bottom: 8px; cursor: pointer; }
.gr-config-title:hover { color: #1e3a8a; }
.gr-config-title .fa { margin-right: 4px; font-size: 10px; }
.gr-config-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 4px 16px; font-size: 11px; }
.gr-config-grid dt { color: var(--muted); }
.gr-config-grid dd { color: #334155; margin: 0; font-family: monospace; }

/* Empty state */
.gr-empty { text-align: center; padding: 48px 24px; color: var(--muted); }
.gr-empty i { font-size: 48px; margin-bottom: 12px; display: block; opacity: .4; }
</style>

<div class="gr">

<?php if (empty($data['clusters'])): ?>
    <div class="gr-card">
        <div class="gr-empty">
            <i class="fa fa-database"></i>
            <div style="font-size:16px;font-weight:600;margin-bottom:4px"><?= __('No Group Replication clusters found') ?></div>
            <div style="font-size:13px"><?= __('Servers with group_replication_group_name will appear here automatically') ?></div>
        </div>
    </div>
<?php else: ?>

<?php foreach ($data['clusters'] as $groupName => $nodes):
    $total = count($nodes);
    $online = 0;
    $primaries = 0;
    $mode = 'single-primary';
    $firstNode = reset($nodes);

    foreach ($nodes as $n) {
        if (($n['mysql_available'] ?? '0') === '1') $online++;
        $m = strtolower($n['group_replication_single_primary_mode'] ?? 'on');
        if (in_array($m, ['off', '0', 'false'], true)) $mode = 'multi-primary';
        $sro = strtolower($n['super_read_only'] ?? 'on');
        $ro = strtolower($n['read_only'] ?? 'on');
        if (($sro === 'off' || $sro === '0') && ($ro === 'off' || $ro === '0')) $primaries++;
    }

    $healthClass = ($online === $total) ? 'ok' : (($online > $total / 2) ? 'warn' : 'crit');
    $healthLabel = ($online === $total) ? 'HEALTHY' : (($online > $total / 2) ? 'DEGRADED' : 'CRITICAL');
?>

<div class="gr-card">
    <div class="gr-card-head">
        <span>
            <i class="fa fa-database"></i>
            <?= htmlspecialchars((string)$groupName) ?>
        </span>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <span class="gr-badge gr-badge-info"><?= $mode ?></span>
            <span class="gr-badge gr-badge-<?= $healthClass ?>"><?= $healthLabel ?></span>
            <span style="font-size:12px;font-weight:400;opacity:.8">
                <?= $online ?> / <?= $total ?> <?= __('online') ?>
                <?php if ($mode === 'single-primary'): ?>
                    &middot; <?= $primaries ?> <?= __('primary') ?>
                <?php endif; ?>
            </span>
        </div>
    </div>

    <div class="gr-nodes">
        <?php foreach ($nodes as $idServer => $node):
            $isOnline = ($node['mysql_available'] ?? '0') === '1';
            $sro = strtolower($node['super_read_only'] ?? 'on');
            $ro = strtolower($node['read_only'] ?? 'on');
            $isPrimary = ($sro === 'off' || $sro === '0') && ($ro === 'off' || $ro === '0');
            $role = ($mode === 'multi-primary') ? 'primary' : ($isPrimary ? 'primary' : 'secondary');
            $dotClass = $isOnline ? 'ok' : 'crit';
            $error = $node['mysql_error'] ?? '';
            $ping = $node['mysql_ping'] ?? '';
        ?>
        <div class="gr-node">
            <div class="gr-node-head">
                <span class="gr-node-dot <?= $dotClass ?>"></span>
                <span class="gr-node-name">
                    <a href="<?= LINK ?>MysqlServer/main/<?= (int)$idServer ?>/pmacontrol">
                        <?= htmlspecialchars($node['display_name'] ?: ($node['hostname'] ?? '')) ?>
                    </a>
                </span>
                <span class="gr-node-role <?= $role ?>"><?= strtoupper($role) ?></span>
            </div>

            <dl class="gr-node-meta">
                <dt><?= __('Version') ?></dt>
                <dd><?= htmlspecialchars($node['version'] ?? '') ?></dd>

                <dt><?= __('Host') ?></dt>
                <dd><?= htmlspecialchars($node['ip'] ?? '') ?>:<?= htmlspecialchars($node['server_port'] ?? '') ?></dd>

                <dt><?= __('GR Address') ?></dt>
                <dd><?= htmlspecialchars($node['group_replication_local_address'] ?? '') ?></dd>

                <dt><?= __('UUID') ?></dt>
                <dd><?= htmlspecialchars($node['server_uuid'] ?? '') ?></dd>

                <?php if ($isOnline && $ping !== ''): ?>
                <dt><?= __('Ping') ?></dt>
                <dd><?= round((float)$ping * 1000, 1) ?> ms</dd>
                <?php endif; ?>

                <dt><?= __('Start on boot') ?></dt>
                <dd><?= htmlspecialchars($node['group_replication_start_on_boot'] ?? '') ?></dd>
            </dl>

            <?php if (!$isOnline && $error !== ''): ?>
            <div class="gr-node-error">
                <i class="fa fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Cluster-level config (collapsed by default) -->
    <div class="gr-config">
        <div class="gr-config-title" onclick="var el=this.nextElementSibling;el.style.display=el.style.display==='none'?'grid':'none';this.querySelector('.fa').classList.toggle('fa-chevron-right');this.querySelector('.fa').classList.toggle('fa-chevron-down')">
            <i class="fa fa-chevron-right"></i> <?= __('Cluster configuration') ?>
        </div>
        <dl class="gr-config-grid" style="display:none">
            <?php
            $configKeys = [
                'group_replication_consistency' => 'Consistency',
                'group_replication_autorejoin_tries' => 'Auto-rejoin tries',
                'group_replication_member_expel_timeout' => 'Expel timeout',
                'group_replication_exit_state_action' => 'Exit state action',
                'group_replication_flow_control_mode' => 'Flow control',
                'group_replication_recovery_use_ssl' => 'Recovery SSL',
                'group_replication_ssl_mode' => 'SSL mode',
                'group_replication_communication_stack' => 'Comm stack',
                'group_replication_paxos_single_leader' => 'Paxos single leader',
                'group_replication_ip_allowlist' => 'IP allowlist',
                'group_replication_group_seeds' => 'Seeds',
                'group_replication_view_change_uuid' => 'View change UUID',
            ];
            foreach ($configKeys as $key => $label):
                $val = $firstNode[$key] ?? '';
                if ($val === '') continue;
            ?>
            <dt><?= $label ?></dt>
            <dd><?= htmlspecialchars($val) ?></dd>
            <?php endforeach; ?>
        </dl>
    </div>
</div>

<?php endforeach; ?>
<?php endif; ?>

</div>
