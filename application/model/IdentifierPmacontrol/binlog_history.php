<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class binlog_history extends Model
{
var $schema = "CREATE TABLE `binlog_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_binlog_max` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `file` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_binlog_max` (`id_binlog_max`),
  CONSTRAINT `binlog_history_ibfk_1` FOREIGN KEY (`id_binlog_max`) REFERENCES `binlog_max` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("date","id","id_binlog_max","file");

var $validate = array(
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'id_binlog_max' => array(
		'reference_to' => array('The constraint to binlog_max.id isn\'t respected.','binlog_max', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
