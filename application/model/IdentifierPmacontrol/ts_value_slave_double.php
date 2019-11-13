<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class ts_value_slave_double extends Model
{
var $schema = "CREATE TABLE `ts_value_slave_double` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_ts_variable` int(11) NOT NULL,
  `connection_name` varchar(64) NOT NULL,
  `date` datetime NOT NULL,
  `value` double NOT NULL,
  PRIMARY KEY (`id`,`date`),
  KEY `id_mysql_server` (`id_mysql_server`,`id_ts_variable`,`date`)
) ENGINE=ROCKSDB DEFAULT CHARSET=latin1
 PARTITION BY RANGE (to_days(`date`))
(PARTITION `p737518` VALUES LESS THAN (737518) ENGINE = ROCKSDB,
 PARTITION `p737519` VALUES LESS THAN (737519) ENGINE = ROCKSDB)";

var $field = array("date","id","value","id_mysql_server","id_ts_variable","connection_name");

var $validate = array(
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'id_ts_variable' => array(
		'reference_to' => array('The constraint to ts_variable.id isn\'t respected.','ts_variable', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
