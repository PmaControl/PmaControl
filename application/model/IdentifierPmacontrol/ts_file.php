<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class ts_file extends Model
{
var $schema = "CREATE TABLE `ts_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_name` (`file_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1";

var $field = array("id","file_name");

var $validate = array(
);

function get_validate()
{
return $this->validate;
}
}
