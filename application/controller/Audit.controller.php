<?php

use \Glial\Synapse\Controller;
use \App\Library\Debug;

class Audit extends Controller
{
    var $log_files = array("/data/www/hb01-mariaforge01.log");
    var $login_host = array();

    public function getuser($param)
    {
        Debug::parseDebug($param);

        foreach ($this->log_files as $file) {
            $handle = fopen($file, "r");
            if ($handle) {

                while (($buffer = fgets($handle, 4096)) !== false) {

                    $output_array = array();
                    preg_match('/(\w+)@(\S+) as anonymous on (\S+)/', $buffer, $output_array);


                    //preg_match('/\w+@\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $buffer, $output_array);

                    if (count($output_array) > 0) {

                        if (empty($this->login_host[$output_array[3]][$output_array[1]][$output_array[2]])) {
                            $this->login_host[$output_array[3]][$output_array[1]][$output_array[2]] = 1;

                            Debug::debug("Added : ".$output_array[0]);
                        } else {
                            $this->login_host[$output_array[3]][$output_array[1]][$output_array[2]] ++;
                        }

                        //$this->login_host[] = $output_array[0];
                    }
                }

                if (!feof($handle)) {
                    echo "Erreur: fgets() a échoué\n";
                }
                fclose($handle);
            }
        }

        arsort($this->login_host);
        Debug::debug($this->login_host);

        $tab = array_keys($this->login_host);

        Debug::debug($tab);
    }
}