<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class menu_group extends Model
{
var $schema = "CREATE TABLE `menu_group` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1";

var $field = array("id","title");

var $validate = array(
);

function get_validate()
{
return $this->validate;
}
}
