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
</style>

<table>
    <thead>
        <tr>
            <th><?=__('ID') ?></th>
            <th><?=__('Local') ?></th>
            <th><?=__('Remote') ?></th>
            <th><?=__('Server') ?></th>
            <th><?=__('Jump Hosts') ?></th>
            <th><?=__('Command') ?></th>
            <th><?=__('Date Created') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $tunnel): ?>
            <tr class="<?= $tunnel['date_end'] ? 'closed' : 'open' ?>">
                <td><?= $tunnel['id'] ?></td>
                <td><?= htmlspecialchars($tunnel['local']) ?></td>
                <td><?= htmlspecialchars($tunnel['remote']) ?></td>
                <td>
                    <?php if ($tunnel['id_mysql_server'] === null): ?>
                        <select data-tunnel-id="<?= $tunnel['id'] ?>">
                            <option value="">-- Choisir --</option>
                            <?php foreach ($servers as $id => $name): ?>
                                <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <?php echo Display::srv($tunnel['id_mysql_server']);
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
                <td style="max-width: 500px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><code><?= htmlspecialchars($tunnel['command']) ?></code></td>
                <td><?= $tunnel['date_created'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.querySelectorAll('select[data-tunnel-id]').forEach(function(select){
    select.addEventListener('change', function(){
        const tunnelId = this.getAttribute('data-tunnel-id');
        const serverId = this.value;

        if(serverId === '') return;

        // Envoi AJAX pour mettre à jour l'id_mysql_server
        fetch('/tunnel/update', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: tunnelId, id_mysql_server: serverId})
        }).then(r => r.json())
          .then(resp => {
              if(resp.success){
                  alert('Tunnel mis à jour avec succès !');
                  location.reload(); // reload pour rafraîchir la table
              } else {
                  alert('Erreur : ' + resp.message);
              }
          });
    });
});
</script>