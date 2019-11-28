<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class mysql_replication_stats extends Model
{
var $schema = "CREATE TABLE `mysql_replication_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `version` varchar(20) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `date` datetime NOT NULL,
  `is_available` int(11) NOT NULL,
  `is_master` int(11) NOT NULL,
  `is_slave` int(11) NOT NULL,
  `ping` int(11) NOT NULL,
  `file` varchar(200) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT 0,
  `databases` varchar(200) NOT NULL DEFAULT '',
  `uptime` int(11) NOT NULL DEFAULT 0,
  `time_zone` varchar(50) NOT NULL,
  `binlog_format` varchar(20) NOT NULL,
  `ssh_ok` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_server` (`id_mysql_server`),
  CONSTRAINT `mysql_replication_stats_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='1.9.6'";

var $field = array("date","is_available","position","id","ssh_ok","is_master","id_mysql_server","is_slave","uptime","ping","binlog_format","version","time_zone","file","databases");

var $validate = array(
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'is_available' => array(
		'numeric' => array('This must be an int.')
	),
	'position' => array(
		'numeric' => array('This must be an int.')
	),
	'ssh_ok' => array(
		'numeric' => array('This must be an int.')
	),
	'is_master' => array(
		'numeric' => array('This must be an int.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'is_slave' => array(
		'numeric' => array('This must be an int.')
	),
	'uptime' => array(
		'numeric' => array('This must be an int.')
	),
	'ping' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
