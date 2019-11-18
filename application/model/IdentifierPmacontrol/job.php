<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class job extends Model
{
var $schema = "CREATE TABLE `job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `class` varchar(64) NOT NULL,
  `method` varchar(64) NOT NULL,
  `param` text NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime DEFAULT NULL,
  `pid` int(11) NOT NULL,
  `log` varchar(255) NOT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'NOT STARTED',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","date_start","date_end","pid","status","uuid","class","method","log","param");

var $validate = array(
	'date_start' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'date_end' => array(
		'dateTime' => array('This must be a datetime.')
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
