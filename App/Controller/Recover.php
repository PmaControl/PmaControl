<?php


namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;


class Recover extends Controller {

    const DIRECTORY='/srv/code/undrop-for-innodb/bin/export/cnfpt_national';
/****
 * 
 * 
 * SET FOREIGN_KEY_CHECKS=0;
LOAD DATA LOCAL INFILE '/srv/code/undrop-for-innodb/bin/dumps/default/users' 
REPLACE INTO TABLE `users` CHARACTER SET UTF8 FIELDS TERMINATED BY '\t' OPTIONALLY ENCLOSED BY '"' 
LINES STARTING BY 'users\t' (`id`, `structure_id`, @var_login, @var_password, @var_email, @var_name, @var_cookie, `created`, `updated`, `contact_id`, `fip_formateur_id`, `e_type`, `modified`)
SET
    `login` = UNHEX(@var_login),
    `password` = UNHEX(@var_password),
    `email` = UNHEX(@var_email),
    `name` = UNHEX(@var_name),
    `cookie` = UNHEX(@var_cookie);
-- STATUS {"records_expected": 4212, "records_dumped": 2117, "records_lost": true} STATUS END


--DROP DE TOUS LES INDEX 
DELIMITER $$

CREATE PROCEDURE ShowAllNonUniqueIndexes()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE tbl_name VARCHAR(255);
    DECLARE idx_name VARCHAR(255);
    DECLARE cur CURSOR FOR
        SELECT table_name, index_name
        FROM information_schema.statistics
        WHERE table_schema = 'cnfpt_national' AND non_unique = 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- Ouverture du curseur
    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO tbl_name, idx_name;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SET @drop_index_stmt = CONCAT('ALTER TABLE ', tbl_name, ' DROP INDEX ', idx_name);
        SELECT @drop_index_stmt AS DropIndexStatement;
    END LOOP;

    -- Fermeture du curseur
    CLOSE cur;
END $$

DELIMITER ;

-- Appel de la procédure pour afficher tous les index non uniques à supprimer
CALL ShowAllNonUniqueIndexes();

-- Optionnel : suppression de la procédure après exécution
DROP PROCEDURE IF EXISTS ShowAllNonUniqueIndexes;

 */

    public function rewrite($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $files = glob(self::DIRECTORY."/*.sql");

        $database = 'cnfpt_national';

        foreach($files as $file)
        {
            echo $file."\n";

            $info = pathinfo($file);

            //Debug::debug($gg,"info");
            $file_csv = $info['dirname'].'/'.$info['filename'].'.csv';

            //echo $file_csv."\n";
            $input_line = file_get_contents($file);

            preg_match_all('/"records_lost": (true|false)/', $input_line, $output_array);

            if (!empty($output_array[1][0]))
            {
                Debug::debug($output_array);

                preg_match_all('/-- STATUS ({.*}) STATUS END/', $input_line, $output_array);

                $json = $output_array[1][0];
                $res = json_decode($json, true);

                Debug::debug($res);

                $table_name = $this->getTableName($file);

                Debug::debug($table_name, "TABLE_NAME");

                if ($table_name == "ws_logs") {
                    continue;
                }

                if ($table_name)
                {
                    preg_match_all("/LOAD DATA LOCAL INFILE '(.*)'\s+R/", $input_line, $output_array);
                    
                    //Debug::debug($output_array, "DHHDFGHDHFG");
                    $result = $input_line;
                    if (!empty($output_array[1][0])) {

                        Debug::debug($output_array, "DHHDFGHDHFG");

                        $result = str_replace($output_array[1][0], $file_csv, $input_line);
                        echo "=====================>".$result."\n";
                    }
                    else {
                        continue;
                    }
    
                    file_put_contents($file, $result);
                    preg_match_all('/CannotOpen/', $input_line, $output_array);

                    $success = 1;
                    if (!empty( $output_array[0][0]))
                    {
                        $success = 0;
                    }

                    $id_recover_table = $this->getTableId($database, $table_name);

                    if ($res['records_lost'] == "1") {
                        $res['records_lost'] = "true";
                    }
                    else {
                        $res['records_lost'] = "false";
                    }

                    preg_match_all('/pages-\w+\.0+([0-9]+)/', $file, $output_array);

                    $page_name = $output_array[1][0];

                    $sql = "INSERT IGNORE INTO recover_page (id_recover_table, page_name, success, records_expected, records_dumped,records_lost, query)
                    VALUES (".$id_recover_table.", '".$page_name."', ".$success.", ".$res['records_expected'].", ".$res['records_dumped'].", '".$res['records_lost']."', '".$db->sql_real_escape_string($result)."') ";

                    //Debug::sql($sql);
                    $db->sql_query($sql);

                }
                else{
                    echo "Impossible to gettable name  '$table_name'\n";
                }
            }

            /*
            $table_name = $this->getTableName($file);

            if ($table_name !== false) {
                echo "$table_name\n";
            }*/
        }
    }


    public function getTableId($table_schema, $table_name)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "INSERT IGNORE INTO `recover_table` (`table_schema`,`table_name`) VALUES ('".$table_schema."', '".$table_name."');";
        //Debug::sql($sql);
        $db->sql_query($sql);

        $sql ="SELECT id from `recover_table`  WHERE table_schema='".$table_schema."' AND table_name='".$table_name."' ";
        //Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            return $ob->id;
        }
    }

    public function getTableName($file)
    {
        preg_match('/pages-(\w+)\.0/', $file, $output_array);

        if (! empty($output_array[1])) {
            //Debug::debug($output_array);
            return $output_array[1];
        }
        
        return false;
    }



    public function importData($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);
        $db2 = Sgbd::sql("server_665369cca6ef4", "RECOVER");


        echo "START\n";
        
        $sql = "SELECT a.table_schema, a.table_name,b.query , b.id
        FROM `recover_table` a
        INNER JOIN `recover_page` b ON b.id_recover_table = a.id
        WHERE success=1 and count_after =0 ORDER BY b.records_dumped ASC";

        Debug::sql($sql);

        $res= $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $content = $this->removeComments($arr['query']);

            $db2->sql_select_db($arr['table_schema']);
            
            echo $content."\n";
            echo "------------------------------------------------------\n";
            $db2->sql_query("SET FOREIGN_KEY_CHECKS=0;");
            $db2->sql_query($content);

            $sql2 = "SELECT count(1) as cpt FROM `".$arr['table_schema']."`.`".$arr['table_name']."`;";
            $res2 = $db2->sql_query($sql2);

            while($ob = $db2->sql_fetch_object($res2))
            {
                echo $arr['table_name']."count() => ".$ob->cpt."\n";

                $sql3 = "UPDATE recover_page SET count_after=".$ob->cpt." WHERE id =".$arr['id'];
                $db->sql_query($sql3);
            }
        }
    }

    function removeComments($content) {

        $content = str_replace('SET FOREIGN_KEY_CHECKS=0;', '', $content);
    
        // Supprimer les commentaires sur une seule ligne
        $content = preg_replace('/--.*(\r?\n)/', '', $content);
        $content = preg_replace('!/\\*.*?\\*/!s', '', $content);
    
        // Supprimer les espaces et tabulations en début de ligne
        $content = preg_replace('/^\s+/m', '', $content);
    
        return $content;
    }
}
