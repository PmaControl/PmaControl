<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Mysql;
use App\Library\Debug;

/**
 * Class responsible for partition workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Partition extends Controller
{
    /**
     * Génère un ALTER TABLE équilibré par RANGE selon la distribution d'ID
     * 
     * Usage CLI :
     * php index.php partition/generate my_table 10
     */
    public function generate($param)
    {
        Debug::parseDebug($param);
        $this->view = false;

        // Étape 1 : Valider et extraire les paramètres
        $params = $this->extractParameters($param);
        if (!$params) {
            return;
        }

        // Étape 2 : Détecter le champ à partitionner
        $params['field'] = $this->detectField($params);
        if (empty($params['field'])) {
            echo "❌ Impossible de détecter un champ à partitionner. Passez le nom du champ en paramètre.\n";
            return;
        }

        // Étape 3 : Récupérer la distribution des données
        $data = $this->getDataDistribution($params);
        if (empty($data)) {
            echo "❌ Aucune donnée trouvée dans `{$params['table_target']}` (ou échantillon trop petit).\n";
            return;
        }

        // Étape 4 : Calculer les limites des partitions
        $partitions_limits = $this->calculatePartitionLimits($data, $params['nb_partitions'], $params['is_numeric']);

        // Étape 5 : Générer le SQL
        $sqlAlter = $this->generatePartitionSQL($params['table_target'], $params['field'], $partitions_limits, $params['is_numeric']);

        // Étape 6 : Sauvegarder le fichier
        $this->saveSQLToFile($sqlAlter, $params['table_target']);
    }

/**
 * Handle `extractParameters`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for extractParameters.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example extractParameters(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function extractParameters($param)
    {
        $id_mysql_server = isset($param[0]) ? $param[0] : null;
        $database        = isset($param[1]) ? $param[1] : null;
        $table_target    = isset($param[2]) ? $param[2] : null;
        $field           = isset($param[3]) && $param[3] !== '' ? $param[3] : null;
        $nb_partitions   = isset($param[4]) && (int)$param[4] > 0 ? (int)$param[4] : 4;
        $sample_ratio    = isset($param[5]) && is_numeric($param[5]) ? (float)$param[5] : 1.0;

        if (empty($database) || empty($table_target)) {
            echo "❌ Usage: php index.php partition/generate <id_mysql_server> <database> <table_target> [field] [nb_partitions]\n";
            echo "Exemple: php index.php partition/generate 1 my_db my_table id 8 0.1\n";
            return null;
        }

        if ($nb_partitions < 2) {
            echo "⚠️ nb_partitions must be >= 2. Forcing to 2.\n";
            $nb_partitions = 2;
        }

        if ($sample_ratio <= 0 || $sample_ratio > 1) {
            echo "⚠️ sample must be in (0,1]. Forcing to 1 (full table).\n";
            $sample_ratio = 1.0;
        }

        return compact('id_mysql_server', 'database', 'table_target', 'field', 'nb_partitions', 'sample_ratio');
    }

/**
 * Handle `detectField`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $params Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $params
 * @psalm-param array<int,mixed> $params
 * @return mixed Returned value for detectField.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example detectField(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function detectField($params)
    {
        $db = Mysql::getDbLink($params['id_mysql_server']);
        $field = $params['field'];

        if (empty($field)) {
            // 1) Chercher index PRIMARY
            $sql = "SELECT COLUMN_NAME FROM information_schema.STATISTICS
                    WHERE TABLE_SCHEMA = '" . $db->sql_real_escape_string($params['database']) . "'
                      AND TABLE_NAME = '" . $db->sql_real_escape_string($params['table_target']) . "'
                      AND INDEX_NAME = 'PRIMARY'
                    ORDER BY SEQ_IN_INDEX LIMIT 1";
            Debug::sql($sql);
            $res = $db->sql_query($sql);
            if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $field = $row['COLUMN_NAME'];
            }
        }

        if (empty($field)) {
            // 2) Chercher premier champ numérique
            $sql = "SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = '" . $db->sql_real_escape_string($params['database']) . "'
                      AND TABLE_NAME = '" . $db->sql_real_escape_string($params['table_target']) . "'
                    ORDER BY ORDINAL_POSITION";
            Debug::sql($sql);
            $res = $db->sql_query($sql);
            while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $dt = strtolower($row['DATA_TYPE']);
                if (in_array($dt, ['int', 'bigint', 'mediumint', 'smallint', 'tinyint', 'decimal', 'float', 'double'])) {
                    $field = $row['COLUMN_NAME'];
                    break;
                }
            }
        }

        return $field;
    }

/**
 * Retrieve `getDataDistribution`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $params Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $params
 * @psalm-param array<int,mixed> $params
 * @return mixed Returned value for getDataDistribution.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example getDataDistribution(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function getDataDistribution($params)
    {
        $db = Mysql::getDbLink($params['id_mysql_server']);
        $sql = "SELECT DATA_TYPE FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '" . $db->sql_real_escape_string($params['database']) . "'
                  AND TABLE_NAME = '" . $db->sql_real_escape_string($params['table_target']) . "'
                  AND COLUMN_NAME = '" . $db->sql_real_escape_string($params['field']) . "' LIMIT 1";
        Debug::sql($sql);
        $res = $db->sql_query($sql);
        $data_type = null;
        if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data_type = strtolower($row['DATA_TYPE']);
        }

        $params['is_numeric'] = in_array($data_type, ['int', 'bigint', 'mediumint', 'smallint', 'tinyint', 'decimal', 'float', 'double', 'year']);

        $groupBySql = "SELECT `{$params['field']}` AS val, COUNT(1) AS cnt
                       FROM `{$params['database']}`.`{$params['table_target']}`";

        if ($params['sample_ratio'] < 1.0) {
            $groupBySql .= " WHERE RAND() < " . (float)$params['sample_ratio'];
        }

        $groupBySql .= " GROUP BY `{$params['field']}` ORDER BY `{$params['field']}` ASC";
        Debug::sql($groupBySql);
        $res = $db->sql_query($groupBySql);

        $data = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data[] = [
                'val' => $params['is_numeric'] ? +$row['val'] : $row['val'],
                'cnt' => (int)$row['cnt']
            ];
        }

        return $data;
    }

/**
 * Handle `calculatePartitionLimits`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int|string,mixed> $data Input value for `data`.
 * @phpstan-param array<int|string,mixed> $data
 * @psalm-param array<int|string,mixed> $data
 * @param int $nb_partitions Input value for `nb_partitions`.
 * @phpstan-param int $nb_partitions
 * @psalm-param int $nb_partitions
 * @param bool $is_numeric Input value for `is_numeric`.
 * @phpstan-param bool $is_numeric
 * @psalm-param bool $is_numeric
 * @return mixed Returned value for calculatePartitionLimits.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example calculatePartitionLimits(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function calculatePartitionLimits($data, $nb_partitions, $is_numeric)
    {
        $cuts_needed = $nb_partitions - 1;
        $total = array_sum(array_column($data, 'cnt'));
        $target = $total / max(1, $cuts_needed);
        $current_sum = 0;
        $partitions_limits = [];

        foreach ($data as $row) {
            $current_sum += $row['cnt'];
            if ($current_sum >= $target && count($partitions_limits) < $cuts_needed) {
                $limit = $row['val'];
                if ($is_numeric && is_int($limit)) {
                    $limit++;
                }
                $partitions_limits[] = $limit;
                $current_sum = 0;
            }
        }

        // s'il manque des cuts (peu de valeurs distinctes), compléter avec la dernière valeur + 1 (si numérique) ou répéter
        while (count($partitions_limits) < $cuts_needed) {
            $last = end($data)['val'];
            if ($is_numeric && is_int($last)) {
                $last = (int)$last + 1;
            } else {
                // pour les chaines, ajouter un suffixe pour éviter égalité (précaution)
                $last = $last . "_max";
            }
            $partitions_limits[] = $last;
            echo "⚠️ Peu de valeurs distinctes — ajout d'une limite supplémentaire : " . ($is_numeric ? $last : "'$last'") . "\n";
        }

        return $partitions_limits;
    }

/**
 * Handle `generatePartitionSQL`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $table Input value for `table`.
 * @phpstan-param mixed $table
 * @psalm-param mixed $table
 * @param mixed $field Input value for `field`.
 * @phpstan-param mixed $field
 * @psalm-param mixed $field
 * @param mixed $limits Input value for `limits`.
 * @phpstan-param mixed $limits
 * @psalm-param mixed $limits
 * @param bool $is_numeric Input value for `is_numeric`.
 * @phpstan-param bool $is_numeric
 * @psalm-param bool $is_numeric
 * @return mixed Returned value for generatePartitionSQL.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example generatePartitionSQL(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function generatePartitionSQL($table, $field, $limits, $is_numeric)
    {
        $sql = "ALTER TABLE `$table`\nPARTITION BY RANGE (`$field`) (\n";
        foreach ($limits as $i => $limit) {
            $sql .= "  PARTITION p$i VALUES LESS THAN (" . ($is_numeric ? $limit : "'$limit'") . "),\n";
        }
        $sql .= "  PARTITION p" . count($limits) . " VALUES LESS THAN (MAXVALUE)\n);";
        return $sql;
    }

/**
 * Update `saveSQLToFile`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $sql Input value for `sql`.
 * @phpstan-param mixed $sql
 * @psalm-param mixed $sql
 * @param mixed $table Input value for `table`.
 * @phpstan-param mixed $table
 * @psalm-param mixed $table
 * @return void Returned value for saveSQLToFile.
 * @phpstan-return void
 * @psalm-return void
 * @example saveSQLToFile(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function saveSQLToFile($sql, $table)
    {
        $outputFile = "/tmp/partition_" . $table . "_" . date('Ymd_His') . ".sql";
        file_put_contents($outputFile, $sql);
        echo "💾 Sauvegardé dans : $outputFile\n";
    }
}

