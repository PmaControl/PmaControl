<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class link__mysql_database__mysql_replication_thread extends Model
{
var $schema = "CREATE TABLE `link__mysql_database__mysql_replication_thread` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_database` int(11) NOT NULL,
  `id_mysql_replication_thread` int(11) NOT NULL,
  `replicate_do_db` int(11) NOT NULL,
  `replicate_ignore_db` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_database` (`id_mysql_database`,`id_mysql_replication_thread`),
  KEY `id_mysql_replication_thread` (`id_mysql_replication_thread`),
  CONSTRAINT `link__mysql_database__mysql_replication_thread_ibfk_1` FOREIGN KEY (`id_mysql_database`) REFERENCES `mysql_database` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `link__mysql_database__mysql_replication_thread_ibfk_2` FOREIGN KEY (`id_mysql_replication_thread`) REFERENCES `mysql_replication_thread` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("replicate_ignore_db","id","id_mysql_database","id_mysql_replication_thread","replicate_do_db");

var $validate = array(
	'replicate_ignore_db' => array(
		'numeric' => array('This must be an int.')
	),
	'id_mysql_database' => array(
		'reference_to' => array('The constraint to mysql_database.id isn\'t respected.','mysql_database', 'id')
	),
	'id_mysql_replication_thread' => array(
		'reference_to' => array('The constraint to mysql_replication_thread.id isn\'t respected.','mysql_replication_thread', 'id')
	),
	'replicate_do_db' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
