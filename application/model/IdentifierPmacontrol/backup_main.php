<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class backup_main extends Model
{
var $schema = "CREATE TABLE `backup_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_backup_storage_area` int(11) NOT NULL,
  `id_backup_type` int(11) NOT NULL,
  `id_crontab` int(11) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8mb4 NOT NULL,
  `database` text NOT NULL COMMENT 'if = 0 we backup all, else database coma separated',
  `date_inserted` datetime NOT NULL,
  `is_active` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_backup_storage_area` (`id_backup_storage_area`),
  KEY `id_backup_type` (`id_backup_type`),
  KEY `id_crontab` (`id_crontab`),
  KEY `id_mysql_server` (`id_mysql_server`) USING BTREE,
  CONSTRAINT `backup_main_ibfk_1` FOREIGN KEY (`id_backup_storage_area`) REFERENCES `backup_storage_area` (`id`),
  CONSTRAINT `backup_main_ibfk_2` FOREIGN KEY (`id_backup_type`) REFERENCES `backup_type` (`id`),
  CONSTRAINT `backup_main_ibfk_3` FOREIGN KEY (`id_crontab`) REFERENCES `crontab` (`id`),
  CONSTRAINT `backup_main_ibfk_4` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id_backup_storage_area","date_inserted","id_backup_type","is_active","id_crontab","id","id_mysql_server","name","database");

var $validate = array(
	'id_backup_storage_area' => array(
		'reference_to' => array('The constraint to backup_storage_area.id isn\'t respected.','backup_storage_area', 'id')
	),
	'date_inserted' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'id_backup_type' => array(
		'reference_to' => array('The constraint to backup_type.id isn\'t respected.','backup_type', 'id')
	),
	'is_active' => array(
		'numeric' => array('This must be an int.')
	),
	'id_crontab' => array(
		'reference_to' => array('The constraint to crontab.id isn\'t respected.','crontab', 'id')
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
