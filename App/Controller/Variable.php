<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *

 *
 *
 *  */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Extraction2;
use App\Library\Debug;

class Variable extends Controller
{

    public function index($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        //$testArray = Extraction2::display(array("variables::is_proxysql"));

        // on retire les PROXY
        $servers = array();
        $list_server = "SELECT distinct ID FROM mysql_server WHERE is_proxy=0";

        if (!empty($_GET['id_mysql_server'])) {
            $list_server = intval($_GET['id_mysql_server']);
        }


        $variable = '';
        if (!empty($_GET['variable'])) {
            $variable = ' AND `variable_name` ="'.$_GET['variable'].'" ';
        }



        $sql = "with z as (select id_mysql_server,variable_name from global_variable FOR SYSTEM_TIME ALL WHERE id_mysql_server IN (".$list_server.")
        $variable
GROUP BY id_mysql_server,variable_name having count(1) > 1)
 SELECT a.id_mysql_server, a.variable_name, a.value,date(ROW_START) as date, DATE_FORMAT(ROW_START, '%H:%i:%s') as time, DATE_FORMAT(ROW_START, '%W') as day
 FROM global_variable FOR SYSTEM_TIME ALL a
 INNER JOIN z ON a.id_mysql_server=z.id_mysql_server and a.variable_name=z.variable_name
 order by a.ROW_START DESC,a.id_mysql_server, a.variable_name LIMIT 1000;";

        Debug::sql($sql);

        $res              = $db->sql_query($sql);
        $data['variable'] = array();
        while ($arr              = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['variable'][] = $arr;
        }
        
        $this->set('data', $data);
    }


    public function tsVariable($param)
    {

    }
}


/*








WITH
-- 1) Toutes les versions (history + current)
all_versions AS (
  SELECT
    id_mysql_server,
    variable_name,
    value,
    row_start
  FROM global_variable
  FOR SYSTEM_TIME ALL
),

-- 2) On identifie les couples qui ont au moins une ligne dans p0
has_p0 AS (
  SELECT DISTINCT
    id_mysql_server,
    variable_name
  FROM global_variable
  PARTITION (p0)
),

-- 3) On ajoute la colonne value_before grâce à LAG()
with_lag AS (
  SELECT
    id_mysql_server,
    variable_name,
    LAG(value) OVER (
      PARTITION BY id_mysql_server, variable_name
      ORDER BY row_start
    ) AS value_before,
    value           AS value_after,
    row_start       AS date
  FROM all_versions
)

-- 4) On ne garde que les changements pour les couples qui avaient du history (p0)
SELECT
  id_mysql_server,
  variable_name,
  value_before    AS `value(before)`,
  value_after     AS `value(now)`,
  date
FROM with_lag AS w
JOIN has_p0 USING(id_mysql_server, variable_name)
WHERE value_before IS NOT NULL
ORDER BY id_mysql_server, variable_name, date;

----------------------------------------------



MariaDB [pmacontrol]> select *,row_start AS `date` FROM global_variable PARTITION (p0) where variable_name ='query_cache_size' and id_mysql_server=1;
+-----+-----------------+------------------+----------+----------------------------+
| id  | id_mysql_server | variable_name    | value    | date                       |
+-----+-----------------+------------------+----------+----------------------------+
| 190 |               1 | query_cache_size | 0        | 2024-09-15 04:05:04.745837 |
| 190 |               1 | query_cache_size |          | 2025-04-08 17:30:37.661452 |
| 190 |               1 | query_cache_size | 0        | 2025-04-09 01:00:16.471327 |
| 190 |               1 | query_cache_size | 10485760 | 2025-06-06 14:42:40.767318 |
+-----+-----------------+------------------+----------+----------------------------+
4 rows in set (0,000 sec)


MariaDB [pmacontrol]> select *,row_start AS `date` FROM global_variable PARTITION (pn) where variable_name ='query_cache_size' and id_mysql_server=1;
+-----+-----------------+------------------+-------+----------------------------+
| id  | id_mysql_server | variable_name    | value | date                       |
+-----+-----------------+------------------+-------+----------------------------+
| 190 |               1 | query_cache_size | 0     | 2025-06-19 00:42:04.160724 |
+-----+-----------------+------------------+-------+----------------------------+





je veux une requette qui me retourne 4 lignes :

avec la date, la value avant, apres , id_mysql_server, variable_name. je suis pas interessé que par la première valeur donc si il y n'y pas de ligne dans la partition 0 je ne veux pas de resultat, on va faire ca apres pour chaque id_mysql_server et variable_name




WITH
-- 1) Versions filtrées pour le test
all_versions AS (
  SELECT
    id_mysql_server,
    variable_name,
    value,
    row_start
  FROM global_variable
  FOR SYSTEM_TIME ALL
  WHERE
    id_mysql_server     = 1
    AND variable_name   = 'query_cache_size'
),

-- 2) On vérifie qu'il existe bien de l'historique (partition p0)
has_p0 AS (
  SELECT 1
  FROM global_variable
  PARTITION (p0)
  WHERE
    id_mysql_server   = 1
    AND variable_name = 'query_cache_size'
  LIMIT 1
),

-- 3) Ajout de la colonne value_before
with_lag AS (
  SELECT
    id_mysql_server,
    variable_name,
    LAG(value) OVER (
      PARTITION BY id_mysql_server, variable_name
      ORDER BY row_start
    ) AS value_before,
    value       AS value_after,
    row_start   AS date
  FROM all_versions
)

-- 4) On ne garde que si has_p0 existe et value_before non NULL
SELECT
  id_mysql_server,
  variable_name,
  value_before    AS `value(before)`,
  value_after     AS `value(now)`,
  date
FROM with_lag
WHERE
  value_before IS NOT NULL
ORDER BY date;

*/