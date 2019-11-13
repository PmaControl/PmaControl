<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class webservice_history_main extends Model
{
var $schema = "CREATE TABLE `webservice_history_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user_main` int(11) NOT NULL,
  `user` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `date` datetime NOT NULL,
  `logon` int(11) NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user_main` (`id_user_main`),
  CONSTRAINT `webservice_history_main_ibfk_1` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("date","id","logon","id_user_main","password","user","message");

var $validate = array(
	'date' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'logon' => array(
		'numeric' => array('This must be an int.')
	),
	'id_user_main' => array(
		'reference_to' => array('The constraint to user_main.id isn\'t respected.','user_main', 'id')
	),
);

function get_validate()
{
return $this->validate;
}
}
