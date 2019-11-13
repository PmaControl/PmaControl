<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class plugin_file extends Model
{
var $schema = "CREATE TABLE `plugin_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_plugin_main` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  `md5` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_plugin_main` (`id_plugin_main`),
  CONSTRAINT `plugin_file_ibfk_1` FOREIGN KEY (`id_plugin_main`) REFERENCES `plugin_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","id_plugin_main","md5","file");

var $validate = array(
	'id_plugin_main' => array(
		'reference_to' => array('The constraint to plugin_main.id isn\'t respected.','plugin_main', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
