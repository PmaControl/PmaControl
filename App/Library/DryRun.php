<?php

namespace App\Library;

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