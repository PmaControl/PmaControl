<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class ts_variable extends Model
{
var $schema = "CREATE TABLE `ts_variable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_ts_file` int(11) NOT NULL DEFAULT 3,
  `name` varchar(64) NOT NULL,
  `type` enum('INT','DOUBLE','TEXT','') NOT NULL COMMENT '1 => int, 2 => double, 3 => text',
  `from` varchar(64) NOT NULL,
  `radical` char(10) NOT NULL,
  `is_derived` int(11) DEFAULT 1,
  `is_dynamic` int(11) DEFAULT 1 COMMENT 'only for global variables',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`from`)
) ENGINE=ROCKSDB AUTO_INCREMENT=2199 DEFAULT CHARSET=latin1";

var $field = array("is_dynamic","id","id_ts_file","is_derived","type","radical","name","from");

var $validate = array(
	'is_dynamic' => array(
		'numeric' => array('This must be an int.')
	),
	'id_ts_file' => array(
		'reference_to' => array('The constraint to ts_file.id isn\'t respected.','ts_file', 'id')
	),
	'is_derived' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
