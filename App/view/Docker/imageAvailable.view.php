<h2>Docker Server: <?=$data['server']['display_name']?> (<?=$data['server']['hostname']?>)</h2>

<a class="btn btn-primary" href="<?=LINK?>docker/linkTagAndImage/<?=$data['server']['id']?>">
    🔄 Sync Images
</a>

<br><br>

<table class="table table-condensed table-bordered table-hover">
<thead>
<tr>
    <th>Family</th>
    <th>Tag</th>
    <th>Full Image</th>
    <th>SHA256</th>
    <th>Size</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php foreach ($data['images'] as $img): ?>
<tr>
    <td>
        <span style="background:<?=$img['background']?>;color:<?=$img['color']?>;padding:2px 8px;border-radius:3px;">
            <?=$img['family']?>
        </span>
    </td>

    <td><?=$img['tag']?></td>
    <td><code><?=$img['repository']?>:<?=$img['tag']?></code></td>
    <td><code><?=$img['sha256'] ?: 'N/A'?></code></td>
    <td><?=$img['size'] ?: 'N/A'?></td>
    <td style="padding:2px">
        <a href="<?=LINK?>dockerContainer/create/<?=$data['server']['id']?>/<?=$img['id_docker_image']?>"
           class="btn btn-success btn-xs">🚀 Run</a>
    </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>