<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class pmacli_drain_process extends Model
{
var $schema = "CREATE TABLE `pmacli_drain_process` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cleaner_main` int(11) NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `time` float NOT NULL,
  `item_deleted` int(11) NOT NULL,
  `time_by_item` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_mysql_server` (`id_mysql_server`),
  KEY `date_end` (`date_end`),
  CONSTRAINT `pmacli_drain_process_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id_mysql_server","item_deleted","time_by_item","date_start","id","date_end","id_cleaner_main","time","name");

var $validate = array(
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'item_deleted' => array(
		'numeric' => array('This must be an int.')
	),
	'time_by_item' => array(
		'decimal' => array('This must be a float.')
	),
	'date_start' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'date_end' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'id_cleaner_main' => array(
		'reference_to' => array('The constraint to cleaner_main.id isn\'t respected.','cleaner_main', 'id')
	),
	'time' => array(
		'decimal' => array('This must be a float.')
	),
);

function get_validate()
{
return $this->validate;
}
}
