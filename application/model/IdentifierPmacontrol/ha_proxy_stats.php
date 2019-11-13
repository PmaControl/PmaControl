<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class ha_proxy_stats extends Model
{
var $schema = "CREATE TABLE `ha_proxy_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_haproxy_main` int(11) NOT NULL,
  `id_haprox_input` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `server_status` char(10) NOT NULL,
  `server_role` int(11) NOT NULL,
  `queue_cur` int(11) NOT NULL,
  `queue_max` int(11) NOT NULL,
  `queue_limit` int(11) NOT NULL,
  `session_rate_cur` int(11) NOT NULL,
  `session_rate_max` int(11) NOT NULL,
  `session_rate_limit` int(11) NOT NULL,
  `session_cur` int(11) NOT NULL,
  `session_max` int(11) NOT NULL,
  `session_limit` int(11) NOT NULL,
  `bytes_in` int(11) NOT NULL,
  `bytes_out` int(11) NOT NULL,
  `enabled` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_haproxy_main` (`id_haproxy_main`),
  KEY `id_haprox_input` (`id_haprox_input`),
  CONSTRAINT `ha_proxy_stats_ibfk_1` FOREIGN KEY (`id_haproxy_main`) REFERENCES `haproxy_main` (`id`),
  CONSTRAINT `ha_proxy_stats_ibfk_2` FOREIGN KEY (`id_haprox_input`) REFERENCES `haproxy_main_input` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("session_max","session_rate_cur","id","session_limit","server_role","session_rate_max","id_haproxy_main","bytes_in","queue_cur","session_rate_limit","id_haprox_input","bytes_out","queue_max","session_cur","date","enabled","queue_limit","server_status");

var $validate = array(
	'session_max' => array(
		'numeric' => array('This must be an int.')
	),
	'session_rate_cur' => array(
		'numeric' => array('This must be an int.')
	),
	'session_limit' => array(
		'numeric' => array('This must be an int.')
	),
	'server_role' => array(
		'numeric' => array('This must be an int.')
	),
	'session_rate_max' => array(
		'numeric' => array('This must be an int.')
	),
	'id_haproxy_main' => array(
		'reference_to' => array('The constraint to haproxy_main.id isn\'t respected.','haproxy_main', 'id')
	),
	'bytes_in' => array(
		'numeric' => array('This must be an int.')
	),
	'queue_cur' => array(
		'numeric' => array('This must be an int.')
	),
	'session_rate_limit' => array(
		'numeric' => array('This must be an int.')
	),
	'id_haprox_input' => array(
		'reference_to' => array('The constraint to haprox_input.id isn\'t respected.','haprox_input', 'id')
	),
	'bytes_out' => array(
		'numeric' => array('This must be an int.')
	),
	'queue_max' => array(
		'numeric' => array('This must be an int.')
	),
	'session_cur' => array(
		'numeric' => array('This must be an int.')
	),
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'enabled' => array(
		'numeric' => array('This must be an int.')
	),
	'queue_limit' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
