<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class cleaner_alert extends Model
{
var $schema = "CREATE TABLE `cleaner_alert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cleaner_main` int(11) NOT NULL,
  `id_user_main` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_cleaner_main` (`id_cleaner_main`),
  KEY `id_user_main` (`id_user_main`),
  CONSTRAINT `cleaner_alert_ibfk_1` FOREIGN KEY (`id_cleaner_main`) REFERENCES `cleaner_main` (`id`),
  CONSTRAINT `cleaner_alert_ibfk_2` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id_user_main","level","id","id_cleaner_main");

var $validate = array(
	'id_user_main' => array(
		'reference_to' => array('The constraint to user_main.id isn\'t respected.','user_main', 'id')
	),
	'level' => array(
		'numeric' => array('This must be an int.')
	),
	'id_cleaner_main' => array(
		'reference_to' => array('The constraint to cleaner_main.id isn\'t respected.','cleaner_main', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
