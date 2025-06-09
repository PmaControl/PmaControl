<?php

use \Glial\Synapse\FactoryController;


echo "=====".$data['server']['display_name']." (".$data['server']['ip'].":".$data['server']['port'].") =====\n";



// dessin du cluster

/*
echo "\n<svgimage>\n";
echo $data['svg'];
echo "</svgimage>\n";
*/

echo "\n==== Les moteurs de stockage ====\n";


FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "engines"));



echo "\n==== Le top 10 des tables les plus volumineurse ====\n";



FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "top10tables"));





if ($data['server']['id'] != "16")
{
  echo "\n==== Le top 10 des tables les plus utilisé ====\n";
  FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "schema_table_statistics"));


  echo "==== Résumé des statistiques d'activité par hôte client ====\n";
  echo "\n\n";
  FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "host_summary"));


  echo "==== Requête les plus consomatrice ====\n";
  echo "\n\n";

  echo "Ce tableau fournit une vue synthétique et agrégée des requêtes SQL exécutées sur le serveur, en regroupant les requêtes similaires par leur structure (sans tenir compte des valeurs littérales). Il inclut des statistiques essentielles telles que le nombre d'exécutions (''exec_count''), le temps total et moyen d'exécution (''total_latency'', ''avg_latency''), le nombre de lectures disque/mémoire (''rows_sent'', ''rows_examined''), ou encore l’utilisation des index. Ce tableau permet ainsi d’identifier rapidement les requêtes les plus coûteuses ou les plus fréquentes, aidant à prioriser les optimisations et à surveiller les performances globales du serveur SQL.";

  echo "Les requêtes dans ce tableau avec une ''*'' dans la colonne ''table_scan'' sont à traiter en priorité";

  $gg = FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "statement_analysis"));



  echo "==== Les erreurs ====\n";

  $gg = FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "statements_with_errors_or_warnings__errors"));


  echo "==== Les warnings ====\n";

  $gg = FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "statements_with_errors_or_warnings__warnings"));



}









FactoryController::addNode("audit", "queryCache", array($data['server']['id']));

