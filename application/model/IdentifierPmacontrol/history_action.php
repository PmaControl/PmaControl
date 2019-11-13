<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class history_action extends Model
{
var $schema = "CREATE TABLE `history_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `action` varchar(80) NOT NULL,
  `point` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","point","title","action");

var $validate = array(
	'point' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
