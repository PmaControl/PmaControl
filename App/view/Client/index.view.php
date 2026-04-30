<?php

use App\Library\Security\CsrfRender;

use Glial\Html\Form\Form;

$clientUpdateCsrfAttributes = CsrfRender::attributes($data, 'client_update');
$clientDeleteCsrfField = CsrfRender::field($data, 'client_delete');
$clientDeleteCsrfToken = CsrfRender::token($data, 'client_delete');
$clientMonitoringCsrfField = CsrfRender::field($data, 'client_monitoring');
$clientMonitoringCsrfToken = CsrfRender::token($data, 'client_monitoring');

$totalAll = 0;
$onlineAll = 0;
$offlineAll = 0;
if (!empty($data['client'])) {
    foreach ($data['client'] as $c) {
        $totalAll += (int)($c['total_servers'] ?? 0);
        $onlineAll += (int)($c['online_servers'] ?? 0);
        $offlineAll += (int)($c['offline_servers'] ?? 0);
    }
}
?>

<style>
.cl { --border: #e2e8f0; --surface: #fff; --head: linear-gradient(135deg,#0f172a,#1e3a8a);
      --ok: #10b981; --crit: #ef4444; --muted: #94a3b8; --txt: #1e293b; --radius: 8px; }
.cl-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
           overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.06); margin-bottom: 20px; }
.cl-card-head { background: var(--head); color: #fff; padding: 12px 16px; font-size: 14px; font-weight: 600;
                display: flex; align-items: center; justify-content: space-between; }
.cl-card-head i { margin-right: 8px; opacity: .7; }

/* KPI strip */
.cl-kpis { display: flex; gap: 0; border-bottom: 1px solid var(--border); }
.cl-kpi { flex: 1; text-align: center; padding: 14px 8px; border-right: 1px solid var(--border); }
.cl-kpi:last-child { border-right: none; }
.cl-kpi-val { font-size: 28px; font-weight: 800; color: var(--txt); }
.cl-kpi-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); margin-top: 2px; font-weight: 600; }

/* Client grid */
.cl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; padding: 16px; }
.cl-item { border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; background: var(--surface);
           transition: box-shadow .15s, border-color .15s; }
.cl-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); border-color: #cbd5e1; }
.cl-item-head { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-bottom: 1px solid #f1f5f9; }
.cl-item-icon { width: 36px; height: 36px; border-radius: 8px; background: #f0f4ff; display: flex;
                align-items: center; justify-content: center; font-size: 16px; color: #1e3a8a; flex-shrink: 0; }
.cl-item-name { flex: 1; font-size: 15px; font-weight: 700; color: var(--txt); cursor: pointer; }
.cl-item-name:hover { color: #1e3a8a; }
.cl-item-id { font-size: 11px; color: var(--muted); }

/* Monitoring toggle */
.cl-toggle { position: relative; width: 36px; height: 20px; }
.cl-toggle input { opacity: 0; width: 0; height: 0; }
.cl-toggle-track { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #cbd5e1; border-radius: 10px;
                    cursor: pointer; transition: background .2s; }
.cl-toggle-track::after { content: ""; position: absolute; width: 16px; height: 16px; border-radius: 50%; background: #fff;
                           top: 2px; left: 2px; transition: transform .2s; box-shadow: 0 1px 2px rgba(0,0,0,.15); }
.cl-toggle input:checked + .cl-toggle-track { background: var(--ok); }
.cl-toggle input:checked + .cl-toggle-track::after { transform: translateX(16px); }

/* Server stats bar */
.cl-stats { padding: 10px 14px; display: flex; align-items: center; gap: 10px; }
.cl-bar { flex: 1; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; display: flex; }
.cl-bar-ok { background: var(--ok); }
.cl-bar-crit { background: var(--crit); }
.cl-bar-muted { background: #cbd5e1; }
.cl-stat-nums { display: flex; gap: 8px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.cl-stat-nums .ok { color: #059669; }
.cl-stat-nums .crit { color: #dc2626; }
.cl-stat-nums .off { color: #64748b; }
.cl-stat-nums .total { color: var(--muted); }

/* Footer actions */
.cl-item-foot { display: flex; align-items: center; justify-content: space-between; padding: 8px 14px;
                border-top: 1px solid #f1f5f9; background: #fafbfc; }
.cl-item-foot .cl-date { font-size: 11px; color: var(--muted); }
.cl-item-foot .btn-group .btn { font-size: 11px; }

/* Add button */
.cl-add { border: 2px dashed var(--border); border-radius: var(--radius); display: flex; align-items: center;
          justify-content: center; min-height: 140px; color: var(--muted); font-size: 14px; font-weight: 600;
          text-decoration: none; transition: all .15s; }
.cl-add:hover { border-color: #1e3a8a; color: #1e3a8a; background: #f0f4ff; text-decoration: none; }
.cl-add i { margin-right: 8px; font-size: 20px; }
</style>

<div class="cl">

<!-- KPI strip -->
<div class="cl-card">
    <div class="cl-kpis">
        <div class="cl-kpi">
            <div class="cl-kpi-val"><?= count($data['client'] ?? []) ?></div>
            <div class="cl-kpi-lbl"><?= __('Clients') ?></div>
        </div>
        <div class="cl-kpi">
            <div class="cl-kpi-val"><?= $totalAll ?></div>
            <div class="cl-kpi-lbl"><?= __('Servers') ?></div>
        </div>
        <div class="cl-kpi">
            <div class="cl-kpi-val" style="color:var(--ok)"><?= $onlineAll ?></div>
            <div class="cl-kpi-lbl"><?= __('Online') ?></div>
        </div>
        <div class="cl-kpi">
            <div class="cl-kpi-val" style="color:<?= $offlineAll > 0 ? 'var(--crit)' : 'var(--ok)' ?>"><?= $offlineAll ?></div>
            <div class="cl-kpi-lbl"><?= __('Offline') ?></div>
        </div>
    </div>
</div>

<!-- Client cards grid -->
<div class="cl-grid">
    <?php if (!empty($data['client'])):
        foreach ($data['client'] as $client):
            $online = (int)($client['online_servers'] ?? 0);
            $offline = (int)($client['offline_servers'] ?? 0);
            $nonMon = (int)($client['non_monitored_servers'] ?? 0);
            $total = (int)($client['total_servers'] ?? 0);
            $barTotal = max($total, 1);
    ?>
    <div class="cl-item">
        <div class="cl-item-head">
            <div class="cl-item-icon">
                <?php if (!empty($client['logo'])): ?>
                    <img src="<?= htmlspecialchars($client['logo']) ?>" style="max-width:28px;max-height:28px" onerror="this.style.display='none';this.nextElementSibling.style.display='inline'">
                    <i class="fa fa-building" style="display:none"></i>
                <?php else: ?>
                    <i class="fa fa-building"></i>
                <?php endif; ?>
            </div>
            <div>
                <div class="cl-item-name line-edit"
                     <?= $clientUpdateCsrfAttributes ?>
                     data-name="libelle" data-pk="<?= (int)$client['id'] ?>"
                     data-type="text" data-url="<?= LINK ?>client/update"
                     data-title="<?= __('Edit name') ?>">
                    <?= htmlspecialchars($client['libelle']) ?>
                </div>
                <div class="cl-item-id">#<?= (int)$client['id'] ?></div>
            </div>
            <div style="margin-left:auto">
                <label class="cl-toggle" title="<?= __('Monitoring') ?>">
                    <?php
                    $checked = ($client['is_monitored'] === "1") ? ['checked' => 'checked'] : [];
                    $attrs = array_merge([
                        'data-id' => $client['id'],
                        'data-csrf-field' => $clientMonitoringCsrfField,
                        'data-csrf-token' => $clientMonitoringCsrfToken,
                        'class' => 'is_monitored',
                        'type' => 'checkbox'
                    ], $checked);
                    echo Form::input("check", "mon_".$client['id'], $attrs);
                    ?>
                    <span class="cl-toggle-track"></span>
                </label>
            </div>
        </div>

        <div class="cl-stats">
            <div class="cl-bar">
                <?php if ($total > 0): ?>
                <div class="cl-bar-ok" style="width:<?= round($online/$barTotal*100) ?>%"></div>
                <div class="cl-bar-crit" style="width:<?= round($offline/$barTotal*100) ?>%"></div>
                <div class="cl-bar-muted" style="width:<?= round($nonMon/$barTotal*100) ?>%"></div>
                <?php endif; ?>
            </div>
            <div class="cl-stat-nums">
                <span class="ok" title="<?= __('Online') ?>"><?= $online ?></span>
                <span class="crit" title="<?= __('Offline') ?>"><?= $offline ?></span>
                <span class="off" title="<?= __('Not monitored') ?>"><?= $nonMon ?></span>
                <span class="total">/<?= $total ?></span>
            </div>
        </div>

        <div class="cl-item-foot">
            <span class="cl-date"><?= htmlspecialchars($client['date']) ?></span>
            <div class="btn-group btn-group-xs">
                <a href="<?= LINK ?>server/main/client:<?= urlencode(json_encode([$client['id']])) ?>"
                   class="btn btn-default" title="<?= __('View servers') ?>">
                    <i class="fa fa-server"></i> <?= $total ?>
                </a>
                <form method="post"
                      action="<?= LINK ?>client/delete/<?= (int)$client['id'] ?>"
                      style="display:inline"
                      onsubmit="return confirm('<?= __('Delete this client? All servers will be orphaned.') ?>')">
                    <input type="hidden" name="<?= $clientDeleteCsrfField ?>" value="<?= $clientDeleteCsrfToken ?>" />
                    <button type="submit" class="btn btn-danger">
                        <i class="fa fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>

    <!-- Add new client card -->
    <a href="<?= LINK ?>client/add/" class="cl-add">
        <i class="fa fa-plus-circle"></i> <?= __('Add a client') ?>
    </a>
</div>

</div>
