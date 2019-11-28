<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class daemon_main extends Model
{
var $schema = "CREATE TABLE `daemon_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `date` datetime NOT NULL,
  `pid` int(11) NOT NULL,
  `id_user_main` int(11) NOT NULL,
  `log_file` varchar(255) NOT NULL,
  `refresh_time` int(11) NOT NULL,
  `thread_concurency` int(11) NOT NULL,
  `max_delay` int(11) NOT NULL,
  `class` varchar(64) NOT NULL,
  `method` varchar(64) NOT NULL,
  `params` varchar(255) NOT NULL,
  `debug` int(11) NOT NULL,
  `queue_number` int(11) NOT NULL,
  `queue_key` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1";

var $field = array("date","thread_concurency","debug","pid","max_delay","queue_number","id_user_main","id","queue_key","refresh_time","class","method","name","params","log_file");

var $validate = array(
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'thread_concurency' => array(
		'numeric' => array('This must be an int.')
	),
	'debug' => array(
		'numeric' => array('This must be an int.')
	),
	'pid' => array(
		'numeric' => array('This must be an int.')
	),
	'max_delay' => array(
		'numeric' => array('This must be an int.')
	),
	'queue_number' => array(
		'numeric' => array('This must be an int.')
	),
	'id_user_main' => array(
		'reference_to' => array('The constraint to user_main.id isn\'t respected.','user_main', 'id')
	),
	'queue_key' => array(
		'numeric' => array('This must be an int.')
	),
	'refresh_time' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
