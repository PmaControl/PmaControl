<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class environment extends Model
{
var $schema = "CREATE TABLE `environment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(20) NOT NULL,
  `key` char(13) NOT NULL,
  `class` varchar(50) NOT NULL,
  `letter` char(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `libelle` (`libelle`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1";

var $field = array("id","letter","key","libelle","class");

var $validate = array(
);

function get_validate()
{
return $this->validate;
}
}
