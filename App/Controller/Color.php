<?php

namespace App\Controller;

use \Glial\I18n\I18n;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;


class Color extends Controller
{
    public function index($param)
    {
        $this->di['js']->addJavascript(array('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.3.6/js/bootstrap-colorpicker.min.js'));
    
        $this->di['js']->code_javascript('$(".colorpicker").colorpicker({format: "hex"});');

        $db = Sgbd::sql(DB_DEFAULT);
        $selectedType = isset($param[0]) ? (string) $param[0] : '';

        // Keep in sync with `chk_style` constraint in `dot3_legend` model
        $dotStyleValues = array(
            'solid',
            'dashed',
            'dotted',
            'bold',
            'invis',
            'filled',
            'rounded',
            'diagonals',
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['dot3_legend']) && is_array($_POST['dot3_legend'])) {
            $updated = 0;

            foreach ($_POST['dot3_legend'] as $id => $legend) {
                if (!is_array($legend)) {
                    continue;
                }

                $id = (int) $id;
                if ($id <= 0) {
                    continue;
                }

                $font = strtoupper(trim((string) ($legend['font'] ?? '')));
                $color = strtoupper(trim((string) ($legend['color'] ?? '')));
                $background = strtoupper(trim((string) ($legend['background'] ?? '')));
                $style = trim((string) ($legend['style'] ?? ''));

                if (!in_array($style, $dotStyleValues, true)) {
                    continue;
                }

                $sql = "UPDATE dot3_legend SET
                        `font` = '".$db->sql_real_escape_string($font)."',
                        `color` = '".$db->sql_real_escape_string($color)."',
                        `background` = '".$db->sql_real_escape_string($background)."',
                        `style` = '".$db->sql_real_escape_string($style)."'
                    WHERE id = ".$id."";

                if ($db->sql_query($sql)) {
                    $updated++;
                }
            }

            $title = I18n::getTranslation(__("Success"));
            $msg = I18n::getTranslation(__("Legend updated."));

            if ($updated === 0) {
                $title = I18n::getTranslation(__("Warning"));
                $msg = I18n::getTranslation(__("No data has been updated."));
                set_flash("caution", $title, $msg);
            } else {
                set_flash("success", $title, $msg);
            }

            header("location: ".LINK."Color/index/".$selectedType."/");
            exit;
        }

        $sql = "SELECT * FROM dot3_legend ORDER BY `order`";

        $res = $db->sql_query($sql);

        $type = [];
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $type[] = $arr['type'];

            $data['legend'][$arr['type']][$arr['const']] = $arr;
        }

        $data['type'] = array_unique($type);
        sort($data['type']);

        if (empty($selectedType) && !empty($data['type'])) {
            $selectedType = $data['type'][0];
        }

        $_GET['type'] = $selectedType;
        $data['dot_style_values'] = $dotStyleValues;

        $this->set('data', $data);
    }


}