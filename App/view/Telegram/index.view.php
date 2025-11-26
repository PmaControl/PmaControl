<?php

echo '<div class="row">';
echo '<div class="col-md-12">';
echo '<h2>'.__("Telegram bots").'</h2>';

if (!empty($data['bots'])) {
    echo '<table class="table table-bordered table-striped">';
    echo '<tr>';
    echo '<th>#</th>';
    echo '<th>'.__("Token").'</th>';
    echo '<th>'.__("Chat ID").'</th>';
    echo '<th>'.__("Created").'</th>';
    echo '<th>'.__("Updated").'</th>';
    echo '<th>'.__("Actions").'</th>';
    echo '</tr>';

    $i = 0;
    foreach ($data['bots'] as $bot) {
        $i++;
        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td><code>'.htmlentities($bot['token']).'</code></td>';
        echo '<td><code>'.htmlentities($bot['chat_id']).'</code></td>';
        echo '<td>'.$bot['insert_at'].'</td>';
        echo '<td>'.$bot['updated_at'].'</td>';
        echo '<td>';
        echo '<a class="btn btn-primary btn-sm" href="'.LINK.'telegram/view/'.$bot['id'].'">'.__("View").'</a> ';
        echo '<a class="btn btn-danger btn-sm" href="'.LINK.'telegram/delete/'.$bot['id'].'?redirect=telegram/index" onclick="return confirm(\''.__("Are you sure?").'\');">'.__("Delete").'</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';
} else {
    echo '<div class="alert alert-info">'.__("No bot configured yet.").'</div>';
}

echo '<a class="btn btn-success" href="'.LINK.'telegram/add">'.__("Add a bot").'</a>';
echo '</div>';
echo '</div>';
