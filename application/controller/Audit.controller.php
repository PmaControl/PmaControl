<?php

use \Glial\Synapse\Controller;
use \App\Library\Debug;

class Audit extends Controller
{

    var $log_files = array("/data/www/rt.log");

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
}