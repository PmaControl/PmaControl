<div class="well">
gg
</div>
<div class="command-shell">
    MariaDB [(pmacontrol)]> <?= $data['query']['command'] ?>
    <br><br><?= $data['table'] ?>
    <br>
    <?= $data['rows'] ?> row in set (<?= $data['query']['duration'] ?> sec)
</div>
<br>
<div class="well" style="font-size:16px">
    <?= $data['explication'] ?>
</div>