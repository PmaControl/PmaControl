<?php

use App\Library\Chiffrement;
use App\Library\Display;
use Glial\Synapse\FactoryController;

$routers = $data['mysqlrouter'] ?? [];

echo '<div class="row" style="padding:12px 10px 0 10px;">';
echo '<div class="col-md-8">';
echo '<h2 style="margin:0 0 6px 0;">MySQL Router Control Plane</h2>';
echo '<div class="text-muted">Vue orientée Router Admin : API, metadata cache, routes et destinations backend.</div>';
echo '</div>';
echo '<div class="col-md-4 text-right">';
echo '<a href="' . LINK . 'MysqlRouter/add" class="btn btn-success" style="margin-top:6px;">' . __('New MySQL Router') . '</a>';
echo '</div>';
echo '</div>';

if (empty($routers)) {
    echo '<div class="alert alert-info" style="margin:15px 10px;">No MySQL Router admin configured.</div>';
    return;
}

$totalRoutes = 0;
$onlineRouters = 0;
$frontends = 0;
foreach ($routers as $router) {
    $totalRoutes += count($router['routes'] ?? []);
    $frontends += count($router['mysql_servers'] ?? []);
    if (empty($router['api_error'])) {
        $onlineRouters++;
    }
}

echo '<div class="row" style="padding:10px 10px 0 10px;">';
$summaryCards = [
    ['Routers', (string) count($routers), '#0f766e'],
    ['Routers API OK', (string) $onlineRouters, '#2563eb'],
    ['Published Routes', (string) $totalRoutes, '#7c3aed'],
    ['Mapped Frontends', (string) $frontends, '#b45309'],
];

foreach ($summaryCards as $card) {
    echo '<div class="col-md-3">';
    echo '<div style="background:#fff;border:1px solid #d7dde6;border-left:5px solid ' . $card[2] . ';border-radius:4px;padding:12px 14px;min-height:86px;">';
    echo '<div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.04em;">' . $card[0] . '</div>';
    echo '<div style="font-size:28px;font-weight:700;line-height:1.2;">' . $card[1] . '</div>';
    echo '</div>';
    echo '</div>';
}
echo '</div>';

foreach ($routers as $router) {
    $routerStatus = $router['router_status'] ?? [];
    $routingStatus = $router['routing_status'] ?? [];
    $apiError = $router['api_error'] ?? '';
    $isOnline = empty($apiError);
    $hostname = htmlspecialchars($router['hostname'] . ':' . $router['port'], ENT_QUOTES, 'UTF-8');
    $displayName = htmlspecialchars($router['display_name'], ENT_QUOTES, 'UTF-8');

    echo '<div class="row" style="padding:0 10px;">';
    echo '<div class="col-md-12">';
    echo '<div style="background:#fff;border:1px solid #cfd8e3;border-radius:6px;margin-top:16px;overflow:hidden;">';

    echo '<div style="background:linear-gradient(135deg,#0f172a,#1e3a8a);color:#fff;padding:16px 18px;">';
    echo '<div class="row">';
    echo '<div class="col-md-7">';
    echo '<div style="font-size:22px;font-weight:700;">' . $displayName . '</div>';
    echo '<div style="opacity:.85;margin-top:4px;">Admin API ' . $hostname . ' · ' . ($router['is_ssl'] ? 'HTTPS' : 'HTTP') . '</div>';
    echo '</div>';
    echo '<div class="col-md-5 text-right">';
    $statusClass = $isOnline ? 'success' : 'danger';
    $statusText = $isOnline ? 'API ONLINE' : 'API ERROR';
    echo '<span class="label label-' . $statusClass . '" style="font-size:13px;">' . $statusText . '</span>';
    echo '<div style="margin-top:8px;">Login: <b>' . htmlspecialchars($router['login'], ENT_QUOTES, 'UTF-8') . '</b> · Password: ';
    FactoryController::addNode('Server', 'passwd', [Chiffrement::encrypt($router['password'])]);
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    echo '<div class="row" style="padding:16px 12px 8px 12px;">';

    echo '<div class="col-md-4">';
    echo '<div style="border:1px solid #e5e7eb;border-radius:4px;padding:12px;min-height:210px;">';
    echo '<div style="font-weight:700;margin-bottom:10px;">Router Runtime</div>';
    if ($isOnline) {
        echo '<div><b>Hostname</b>: ' . htmlspecialchars((string) ($routerStatus['hostname'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') . '</div>';
        echo '<div><b>Version</b>: ' . htmlspecialchars((string) ($routerStatus['version'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') . '</div>';
        echo '<div><b>PID</b>: ' . htmlspecialchars((string) ($routerStatus['processId'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') . '</div>';
        echo '<div><b>Started</b>: ' . htmlspecialchars((string) ($routerStatus['timeStarted'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') . '</div>';
        echo '<hr style="margin:10px 0;">';
        echo '<div><b>Current Connections</b>: ' . htmlspecialchars((string) ($routingStatus['currentTotalConnections'] ?? '0'), ENT_QUOTES, 'UTF-8') . '</div>';
        echo '<div><b>Max Connections</b>: ' . htmlspecialchars((string) ($routingStatus['maxTotalConnections'] ?? '0'), ENT_QUOTES, 'UTF-8') . '</div>';
        echo '<div><b>Mapped Frontends</b>:</div>';
        if (!empty($router['mysql_servers'])) {
            echo '<ul style="margin:6px 0 0 18px;padding:0;">';
            foreach ($router['mysql_servers'] as $mysqlServer) {
                echo '<li>' . Display::srv((int) $mysqlServer['id'], true) . ' <span class="text-muted">('
                    . htmlspecialchars($mysqlServer['ip'] . ':' . $mysqlServer['port'], ENT_QUOTES, 'UTF-8') . ')</span></li>';
            }
            echo '</ul>';
        } else {
            echo '<div class="text-muted">No mapped frontend.</div>';
        }
    } else {
        echo '<div class="alert alert-danger" style="margin-bottom:0;">' . htmlspecialchars($apiError, ENT_QUOTES, 'UTF-8') . '</div>';
    }
    echo '</div>';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<div style="border:1px solid #e5e7eb;border-radius:4px;padding:12px;min-height:210px;">';
    echo '<div style="font-weight:700;margin-bottom:10px;">Metadata Cache / Group Replication</div>';
    if (!empty($router['metadata'])) {
        foreach ($router['metadata'] as $metadataName => $metadata) {
            $config = $metadata['config'] ?? [];
            $status = $metadata['status'] ?? [];
            echo '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:10px;margin-bottom:10px;">';
            echo '<div style="font-weight:700;color:#334155;">' . htmlspecialchars((string) $metadataName, ENT_QUOTES, 'UTF-8') . '</div>';
            echo '<div><b>GR ID</b>: ' . htmlspecialchars((string) ($config['groupReplicationId'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') . '</div>';
            echo '<div><b>Refresh</b>: ' . htmlspecialchars((string) ($config['timeRefreshInMs'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') . ' ms</div>';
            echo '<div><b>Last refresh host</b>: ' . htmlspecialchars((string) (($status['lastRefreshHostname'] ?? 'n/a') . ':' . ($status['lastRefreshPort'] ?? '')), ENT_QUOTES, 'UTF-8') . '</div>';
            echo '<div><b>Refresh OK/KO</b>: ' . htmlspecialchars((string) ($status['refreshSucceeded'] ?? '0'), ENT_QUOTES, 'UTF-8') . ' / ' . htmlspecialchars((string) ($status['refreshFailed'] ?? '0'), ENT_QUOTES, 'UTF-8') . '</div>';
            echo '<div style="margin-top:6px;"><b>Cluster nodes</b>:</div>';
            echo '<ul style="margin:6px 0 0 18px;padding:0;">';
            foreach (($config['nodes'] ?? []) as $node) {
                echo '<li>' . htmlspecialchars(($node['hostname'] ?? 'n/a') . ':' . ($node['port'] ?? ''), ENT_QUOTES, 'UTF-8') . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    } else {
        echo '<div class="text-muted">No metadata cache payload available.</div>';
    }
    echo '</div>';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<div style="border:1px solid #e5e7eb;border-radius:4px;padding:12px;min-height:210px;">';
    echo '<div style="font-weight:700;margin-bottom:10px;">Published Routes</div>';
    if (!empty($router['routes'])) {
        foreach ($router['routes'] as $route) {
            $routeName = (string) ($route['route'] ?? 'unknown');
            $bindAddress = (string) ($route['bind_address'] ?? '0.0.0.0');
            $bindPort = (int) ($route['bind_port'] ?? 0);
            $isAlive = !empty($route['health']['isAlive']);
            $labelClass = $isAlive ? 'success' : 'danger';

            echo '<div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px;margin-bottom:10px;">';
            echo '<div><span class="label label-' . $labelClass . '">' . ($isAlive ? 'ALIVE' : 'DOWN') . '</span> ';
            echo '<b>' . htmlspecialchars($routeName, ENT_QUOTES, 'UTF-8') . '</b></div>';
            echo '<div class="text-muted" style="margin-top:4px;">Listener ' . htmlspecialchars($bindAddress . ':' . $bindPort, ENT_QUOTES, 'UTF-8') . '</div>';
            echo '<div style="margin-top:6px;"><b>Destinations</b>:</div>';
            if (!empty($route['destinations_payload']['items'])) {
                echo '<ul style="margin:6px 0 0 18px;padding:0;">';
                foreach ($route['destinations_payload']['items'] as $destination) {
                    echo '<li>' . htmlspecialchars(($destination['address'] ?? 'n/a') . ':' . ($destination['port'] ?? ''), ENT_QUOTES, 'UTF-8') . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<div class="text-muted">No destination published.</div>';
            }
            echo '</div>';
        }
    } else {
        echo '<div class="text-muted">No route payload available.</div>';
    }
    echo '</div>';
    echo '</div>';

    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
