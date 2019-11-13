<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class benchmark_config extends Model
{
var $schema = "CREATE TABLE `benchmark_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `threads` text NOT NULL,
  `lua_script` varchar(255) NOT NULL,
  `tables_count` int(11) NOT NULL,
  `table_size` int(11) NOT NULL,
  `max_time` int(11) NOT NULL,
  `mode` varchar(64) NOT NULL,
  `read_only` char(3) NOT NULL,
  `pid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("table_size","id","max_time","tables_count","pid","read_only","mode","lua_script","threads");

var $validate = array(
	'table_size' => array(
		'numeric' => array('This must be an int.')
	),
	'max_time' => array(
		'numeric' => array('This must be an int.')
	),
	'tables_count' => array(
		'numeric' => array('This must be an int.')
	),
	'pid' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
