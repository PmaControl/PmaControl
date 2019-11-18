<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class backup_type extends Model
{
var $schema = "CREATE TABLE `backup_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `libelle` (`libelle`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8";

var $field = array("id","libelle");

var $validate = array(
);

function get_validate()
{
return $this->validate;
}
}
