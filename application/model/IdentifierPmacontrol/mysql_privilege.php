<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class mysql_privilege extends Model
{
var $schema = "CREATE TABLE `mysql_privilege` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(25) NOT NULL,
  `privilege` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","type","privilege");

var $validate = array(
);

function get_validate()
{
return $this->validate;
}
}
