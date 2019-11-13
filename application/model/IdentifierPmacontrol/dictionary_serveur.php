<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class dictionary_serveur extends Model
{
var $schema = "CREATE TABLE `dictionary_serveur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` char(15) NOT NULL,
  `dns` varchar(200) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

var $field = array("id","ip","dns","comment");

var $validate = array(
	'ip' => array(
		'ip' => array('your IP is not valid')
	),
);

function get_validate()
{
return $this->validate;
}
}
