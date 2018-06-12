<?php

use Glial\Html\Form\Form;
?>

<div class="row">
    <div class="col-md-6">
        <form action="" method="post">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __('Export configuration') ?></h3>
                </div>
                <div class="well">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="checkbox checbox-switch switch-success">
                                    <label>
                                        <?php
                                        //var_dump($_GET['ldap']['check']);

                                        /*
                                          if ($_GET['ldap']['check'] === true || $_GET['ldap']['check'] === "on") {
                                          $params['checked'] = "checked";
                                          } */
                                        ?>
                                        <?= Form::input("export_option", "all", array("class" => "form-control", "type" => "checkbox")) ?>
                                        <span></span>
                                        <?= __("All") ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                        foreach ($data['options'] as $options):
                            ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="checkbox checbox-switch switch-success">
                                        <label>
                                            <?php
                                            //var_dump($_GET['ldap']['check']);

                                            $params = array("class" => "form-control", "type" => "checkbox");
                                            /*
                                              if ($_GET['ldap']['check'] === true || $_GET['ldap']['check'] === "on") {
                                              $params['checked'] = "checked";
                                              } */
                                            ?>
                                            <?= Form::input("export_option", $options['libelle'], $params) ?>
                                            <span></span>
                                            <?= $options['libelle'] ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endforeach;
                        ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6">Password
                            <?=
                            Form::input("export_option", "password", array("class" => "form-control", "type" => "password", "placeholder" => "Password used to crypt the export file"))
                            ?>
                        </div>

                        <div class="col-md-6">Repeat
                            <?= Form::input("export_option", "password2", array("class" => "form-control", "type" => "password", "placeholder" => "Repeat again the password")) ?>
                        </div>
                    </div>

                    <br />
                    <button type="button" class="btn btn-success"><span class="glyphicon glyphicon-export"></span> Export</button>


                </div>

            </div>
        </form>
    </div>



    <div class="col-md-6">


        <div class="panel panel-primary">
            <div class="panel-heading">

                <h3 class="panel-title"><?= __('Export configuration') ?></h3>
            </div>

            <div class="well">


                <div class="row">
                    <div class="col-md-4">

                        <div class="checkbox">
                            <label><input type="checkbox" value="">All</label>
                        </div>


                    </div>
                    <div class="col-md-4"></div>
                    <div class="col-md-4"></div>
                </div>


                <div class="row">
                    <?php
                    foreach ($data['options'] as $options):
                        ?>
                        <div class="col-md-4">
                            <div class="checkbox">
                                <label><input type="checkbox" value="<?= $options['mask'] ?>"><?= $options['libelle'] ?></label>
                            </div>
                        </div>
                        <?php
                    endforeach;
                    ?>
                </div>

                <div class="row">&nbsp;
                </div>

                <div class="row">
                    <div class="col-md-6">File to import
                        <?= Form::input("ldap", "bind_dn", array("type" => "file")) ?>
                    </div>

                    <div class="col-md-6">Password
                        <?=
                        Form::input("ldap", "bind_dn", array("class" => "form-control", "type" => "password", "placeholder" => "Password to decrypt the export file"))
                        ?>
                    </div>


                </div>

                <br />
                <button type="button" class="btn btn-danger"><span class="glyphicon glyphicon-import"></span> Export</button> (Be carrefull the identical value will be overwriten)


            </div>

        </div>

    </div>

</div>
