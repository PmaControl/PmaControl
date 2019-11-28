<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class binlog_backup extends Model
{
var $schema = "CREATE TABLE `binlog_backup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `logfile_name` varchar(64) NOT NULL,
  `logfile_size` bigint(20) NOT NULL,
  `md5` char(32) NOT NULL,
  `date_backup` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server` (`id_mysql_server`,`logfile_name`),
  CONSTRAINT `binlog_backup_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("logfile_size","id","date_backup","id_mysql_server","md5","logfile_name");

var $validate = array(
	'logfile_size' => array(
		'numeric' => array('This must be an int.')
	),
	'date_backup' => array(
		'dateTime' => array('This must be a datetime.')
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
