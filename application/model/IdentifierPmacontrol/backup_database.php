<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class backup_database extends Model
{
var $schema = "CREATE TABLE `backup_database` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_backup_storage_area` int(11) NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `id_mysql_database` int(11) NOT NULL,
  `id_backup_type` int(11) NOT NULL,
  `id_crontab` int(11) NOT NULL,
  `is_active` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mysql_server` (`id_mysql_server`),
  KEY `id_mysql_database` (`id_mysql_database`),
  KEY `id_backup_type` (`id_backup_type`),
  KEY `id_backup_storage_area` (`id_backup_storage_area`),
  KEY `id_crontab` (`id_crontab`),
  CONSTRAINT `backup_database_ibfk_1` FOREIGN KEY (`id_backup_storage_area`) REFERENCES `backup_storage_area` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `backup_database_ibfk_2` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `backup_database_ibfk_3` FOREIGN KEY (`id_mysql_database`) REFERENCES `mysql_database` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `backup_database_ibfk_4` FOREIGN KEY (`id_backup_type`) REFERENCES `backup_type` (`id`),
  CONSTRAINT `backup_database_ibfk_5` FOREIGN KEY (`id_crontab`) REFERENCES `crontab` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id_backup_type","id","id_crontab","id_backup_storage_area","is_active","id_mysql_server","id_mysql_database");

var $validate = array(
	'id_backup_type' => array(
		'reference_to' => array('The constraint to backup_type.id isn\'t respected.','backup_type', 'id')
	),
	'id_crontab' => array(
		'reference_to' => array('The constraint to crontab.id isn\'t respected.','crontab', 'id')
	),
	'id_backup_storage_area' => array(
		'reference_to' => array('The constraint to backup_storage_area.id isn\'t respected.','backup_storage_area', 'id')
	),
	'is_active' => array(
		'numeric' => array('This must be an int.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'id_mysql_database' => array(
		'reference_to' => array('The constraint to mysql_database.id isn\'t respected.','mysql_database', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
