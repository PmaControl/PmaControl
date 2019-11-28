<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class archive extends Model
{
var $schema = "CREATE TABLE `archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cleaner_main` int(11) NOT NULL,
  `id_backup_storage_area` int(11) NOT NULL,
  `is_crypted` int(11) NOT NULL,
  `md5_sql` char(32) NOT NULL,
  `size_sql` bigint(20) unsigned NOT NULL,
  `md5_compressed` char(32) NOT NULL,
  `size_compressed` int(11) NOT NULL,
  `md5_crypted` char(32) NOT NULL,
  `md5_remote` char(32) NOT NULL,
  `size_remote` bigint(20) unsigned NOT NULL,
  `size_crypted` bigint(20) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `time_to_compress` int(11) NOT NULL,
  `time_to_crypt` int(11) NOT NULL,
  `time_to_transfert` int(11) NOT NULL,
  `pathfile` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_backup_storage_area` (`id_backup_storage_area`),
  KEY `id_cleaner_main` (`id_cleaner_main`),
  CONSTRAINT `archive_ibfk_1` FOREIGN KEY (`id_backup_storage_area`) REFERENCES `backup_storage_area` (`id`),
  CONSTRAINT `archive_ibfk_2` FOREIGN KEY (`id_cleaner_main`) REFERENCES `cleaner_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("size_crypted","id_backup_storage_area","size_compressed","date","is_crypted","time_to_compress","id","time_to_crypt","size_sql","size_remote","id_cleaner_main","time_to_transfert","md5_crypted","md5_sql","md5_remote","md5_compressed","pathfile");

var $validate = array(
	'size_crypted' => array(
		'numeric' => array('This must be an int.')
	),
	'id_backup_storage_area' => array(
		'reference_to' => array('The constraint to backup_storage_area.id isn\'t respected.','backup_storage_area', 'id')
	),
	'size_compressed' => array(
		'numeric' => array('This must be an int.')
	),
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'is_crypted' => array(
		'numeric' => array('This must be an int.')
	),
	'time_to_compress' => array(
		'numeric' => array('This must be an int.')
	),
	'time_to_crypt' => array(
		'numeric' => array('This must be an int.')
	),
	'size_sql' => array(
		'numeric' => array('This must be an int.')
	),
	'size_remote' => array(
		'numeric' => array('This must be an int.')
	),
	'id_cleaner_main' => array(
		'reference_to' => array('The constraint to cleaner_main.id isn\'t respected.','cleaner_main', 'id')
	),
	'time_to_transfert' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
