<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;


/**
 * Class responsible for html workflows.
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
class Html
{
/**
 * Handle html state through `box`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $title Input value for `title`.
 * @phpstan-param mixed $title
 * @psalm-param mixed $title
 * @param mixed $body Input value for `body`.
 * @phpstan-param mixed $body
 * @psalm-param mixed $body
 * @return mixed Returned value for box.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::box()
 * @example /fr/html/box
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function box($title, $body)
    {
        $html = '<div class="panel panel-primary">';
        $html .= '<div class="panel-heading">';
        $html .= '<h3 class="panel-title">'.$title.'</h3>';
        $html .= '</div>';
        $html .= $body;
        $html .= '</div>';
        return $html;
    }

/**
 * Handle html state through `table`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $thead Input value for `thead`.
 * @phpstan-param mixed $thead
 * @psalm-param mixed $thead
 * @param mixed $tbody Input value for `tbody`.
 * @phpstan-param mixed $tbody
 * @psalm-param mixed $tbody
 * @return mixed Returned value for table.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::table()
 * @example /fr/html/table
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function table($thead, $tbody)
    {
        $html = '<table class="table table-condensed table-bordered table-striped" id="table">';
        $html .= $thead;
        $html .= $tbody;
        $html .= '</table>';

        return $html;
    }

/**
 * Handle html state through `thead`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $th Input value for `th`.
 * @phpstan-param array $th
 * @psalm-param array $th
 * @return mixed Returned value for thead.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::thead()
 * @example /fr/html/thead
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function thead(array $th)
    {
        $html = "<tr><th>".implode("</th><th>",$th)."</th></tr>";
        return $html;
    }

/**
 * Handle html state through `tbody`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $td Input value for `td`.
 * @phpstan-param array $td
 * @psalm-param array $td
 * @return mixed Returned value for tbody.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::tbody()
 * @example /fr/html/tbody
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function tbody(array $td)
    {

        $percent = floor(100 / count($td));

        $html = '<tr><td width="'.$percent.'%">'.implode('</td><td width="'.$percent.'%">',$td)."</td></tr>";
        return $html;
    }
}
