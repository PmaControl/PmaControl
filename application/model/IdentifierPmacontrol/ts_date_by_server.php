<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class ts_date_by_server extends Model
{
var $schema = "CREATE TABLE `ts_date_by_server` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_ts_file` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`,`date`),
  UNIQUE KEY `id_mysql_server` (`id_mysql_server`,`id_ts_file`,`date`)
) ENGINE=ROCKSDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT
 PARTITION BY RANGE (to_days(`date`))
(PARTITION `p737518` VALUES LESS THAN (737518) ENGINE = ROCKSDB,
 PARTITION `p737519` VALUES LESS THAN (737519) ENGINE = ROCKSDB)";

var $field = array("date","id","id_mysql_server","id_ts_file");

var $validate = array(
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'id_mysql_server' => array(
		'reference_to' => array('The constraint to mysql_server.id isn\'t respected.','mysql_server', 'id')
	),
	'id_ts_file' => array(
		'reference_to' => array('The constraint to ts_file.id isn\'t respected.','ts_file', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
