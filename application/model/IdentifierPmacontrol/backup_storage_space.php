<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class backup_storage_space extends Model
{
var $schema = "CREATE TABLE `backup_storage_space` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_backup_storage_area` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `size` bigint(20) NOT NULL,
  `used` bigint(20) NOT NULL,
  `available` bigint(20) NOT NULL,
  `percent` int(11) NOT NULL,
  `backup` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_backup_storage_area` (`id_backup_storage_area`),
  CONSTRAINT `backup_storage_space_ibfk_1` FOREIGN KEY (`id_backup_storage_area`) REFERENCES `backup_storage_area` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("date","backup","size","used","id","available","id_backup_storage_area","percent");

var $validate = array(
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'backup' => array(
		'numeric' => array('This must be an int.')
	),
	'size' => array(
		'numeric' => array('This must be an int.')
	),
	'used' => array(
		'numeric' => array('This must be an int.')
	),
	'available' => array(
		'numeric' => array('This must be an int.')
	),
	'id_backup_storage_area' => array(
		'reference_to' => array('The constraint to backup_storage_area.id isn\'t respected.','backup_storage_area', 'id')
	),
	'percent' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
