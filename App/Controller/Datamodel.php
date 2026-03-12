<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for datamodel workflows.
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
class Datamodel extends Controller {

/**
 * Render datamodel state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/datamodel/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index() {
        
    }

/**
 * Create datamodel state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/datamodel/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

