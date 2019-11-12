<?php

namespace App\Controller;

use \Glial\Synapse\Controller;

class PmaCli extends Controller {

    use \Glial\Neuron\PmaCli\PmaCli;

    use \Glial\Neuron\PmaCli\PmaCliSwitch;

    const TIME_BEHING_MAX = 120; // en secondes doit être compris entre 0 et inf (0 not allowed

}
