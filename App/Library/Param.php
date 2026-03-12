<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

/**
 * Class responsible for param workflows.
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
class Param
{

    /**
     * @author Aurélien LEQUOY <aurelien.lequoy@esysteme.com>
     * @license GNU/GPL
     * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
     * @param string name of connection
     * @return true or false
     * @description check if param is present in arg
     * @access public
     * @example if (Param::Option($param , "--force"))
     * @package Library
     * @since 2.0.20
     * @version 1.0
     */
    static function option(& $param, $option)
    {
        if (!empty($param)) {
            if (is_array($param)) {
                foreach ($param as $key => $elem) {
                    if ($elem === $option) {
                        unset($param[$key]);
                        return true;
                    }
                }
            } else {
                if ($param == $option) {
                    return true;
                }
            }
        }
        return false;
    }
}
