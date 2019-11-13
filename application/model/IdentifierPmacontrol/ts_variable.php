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
) ENGINE=InnoDB AUTO_INCREMENT=2072 DEFAULT CHARSET=latin1";

var $field = array("id","id_ts_file","is_derived","is_dynamic","type","radical","from","name");

var $validate = array(
	'id_ts_file' => array(
		'reference_to' => array('The constraint to ts_file.id isn\'t respected.','ts_file', 'id')
	),
	'is_derived' => array(
		'numeric' => array('This must be an int.')
	),
	'is_dynamic' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
