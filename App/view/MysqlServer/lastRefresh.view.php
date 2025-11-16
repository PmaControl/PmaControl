<?php

function human_time_diff_dec($date_start, $precision = 1) {
    $seconds = time() - strtotime($date_start);
    $seconds--;

    if ($seconds < 60) {
        return round($seconds, $precision) . 's';
    }

    $minutes = $seconds / 60;
    if ($minutes < 60) {
        return round($minutes, $precision) . 'm';
    }

    $hours = $minutes / 60;
    if ($hours < 24) {
        return round($hours, $precision) . 'h';
    }

    $days = $hours / 24;
    return round($days, $precision) . 'j';
}

function age_color($date) {
    $seconds = time() - strtotime($date);
    if ($seconds < 60) return 'label-success';
    if ($seconds < 3600) return 'label-warning';
    return 'label-danger';
}
?>

<table class="table table-condensed table-bordered table-striped">

<tr>
    <th>#</th> 
    <th><?=__('File') ?></th> 
    <th><?=__('Last refresh') ?></th>
    <th><?=__('Date') ?></th>
    <th><?=__('Date +1') ?></th>
    <th><?=__('Date +2') ?></th>
    <th><?=__('Date +3') ?></th>
    <th><?=__('Date +4') ?></th>
    <th><?=__('Listener refresh') ?></th>
</tr>


<?php 
    $i=0;
    foreach ($data['rows'] as $row): 
        $i++;
?>
<tr>
  <td><?= $i ?></td>
  <td><?= $row['file_name'] ?></td>
    <td>
        <span class="label <?= age_color($row['date']); ?>">
            <?= human_time_diff_dec($row['date']); ?>
        </span>
    </td>
  <td><?= $row['date'] ?>
      <small class="text-muted">(<?= $row['diff_date'] ?>)</small>
  </td>

  <td><?= $row['date_p1'] ?>
      <small class="text-muted">(<?= $row['diff_date_p1'] ?>)</small>
  </td>

  <td><?= $row['date_p2'] ?>
      <small class="text-muted">(<?= $row['diff_date_p2'] ?>)</small>
  </td>

  <td><?= $row['date_p3'] ?>
      <small class="text-muted">(<?= $row['diff_date_p3'] ?>)</small>
  </td>

  <td><?= $row['date_p4'] ?>
      <small class="text-muted">(<?= $row['diff_date_p4'] ?>)</small>
  </td>

  <td><?= $row['last_date_listener'] ?>
      <small class="text-muted">(<?= $row['diff_last_listener'] ?>)</small>
  </td>
</tr>
<?php endforeach; ?>
</table>


<a href="<?=LINK ?>MysqlServer/refresh/<?= $id_mysql_server ?>" class="btn btn-default btn-sm">
    🔄 Refresh all metrics now !
</a>
