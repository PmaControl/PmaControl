<?php

$router = $data['router'] ?? [];
$mysqlRouterAddCsrfField = htmlspecialchars((string) ($data['mysqlrouter_add_csrf_field'] ?? '_csrf_token'), ENT_QUOTES, 'UTF-8');
$mysqlRouterAddCsrfToken = htmlspecialchars((string) ($data['mysqlrouter_add_csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8');

$displayName = htmlspecialchars((string) ($router['display_name'] ?? 'MySQL Router Admin'), ENT_QUOTES, 'UTF-8');
$hostname = htmlspecialchars((string) ($router['hostname'] ?? ''), ENT_QUOTES, 'UTF-8');
$port = htmlspecialchars((string) ($router['port'] ?? '8443'), ENT_QUOTES, 'UTF-8');
$login = htmlspecialchars((string) ($router['login'] ?? ''), ENT_QUOTES, 'UTF-8');
$password = htmlspecialchars((string) ($router['password'] ?? ''), ENT_QUOTES, 'UTF-8');
$isSsl = (int) ($router['is_ssl'] ?? 1);

echo '<div class="row">';
echo '<div class="col-md-6">';
echo '<h2>' . __('Add MySQL Router') . '</h2>';

echo '<form method="post" action="' . LINK . 'MysqlRouter/add" class="form-horizontal">';
echo '<input type="hidden" name="' . $mysqlRouterAddCsrfField . '" value="' . $mysqlRouterAddCsrfToken . '">';

echo '<div class="form-group">';
echo '<label class="control-label">' . __('Display name') . '</label>';
echo '<input type="text" class="form-control" name="mysqlrouter_server[display_name]" value="' . $displayName . '" required>';
echo '</div>';

echo '<div class="form-group">';
echo '<label class="control-label">' . __('Admin hostname') . '</label>';
echo '<input type="text" class="form-control" name="mysqlrouter_server[hostname]" value="' . $hostname . '" required>';
echo '</div>';

echo '<div class="form-group">';
echo '<label class="control-label">' . __('Admin port') . '</label>';
echo '<input type="number" min="1" max="65535" class="form-control" name="mysqlrouter_server[port]" value="' . $port . '" required>';
echo '</div>';

echo '<div class="form-group">';
echo '<label class="control-label">' . __('Login') . '</label>';
echo '<input type="text" class="form-control" name="mysqlrouter_server[login]" value="' . $login . '" required>';
echo '</div>';

echo '<div class="form-group">';
echo '<label class="control-label">' . __('Password') . '</label>';
echo '<input type="password" class="form-control" name="mysqlrouter_server[password]" value="' . $password . '" required>';
echo '</div>';

echo '<div class="checkbox">';
echo '<label>';
echo '<input type="hidden" name="mysqlrouter_server[is_ssl]" value="0">';
echo '<input type="checkbox" name="mysqlrouter_server[is_ssl]" value="1"' . ($isSsl === 1 ? ' checked' : '') . '> HTTPS';
echo '</label>';
echo '</div>';

echo '<button type="submit" class="btn btn-success">' . __('Save') . '</button> ';
echo '<a class="btn btn-default" href="' . LINK . 'MysqlRouter/index">' . __('Back to list') . '</a>';

echo '</form>';
echo '</div>';
echo '</div>';
