<?php

$bot = $data['bot'] ?? [];

echo '<div class="row">';
echo '<div class="col-md-8">';
echo '<h2>'.sprintf(__("Telegram bot #%d"), $bot['id']).'</h2>';

echo '<table class="table table-bordered table-striped">';
echo '<tr><th>'.__("Field").'</th><th>'.__("Value").'</th></tr>';
echo '<tr><td>'.__("ID").'</td><td>'.$bot['id'].'</td></tr>';
echo '<tr><td>'.__("Token").'</td><td><code>'.htmlentities($bot['token']).'</code></td></tr>';
echo '<tr><td>'.__("Chat ID").'</td><td><code>'.htmlentities($bot['chat_id']).'</code></td></tr>';
echo '<tr><td>'.__("Created at").'</td><td>'.$bot['insert_at'].'</td></tr>';
echo '<tr><td>'.__("Updated at").'</td><td>'.$bot['updated_at'].'</td></tr>';
echo '</table>';

echo '<div class="btn-group">';
echo '<a class="btn btn-default" href="'.LINK.'telegram/index">'.__("Back to list").'</a>';
echo '<a class="btn btn-danger" href="'.LINK.'telegram/delete/'.$bot['id'].'?redirect=telegram/index" onclick="return confirm(\''.__("Delete this bot?").'\');">'.__("Delete").'</a>';
echo '</div>';

echo '</div>';
echo '</div>';
