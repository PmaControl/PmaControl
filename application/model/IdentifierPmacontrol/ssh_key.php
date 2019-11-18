<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class ssh_key extends Model
{
var $schema = "CREATE TABLE `ssh_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `added_on` datetime NOT NULL,
  `fingerprint` char(100) NOT NULL,
  `user` varchar(64) NOT NULL,
  `public_key` text NOT NULL,
  `private_key` text NOT NULL,
  `type` varchar(20) DEFAULT NULL,
  `bit` int(11) NOT NULL,
  `comment` varchar(255) NOT NULL DEFAULT 'no comment',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fingerprint` (`fingerprint`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("added_on","bit","id","type","user","name","fingerprint","comment","public_key","private_key");

var $validate = array(
	'added_on' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'bit' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
