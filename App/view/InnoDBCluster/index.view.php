<?php

echo '<h3>InnoDB Cluster</h3>';

if (empty($data['clusters'])) {
    echo '<div class="alert alert-info">No InnoDB Cluster data available.</div>';
    return;
}

foreach ($data['clusters'] as $groupName => $nodes) {
    $total = count($nodes);
    $online = 0;

    foreach ($nodes as $node) {
        if ((string)($node['mysql_available'] ?? '0') === '1') {
            $online++;
        }
    }

    echo '<h4>Group: <code>'.htmlspecialchars((string)$groupName, ENT_QUOTES, 'UTF-8').'</code> ('.$online.'/'.$total.' online)</h4>';
    echo '<table class="table table-condensed table-bordered table-striped">';
    echo '<tr>';
    echo '<th>#</th>';
    echo '<th>ID</th>';
    echo '<th>Node</th>';
    echo '<th>Version</th>';
    echo '<th>Mode</th>';
    echo '<th>Status</th>';
    echo '<th>Role</th>';
    echo '<th>super_read_only</th>';
    echo '<th>Seeds</th>';
    echo '<th>Local address</th>';
    echo '</tr>';

    $i = 1;
    foreach ($nodes as $idMysqlServer => $node) {
        $isOnline = (string)($node['mysql_available'] ?? '0') === '1';
        $status = $isOnline ? 'ONLINE' : (!empty($node['mysql_error']) ? 'ERROR' : 'OFFLINE');

        $mode = strtolower((string)($node['group_replication_single_primary_mode'] ?? 'off'));
        $modeLabel = in_array($mode, array('on', '1', 'true'), true) ? 'single-primary' : 'multi-primary';

        $superReadOnly = strtolower((string)($node['super_read_only'] ?? 'on'));
        $readOnly = strtolower((string)($node['read_only'] ?? 'on'));
        $role = ($modeLabel === 'multi-primary' || $superReadOnly === 'off' || $readOnly === 'off' || $readOnly === '0') ? 'PRIMARY' : 'SECONDARY';

        $trClass = '';
        if (!$isOnline) {
            $trClass = ' class="danger"';
        }

        echo '<tr'.$trClass.'>';
        echo '<td>'.$i.'</td>';
        echo '<td>'.$idMysqlServer.'</td>';
        echo '<td>'.htmlspecialchars((string)($node['hostname'] ?? ''), ENT_QUOTES, 'UTF-8').':'.htmlspecialchars((string)($node['port'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td>'.htmlspecialchars((string)($node['version'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td>'.$modeLabel.'</td>';
        echo '<td>'.$status.'</td>';
        echo '<td>'.$role.'</td>';
        echo '<td>'.htmlspecialchars((string)($node['super_read_only'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td style="max-width:280px;word-wrap:break-word;">'.htmlspecialchars((string)($node['group_replication_group_seeds'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td>'.htmlspecialchars((string)($node['group_replication_local_address'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
        echo '</tr>';

        $i++;
    }

    echo '</table>';
}
