<?php

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Post;
use App\Library\Extraction;
use App\Library\Transfer;

class Audit extends Controller
{

    use App\Library\Filter;
    var $log_files = array("/data/www/pmacontrol/data/general.log");
    var $granted   = array();
    var $denied    = array();

    public function getuser($param)
    {
        Debug::parseDebug($param);

        foreach ($this->log_files as $file) {
            $handle = fopen($file, "r");
            if ($handle) {

                while (($buffer = fgets($handle, 4096)) !== false) {


                    $output_array = array();

                    preg_match('/Connect\s+(\S+)@(\S+)/', $buffer, $output_array);

                    //preg_match(' /(\S+)@(\S+) as anonymous on\s?(\S+)?/', $buffer, $output_array);
                    //preg_match_all('/(\S+)@(\S+) as anonymous on\s?(\S+)?/', $input_line, $output_array);
                    //preg_match('/(\S+)@(\S+) as anonymous on\s?(\S+)/', $buffer, $output_array);
                    //Debug::debug($output_array);

                    preg_match('/(\S+)@(\S+) (as anonymous\s)?on (\S+)/', $buffer, $output_array3);

                    if (!empty($output_array3[0])) {
                        //Debug::debug($output_array3);
                    }


                    if (count($output_array) > 0) {


                        $buffer2 = fgets($handle, 4096);

                        preg_match('/Access\sdenied for\suser\s\'([\w-]+)\'@\'(\S+)\'\s/', $buffer2, $output_array2);
                        //preg_match('/Access\sdenied for\suser\s\'([\w-]+)\'@\'(\S+)\'\sto\sdatabase\s\'([\w-]+)\'/', $buffer2, $output_array2);


                        if (!empty($output_array3[4])) {
                            $output_array[3] = $output_array3[4];
                        } else {
                            $output_array[3] = "N/A";
                        }

                        if (count($output_array2) > 0) {
                            if (empty($this->denied[$output_array[3]][$output_array[1]][$output_array[2]])) {
                                $this->denied[$output_array[3]][$output_array[1]][$output_array[2]] = 1;

                                Debug::debug("Added to denied : ".$output_array2[0]);
                            } else {
                                $this->denied[$output_array[3]][$output_array[1]][$output_array[2]] ++;
                            }
                        } else {

                            if (empty($this->granted[$output_array[3]][$output_array[1]][$output_array[2]])) {
                                $this->granted[$output_array[3]][$output_array[1]][$output_array[2]] = 1;

                                Debug::debug("Added to granted : ".$output_array[0]);
                            } else {
                                $this->granted[$output_array[3]][$output_array[1]][$output_array[2]] ++;
                            }
                        }
                    }

                    //$this->login_host[] = $output_array[0];
                }
            }

            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }
            fclose($handle);
        }


        arsort($this->denied);
        arsort($this->granted);
        Debug::debug($this->granted, "granted");
        $tab = array_keys($this->granted, "granted");

        Debug::debug($tab, "granted");

        Debug::debug($this->denied, "denied");
        $tab2 = array_keys($this->denied, "denied");

        Debug::debug($tab2, "denied");
    }

    public function general_log($param)
    {
        Debug::parseDebug($param);

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            Debug::debug($_POST);

            if (!empty($_POST['general_log']['activate'])) {
                $get = Post::getToPost();


                $url = LINK.__CLASS__."/".__FUNCTION__."/".$get;

                Debug::debug($url);

                header('location: '.$url);
            }
        }

        if (!empty($_GET['mysql_server']['id'])) {

            $db = $this->di['db']->sql(DB_DEFAULT);

            Extraction::setDb($db);
            $data['logs'] = Extraction::display(array("variables::general_log_file", "variables::datadir"), array($_GET['mysql_server']['id']));

            Debug::debug($data['logs']);
        }
    }

    public function scp($param)
    {

        Debug::parseDebug($param);

        $_GET['mysql_server']['id'] = 104;

        Debug::debug($param);



        $db = $this->di['db']->sql(DB_DEFAULT);
        Extraction::setDb($db);

        $data['logs'] = Extraction::display(array("variables::general_log_file", "variables::datadir"), array($_GET['mysql_server']['id']));

        Debug::debug($data['logs']);


        $general_log_file = $data['logs'][$_GET['mysql_server']['id']]['']['general_log_file'];
        $datadir          = $data['logs'][$_GET['mysql_server']['id']]['']['datadir'];


        Debug::debug($general_log_file, "general_log_file");


        $dst = ROOT."/data/general.log";
        Debug::debug($dst, "dst");

        Transfer::setDb($db);
        $info = Transfer::getFileFromMysql($_GET['mysql_server']['id'], $general_log_file,$dst);

        Debug::debug($info);

        
    }
}