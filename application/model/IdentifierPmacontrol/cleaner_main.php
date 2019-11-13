<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class cleaner_main extends Model
{
var $schema = "CREATE TABLE `cleaner_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_backup_storage_area` int(11) DEFAULT 0,
  `id_user_main` int(11) NOT NULL,
  `database` varchar(64) NOT NULL,
  `libelle` varchar(50) NOT NULL,
  `main_table` varchar(64) NOT NULL,
  `query` text NOT NULL,
  `wait_time_in_sec` int(11) NOT NULL,
  `log_file` varchar(250) NOT NULL DEFAULT '',
  `cleaner_db` varchar(50) NOT NULL,
  `prefix` varchar(50) NOT NULL,
  `pid` int(11) NOT NULL DEFAULT 0,
  `limit` int(11) NOT NULL DEFAULT 1000,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`libelle`),
  KEY `id_mysql_server` (`id_mysql_server`),
  KEY `id_mysql_database` (`database`),
  CONSTRAINT `cleaner_main_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("limit","id","id_mysql_server","id_backup_storage_area","pid","id_user_main","wait_time_in_sec","libelle","cleaner_db","prefix","database","main_table","log_file","query");

var $validate = array(
	'limit' => array(
		'numeric' => array('This must be an int.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'id_backup_storage_area' => array(
		'reference_to' => array('The constraint to backup_storage_area.id isn\'t respected.','backup_storage_area', 'id')
	),
	'pid' => array(
		'numeric' => array('This must be an int.')
	),
	'id_user_main' => array(
		'reference_to' => array('The constraint to user_main.id isn\'t respected.','user_main', 'id')
	),
	'wait_time_in_sec' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
