<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class tag extends Model
{
var $schema = "CREATE TABLE `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) NOT NULL,
  `background` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id","color","background","name");

var $validate = array(
);

function get_validate()
{
return $this->validate;
}
}
