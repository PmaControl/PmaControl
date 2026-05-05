<?php

namespace App\Library;

/**
 * Class responsible for dry run workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class DryRun
{
    /**
    * Parse la présence du flag --dry-run
    *
    * @param array|string $param  Paramètres CLI (par référence pour suppression)
    * @return bool                True si --dry-run est présent, sinon false
    */
    public static function parseDryRun(& $param): bool
    {
        $dryRun = false;

        if (empty($param)) {
            return false;
        }

        // param sous forme de tableau
        if (is_array($param)) {

            foreach ($param as $key => $elem) {
                if ($elem === "--dry-run") {
                    $dryRun = true;
                    unset($param[$key]); // on retire le flag
                }
            }
        }
        // param sous forme de string
        else {
            if ($param === "--dry-run") {
                $dryRun = true;
            }
        }

        return $dryRun;
    }
}
