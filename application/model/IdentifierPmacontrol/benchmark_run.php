<?php

namespace Application\Model\IdentifierPmacontrol;
use \Glial\Synapse\Model;
class benchmark_run extends Model
{
var $schema = "CREATE TABLE `benchmark_run` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_benchmark_main` int(11) NOT NULL,
  `date` date NOT NULL,
  `threads` int(11) NOT NULL,
  `read` int(11) NOT NULL,
  `write` int(11) NOT NULL,
  `other` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `transaction` int(11) NOT NULL,
  `error` int(11) NOT NULL,
  `time` double NOT NULL,
  `reponse_min` double NOT NULL,
  `reponse_max` double NOT NULL,
  `reponse_avg` double NOT NULL,
  `reponse_percentile95` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_benchmark_main` (`id_benchmark_main`),
  CONSTRAINT `benchmark_run_ibfk_1` FOREIGN KEY (`id_benchmark_main`) REFERENCES `benchmark_main` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

var $field = array("reponse_avg","read","error","id","reponse_percentile95","write","time","id_benchmark_main","other","reponse_min","date","total","reponse_max","threads","transaction");

var $validate = array(
	'read' => array(
		'numeric' => array('This must be an int.')
	),
	'error' => array(
		'numeric' => array('This must be an int.')
	),
	'write' => array(
		'numeric' => array('This must be an int.')
	),
	'id_benchmark_main' => array(
		'reference_to' => array('The constraint to benchmark_main.id isn\'t respected.','benchmark_main', 'id')
	),
	'other' => array(
		'numeric' => array('This must be an int.')
	),
	'date' => array(
		'date' => array('This must be a date.')
	),
	'total' => array(
		'numeric' => array('This must be an int.')
	),
	'threads' => array(
		'numeric' => array('This must be an int.')
	),
	'transaction' => array(
		'numeric' => array('This must be an int.')
	),
);

function get_validate()
{
return $this->validate;
}
}
