<?php

use Glial\Html\Form\Form;
use App\Library\Display;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * login
 * password
 * sudo su -
 * 
 * login public key => generate auto ?
 * login private key
 * private key
 *
 * select server
 *
 */
?>
<div class="well">
    <?= \Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array()); ?>
</div>

<form action="" method="post" class="form-inline" autocomplete="off">
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __('Credential to use') ?></h3>
                </div>
                <div class="well">
                    <div class="row">
                        <div class="col-md-4 stackem">
                            <div>
                                Login SSH<br />
                                <?= Form::input("mysql_server", "login_ssh", array("class" => "form-control", "style" => "width:100%", "autocomplete" => "no", "placeholder"=>__("User who is linked with this private key"))); ?>
                            </div>

                            <div>
                                Password SSH<br />
                                <?=
                                Form::input("mysql_server", "password_ssh", array("class" => "form-control", "style" => "width:100%", "type" => "password", "autocomplete" => "off"));
                                ?>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <?= __('KEY private') ?>;
                            <textarea name="mysql_server[key_ssh]" class="form-control" id="exampleFormControlTextarea1" placeholder="If not empty the password will be used" rows="4" style="width:100%"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-1">
            OR
        </div>
        <div class="col-md-5">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __('Private Key to use') ?></h3>
                </div>
                <div class="well">
                    <div class="row">
                        <div class="col-md-12">
                            <?= __("Select private key to use"); ?> :
                            <?=
                            Form::select("ssh_key_pv", "id", $data['key_ssh'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "100%"));
                            ?>
                            <br><br><br><br><br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Public key to push') ?></h3>
        </div>
        <div class="well">
            <div class="row">
                <br />
                <div class="col-md-6">
                    <?= __('Public key to deploy') ?>;
                    <?= Form::select("ssh_key", "id", $data['key_ssh'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "100%")); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <br /><button type="submit" class="btn btn-primary">Deploy</button>
                </div>
            </div>
        </div>
    </div>





    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Credential to use') ?></h3>
        </div>


        <?php
        echo '<table class="table table-bordered table-striped" id="table">';
        echo '<tr>';
        echo '<th>';
        ?>
        <div class="form-group">
            <div class="checkbox checbox-switch switch-primary">
                <label>
                    <?= Form::input("check", "all", array("class" => "form-control", "type" => "checkbox", "onClick" => "toggle(this)")) ?>
                    <span></span>
                    <b><?= __("Deploy") ?></b>
                </label>
            </div>
        </div>



        <?php
//echo '<input id="checkAll" type="checkbox" onClick="toggle(this)" />';

        echo '</th>';
        echo '<th>'.__('Top').'</th>';
        echo '<th>'.__('ID').'</th>';
        echo '<th>'.__('MySQL').'</th>';
        echo '<th>'.__('Key').'</th>';
        echo '<th>'.__('Active').'</th>';
        echo '<th>'.__('SSH').'</th>';
        echo '<th>'.__('Organization').'</th>';
        echo '<th>'.__('Environment').'</th>';

        echo '<th>'.__('Tags').'</th>';
        echo '<th>'.__('Name').'</th>';
        echo '<th>'.__('IP').'</th>';
        echo '<th>'.__('Port').'</th>';

        echo '</tr>';

        $i     = 0;
        $style = '';

        Form::setIndice(true);

        foreach ($data['servers'] as $server) {

            $i++;
            echo '<tr class="row-server key-'.implode(" key-", explode(',',$server['id_ssh_key'])).'">';


            echo '<td style = "'.$style.'">';
            ?>
            <div class="form-group">
                <div class="checkbox checbox-switch switch-primary">
                    <label>
                        <?= Form::input("link__mysql_server__ssh_key", "deploy", array("class" => "form-control", "type" => "checkbox")) ?>
                        <span></span>
                    </label>
                </div>
            </div>


            <?php
            // .'<input type = "checkbox" name = "mysql_server['.($i - 1).'][is_monitored]" '.$checked.' />'.
            echo '</td>';

            echo '<td>'.$i.'</td>';
            echo '<td>'.$server['id_mysql_server'];
            //print_r($server);

            echo Form::input("link__mysql_server__ssh_key", "id_mysql_server", array("value"=> $server['id_mysql_server'], "type"=>"hidden"));
            echo '</td>';

            echo '<td style = "'.$style.'">';
            echo '<span class = "glyphicon '.(empty($server['error']) ? "glyphicon-ok" : "glyphicon-remove").'" aria-hidden = "true"></span>';
            echo '</td>';


            echo '<td style = "'.$style.'">';



            echo array_sum(explode(",", $server['active']));


            //echo $server['cpt'];
            echo '</td>';

            echo '<td>'.$server['id_ssh_key'].'</td>';

            echo '<td style = "'.$style.'">';
            echo '<span class = "glyphicon '.(empty($server['ssh_available']) ? "glyphicon-remove" : "glyphicon-ok").'" aria-hidden = "true"></span>';
            echo '</td>';


            echo '<td>'.$server['organization'].'</td>';
            echo '<td>'.Display::server($server).'</td>';


            echo '<td>'.__('Tags').'</td>';
            echo '<td>'.$server['display_name'].'</td>';


            echo '<td>'.$server['ip'].'</td>';
            echo '<td>'.$server['port'].'</td>';
            echo '</tr>'."\n";
        }

        Form::setIndice(false);

        echo '</table>';

        echo '<input type = "hidden" name = "settings" value = "1" />';
        ?>

    </div>
</form>