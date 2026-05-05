<?php

use App\Library\Security\CsrfRender;

$bot = $data['bot'] ?? [];
$telegramDeleteCsrfField = CsrfRender::field($data, 'telegram_delete');
$telegramDeleteCsrfToken = CsrfRender::token($data, 'telegram_delete');
$deleteConfirm = htmlspecialchars(__("Delete this bot?"), ENT_QUOTES, 'UTF-8');

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
echo '<form method="post" action="'.LINK.'telegram/delete/'.(int)$bot['id'].'" style="display:inline" data-confirm="'.$deleteConfirm.'" onsubmit="return confirm(this.getAttribute(\'data-confirm\'));">';
echo '<input type="hidden" name="'.$telegramDeleteCsrfField.'" value="'.$telegramDeleteCsrfToken.'">';
echo '<input type="hidden" name="id_telegram_bot" value="'.(int)$bot['id'].'">';
echo '<button type="submit" class="btn btn-danger">'.__("Delete").'</button>';
echo '</form>';
echo '</div>';

echo '</div>';
echo '</div>';
