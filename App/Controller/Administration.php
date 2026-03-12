<?php

namespace App\Controller;

use \Glial\Synapse\Controller;

/**
 * Class responsible for administration workflows.
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
class Administration extends Controller {

    use \Glial\Neuron\Controller\Administration;

/**
 * Stores `$module_group` for module group.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    public $module_group = "Administration";

/**
 * Handle administration state through `test`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for test.
 * @phpstan-return void
 * @psalm-return void
 * @see self::test()
 * @example /fr/administration/test
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function test() {
        $this->view = false;
        echo "main";
    }

}

