<?php

use Glial\Html\Form\Form;
?>
<form action="" method="post">
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

debug($data['list']);
?>