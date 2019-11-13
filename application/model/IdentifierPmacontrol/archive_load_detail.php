<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class archive_load_detail extends Model
{
var $schema = "CREATE TABLE `archive_load_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_archive` int(11) NOT NULL,
  `id_archive_load` int(11) NOT NULL,
  `status` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `md5_sql` char(32) NOT NULL DEFAULT '',
  `md5_compressed` char(32) NOT NULL DEFAULT '',
  `md5_crypted` char(32) NOT NULL DEFAULT '',
  `md5_remote` char(32) NOT NULL DEFAULT '',
  `size_sql` bigint(20) unsigned NOT NULL DEFAULT 0,
  `size_compressed` bigint(20) unsigned NOT NULL DEFAULT 0,
  `size_remote` bigint(20) unsigned NOT NULL DEFAULT 0,
  `size_crypted` bigint(20) unsigned NOT NULL DEFAULT 0,
  `time_to_uncompress` int(11) NOT NULL DEFAULT 0,
  `time_to_decrypt` int(11) NOT NULL DEFAULT 0,
  `time_to_transfert` int(11) NOT NULL DEFAULT 0,
  `time_to_mysql` int(11) NOT NULL DEFAULT 0,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `error_msg` text NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_archive` (`id_archive`,`id_archive_load`),
  KEY `id_archive_load` (`id_archive_load`),
  CONSTRAINT `archive_load_detail_ibfk_1` FOREIGN KEY (`id_archive`) REFERENCES `archive` (`id`),
  CONSTRAINT `archive_load_detail_ibfk_2` FOREIGN KEY (`id_archive_load`) REFERENCES `archive_load` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("time_to_decrypt","size_compressed","id","time_to_transfert","size_remote","id_archive","time_to_mysql","size_crypted","id_archive_load","date_start","time_to_uncompress","date_end","size_sql","status","md5_sql","md5_compressed","md5_crypted","md5_remote","error_msg");

var $validate = array(
	'time_to_decrypt' => array(
		'numeric' => array('This must be an int.')
	),
	'size_compressed' => array(
		'numeric' => array('This must be an int.')
	),
	'time_to_transfert' => array(
		'numeric' => array('This must be an int.')
	),
	'size_remote' => array(
		'numeric' => array('This must be an int.')
	),
	'id_archive' => array(
		'reference_to' => array('The constraint to archive.id isn\'t respected.','archive', 'id')
	),
	'time_to_mysql' => array(
		'numeric' => array('This must be an int.')
	),
	'size_crypted' => array(
		'numeric' => array('This must be an int.')
	),
	'id_archive_load' => array(
		'reference_to' => array('The constraint to archive_load.id isn\'t respected.','archive_load', 'id')
	),
	'date_start' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'time_to_uncompress' => array(
		'numeric' => array('This must be an int.')
	),
	'date_end' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'size_sql' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
