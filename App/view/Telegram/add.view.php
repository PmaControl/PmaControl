<?php

echo '<div class="row">';
echo '<div class="col-md-6">';
echo '<h2>'.__("Add a Telegram bot").'</h2>';

if (!empty($data['errors'])) {
    echo '<div class="alert alert-danger">';
    echo '<ul>';
    foreach ($data['errors'] as $error) {
        echo '<li>'.$error.'</li>';
    }
    echo '</ul>';
    echo '</div>';
}

echo '<form method="post" action="'.LINK.'telegram/add" class="form-horizontal">';

echo '<div class="form-group">';
echo '<label class="control-label">'.__("Bot token").'</label>';
echo '<input type="text" class="form-control" name="telegram_bot[token]" value="'.htmlentities($data['bot']['token'] ?? '').'" required>';
echo '</div>';

echo '<div class="form-group">';
echo '<label class="control-label">'.__("Chat ID").'</label>';
echo '<input type="text" class="form-control" name="telegram_bot[chat_id]" value="'.htmlentities($data['bot']['chat_id'] ?? '').'" required>';
echo '<span class="help-block">'.__("Use negative chat id for groups.").'</span>';
echo '</div>';

echo '<button type="submit" class="btn btn-success">'.__("Save").'</button> ';
echo '<a class="btn btn-default" href="'.LINK.'telegram/index">'.__("Back to list").'</a>';

echo '</form>';
echo '</div>';
echo '</div>';
