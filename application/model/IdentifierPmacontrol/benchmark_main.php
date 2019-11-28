<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class benchmark_main extends Model
{
var $schema = "CREATE TABLE `benchmark_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_user_main` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `sysbench_version` varchar(64) NOT NULL,
  `threads` text NOT NULL,
  `tables_count` int(11) NOT NULL,
  `table_size` int(11) NOT NULL,
  `max_time` int(11) NOT NULL,
  `mode` varchar(10) NOT NULL,
  `read_only` char(3) NOT NULL,
  `status` varchar(20) NOT NULL,
  `progression` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mysql_server` (`id_mysql_server`),
  CONSTRAINT `benchmark_main_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id_user_main","date","tables_count","date_start","table_size","id","progression","date_end","max_time","id_mysql_server","read_only","mode","status","sysbench_version","threads");

var $validate = array(
	'id_user_main' => array(
		'reference_to' => array('The constraint to user_main.id isn\'t respected.','user_main', 'id')
	),
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'tables_count' => array(
		'numeric' => array('This must be an int.')
	),
	'date_start' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'table_size' => array(
		'numeric' => array('This must be an int.')
	),
	'progression' => array(
		'decimal' => array('This must be a float.')
	),
	'date_end' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'max_time' => array(
		'numeric' => array('This must be an int.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
