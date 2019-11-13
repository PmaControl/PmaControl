<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class mysql_replication_thread extends Model
{
var $schema = "CREATE TABLE `mysql_replication_thread` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_replication_stats` int(11) NOT NULL,
  `relay_master_log_file` varchar(200) CHARACTER SET utf8 NOT NULL,
  `exec_master_log_pos` int(11) NOT NULL,
  `thread_io` varchar(65) NOT NULL,
  `thread_sql` varchar(65) NOT NULL,
  `thread_name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `time_behind` varchar(11) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `master_host` char(15) CHARACTER SET utf8 NOT NULL,
  `master_port` int(11) NOT NULL,
  `last_io_error` text NOT NULL,
  `last_sql_error` text NOT NULL,
  `last_sql_errno` int(11) NOT NULL,
  `last_io_errno` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_mysql_replication_stats` (`id_mysql_replication_stats`,`thread_name`),
  CONSTRAINT `mysql_replication_thread_ibfk_1` FOREIGN KEY (`id_mysql_replication_stats`) REFERENCES `mysql_replication_stats` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

var $field = array("id_mysql_replication_stats","last_sql_errno","exec_master_log_pos","last_io_errno","master_port","id","time_behind","master_host","thread_sql","thread_io","thread_name","relay_master_log_file","last_io_error","last_sql_error");

var $validate = array(
	'id_mysql_replication_stats' => array(
		'reference_to' => array('The constraint to mysql_replication_stats.id isn\'t respected.','mysql_replication_stats', 'id')
	),
	'last_sql_errno' => array(
		'numeric' => array('This must be an int.')
	),
	'exec_master_log_pos' => array(
		'numeric' => array('This must be an int.')
	),
	'last_io_errno' => array(
		'numeric' => array('This must be an int.')
	),
	'master_port' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
