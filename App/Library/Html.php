<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;


class Html
{
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

    static function table($thead, $tbody)
    {
        $html = '<table class="table table-condensed table-bordered table-striped" id="table">';
        $html .= $thead;
        $html .= $tbody;
        $html .= '</table>';

        return $html;
    }

    static function thead(array $th)
    {
        $html = "<tr><th>".implode("</th><th>",$th)."</th></tr>";
        return $html;
    }

    static function tbody(array $td)
    {
        $html = "<tr><td>".implode("</td></td>",$td)."</td></tr>";
        return $html;
    }
}