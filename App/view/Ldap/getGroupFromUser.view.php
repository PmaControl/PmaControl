<?php

use Glial\Html\Form\Form;

$ldapGetGroupFromUserCsrfField = htmlspecialchars((string) ($data['ldap_get_group_from_user_csrf_field'] ?? '_csrf_token'), ENT_QUOTES, 'UTF-8');
$ldapGetGroupFromUserCsrfToken = htmlspecialchars((string) ($data['ldap_get_group_from_user_csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<form action="" method="post">
    <input type="hidden" name="<?= $ldapGetGroupFromUserCsrfField ?>" value="<?= $ldapGetGroupFromUserCsrfToken ?>">
    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title"><?= __('Search for a group ') ?></h3>
        </div>

        <div class="well">


            <?= Form::input("ldap", "user", array("class" => "form-control", "placeholder" => "Enter username")) ?>
            <br />
            <button type="submit" class="btn btn-primary">Get groups</button>
        </div>
    </div>
</form>

<?php
if (!empty($data['list'])) {

    echo '<div class="panel panel-primary">';
    echo '<div class="panel-heading">';
    echo '<h3 class="panel-title">"'.htmlspecialchars((string) $data['user'], ENT_QUOTES, 'UTF-8').'" '.__('is member of').'</h3>';
    echo '</div>';
    echo '<div class="well">';



    echo '<table class="table table-condensed table-bordered table-striped" >';

    echo '<tr>';
    echo '<th>'.__("Top").'</th>';
    echo '<th>'.__("Groups").'</th>';
    echo '</tr>';


    $i = 0;
    foreach ($data['list'] as $list) {
        $i++;

        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td>'.htmlspecialchars((string) $list, ENT_QUOTES, 'UTF-8').'</td>';
        echo '</tr>';
    }

    echo '</table></div></div>';
}
