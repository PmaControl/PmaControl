<?php
use \Glial\Synapse\FactoryController;
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Host Information : <?= $data['server']['display_name'] ?></h3>
    </div>
    <div class="well">
        <p><b>Hostname:</b> <?= $data['server']['hostname'] ?>:<?= $data['server']['port'] ?></p>
        <p><b>SSH Key:</b> <?= $data['server']['ssh_key_name'] ?> (user: <?= $data['server']['ssh_user'] ?>)</p>
        <p><b>Date Added:</b> <?= $data['server']['date_inserted'] ?></p>
        <div style="margin-bottom: 15px;">
            <a class="btn btn-success" href="<?= LINK ?>docker/addContainer/<?= $data['server']['id'] ?>">
                <i class="fa fa-plus"></i> Add Container
            </a>
        </div>
    </div>

</div>

<h3>Containers detected</h3>
<table class="table table-condensed table-bordered">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Engine</th>
    <th>Tag</th>
    <th>Port</th>
    <th>Status</th>
</tr>

<?php foreach ($data['instances'] as $row): ?>
<tr>
    <td><?= $row['container_id'] ?></td>
    <td><?= $row['container_name'] ?></td>
    <td><?= $row['software_name'] ?></td>
    <td><?= $row['tag'] ?></td>
    <td><?= $row['exposed_port'] ?></td>
    <td><?= $row['status'] ?></td>
</tr>
<?php endforeach; ?>

</table>

<?php
FactoryController::addNode("Docker", "imageAvailable", [$data['server']['id']]);

if (isset($_SESSION['docker_containers_pending'])) {
    debug($_SESSION['docker_containers_pending']);
}

?>
