<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class mysql_dump extends Model
{
var $schema = "CREATE TABLE `mysql_dump` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_backup_database` int(11) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `time` int(11) NOT NULL,
  `database` varchar(30) CHARACTER SET utf8 NOT NULL,
  `file_name` varchar(191) CHARACTER SET utf8 NOT NULL,
  `size` bigint(11) NOT NULL,
  `md5` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `is_gziped` int(11) NOT NULL DEFAULT 0,
  `is_available` int(11) NOT NULL DEFAULT 1,
  `is_completed` int(11) NOT NULL,
  `master_data` text CHARACTER SET utf8 NOT NULL,
  `slave_data` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mysql_serveur` (`id_mysql_server`),
  KEY `date_end` (`date_end`),
  KEY `id_backup_database` (`id_backup_database`),
  CONSTRAINT `mysql_dump_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

var $field = array("is_completed","date_start","size","date_end","id","time","is_gziped","id_mysql_server","is_available","id_backup_database","database","md5","file_name","master_data","slave_data");

var $validate = array(
	'is_completed' => array(
		'numeric' => array('This must be an int.')
	),
	'date_start' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'size' => array(
		'numeric' => array('This must be an int.')
	),
	'date_end' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'time' => array(
		'numeric' => array('This must be an int.')
	),
	'is_gziped' => array(
		'numeric' => array('This must be an int.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'is_available' => array(
		'numeric' => array('This must be an int.')
	),
	'id_backup_database' => array(
		'reference_to' => array('The constraint to backup_database.id isn\'t respected.','backup_database', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
