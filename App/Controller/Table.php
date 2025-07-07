<?php

namespace App\Controller;

use App\Library\Graphviz;
use Glial\Synapse\Controller;
use Glial\Synapse\FactoryController;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use \App\Library\Mysql;
use \App\Library\Table as Tablelib;

class Table extends Controller {

    //number of line used by header to make a good analyse when condense the screen
    const NB_ROWS_EXTRA = 5;
    //number of line who feet in screen
    const MAX_HIGHT = 43;
    const MAX_COLUMN = 4; // we don't count main table

    static $type_graph = "compound";
    static $table_number = 1;

    static $tables = array();

    static $main_table = array();
    static $edges = array();
    static $hidden_edges = array();

    //contain all result from query, the goal is not to surchage the main function, and keep it easy to maintain
    static $result = array();

    function mpd($param) {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2] ?? false;
        $default = Sgbd::sql(DB_DEFAULT);

        $_GET['mysql_server']['id'] = $id_mysql_server;

        //force a table if empty
        $node = FactoryController::getRootNode();
        
        if (strtolower($node[0]) === "table" && strtolower($node[1]) == "mpd")
        {
            $db = Mysql::getDbLink($id_mysql_server);
            $sql ="SELECT count(1) as cpt FROM information_schema.tables WHERE table_schema = '".$table_schema."' AND table_name = '".$table_name."'";
            $res = $db->sql_query($sql);
            while ($ob = $db->sql_fetch_object($res)) {
                $cpt = $ob->cpt;
            }

            if ($table_name === false || $cpt == "0") {
                
                $table_list = Tablelib::getTableWithFk($param);
                
                if (count($table_list) > 0){
                    $table_name = $table_list[0];

                    $_GET['mysql_table']['id'] = $table_name;
        
                    $elems = explode("\\", __CLASS__);
                    $class = strtolower(end($elems));
                    $url = $class.'/'.__FUNCTION__.'/'.$id_mysql_server.'/'.$table_schema."/".$table_name.'/';
                    
                    header("location: ".LINK.$url);
                }
            }    
        }
        //end


        //deport to lib/database
        $sql = "SELECT id from mysql_database where id_mysql_server=".$id_mysql_server." AND schema_name = '".$table_schema."'";
        $res = $default->sql_query($sql);
        while ($ob = $default->sql_fetch_object($res))
        {
            $_GET['mysql_database']['id'] = $ob->id;
        }
        /* end of deport */

        Debug::debug($param);

        $data = array();
        $data['param'] = $param;

        /*

        find all path
        */

        //refresh foreign key
        //Tablelib::importRealForeignKey($param);


        $sql = "SELECT id_mysql_server, constraint_schema,constraint_table, constraint_column, 
        id_mysql_server__link, referenced_schema, referenced_table, referenced_column
        FROM `foreign_key_real`
        WHERE `id_mysql_server` = ".$id_mysql_server." 
        AND `referenced_schema` = '".$table_schema."' ";

        if (!empty($table_name)) {
            $sql .= " AND (`referenced_table` = '".$table_name."' OR `constraint_table` = '".$table_name."') ";
        }
        
        $sql .= "UNION
        SELECT id_mysql_server, constraint_schema,constraint_table, constraint_column, 
        id_mysql_server__link, referenced_schema, referenced_table, referenced_column
        FROM `foreign_key_virtual`
        WHERE `id_mysql_server` = ".$id_mysql_server." 
        AND `referenced_schema` = '".$table_schema."' ";

        if (!empty($table_name)) {
            $sql .= "AND (`referenced_table` = '".$table_name."' OR `constraint_table` = '".$table_name."')";
        }
        $sql .=";";

        // debug($sql);

        $this->getElemeFromQuery($sql);
        
        $this->compressDisplay($param);

        Debug::sql($sql);

        $graph = "";
        $graph .= Graphviz::generateStart(array());


        //debug(self::$main_table);
        //debug(self::$tables);

        // pour grouper les tables fille au sein d'un meme cluster
        // generate struc to order lot of table by row

        if (!empty(self::$main_table['table_par_colonne']) && self::$main_table['table_par_colonne'] > 1 )
        {
            $cluster['color'] = Graphviz::getColor($table_name);
            $cluster['bgcolor'] = $this->diluerCouleur($cluster['color'], 85) ;
            $cluster['penwidth'] = "4";
            $cluster['tooltip'] = $table_schema.".".$table_name;
            
            $graph .= Graphviz::openSubgraph($cluster);


            $premiere_table_de_chaque_colonne = array();
            foreach(self::$main_table['table_par_colonne'] as $colonne_numero => $tables) {

                $cluster = array();
                $cluster['penwidth'] = "0";
                $cluster['label'] = "";
                $cluster['bgcolor'] = "";

                $graph .= Graphviz::openSubgraph($cluster);

                //on trie les tables dans chaque colonnes par ordre alphabétique
                sort(self::$main_table['table_par_colonne'][$colonne_numero]);
                sort($tables);

                foreach($tables as $table_in_struct ) {
                    if (empty($premiere_table_de_chaque_colonne[$colonne_numero])) {
                        $premiere_table_de_chaque_colonne[$colonne_numero][0] = $table_in_struct;
                    }

                    $field = self::$tables['tables'][$id_mysql_server][$table_schema][$table_in_struct]['field'];
                    $graph .= Graphviz::generateTable(array($id_mysql_server,$table_schema, $table_in_struct),$field);
                }
                $graph .= Graphviz::closeSubgraph($param);
            }
            
            $graph .= Graphviz::closeSubgraph($param);
            $graph .= "".PHP_EOL;
            
            //debug(self::$main_table);
            //debug(self::$tables);

            //generate invisible edge bewteen column
            $this->generateHiddenArrow($premiere_table_de_chaque_colonne);

            //edge between cluster and main table
            //$first_table = array_key_first());
            $last_colone = end(self::$main_table['table_par_colonne']);
            //debug($last_colone);
            $middle = floor(count($last_colone)/2); // add +1 car l'id du table commence a 0
            //debug($middle);

            //ajouter 
            $field1 = array_key_first(self::$tables['tables'][$id_mysql_server][$table_schema][$last_colone[$middle]]['field']);

            $indice_1 = sha1($id_mysql_server.$table_schema.$last_colone[$middle]);
            
            $pos1 = Tablelib::findFieldPosition(array($id_mysql_server, $table_schema , $last_colone[$middle], $field1));
            $pos2 = self::$tables['table'][$indice_1]['column'][$field1]['target_position'];

            //debug(self::$main_table);
            //debug(self::$tables);

            $tmp = array();
            $tmp['constraint_table'] = $table_name;
            $tmp['referenced_table'] = $table_name;
            $tmp['tooltip'] = "* => ".$tmp['constraint_table'].".".$tmp['referenced_table'];

            $tmp['arrow'] = $last_colone[$middle].":d".$pos1." -> ".$tmp['referenced_table'].":a".$pos2." ";
            $tmp['color'] = Graphviz::getColor($tmp['referenced_table']);
            
            // don't work :(
            $tmp['options'] = array("ltail" => "cluster_4");
            self::$edges[] = $tmp;
        }

        //debug(self::$main_table);

        // generate all table
        //debug(self::$tables['tables']);

        if (! empty(self::$tables['tables'])) {
            foreach(self::$tables['tables'] as $mysql_server_id => $databases) {
                foreach($databases as $schema => $tables ){
                    foreach($tables as $table_nom => $field){
                        if (! in_array($table_nom, array_keys(self::$main_table['tables_before'])) ||  self::$main_table['nb_column_left'] ===1 ) {
                            $graph .= Graphviz::generateTable(array($mysql_server_id,$schema, $table_nom),$field['field']);
                        }
                    }
                }
            }
        }
        // edge
        foreach(self::$edges as $edge){
            if (! in_array($edge['constraint_table'], array_keys(self::$main_table['tables_before']))  ||  self::$main_table['nb_column_left'] ===1 ) {
                $graph .= Graphviz::generateEdge($edge);
            }
        }

        //generate hiddenEdge after table
        if (self::$main_table['nb_column_right'] > 1)
        {
            $colones = $this->splitTableByColumnBasic(self::$main_table['link'],self::$main_table['nb_column_right']);
            $this->generateHiddenArrow($colones);
        }

        foreach(self::$hidden_edges as $hidden_edge){
            $graph .= Graphviz::generateHiddenEdge($hidden_edge);
        }

        $graph .= Graphviz::generateEnd(array());

        //debug($graph);

        $data['debug'] = $graph;
        $data['graph'] = Graphviz::generateDot($id_mysql_server."-".$table_schema."-".$table_name, $graph);

        $data['table_schema'] = $table_schema;
        $data['table_name'] = $table_name;

        $data['param'] = $param;
        $this->set('param', $param);
        $this->set('data', $data);
    }

    public function getElemeFromQuery($sql)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query($sql);

        while($arr = $db->sql_fetch_array($res , MYSQLI_ASSOC))
        {
            self::$result[] = $arr;

            $contraint = sha1($arr['id_mysql_server'].$arr['constraint_schema'].$arr['constraint_table']);
            $referenced = sha1($arr['id_mysql_server__link'].$arr['referenced_schema'].$arr['referenced_table']);

            //table de gauche
            $options_1 = array($arr['id_mysql_server'], $arr['constraint_schema'], $arr['constraint_table'], $arr['constraint_column']);
            //table de droite
            $options_2 = array($arr['id_mysql_server__link'], $arr['referenced_schema'], $arr['referenced_table'], $arr['referenced_column']);

            //table de gauche
            self::$tables['table'][$contraint]['server'] = $arr['id_mysql_server'];
            self::$tables['table'][$contraint]['schema'] = $arr['constraint_schema'];
            self::$tables['table'][$contraint]['table'] = $arr['constraint_table'];
            self::$tables['table'][$contraint]['color'] = Graphviz::getColor($contraint);
            self::$tables['table'][$contraint]['column'][$arr['constraint_column']]['color'] = Graphviz::getColor($referenced);
            self::$tables['table'][$contraint]['column'][$arr['constraint_column']]['position'] = Tablelib::findFieldPosition($options_1);
            self::$tables['table'][$contraint]['column'][$arr['constraint_column']]['target_position'] = Tablelib::findFieldPosition($options_2);
            self::$tables['table'][$contraint]['column'][$arr['constraint_column']]['target_table'] = $referenced;
            self::$tables['table'][$contraint]['column'][$arr['constraint_column']]['target_field'] = $arr['referenced_column'];
            self::$tables['table'][$contraint]['number_field'] = Tablelib::getNumberOfField($options_1) + self::NB_ROWS_EXTRA;

            //table de droite celle référencé
            self::$tables['table'][$referenced]['server'] = $arr['id_mysql_server__link'];
            self::$tables['table'][$referenced]['schema'] = $arr['referenced_schema'];
            self::$tables['table'][$referenced]['table']  = $arr['referenced_table'];
            self::$tables['table'][$referenced]['color'] = Graphviz::getColor($referenced);
            self::$tables['table'][$referenced]['column'][$arr['referenced_column']]['color'] = Graphviz::getColor($arr['referenced_table']);
            self::$tables['table'][$referenced]['column'][$arr['referenced_column']]['position'] = Tablelib::findFieldPosition($options_2);

            self::$tables['tables'][$arr['id_mysql_server']][$arr['constraint_schema']][$arr['constraint_table']]['field'][$arr['constraint_column']]['color'] = Graphviz::getColor($arr['referenced_table']);
            self::$tables['tables'][$arr['id_mysql_server__link']][$arr['referenced_schema']][$arr['referenced_table']]['field'][$arr['referenced_column']]['color'] = Graphviz::getColor($arr['referenced_table']);

            $pos1 = Tablelib::findFieldPosition(array($arr['id_mysql_server'], $arr['constraint_schema'], $arr['constraint_table'], $arr['constraint_column']));
            $pos2 = Tablelib::findFieldPosition(array($arr['id_mysql_server__link'], $arr['referenced_schema'], $arr['referenced_table'], $arr['referenced_column']));

            $tmp = array();
            $tmp['constraint_table'] = $arr['constraint_table'];
            $tmp['referenced_table'] = $arr['referenced_table'];
            $tmp['tooltip'] = $arr['constraint_table'].".".$arr['constraint_column']." => ".$arr['referenced_table'].".".$arr['referenced_column'];
            $tmp['arrow'] = $arr['constraint_table'].":d".$pos1." -> ".$arr['referenced_table'].":a".$pos2;
            $tmp['color'] = Graphviz::getColor($arr['referenced_table']);
            $tmp['options']['arrowhead'] = "none";
            $tmp['options']['arrowtail'] = "crow";
            

            self::$edges[] = $tmp;
        }

        //debug(self::$tables);
    }

    public function compressDisplay($param)
    {
        $id_mysql_server = $param[0] ?? "";
        $table_schema = $param[1] ?? "";
        $table_name = $param[2] ?? "";

        self::$main_table['nb_table_before'] = 0;
        self::$main_table['nb_table_after'] = 0;

        self::$main_table['tables_before'] = array();
        self::$main_table['tables_after'] = array();

        self::$main_table['link'] = array();

        foreach(self::$result as $arr) {
            // seulement pour générer un affichage condensé et de multiplier les colones (on part sur un affichage à 5 colonnes)
            if ($id_mysql_server === $arr['id_mysql_server'] && $arr['referenced_schema'] === $table_schema && $arr['referenced_table'] === $table_name){
                self::$main_table['nb_table_before'] += 1;

                $param[2] = $arr['constraint_table'];
                self::$main_table['tables_before'][$arr['constraint_table']] = Tablelib::getNumberOfField($param) + self::NB_ROWS_EXTRA;
            }
            if ($id_mysql_server === $arr['id_mysql_server'] && $arr['constraint_schema'] === $table_schema && $arr['constraint_table'] === $table_name){
                self::$main_table['nb_table_after'] += 1;

                $param[2] = $arr['referenced_table'];
                self::$main_table['tables_after'][$arr['referenced_table']] = Tablelib::getNumberOfField($param) + self::NB_ROWS_EXTRA;

                $position = Tablelib::findFieldPosition(array($id_mysql_server, $table_schema, $table_name, $arr['constraint_column']));

                self::$main_table['link'][$position] = $arr['referenced_table']; 
            }
        }

        ksort(self::$main_table['link']);

        self::$main_table['total_before'] = array_sum(self::$main_table['tables_before']);
        self::$main_table['total_after'] = array_sum(self::$main_table['tables_after']);
        self::$main_table['total'] = self::$main_table['total_before'] + self::$main_table['total_after'];

        $this->getNumberOfColumn($param);
    }


    public function getNumberOfColumn($param)
    {

        if (self::$main_table['total_before'] === 0) {
            self::$main_table['nb_column_left'] = 0;

            self::$main_table['nb_column_right'] = ceil(self::$main_table['total_after'] / self::MAX_HIGHT);

            if (self::$main_table['nb_column_right'] > self::MAX_COLUMN) {
                self::$main_table['nb_column_right'] = self::MAX_COLUMN;
            }
        }

        if (self::$main_table['total_after'] === 0) {
            self::$main_table['nb_column_right'] = 0;

            self::$main_table['nb_column_left'] = ceil(self::$main_table['total_before'] / self::MAX_HIGHT);

            if (self::$main_table['nb_column_left'] > self::MAX_COLUMN) {
                self::$main_table['nb_column_left'] = self::MAX_COLUMN;
            }
        }

        if (self::$main_table['total_before'] != 0 && self::$main_table['total_after'] != 0)
        {
            $percent_before = self::$main_table['total_before']/self::$main_table['total'];
            $reference = 1/self::MAX_COLUMN;

            for($i=1; $i <= self::MAX_COLUMN; $i++)
            {
                if ($percent_before < ($reference* $i ))
                {
                    self::$main_table['nb_column_left'] = $i;
                    self::$main_table['nb_column_right'] = self::MAX_COLUMN - self::$main_table['nb_column_left'];
                    break;
                }
            }

            if (self::$main_table['nb_column_left'] === self::MAX_COLUMN)
            {
                self::$main_table['nb_column_left'] = self::MAX_COLUMN - 1;
                self::$main_table['nb_column_right'] = self::MAX_COLUMN - self::$main_table['nb_column_left'];
            }
        }

        if (self::$main_table['total_before'] !== 0 && self::$main_table['total_before'] < self::MAX_HIGHT){
            self::$main_table['nb_column_left'] = 1;
        }

        if (self::$main_table['total_after'] !== 0 && self::$main_table['total_after'] < self::MAX_HIGHT){
            self::$main_table['nb_column_right'] = 1;
        }

        //Il peux pas y avoir plus de colones que de tables
        if (self::$main_table['nb_column_left'] > self::$main_table['nb_table_before']) {
            self::$main_table['nb_column_left'] = self::$main_table['nb_table_before'];
        }

        //Il peux pas y avoir plus de colones que de tables
        if (self::$main_table['nb_column_right'] > self::$main_table['nb_table_after']) {
            self::$main_table['nb_column_right'] = self::$main_table['nb_table_after'];
        }

        if (self::$main_table['nb_column_left'] > 1) {
            $tables_by_colone = $this->splitTableByColumn(self::$main_table['tables_before'],self::$main_table['nb_column_left'] );

            self::$main_table['table_par_colonne'] = $tables_by_colone;
            //$this->generateHiddenArrow($tables_by_colone);
        }
    }
    
    // C'est clairement le meilleur algorythme pour repartir des éléments en fonction de leur poids
    // Le problème c'est que par moment le passage des flèches est problèmatique avec graphviz
    
    public function splitTableByColumn($tables_before,$nombreColonnes )
    {

        //$nombreColonnes = 4; // Ajustez selon le besoin
        $colonnes = array_fill(0, $nombreColonnes, []);

        // Trier les tables par poids décroissant pour initialiser la distribution
        arsort($tables_before);

        // Distribution initiale des tables dans les colonnes
        foreach ($tables_before as $table => $poids) {
            $indexColonneMin = 0;
            $poidsColonneMin = PHP_INT_MAX;
            foreach ($colonnes as $index => $col) {
                $poidsCol = array_sum(array_map(function($t) use ($tables_before) { return $tables_before[$t]; }, $col));
                if ($poidsCol < $poidsColonneMin) {
                    $poidsColonneMin = $poidsCol;
                    $indexColonneMin = $index;
                }
            }
            $colonnes[$indexColonneMin][] = $table;
        }

        // Phase de post-optimisation pour un meilleur équilibrage
        $amelioration = true;
        while ($amelioration) {
            $amelioration = false;
            $meilleureDiff = 0;
            $echangePropose = null;

            // Évaluer tous les échanges possibles entre les tables de différentes colonnes
            for ($i = 0; $i < $nombreColonnes; $i++) {
                for ($j = $i + 1; $j < $nombreColonnes; $j++) {
                    foreach ($colonnes[$i] as $tableI) {
                        foreach ($colonnes[$j] as $tableJ) {
                            // Calculer le poids avant échange
                            $poidsAvantI = $this->calculerPoidsColonne($colonnes[$i], $tables_before);
                            $poidsAvantJ = $this->calculerPoidsColonne($colonnes[$j], $tables_before);
                            $diffAvant = abs($poidsAvantI - $poidsAvantJ);

                            // Simuler l'échange
                            $poidsApresI = $poidsAvantI - $tables_before[$tableI] + $tables_before[$tableJ];
                            $poidsApresJ = $poidsAvantJ - $tables_before[$tableJ] + $tables_before[$tableI];
                            $diffApres = abs($poidsApresI - $poidsApresJ);

                            // Vérifier si l'échange améliore l'équilibrage
                            if ($diffApres < $diffAvant && $diffAvant - $diffApres > $meilleureDiff) {
                                $meilleureDiff = $diffAvant - $diffApres;

                                $echangePropose = [$i, $tableI, $j, $tableJ];
                                $amelioration = true;
                            }
                        }
                    }
                }
            }

            // Effectuer le meilleur échange trouvé
            if ($amelioration) {
                list($indexColI, $tableI, $indexColJ, $tableJ) = $echangePropose;
                // Trouver et retirer les tables des colonnes actuelles
                unset($colonnes[$indexColI][array_search($tableI, $colonnes[$indexColI])]);
                unset($colonnes[$indexColJ][array_search($tableJ, $colonnes[$indexColJ])]);
                // Ajouter les tables aux nouvelles colonnes
                $colonnes[$indexColI][] = $tableJ;
                $colonnes[$indexColJ][] = $tableI;
                // Réindexer les arrays pour éviter les problèmes lors des itérations futures
                $colonnes[$indexColI] = array_values($colonnes[$indexColI]);
                $colonnes[$indexColJ] = array_values($colonnes[$indexColJ]);
            }
        }

        return $colonnes;
    }

    // Fonction pour calculer le poids total d'une colonne
    function calculerPoidsColonne($colonne, $tables_before) {
        return array_sum(array_map(function($table) use ($tables_before) { return $tables_before[$table]; }, $colonne));
    }

    public function generateHiddenArrow($tables)
    {
        $maxElements = max(array_map('count', $tables));

        $result = [];
        for ($i = 0; $i < $maxElements; $i++) {
            for ($j = 0; $j < count($tables) - 1; $j++) {
                // Vérifier si l'indice existe dans la liste actuelle et la suivante
                if (isset($tables[$j][$i]) && isset($tables[$j + 1][$i])) {
                    $result[$tables[$j][$i]] = $tables[$j + 1][$i];
                } else if (isset($tables[$j][$i]) && !isset($tables[$j + 1][$i])) {
                    // Si l'élément existe dans la liste actuelle mais pas dans la suivante, lier à l'élément initial de la liste suivante

                    if (! empty($tables[$j + 1][0]))
                    {
                        // il y a peut un cas ou il faudra réactiver pour les tables de gauche
                        //$result[$tables[$j][$i]] = $tables[$j + 1][0];
                    }
                }
            }
        }

        // Export des résultats
        foreach ($result as $key => $value) {
            self::$hidden_edges[] =  $key . ":title -> " . $value.":title";
        }
    }

    public function splitTableByColumnBasic($table, $nombre_colonnes)
    {
        $i=0;
        $colonnes = array_fill(0, $nombre_colonnes, []);

        //debug($table);

        foreach($table as $elem)
        {
            $numero_colone = $i % $nombre_colonnes;
            $colonnes[$numero_colone][] = $elem;
            $i++;
        }

        return $colonnes;
    }


    public function index($param)
    {
        //liste des tables d'un DB 
        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2];

        $sql = "SELECT * FROM index_stats 
        WHERE id_mysql_server=".$id_mysql_server." AND table_schema = '".$table_schema."' AND table_name= '".$table_name."'";

        Debug::sql($sql);



    }


    // to remove and replace with the one from Graphiz
    function diluerCouleur($hex, $percent) {
        // Assurez-vous que le format hexadécimal est valide
        if (strlen($hex) != 7 || $hex[0] != '#') {
            return 'Format de couleur invalide.';
        }
    
        // Convertir les composantes hexadécimales en valeurs décimales
        $r = hexdec(substr($hex, 1, 2));
        $g = hexdec(substr($hex, 3, 2));
        $b = hexdec(substr($hex, 5, 2));
    
        // Calculer la nouvelle couleur en augmentant la luminosité
        $nouveau_r = min(255, $r + (255 - $r) * $percent / 100);
        $nouveau_g = min(255, $g + (255 - $g) * $percent / 100);
        $nouveau_b = min(255, $b + (255 - $b) * $percent / 100);
    
        // Reconvertir les valeurs RGB en hexadécimal
        $nouveau_hex = sprintf("#%02x%02x%02x", $nouveau_r, $nouveau_g, $nouveau_b);
    
        return $nouveau_hex;
    }


    function Query($param)
    {




        
    }
}
