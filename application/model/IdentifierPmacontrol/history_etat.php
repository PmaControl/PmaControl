<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class history_etat extends Model
{
var $schema = "CREATE TABLE `history_etat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8";

var $field = array("id","libelle");

var $validate = array(
);

function get_validate()
{
return $this->validate;
}
}
