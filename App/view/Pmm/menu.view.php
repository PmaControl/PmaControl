<?php

use App\Library\PmmDashboardCatalog;
use Glial\Synapse\FactoryController;

$serverId = isset($param[0]) && ctype_digit((string)$param[0]) ? (int)$param[0] : 0;
$dashboard = (string)($param[1] ?? 'overview');
$menu = PmmDashboardCatalog::getDashboards();

FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-style" => "btn-primary", "data-width" => "auto","all_selectable"=> "true"), $serverId));
?>

<?php if ($serverId < 1) : ?>
<div class="alert alert-info" style="margin:12px 0;">
    Select a monitored MySQL server to open the PMM dashboards.
</div>
<?php return; ?>
<?php endif; ?>

<div class="btn-group" style="margin-bottom:12px;">
<?php
foreach ($menu as $slug => $entry) {
    $active = $dashboard === $slug ? 'active' : '';
    echo '<a href="' . LINK . 'Pmm/' . $slug . '/' . $serverId . '/" class="btn btn-primary ' . $active . '">' . htmlspecialchars((string)$entry['title'], ENT_QUOTES, 'UTF-8') . '</a>' . "\n";
}
?>
</div>
