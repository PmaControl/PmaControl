<?php

use Glial\Html\Form\Form;
?>
<form action="" method="post">


    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title"><?= __('LDAP General') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-4">


                    <div class="form-group">
                        <div class="checkbox checbox-switch switch-success">
                            <label>
                                <?php
                                //var_dump($_GET['ldap']['check']);

                                $params = array("class" => "form-control", "type" => "checkbox");
                                if ($_GET['ldap']['check'] === true || $_GET['ldap']['check'] === "on") {
                                    $params['checked'] = "checked";
                                }
                                ?>
                                <?= Form::input("ldap", "check", $params) ?>
                                <span></span>
                                Activate LDAP
                            </label>
                        </div>
                    </div>

                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4"></div>
            </div>

            <div class="row">
                <div class="col-md-4">Serveur LDAP
                    <?= Form::input("ldap", "url", array("class" => "form-control", "placeholder" => "Server name / IP")) ?></div>
                <div class="col-md-4">Port LDAP
                    <?= Form::input("ldap", "port", array("class" => "form-control", "placeholder" => "Port by default : 389")) ?></div>
                <div class="col-md-4">
                    <?php
                    if ($data['check_server'] === true) {
                        echo '<div class="alert alert-success">';
                        echo '<span class="glyphicon glyphicon-ok"></span> <strong>Success!</strong> '.__("This server is valid");
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-danger">';
                        echo '<span class="glyphicon glyphicon-remove"></span> <strong>Error !</strong> '.$data['check_server'];
                        echo '</div>';
                    }
                    ?>

                </div>
            </div>



            <div class="row">
                <div class="col-md-8">Bind DN
                    <?= Form::input("ldap", "bind_dn", array("class" => "form-control", "placeholder" => "Bind DN (User)")) ?>
                </div>

                <div class="col-md-4"></div>
            </div>

            <div class="row">
                <div class="col-md-4">Root DN
                    <?= Form::input("ldap", "root_dn", array("class" => "form-control", "placeholder" => "Root DN")) ?>
                </div>

                <div class="col-md-4">Root DN (optional, used for search group)
                    <?= Form::input("ldap", "root_dn_search", array("class" => "form-control", "placeholder" => "LDAP search base")) ?>
                </div>

                <div class="col-md-4"></div>
            </div>


            <div class="row">
                <div class="col-md-4">Bind password
                    <?= Form::input("ldap", "bind_passwd", array("class" => "form-control", "placeholder" => "Bind password", "type" => "password")) ?>
                </div>
                <div class="col-md-4">

                    Confirm password
                    <?= Form::input("ldap", "bind_passwd_confirm", array("class" => "form-control", "placeholder" => "Bind password (confirm)", "type" => "password")) ?>
                </div>
                <div class="col-md-4">

                    <?php
                    if ($data['check_credential'] === true) {
                        echo '<div class="alert alert-success">';
                        echo '<span class="glyphicon glyphicon-ok"></span> <strong>Success!</strong> '.__("These credentials are valid");
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-danger">';
                        echo '<span class="glyphicon glyphicon-remove"></span> <strong>Error !</strong> '.$data['check_credential']." - ".__('Check Bind DN and/or Bind password');
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>


            <br />

            <button type="submit" class="btn btn-primary">Update LDAP configuration</button>

        </div>

    </div>


</form>

<?php
if ($data['check_credential'] === true):
    ?>


    <form action="" method="post">
        <div class="panel panel-primary">
            <div class="panel-heading">

                <h3 class="panel-title"><?= __('LDAP Group') ?></h3>
            </div>

            <div class="well">

                <?php
                Form::setIndice(true);

                $i =0;

                foreach ($data['group'] as $group):
                    if ($group['id'] !== "1") {
                        ?>
                        <div class="row">
                            <div class="col-md-6">


                                
                                <?php
                                
                                $_GET['ldap_group'][$i]['id'] = $group['id'];
                                $_GET['ldap_group'][$i]['name'] = $group['cn'];
                                echo Form::input("ldap_group", "id", array("type" => "hidden")); //
                                ?>
                                <?= $group['name'] ?>
                                <?php
                                //echo Form::select("ldap_group", "name", $data['cn'], $group['cn'], array("data-live-search" => "true", "data-size" => "10", "class" => "selectpicker", "data-width" => "100%"));
                                echo Form::input("ldap_group", "name", array("class"=>"form-control" ));

                                ?>
                            </div>
                            <div class="col-md-6"><br /><?php echo __('Number of groups')." : ".count($data['cn']) ?></div>
                        </div>

                        <?php
                        $i++;
                    }
                
                endforeach;
                //debug($_GET['ldap_group']);
                Form::setIndice(false);
                ?>

                <br />

                <button type="submit" class="btn btn-primary">Update group configuration</button>
            </div>
        </div>
    </form>
    <?php
endif;
?>

<?php

\Glial\Synapse\FactoryController::addNode("Ldap", "getGroupFromUser", array());
?>