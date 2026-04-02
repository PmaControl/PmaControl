<?php

use App\Library\PmmDashboardCatalog;
use Glial\Synapse\FactoryController;

$serverId = (int)($param[0] ?? 1);
$dashboard = (string)($param[1] ?? 'overview');
$menu = PmmDashboardCatalog::getDashboards();

FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-style" => "btn-primary", "data-width" => "auto","all_selectable"=> "true"), $serverId));
?>

<div class="btn-group" style="margin-bottom:12px;">
<?php
foreach ($menu as $slug => $entry) {
    $active = $dashboard === $slug ? 'active' : '';
    echo '<a href="' . LINK . 'Pmm/' . $slug . '/' . $serverId . '/" class="btn btn-primary ' . $active . '">' . htmlspecialchars((string)$entry['title'], ENT_QUOTES, 'UTF-8') . '</a>' . "\n";
}
?>
</div>
