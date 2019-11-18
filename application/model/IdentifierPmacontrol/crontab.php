<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class crontab extends Model
{
var $schema = "CREATE TABLE `crontab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `minute` char(10) NOT NULL,
  `hour` char(10) NOT NULL,
  `day_of_month` char(10) NOT NULL,
  `month` char(10) NOT NULL,
  `day_of_week` char(10) NOT NULL,
  `command` text NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("id","hour","day_of_month","month","day_of_week","minute","comment","command");

var $validate = array(
);

function get_validate()
{
return $this->validate;
}
}
