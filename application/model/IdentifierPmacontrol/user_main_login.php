<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class user_main_login extends Model
{
var $schema = "CREATE TABLE `user_main_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user_main` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `ip` char(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_agent` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `is_logged` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user_main` (`id_user_main`),
  CONSTRAINT `user_main_login_ibfk_1` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","is_logged","id_user_main","date","ip","user_agent");

var $validate = array(
	'is_logged' => array(
		'numeric' => array('This must be an int.')
	),
	'id_user_main' => array(
		'reference_to' => array('The constraint to user_main.id isn\'t respected.','user_main', 'id')
	),
	'date' => array(
		'dateTime' => array('This must be a datetime.')
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
