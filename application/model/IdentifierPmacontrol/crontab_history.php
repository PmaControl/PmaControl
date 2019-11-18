<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class crontab_history extends Model
{
var $schema = "CREATE TABLE `crontab_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_crontab` int(11) NOT NULL,
  `date_started` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `code_retour` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_crontab` (`id_crontab`),
  CONSTRAINT `crontab_history_ibfk_1` FOREIGN KEY (`id_crontab`) REFERENCES `crontab` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("date_started","date_end","code_retour","id","id_crontab");

var $validate = array(
	'date_started' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'date_end' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'code_retour' => array(
		'numeric' => array('This must be an int.')
	),
	'id_crontab' => array(
		'reference_to' => array('The constraint to crontab.id isn\'t respected.','crontab', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
