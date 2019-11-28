<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class ts_max_date extends Model
{
var $schema = "CREATE TABLE `ts_max_date` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_daemon_main` int(11) NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `date_p1` datetime NOT NULL,
  `date_p2` datetime NOT NULL,
  `date_p3` datetime NOT NULL,
  `date_p4` datetime NOT NULL,
  `id_ts_file` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server_2` (`id_mysql_server`,`id_ts_file`),
  UNIQUE KEY `id_mysql_server` (`id_mysql_server`,`id_ts_file`,`date`),
  UNIQUE KEY `date` (`date`,`id_ts_file`,`id_mysql_server`),
  KEY `id_ts_file` (`id_ts_file`),
  CONSTRAINT `ts_max_date_ibfk_2` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ts_max_date_ibfk_3` FOREIGN KEY (`id_ts_file`) REFERENCES `ts_file` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id_daemon_main","date_p3","id_mysql_server","date_p4","date","id_ts_file","date_p1","id","date_p2");

var $validate = array(
	'id_daemon_main' => array(
		'reference_to' => array('The constraint to daemon_main.id isn\'t respected.','daemon_main', 'id')
	),
	'date_p3' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'date_p4' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'id_ts_file' => array(
		'reference_to' => array('The constraint to ts_file.id isn\'t respected.','ts_file', 'id')
	),
	'date_p1' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'date_p2' => array(
		'dateTime' => array('This must be a datetime.')
	),
);

function get_validate()
{
return $this->validate;
}
}
