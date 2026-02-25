<?php

use \App\Library\Display;
use \Glial\Synapse\FactoryController;

FactoryController::addNode("MysqlServer", "menu", $param);



//debug($data);
/** @var array $data */
$id = (int)$data['id_mysql_server'];
?>

<style>
/*
 * Stabilise l'affichage en 3 colonnes sur desktop,
 * avec une petite marge de sécurité pour éviter les retours à la ligne
 * dus aux arrondis ou aux contenus un peu longs.
 */
@media (min-width: 992px) {
    .grid-row-third {
        display: flex;
        flex-wrap: wrap;
        margin-left: -6px;
        margin-right: -6px;
    }

    .grid-row-third > .grid-item {
        float: none;
        width: calc(33.3333% - 1px);
        padding-left: 6px;
        padding-right: 6px;
        margin-bottom: 10px;
    }
}

.grid-item .table {
    table-layout: fixed;
}

.grid-item .table td {
    word-break: break-word;
}

.usage-meter-wrap {
    text-align: right;
}

.usage-meter-wrap--left {
    text-align: left;
}

.usage-meter-text {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    justify-content: flex-end;
    max-width: 100%;
}

.usage-meter-text--left {
    justify-content: flex-start;
}

.usage-meter-icon {
    width: 14px;
    height: 14px;
    flex: 0 0 auto;
}

.usage-meter-progress {
    margin-top: 4px;
    width: 100%;
    height: 4px;
    background: #e8edf3;
    border-radius: 4px;
    overflow: hidden;
}

.usage-meter-progress-value {
    height: 100%;
    background: #5cb85c;
}

.cmd-actions {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.cmd-link {
    font-family: monospace;
    font-size: 12px;
}
</style>


  <?php
  // Panels simples pour chaque catégorie
  $groups = [
      "Résumé"        => $data['summary'],
      "OS / Système"  => $data['os'],
      "InnoDB"        => $data['innodb'],
      "Aria"          => $data['aria'] ?? [],
      "Connexions"    => $data['connections'],
      "Binlog"        => $data['binlog'],
      "Réplication / WSREP" => $data['wsrep'],
      "SSL"           => $data['ssl'],
      
  ];

  if (!empty($data['rocksdb']) && is_array($data['rocksdb'])) {
      $groups['RocksDB'] = $data['rocksdb'];
  }

  if (!empty($data['myisam']) && is_array($data['myisam'])) {
      $groups['MyISAM'] = $data['myisam'];
  }

  if (!empty($data['columnstore']) && is_array($data['columnstore'])) {
      $groups['ColumnStore'] = $data['columnstore'];
  }

  if (!empty($data['spider']) && is_array($data['spider'])) {
      $groups['Spider'] = $data['spider'];
  }
  ?>

<div style="padding-right:20px">
    <div class="grid row grid-row-third" style="margin-top:20px;">
        <?php foreach ($groups as $title => $items): ?>
        <div class="col-md-4 grid-item" style="margin-bottom:0px;">
            <div class="panel panel-default">
            <div class="panel-heading"><strong><?= htmlspecialchars($title) ?></strong></div>
            <div class="panel-body" style="">
                <table class="table table-condensed table-striped" style="margin:0">
                <tbody>
                <?php foreach ($items as $k => $v): ?>
                    <tr>
                    <td style="width:50%"><?= $k ?></td>
                    <td style="width:50%; text-align:right">
                        <?php if (is_array($v) && ($v['type'] ?? '') === 'usage_meter'): ?>
                            <?php $percent = max(0, min(100, (float)($v['percent'] ?? 0))); ?>
                            <?php $meterColor = $v['color'] ?? '#5cb85c'; ?>
                            <?php $metric = $v['metric'] ?? 'ram'; ?>
                            <div class="usage-meter-wrap">
                                <div class="usage-meter-text">

                                    <span><?= htmlspecialchars((string)($v['text'] ?? 'RAM usage : n/a')) ?></span>
                                </div>
                                <div class="usage-meter-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $percent ?>">
                                    <div class="usage-meter-progress-value" style="width: <?= $percent ?>%; background: <?= htmlspecialchars($meterColor) ?>;"></div>
                                </div>
                            </div>
                        <?php elseif (is_array($v) && ($v['type'] ?? '') === 'copy_clipboard'): ?>
                            <span
                                data-clipboard-text="<?= htmlspecialchars((string)($v['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                onclick="copyMysqlCmd(<?= htmlspecialchars(json_encode((string)($v['text'] ?? '')), ENT_QUOTES, 'UTF-8') ?>); return false;"
                                class="copy-button clipboard badge badge-info"
                                style="font-variant: small-caps; font-size: 14px; vertical-align: middle; background-color: #4384c7; cursor:pointer;"
                                title="Copy">
                                <?= $v['icon'] ?? '<i class="fa fa-files-o" aria-hidden="true"></i>' ?>
                            </span>
                        <?php elseif ($k === 'Cmd' && is_string($v)): ?>
                            <div class="cmd-actions">

                                <button type="button" class="btn btn-xs btn-success" onclick="copyMysqlCmd(<?= htmlspecialchars(json_encode($v), ENT_QUOTES, 'UTF-8') ?>)">Copy</button>
                            </div>
                        <?php else: ?>
                            <?= (string)$v ?>
                        <?php endif; ?>
                    </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                </table>
            </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

  <!-- DISQUES -->
  <?php if (!empty($data['disks']) && is_array($data['disks'])): ?>
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-info">
        <div class="panel-heading"><strong>Disques</strong></div>
        <div class="panel-body" style="padding:0; overflow:auto">
          <table class="table table-condensed table-bordered table-striped" style="margin:0">
            <thead>
              <tr>
                <th>Mount</th>
                <th>Filesystem</th>
                <th>Taille</th>
                <th>Utilisé</th>
                <th>Libre</th>
                <th>%</th>
                <th>Point de montage</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($data['disks'] as $mount => $info): ?>
              <tr>
                <td><?= htmlspecialchars($mount) ?></td>
                <td><?= htmlspecialchars($info[0] ?? '') ?></td>
                <td><?= htmlspecialchars($info[1] ?? '') ?></td>
                <td><?= htmlspecialchars($info[2] ?? '') ?></td>
                <td><?= htmlspecialchars($info[3] ?? '') ?></td>
                <td><?= htmlspecialchars($info[4] ?? '') ?></td>
                <td><?= htmlspecialchars($info[5] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php endif; ?>


  <!-- PROCESSLIST -->
  <?php if (!empty($data['processlist']) && is_array($data['processlist'])): ?>
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-warning">
        <div class="panel-heading"><strong>Processlist</strong></div>
        <div class="panel-body" style="overflow:auto; max-height:600px; padding:0">
          <table class="table table-condensed table-bordered table-striped">
            <thead>
            <tr>
              <?php $first = reset($data['processlist']); ?>
              <?php if ($first && is_array($first)): ?>
                <?php foreach (array_keys($first) as $col): ?>
                  <th><?= htmlspecialchars($col) ?></th>
                <?php endforeach; ?>
              <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data['processlist'] as $row): ?>
              <tr>
                <?php foreach ($row as $val): ?>
                  <td>
                    <?php
                      $text = (string)$val;
                      if (strlen($text) > 200) {
                          $text = substr($text, 0, 200) . "…";
                      }
                      echo nl2br(htmlspecialchars($text));
                    ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>



<?php

FactoryController::addNode("MysqlServer", "lastRefresh", $param);


?>

<script>
function copyMysqlCmd(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text);
        return;
    }

    var temp = document.createElement('textarea');
    temp.value = text;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
}
</script>
