<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class mysql_server extends Model
{
var $schema = "CREATE TABLE `mysql_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_client` int(11) NOT NULL,
  `id_environment` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `ip` char(15) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `hostname` varchar(200) NOT NULL DEFAULT '',
  `login` varchar(32) CHARACTER SET utf8 NOT NULL,
  `passwd` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `database` varchar(64) NOT NULL,
  `is_password_crypted` int(11) NOT NULL,
  `port` int(11) NOT NULL,
  `ssh_port` int(11) NOT NULL DEFAULT 22,
  `ssh_login` text CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '\'\'',
  `is_sudo` int(11) NOT NULL DEFAULT 0,
  `is_root` int(11) NOT NULL DEFAULT 1,
  `is_monitored` int(11) NOT NULL DEFAULT 1,
  `is_proxy` int(11) NOT NULL DEFAULT 0,
  `is_available` int(11) NOT NULL DEFAULT 0,
  `is_acknowledged` int(11) NOT NULL DEFAULT 0,
  `error` text NOT NULL DEFAULT '',
  `date_refresh` datetime NOT NULL DEFAULT current_timestamp(),
  `ssh_available` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `ip` (`ip`,`port`),
  KEY `id_client` (`id_client`),
  KEY `id_environment` (`id_environment`),
  CONSTRAINT `mysql_server_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `client` (`id`),
  CONSTRAINT `mysql_server_ibfk_2` FOREIGN KEY (`id_environment`) REFERENCES `environment` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

var $field = array("is_root","port","id_environment","is_monitored","date_refresh","ssh_port","is_proxy","ssh_available","is_available","id","is_sudo","is_acknowledged","is_password_crypted","id_client","ip","login","database","name","display_name","hostname","passwd","error","ssh_login");

var $validate = array(
	'is_root' => array(
		'numeric' => array('This must be an int.')
	),
	'port' => array(
		'numeric' => array('This must be an int.')
	),
	'id_environment' => array(
		'reference_to' => array('The constraint to environment.id isn\'t respected.','environment', 'id')
	),
	'is_monitored' => array(
		'numeric' => array('This must be an int.')
	),
	'date_refresh' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'ssh_port' => array(
		'numeric' => array('This must be an int.')
	),
	'is_proxy' => array(
		'numeric' => array('This must be an int.')
	),
	'ssh_available' => array(
		'numeric' => array('This must be an int.')
	),
	'is_available' => array(
		'numeric' => array('This must be an int.')
	),
	'is_sudo' => array(
		'numeric' => array('This must be an int.')
	),
	'is_acknowledged' => array(
		'numeric' => array('This must be an int.')
	),
	'is_password_crypted' => array(
		'numeric' => array('This must be an int.')
	),
	'id_client' => array(
		'reference_to' => array('The constraint to client.id isn\'t respected.','client', 'id')
	),
	'ip' => array(
		'ip' => array('your IP is not valid')
	),
);

function get_validate()
{
return $this->validate;
}
}
