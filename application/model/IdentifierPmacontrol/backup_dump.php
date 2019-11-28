<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class backup_dump extends Model
{
var $schema = "CREATE TABLE `backup_dump` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_backup_database` int(11) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `time` int(11) NOT NULL,
  `time_gz` int(11) NOT NULL,
  `time_transfered` int(11) NOT NULL,
  `database` varchar(30) CHARACTER SET utf8 NOT NULL,
  `file_name` varchar(191) CHARACTER SET utf8 NOT NULL,
  `size_file` bigint(20) NOT NULL,
  `size_gz` bigint(20) NOT NULL,
  `size_transfered` bigint(20) NOT NULL,
  `md5` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `md5_gz` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `md5_transfered` char(32) NOT NULL,
  `is_completed` int(11) NOT NULL,
  `master_data` text CHARACTER SET utf8 NOT NULL,
  `slave_data` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_end` (`date_end`),
  KEY `id_backup_database` (`id_backup_database`),
  CONSTRAINT `backup_dump_ibfk_1` FOREIGN KEY (`id_backup_database`) REFERENCES `backup_database` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

var $field = array("size_transfered","date_start","date_end","time","size_file","id","time_gz","size_gz","id_backup_database","is_completed","time_transfered","database","md5","md5_gz","md5_transfered","file_name","master_data","slave_data");

var $validate = array(
	'size_transfered' => array(
		'numeric' => array('This must be an int.')
	),
	'date_start' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'date_end' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'time' => array(
		'numeric' => array('This must be an int.')
	),
	'size_file' => array(
		'numeric' => array('This must be an int.')
	),
	'time_gz' => array(
		'numeric' => array('This must be an int.')
	),
	'size_gz' => array(
		'numeric' => array('This must be an int.')
	),
	'id_backup_database' => array(
		'reference_to' => array('The constraint to backup_database.id isn\'t respected.','backup_database', 'id')
	),
	'is_completed' => array(
		'numeric' => array('This must be an int.')
	),
	'time_transfered' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
