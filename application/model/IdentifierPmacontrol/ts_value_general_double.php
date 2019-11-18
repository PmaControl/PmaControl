<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class ts_value_general_double extends Model
{
var $schema = "CREATE TABLE `ts_value_general_double` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_ts_variable` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `value` double NOT NULL,
  PRIMARY KEY (`id`,`date`),
  KEY `id_mysql_server` (`id_mysql_server`,`id_ts_variable`,`date`)
) ENGINE=ROCKSDB DEFAULT CHARSET=latin1
 PARTITION BY RANGE (to_days(`date`))
(PARTITION `p737600` VALUES LESS THAN (737600) ENGINE = ROCKSDB,
 PARTITION `p737601` VALUES LESS THAN (737601) ENGINE = ROCKSDB)";

var $field = array("id_mysql_server","id_ts_variable","date","value","id");

var $validate = array(
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'id_ts_variable' => array(
		'reference_to' => array('The constraint to ts_variable.id isn\'t respected.','ts_variable', 'id')
	),
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
);

function get_validate()
{
return $this->validate;
}
}
