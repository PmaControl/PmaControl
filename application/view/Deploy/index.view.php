<?php

use Glial\Html\Form\Form;

if (empty($_GET['ssh']['password'])) {
    $_GET['ssh']['password'] = 22;
}
?>
<form action="" method="post">


    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title"><?= __('SSH Settings') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-4">

                    SSH User <a href="#" data-toggle="popover" title="<?=__("SSH User")?>" data-content="<ul><li><?=__("Specify root if you have root credentials.")?></li><li><?=__("If you use sudo ro execute system commands, specify the username that you wish to use here. The user must exists on all nodes.")?></li></ul>">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </a>
                    <?= Form::input("ssh", "user", array("class" => "form-control", "placeholder" => "Enter SSH User")) ?>
                </div>
                <div class="col-md-4">
                    SSH Key Path <i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?= Form::input("ssh", "key_path", array("class" => "form-control", "placeholder" => "Enter Path, i.e.: /home/<ssh user>/.ssh/id_rsa")) ?>
                </div>
                <div class="col-md-4">
                    Sudo Password <i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?= Form::input("ssh", "password", array("class" => "form-control", "placeholder" => "Enter Sudo Password", "type" => "password")) ?>
                </div>
            </div>

            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-4">
                    SSH Port
                    <?= Form::input("ssh", "port", array("class" => "form-control", "placeholder" => "Enter ssh port")) ?>
                </div>
                <div class="col-md-4">

                </div>
                <div class="col-md-4">

                </div>
            </div>

        </div>
    </div>


    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('General') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-4">
                    Cluster Name
                    <?= Form::input("galera", "cluster_name", array("class" => "form-control", "placeholder" => "Enter Cluster Name")) ?>
                </div>
                <div class="col-md-8">
                    Architecture<br />
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default">
                            <input type="radio" name="options" id="option1" autocomplete="off"> Replication
                        </label>
                        <label class="btn btn-default active">
                            <input type="radio" name="options" id="option2" autocomplete="off" checked> Galera
                        </label>
                        <label class="btn btn-default">
                            <input type="radio" name="options" id="option3" autocomplete="off"> Cluster (NDB)
                        </label>
                        <label class="btn btn-default">
                            <input type="radio" name="options" id="option3" autocomplete="off"> Group replication
                        </label>
                    </div>

                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="checkbox checbox-switch switch-success">
                            <label>
                                <?= Form::input("general", "install_software", array("class" => "form-control", "type" => "checkbox")) ?>
                                <span></span>
                                Install Software
                            </label>
                        </div>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="checkbox checbox-switch switch-success">
                            <label>
                                <?= Form::input("general", "disable_firewall", array("class" => "form-control", "type" => "checkbox")) ?>
                                <span></span>
                                Disable Firewall?
                            </label>
                        </div>
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="checkbox checbox-switch switch-success">
                            <label>
                                <?= Form::input("general", "disable_selinux_apparmor", array("class" => "form-control", "type" => "checkbox")) ?>
                                <span></span>
                                Disable AppArmor/SELinux?
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Add node') ?></h3>
        </div>
        <div class="well">

            <div class="row">
                <div class="col-md-5">
                    <div class="form-inline">
                        <?= Form::input("galera", "port", array("class" => "form-control", "placeholder" => "127.0.0.1 or DNS")) ?>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </div>
                <div class="col-md-2">
                    <big><big><big><big>
                                    <i class="fa fa-long-arrow-left" aria-hidden="true"></i>
                                    <i class="fa fa-files-o" aria-hidden="true"></i>
                                    <i class="fa fa-long-arrow-right" aria-hidden="true"></i>
                                </big></big></big></big>
                </div>
                <div class="col-md-5">
                    <div class="form-inline">
                        <?= Form::input("galera", "port", array("class" => "form-control", "placeholder" => "127.0.0.1 or DNS")) ?>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Define MySQL Servers') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-4">
                    Vendor<br />

                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default active">
                            <input type="radio" name="options" id="option1" autocomplete="off" checked> MariaDB
                        </label>
                        <label class="btn btn-default">
                            <input type="radio" name="options" id="option2" autocomplete="off"> Oracle
                        </label>
                        <label class="btn btn-default">
                            <input type="radio" name="options" id="option3" autocomplete="off"> Percona
                        </label>
                    </div>



                </div>
                <div class="col-md-4">
                    Version<br />
                    <div class="btn-group" role="group" aria-label="...">

                        <div class="btn-group" data-toggle="buttons">
                            <label class="btn btn-default">
                                <input type="radio" name="options" id="option1" autocomplete="off"> 10.0
                            </label>
                            <label class="btn btn-default">
                                <input type="radio" name="options" id="option2" autocomplete="off"> 10.1
                            </label>
                            <label class="btn btn-default active">
                                <input type="radio" name="options" id="option3" autocomplete="off" checked> 10.2
                            </label>
                            <label class="btn btn-default">
                                <input type="radio" name="options" id="option3" autocomplete="off"> 10.3
                            </label>
                        </div>


                    </div>
                </div>
                <div class="col-md-4">
                    Server Data Directory<br />
                    <?= Form::input("galera", "datadir", array("class" => "form-control", "placeholder" => "/var/lib/mysql")) ?>
                </div>
            </div>
            <div class="row">&nbsp;</div>

            <div class="row">
                <div class="col-md-4">
                    Server Port
                    <?= Form::input("galera", "port", array("class" => "form-control", "placeholder" => "3306")) ?>
                </div>
                <div class="col-md-4">
                    my.cnf Template
                    <?= Form::input("galera", "port", array("class" => "form-control", "placeholder" => "my.cnf.galera")) ?>
                </div>
                <div class="col-md-4">
                    Root password
                    <?= Form::input("galera", "port", array("class" => "form-control", "placeholder" => "Enter root password")) ?>
                </div>
            </div>
            <div class="row">&nbsp;</div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="checkbox checbox-switch switch-success">
                            <label>
                                <?= Form::input("general", "disable_selinux_apparmor", array("class" => "form-control", "type" => "checkbox")) ?>
                                <span></span>
                                Use vendor repository ?
                            </label>
                        </div>
                    </div>


                </div>

                <div class="col-md-6">

                </div>
            </div>
        </div>

    </div>

    <button class="btn btn-primary">Deploy</button>

</form>
