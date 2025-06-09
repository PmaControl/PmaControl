<?php

use \Glial\Synapse\FactoryController;


echo "=====".$data['server']['display_name']." (".$data['server']['ip'].":".$data['server']['port'].") =====\n";



echo "==== Cluster ====\n";

echo "Chaque serveur affiché est rattaché dynamiquement à un cluster identifié, ce qui permet de visualiser rapidement sa répartition dans l’architecture globale.";


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


echo "\n==== Le top 10 des tables les plus utilisé ====\n";


FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "schema_table_statistics"));


echo "==== Résumé des statistiques d'activité par hôte client ====\n";
echo "\n\n";

/**
 * 
 TRES IMPORTANT : écris mois ta reponse au format dokuwiki

voici un tableau générer "host summary", provenant de SYS pour MariaDB. fais moi un analyse, ne tient pas compte des colonnes current_memory et total_memory_allocated,

si tu dois utilisé des titres tu doit démarrer depuis "=== titre ===" les titres avec ==== sont déjà utilisé
 * 
 * 
 */

FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "host_summary"));


/*
echo "\n\n";
echo "==== Auto Increment ====\n";

echo "Lorsque la colonne AUTO_INCREMENT atteint 100% de sa capacité (valeur maximale), le serveur ne peut plus attribuer de nouveaux identifiants : toute insertion de ligne provoque alors une erreur. En pratique, MySQL/MariaDB renvoie des messages tels que ''ERROR 1062 (23000): Duplicate entry ‘2147483647’ for key ‘PRIMARY’ ou ERROR 1467 (HY000): Failed to read auto-increment value from storage engine''. Ces erreurs indiquent que la prochaine valeur d’auto-incrément dépasserait la limite du type (par exemple 2 147 483 647 pour un ''INT'' signé) et est considérée comme un doublon ou invalide. En conséquence, les requêtes ''INSERT'' sur la table échouent, et l’application se retrouve dans l’incapacité d’ajouter de nouvelles données tant que le problème n’est pas résolu. Pour corriger ce problème, il faut augmenter la plage du champ incrémenté en changeant son type. Par exemple, convertir un ''INT SIGNED'' en ''INT UNSIGNED'' double la capacité disponible (car on supprime l’usage des valeurs négatives)
On peut aussi passer à ''BIGINT'' (de préférence ''BIGINT UNSIGNED'') pour étendre encore plus largement la limite. Le manuel MySQL rappelle d’ailleurs qu’on doit utiliser ''UNSIGNED'' pour élargir la plage d’un champ auto-incrément dès que le type initial atteint sa limite
Sur une table ''InnoDB'' volumineuse, un ''ALTER TABLE'' traditionnel serait long et bloquerait l’accès. On utilise alors l’outil ''pt-online-schema-change'' (Percona Toolkit), qui ne fonctionne qu’avec les tables InnoDB : il crée en arrière-plan une copie de la table modifiée (colonne convertie), copie les données par petits lots en maintenant des triggers pour synchroniser les changements, puis effectue un RENAME TABLE atomique pour basculer. Cette procédure en ligne permet à la table de rester accessible (en lecture, et sans doute en écriture via les triggers) durant la migration, minimisant ainsi les interruptions de service
. ";

echo "On affiche ici uniquement les valeurs dépassant les 50% de remplissage :\n";

FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "schema_auto_increment_columns"));
*/


/*
echo "==== Index ====\n";
echo "\n\n";

echo "=== Analyse des index redondants ===\n";
echo "\n\n";


echo "Un index est considéré comme redondant lorsqu’un autre index existant couvre déjà les mêmes colonnes dans le même ordre, voire plus.

Supprimer ces index redondants permet de :

  * Réduire la taille des fichiers d’index sur disque,
  * Accélérer les opérations de modification (INSERT, UPDATE, DELETE), car chaque index ajouté implique une surcharge,
  * Limiter la consommation mémoire (notamment pour les caches d’index),
  * Faciliter la maintenance de la base en réduisant la complexité du schéma.

De plus, éviter les index trop larges ou inutiles contribue à une meilleure performance globale et à un temps d’analyse de requêtes plus court, tout en minimisant l’empreinte de stockage.
";

$gg = FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "schema_redundant_indexes"));
*/


/*
echo "=== Analyse des index non utilisé ===\n";
echo "\n\n";


echo "
Le tableau schema_unused_indexes du schéma sys identifie les index qui existent mais ne sont jamais utilisés par le moteur MariaDB (ni dans des lectures, ni dans des plans d'exécution optimisés).

Conserver de tels index a plusieurs inconvénients :

  * Occupation inutile de l’espace disque,
  * Surcharge lors des écritures (chaque modification d’une table met aussi à jour tous ses index),
  * Consommation mémoire excessive (s’ils sont chargés en cache sans utilité réelle),
  * Complexité accrue du schéma, ce qui nuit à la lisibilité et à la maintenance.

La suppression des index inutilisés permet donc d’améliorer les performances, de réduire les coûts en ressources (I/O, RAM) et de simplifier l’administration de la base.

⚠️  Remarque importante : les données de ce tableau sont fiables uniquement si le serveur tourne depuis un moment avec la surveillance des index activée (user/statistics), sans redémarrage récent, et avec une charge représentative de l’activité réelle. Les données sont remise à zéro après chaque redemarrage du serveur.

";

$gg = FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "schema_unused_indexes"));
*/

echo "==== Requête les plus consomatrice ====\n";
echo "\n\n";

echo "Ce tableau fournit une vue synthétique et agrégée des requêtes SQL exécutées sur le serveur, en regroupant les requêtes similaires par leur structure (sans tenir compte des valeurs littérales). Il inclut des statistiques essentielles telles que le nombre d'exécutions (''exec_count''), le temps total et moyen d'exécution (''total_latency'', ''avg_latency''), le nombre de lectures disque/mémoire (''rows_sent'', ''rows_examined''), ou encore l’utilisation des index. Ce tableau permet ainsi d’identifier rapidement les requêtes les plus coûteuses ou les plus fréquentes, aidant à prioriser les optimisations et à surveiller les performances globales du serveur SQL.";

echo "Les requêtes dans ce tableau avec une ''*'' dans la colonne ''table_scan'' sont à traiter en priorité";

$gg = FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "statement_analysis"));



echo "==== Les erreurs ====\n";

$gg = FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "statements_with_errors_or_warnings__errors"));


echo "==== Les warnings ====\n";

$gg = FactoryController::addNode("mysqlsys", "export", array($data['server']['id'], "statements_with_errors_or_warnings__warnings"));


FactoryController::addNode("audit", "queryCache", array($data['server']['id']));

