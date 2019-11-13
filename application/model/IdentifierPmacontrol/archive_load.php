<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class archive_load extends Model
{
var $schema = "CREATE TABLE `archive_load` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cleaner_main` int(11) NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `database` varchar(64) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime DEFAULT NULL,
  `progression` tinyint(4) NOT NULL,
  `duration` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `id_user_main` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("date_start","id","date_end","id_user_main","id_cleaner_main","progression","id_mysql_server","duration","pid","status","database");

var $validate = array(
	'date_start' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'date_end' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'id_user_main' => array(
		'reference_to' => array('The constraint to user_main.id isn\'t respected.','user_main', 'id')
	),
	'id_cleaner_main' => array(
		'reference_to' => array('The constraint to cleaner_main.id isn\'t respected.','cleaner_main', 'id')
	),
	'progression' => array(
		'numeric' => array('This must be an int.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'duration' => array(
		'numeric' => array('This must be an int.')
	),
	'pid' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
