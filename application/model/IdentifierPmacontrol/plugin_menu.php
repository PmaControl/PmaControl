<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class plugin_menu extends Model
{
var $schema = "CREATE TABLE `plugin_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_plugin_main` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_plugin_main` (`id_plugin_main`),
  CONSTRAINT `plugin_menu_ibfk_1` FOREIGN KEY (`id_plugin_main`) REFERENCES `plugin_main` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1";

var $field = array("id","id_plugin_main","url");

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
