<?php

namespace App\Controller;

use \Glial\Synapse\Controller;

class Datamodel extends Controller {

    public function index() {
        
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            foreach ($_POST['cleaner_foreign_key'] as $cleaner_foreign_key) {
                $ob_foreign_key['cleaner_foreign_key'] = $cleaner_foreign_key;
                $ob_foreign_key['cleaner_foreign_key']['id_cleaner_main'] = $id_cleaner_main;


                if (!empty($ob_foreign_key['cleaner_foreign_key']['constraint_column']) && !empty($ob_foreign_key['cleaner_foreign_key']['referenced_column'])) {
                    $id_cleaner_foreign_key = $db->sql_save($ob_foreign_key);
                }
            }
        }
    }

}
