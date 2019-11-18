<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class mysql_database extends Model
{
var $schema = "CREATE TABLE `mysql_database` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `tables` int(11) NOT NULL,
  `rows` bigint(20) NOT NULL,
  `data_length` bigint(20) NOT NULL,
  `data_free` bigint(20) NOT NULL,
  `index_length` bigint(20) NOT NULL,
  `collation_name` varchar(64) NOT NULL,
  `character_set_name` varchar(32) NOT NULL,
  `binlog_do_db` int(11) NOT NULL,
  `binlog_ignore_db` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`id_mysql_server`),
  KEY `id_mysql_server` (`id_mysql_server`),
  CONSTRAINT `mysql_database_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("tables","rows","id","data_length","binlog_do_db","id_mysql_server","data_free","binlog_ignore_db","index_length","character_set_name","collation_name","name");

var $validate = array(
	'tables' => array(
		'numeric' => array('This must be an int.')
	),
	'rows' => array(
		'numeric' => array('This must be an int.')
	),
	'data_length' => array(
		'numeric' => array('This must be an int.')
	),
	'binlog_do_db' => array(
		'numeric' => array('This must be an int.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'data_free' => array(
		'numeric' => array('This must be an int.')
	),
	'binlog_ignore_db' => array(
		'numeric' => array('This must be an int.')
	),
	'index_length' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
