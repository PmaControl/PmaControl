<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class haproxy_main extends Model
{
var $schema = "CREATE TABLE `haproxy_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(100) NOT NULL,
  `ip` char(15) NOT NULL,
  `vip` char(15) NOT NULL,
  `csv` varchar(250) NOT NULL,
  `stats_login` varchar(50) NOT NULL,
  `stats_password` varchar(50) NOT NULL,
  `private_key` varchar(250) NOT NULL,
  `user_private_key` varchar(25) NOT NULL,
  `path_conf` varchar(200) NOT NULL,
  `date_refresh` datetime NOT NULL,
  `config` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

var $field = array("id","date_refresh","ip","vip","user_private_key","stats_login","stats_password","hostname","path_conf","csv","private_key","config");

var $validate = array(
	'date_refresh' => array(
		'dateTime' => array('This must be a datetime.')
	),
	'ip' => array(
		'ip' => array('your IP is not valid')
	),
);

function get_validate()
{
return $this->validate;
}
}
