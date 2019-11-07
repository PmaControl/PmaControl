<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form;
?>


<div class="well">

    <form action="" method="POST">

        <?php
        \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "auto")));

        $_GET['general_log']['activate'] = 1;
        echo Form::input("general_log", "activate", array("type" => "hidden"));
        ?>



        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>
