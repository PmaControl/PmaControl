<?php
use Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;
use App\Library\Diff;

?>

<form action="" method="post">
    <div class="well">
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4"> <b><?= __("Orginal") ?></b></div>
            <div class="col-md-4"> <b><?= __("Compare") ?></b></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Server") ?></div>
            <div class="col-md-4"><?php
                \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("compare_main", "id_mysql_server__original", array("data-width"=>"100%")));
                ?></div>
            <div class="col-md-4"><?php
                \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("compare_main", "id_mysql_server__compare", array("data-width"=>"100%")));
                ?></div>
        </div>

        <div class="row" style="height:5px">
        </div>
        <div class="row">
            <div class="col-md-4"><?= __("Database") ?></div>
            <div class="col-md-4">
                <?php
                echo Form::select("compare_main", "database__original", $data['listdb1'], "", array("data-live-search" => "true", "class" => "selectpicker","data-width"=>"100%" ))
                ?>
            </div>
            <div class="col-md-4">
                <?php
                echo Form::select("compare_main", "database__compare", $data['listdb2'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width"=>"100%"))
                ?>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-transfer" style="font-size:12px"></span> <?= __("Compare") ?></button>
                <button type="reset" class="btn btn-danger"><span class="glyphicon glyphicon-remove" style="font-size:12px"></span> <?= __("Reset") ?></button>
            </div>
        </div>
    </div>
</form>



<?php
if ($data['display']) {
    $menu = FactoryController::addNode("Compare", "menu", array($data));
    FactoryController::addNode("Compare", "getObjectDiff", array($data, $menu));
}


$table1 = "CREATE TABLE IF NOT EXISTS `translation_zh-cn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_history_etat` int(11) NOT NULL,
  `key` char(40) NOT NULL,
  `source` char(5) NOT NULL,
  `destination` char(5) DEFAULT NULL,
  `text` text NOT NULL,
  `date_inserted` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `translate_auto` int(11) NOT NULL,
  `file_found` varchar(255) NOT NULL,
  `line_found` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`,`file_found`),
  KEY `id_history_etat` (`id_history_etat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$table2 = "CREATE TABLE IF NOT EXISTS `translation_zh-cn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_history_etat` int(11) NOT NULL,
  `key` char(40) NOT NULL,
  `source` char(5) NOT NULL,
  `destination` char(5) DEFAULT NULL,
  `text` text NOT NULL,
  `date_inserted` datetime NOT NULL,
  `calcul` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `translate_auto` int(11) NOT NULL,
  `file_found` varchar(255) NOT NULL,
  `line_found` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`,`file_found`),
  KEY `id_history_etat` (`id_history_etat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";




$diff = Diff::compare($table1, $table2);


debug($diff);

//$diffTable = Diff::toTable($diff);



$diffTable = Diff::toSql($diff, $table1, $table2);


echo $diffTable;

//echo $diffTable;