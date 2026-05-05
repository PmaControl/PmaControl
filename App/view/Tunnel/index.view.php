<!-- src/Template/Tunnel/index.php -->
<?php

use App\Library\Display;

function isoToFlag(string $iso): string {
    // Chaque lettre est convertie en Regional Indicator Symbol
    $flag = '';
    $iso = strtoupper($iso);
    if (strlen($iso) === 2) {
        $flag .= mb_chr(127397 + ord($iso[0]));
        $flag .= mb_chr(127397 + ord($iso[1]));
    }
    return $flag;
}

?>



<style>
table {
    border-collapse: collapse;
    width: 100%;
}
th, td {
    border: 1px solid #ddd;
    padding: 8px;
}
th {
    background-color: #f2f2f2;
}
.jump {
    font-size: 0.9em;
    color: #555;
    margin-left: 15px;
}
.open {
    background-color: #d4edda;
}
.closed {
    background-color: #f8d7da;
}
.status-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    vertical-align: middle;
    margin-right: 6px;
}
.status-up {
    background: #28a745;
}
.status-down {
    background: #dc3545;
}
.duplicate-help {
    margin-top: 6px;
    padding: 6px 8px;
    border-radius: 4px;
    font-size: 12px;
    line-height: 1.4;
}
.duplicate-help--keep {
    background: #dff0d8;
    color: #3c763d;
}
.duplicate-help--purge {
    background: #f2dede;
    color: #a94442;
}
.duplicate-command {
    margin-top: 6px;
}
</style>

<table>
    <thead>
        <tr>
            <th><?=__('ID') ?></th>
            <th><?=__('Local') ?></th>
            <th><?=__('Remote') ?></th>
            <th><?=__('Duplicate') ?></th>
            <th><?=__('Reachable') ?></th>
            <th><?=__('Server') ?></th>
            <th><?=__('Jump Hosts') ?></th>
            <th><?=__('Pid') ?></th>
            <th><?=__('Date Created') ?></th>
        </tr>
    </thead>
    <tbody>

        <?php foreach ($data as $tunnel): ?>

            <tr class="<?= $tunnel['date_end'] ? 'closed' : 'open' ?>">
                <td><?= $tunnel['id'] ?></td>
                <td><?= htmlspecialchars($tunnel['local_host'].":".$tunnel['local_port']) ?></td>
                <td><?= htmlspecialchars($tunnel['remote_host'].":".$tunnel['remote_port']) ;
                
                $key = crc32(trim($tunnel['remote_host']).":".trim($tunnel['remote_port']));
                if ($doublon[$key] > 1) {
                    echo ' 🔁 [Doublons]';
                }
                ?></td>
                <td>
                    <?php if (($tunnel['duplicate_status'] ?? 'unique') === 'keep'): ?>
                        <span class="label label-success"><?= __('KEEP') ?></span>
                        <div><small><?= htmlspecialchars($tunnel['duplicate_reason'] ?? '') ?></small></div>
                        <?php if (!empty($tunnel['duplicate_action'])): ?>
                            <div class="duplicate-help duplicate-help--keep"><?= htmlspecialchars($tunnel['duplicate_action']) ?></div>
                        <?php endif; ?>
                    <?php elseif (($tunnel['duplicate_status'] ?? 'unique') === 'purge'): ?>
                        <span class="label label-danger"><?= __('PURGE') ?></span>
                        <div><small><?= htmlspecialchars($tunnel['duplicate_reason'] ?? '') ?></small></div>
                        <?php if (!empty($tunnel['duplicate_action'])): ?>
                            <div class="duplicate-help duplicate-help--purge"><?= htmlspecialchars($tunnel['duplicate_action']) ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="label label-default"><?= __('UNIQUE') ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($tunnel['is_reachable'])): ?>
                        <span title="<?= __('Online') ?>">
                            <span class="status-dot status-up"></span><?= __('Online') ?>
                        </span>
                    <?php else: ?>
                        <span title="<?= __('Offline') ?>">
                            <span class="status-dot status-down"></span><?= __('Offline') ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($tunnel['id_mysql_server'] === null && $tunnel['id_maxscale_server'] === null && $tunnel['id_proxysql_server'] === null): ?>
                        <?php 
                            

                            echo '<a href="'.LINK.'Mysql/add/mysql_server:ip:'.$tunnel['local_host'].'/mysql_server:port:'.$tunnel['local_port'].'" type="button" class="btn btn-primary btn-xs">Add MySQL Server</a>'; 
                            echo ' <a href="'.LINK.'Mysql/add/mysql_server:ip:'.$tunnel['local_host'].'/mysql_server:port:'.$tunnel['local_port'].'" type="button" class="btn btn-primary btn-xs">Add MaxScale Admin</a>'; 
                            echo ' <a href="'.LINK.'Mysql/add/mysql_server:ip:'.$tunnel['local_host'].'/mysql_server:port:'.$tunnel['local_port'].'" type="button" class="btn btn-primary btn-xs">Add ProxySQL Admin</a>'; 
                            //echo ' <a href="'.LINK.'Mysql/add/mysql_server:ip:'.$tunnel['local_host'].'/mysql_server:port:'.$tunnel['local_port'].'" type="button" class="btn btn-primary btn-xs">Add HA Proxy</a>'; 
                        ?>
                    <?php else: ?>
                        <?php 

                        if (!empty($tunnel['id_mysql_server'])) {
                            echo Display::srv($tunnel['id_mysql_server'], true, LINK.'MysqlServer/main/'.$tunnel['id_mysql_server'].'/');
                        }
                        if (!empty($tunnel['id_maxscale_server'])) {
                            echo '<img title="MaxScale Server" alt="MaxScale Server" height="16" width="16" src="'.IMG.'/icon/maxscale.svg">';
                            echo " [MaxScale Admin] ". Display::srv($tunnel['id_maxscale_server']);
                        }

                        
                        ?>
                    <?php endif; ?> 
                </td>
                <td>
                    <?php if (!empty($tunnel['servers_jump'])): ?>
                        <?php foreach ($tunnel['servers_jump'] as $jump): ?>
                            <div class="jump">
                                <?php
                                try {
                                    $reader = new \GeoIp2\Database\Reader(ROOT.'/data/GeoLite2-Country.mmdb');
                                    $record = $reader->country($jump['ip']);
                                    $flag = isoToFlag($record->country->isoCode); 
                                } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                                    // IP non trouvée, on continue avec un flag vide ou neutre
                                    $flag = "🌐"; // drapeau par défaut ou vide ""
                                }
                                echo $flag."&nbsp;".htmlspecialchars($jump['ip']);
                            ?>:<?=htmlspecialchars($jump['port'] ?? 22) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($tunnel['pid']) ?></td>
                <!--<td style="max-width: 500px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><code><?= htmlspecialchars($tunnel['command']) ?></code></td>-->
                <td><?= $tunnel['date_created'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<?php
$pids = [];
foreach ($data as $tunnel) {
    if (!empty($tunnel['pid'])) {
        $pids[] = (int) $tunnel['pid'];
    }
}
$pids = array_values(array_unique(array_filter($pids)));
$killCommand = !empty($pids) ? 'kill -9 ' . implode(' ', $pids) : '';
$purgePids = array_values(array_unique(array_filter($pids_to_purge ?? [])));
$purgeKillCommand = !empty($purgePids) ? 'kill -9 ' . implode(' ', $purgePids) : '';
?>

<div class="text-right" style="margin-top: 15px; margin-bottom: 15px;">
    <button id="copy-purge-kill-command" type="button" class="btn btn-warning js-copy-kill-command" data-command="<?= htmlspecialchars($purgeKillCommand, ENT_QUOTES, 'UTF-8') ?>" data-feedback="copy-purge-feedback" <?= empty($purgeKillCommand) ? 'disabled' : '' ?>>
        <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
        <?= __('Copier la commande kill des tunnels à purger') ?>
    </button>
    <small id="copy-purge-feedback" class="text-success" style="margin-left: 10px; display: none;"><?= __('Commande copiée dans le presse-papiers') ?></small>
    <button id="copy-kill-command" type="button" class="btn btn-danger js-copy-kill-command" data-command="<?= htmlspecialchars($killCommand, ENT_QUOTES, 'UTF-8') ?>" data-feedback="copy-kill-feedback" <?= empty($killCommand) ? 'disabled' : '' ?>>
        <span class="glyphicon glyphicon-copy" aria-hidden="true"></span>
        <?= __('Copier la commande kill de tous les tunnels') ?>
    </button>
    <small id="copy-kill-feedback" class="text-success" style="margin-left: 10px; display: none;"><?= __('Commande copiée dans le presse-papiers') ?></small>
</div>

<script>
function copyKillTunnelCommand(command, feedbackId) {
    if (!command) {
        return;
    }

    var feedback = document.getElementById(feedbackId || 'copy-kill-feedback');

    function showCopiedMessage() {
        if (!feedback) {
            return;
        }
        feedback.style.display = 'inline';
        setTimeout(function () {
            feedback.style.display = 'none';
        }, 2000);
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(command).then(showCopiedMessage);
        return;
    }

    var textarea = document.createElement('textarea');
    textarea.value = command;
    textarea.setAttribute('readonly', 'readonly');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();

    try {
        document.execCommand('copy');
        showCopiedMessage();
    } finally {
        document.body.removeChild(textarea);
    }
}

document.addEventListener('click', function (event) {
    var button = event.target.closest('.js-copy-kill-command');
    if (!button) {
        return;
    }

    copyKillTunnelCommand(button.getAttribute('data-command') || '', button.getAttribute('data-feedback') || 'copy-kill-feedback');
});
</script>
