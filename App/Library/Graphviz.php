<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \App\Library\Table;
use \App\Library\Format;
use \App\Controller\Dot3;
use \Glial\Sgbd\Sgbd;

use \Glial\Extract\Grabber;
class Graphviz
{
    // en dessous de MAX_ROWS_TO_REQUEST on va faire un select count(1) pour avoir le nombre de ligne exacte dans la table
    const MAX_ROWS_TO_REQUEST = 10000;

    //max char for type, to prevent really big table with enum
    const MAX_LENGTH = 25;
    static $color = array('aliceblue', 'antiquewhite', 'antiquewhite1', 'antiquewhite2', 'antiquewhite3', 'antiquewhite4', 'aquamarine', 'aquamarine1', 'aquamarine2', 'aquamarine3', 'aquamarine4', 'azure',
        'azure1', 'azure2', 'azure3', 'azure4', 'beige', 'bisque', 'bisque1', 'bisque2', 'bisque3', 'bisque4', 'black', 'blanchedalmond', 'blue', 'blue1', 'blue2', 'blue3', 'blue4', 'blueviolet', 'brown',
        'brown1',
        'brown2', 'brown3', 'brown4', 'burlywood', 'burlywood1', 'burlywood2', 'burlywood3', 'burlywood4', 'cadetblue', 'cadetblue1', 'cadetblue2', 'cadetblue3', 'cadetblue4', 'chartreuse', 'chartreuse1',
        'chartreuse2', 'chartreuse3', 'chartreuse4', 'chocolate', 'chocolate1', 'chocolate2', 'chocolate3', 'chocolate4', 'coral', 'coral1', 'coral2', 'coral3', 'coral4', 'cornflowerblue', 'cornsilk',
        'cornsilk1', 'cornsilk2', 'cornsilk3', 'cornsilk4', 'crimson', 'cyan', 'cyan1', 'cyan2', 'cyan3', 'cyan4', 'darkgoldenrod', 'darkgoldenrod1', 'darkgoldenrod2', 'darkgoldenrod3', 'darkgoldenrod4',
        'darkgreen',
        'darkkhaki', 'darkolivegreen', 'darkolivegreen1', 'darkolivegreen2', 'darkolivegreen3', 'darkolivegreen4', 'darkorange', 'darkorange1', 'darkorange2', 'darkorange3', 'darkorange4', 'darkorchid',
        'darkorchid1',
        'darkorchid2', 'darkorchid3', 'darkorchid4', 'darksalmon', 'darkseagreen', 'darkseagreen1', 'darkseagreen2', 'darkseagreen3', 'darkseagreen4', 'darkslateblue', 'darkslategray', 'darkslategray1',
        'darkslategray2', 'darkslategray3', 'darkslategray4', 'darkslategrey', 'darkturquoise', 'darkviolet', 'deeppink', 'deeppink1', 'deeppink2', 'deeppink3', 'deeppink4', 'deepskyblue', 'deepskyblue1',
        'deepskyblue2',
        'deepskyblue3', 'deepskyblue4', 'dimgray', 'dimgrey', 'dodgerblue', 'dodgerblue1', 'dodgerblue2', 'dodgerblue3', 'dodgerblue4', 'firebrick', 'firebrick1', 'firebrick2', 'firebrick3', 'firebrick4',
        'floralwhite',
        'forestgreen', 'gainsboro', 'ghostwhite', 'gold', 'gold1', 'gold2', 'gold3', 'gold4', 'goldenrod', 'goldenrod1', 'goldenrod2', 'goldenrod3', 'goldenrod4', 'gray', 'gray0', 'gray1', 'gray10', 'gray100',
        'honeydew', 'honeydew1', 'honeydew2', 'honeydew3', 'honeydew4', 'hotpink', 'hotpink1', 'hotpink2', 'hotpink3', 'hotpink4', 'indianred', 'indianred1', 'indianred2',
        'indianred3', 'indianred4', 'indigo', 'invis', 'ivory', 'ivory1', 'ivory2', 'ivory3', 'ivory4', 'khaki', 'khaki1', 'khaki2', 'khaki3', 'khaki4', 'lavender', 'lavenderblush', 'lavenderblush1', 'lavenderblush2',
        'lavenderblush3', 'lavenderblush4', 'lawngreen', 'lemonchiffon', 'lemonchiffon1', 'lemonchiffon2', 'lemonchiffon3', 'lemonchiffon4', 'lightblue', 'lightblue1', 'lightblue2', 'lightblue3', 'lightblue4',
        'lightcoral', 'lightcyan', 'lightcyan1', 'lightcyan2', 'lightcyan3', 'lightcyan4', 'lightgoldenrod', 'lightgoldenrod1', 'lightgoldenrod2', 'lightgoldenrod3', 'lightgoldenrod4', 'lightgoldenrodyellow',
        'lightgray', 'lightgrey', 'lightpink', 'lightpink1', 'lightpink2', 'lightpink3', 'lightpink4', 'lightsalmon', 'lightsalmon1', 'lightsalmon2', 'lightsalmon3', 'lightsalmon4', 'lightseagreen', 'lightskyblue',
        'lightskyblue1', 'lightskyblue2', 'lightskyblue3', 'lightskyblue4', 'lightslateblue', 'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightsteelblue1', 'lightsteelblue2', 'lightsteelblue3',
        'lightsteelblue4', 'lightyellow', 'lightyellow1', 'lightyellow2', 'lightyellow3', 'lightyellow4', 'limegreen', 'linen', 'magenta', 'magenta1', 'magenta2', 'magenta3', 'magenta4', 'maroon', 'maroon1',
        'maroon2', 'maroon3', 'maroon4', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumorchid1', 'mediumorchid2', 'mediumorchid3', 'mediumorchid4', 'mediumpurple', 'mediumpurple1', 'mediumpurple2',
        'mediumpurple3', 'mediumpurple4', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'mistyrose1', 'mistyrose2',
        'mistyrose3', 'mistyrose4', 'moccasin', 'navajowhite', 'navajowhite1', 'navajowhite2', 'navajowhite3', 'navajowhite4', 'navy', 'navyblue', 'none', 'oldlace', 'olivedrab', 'olivedrab1', 'olivedrab2',
        'olivedrab3', 'olivedrab4', 'orange', 'orange1', 'orange2', 'orange3', 'orange4', 'orangered', 'orangered1', 'orangered2', 'orangered3', 'orangered4', 'orchid', 'orchid1', 'orchid2', 'orchid3',
        'orchid4', 'palegoldenrod', 'palegreen', 'palegreen1', 'palegreen2', 'palegreen3', 'palegreen4', 'paleturquoise', 'paleturquoise1', 'paleturquoise2', 'paleturquoise3', 'paleturquoise4', 'palevioletred',
        'palevioletred1',
        'palevioletred2', 'palevioletred3', 'palevioletred4', 'papayawhip', 'peachpuff', 'peachpuff1', 'peachpuff2', 'peachpuff3', 'peachpuff4', 'peru', 'pink', 'pink1', 'pink2', 'pink3', 'pink4', 'plum',
        'plum1', 'plum2', 'plum3', 'plum4', 'powderblue', 'purple', 'purple1', 'purple2', 'purple3', 'purple4', 'red', 'red1', 'red2', 'red3', 'red4', 'rosybrown', 'rosybrown1', 'rosybrown2', 'rosybrown3',
        'rosybrown4', 'royalblue', 'royalblue1', 'royalblue2', 'royalblue3', 'royalblue4', 'saddlebrown', 'salmon', 'salmon1', 'salmon2', 'salmon3', 'salmon4', 'sandybrown', 'seagreen', 'seagreen1', 'seagreen2',
        'seagreen3', 'seagreen4', 'seashell', 'seashell1', 'seashell2', 'seashell3', 'seashell4', 'sienna', 'sienna1', 'sienna2', 'sienna3', 'sienna4', 'skyblue', 'skyblue1', 'skyblue2', 'skyblue3', 'skyblue4',
        'slateblue', 'slateblue1', 'slateblue2', 'slateblue3', 'slateblue4', 'slategray', 'slategray1', 'slategray2', 'slategray3', 'slategray4', 'slategrey', 'snow', 'snow1', 'snow2', 'snow3', 'snow4',
        'springgreen', 'springgreen1', 'springgreen2', 'springgreen3', 'springgreen4', 'steelblue', 'steelblue1', 'steelblue2', 'steelblue3', 'steelblue4', 'tan', 'tan1', 'tan2', 'tan3', 'tan4', 'thistle',
        'thistle1',
        'thistle2', 'thistle3', 'thistle4', 'tomato', 'tomato1', 'tomato2', 'tomato3', 'tomato4', 'transparent', 'turquoise', 'turquoise1', 'turquoise2', 'turquoise3', 'turquoise4', 'violet', 'violetred',
        'violetred1',
        'violetred2', 'violetred3', 'violetred4', 'wheat', 'wheat1', 'wheat2', 'wheat3', 'wheat4', 'white', 'whitesmoke', 'yellow', 'yellow1', 'yellow2', 'yellow3', 'yellow4', 'yellowgreen', 'gray11',
        'gray12', 'gray13', 'gray14', 'gray15', 'gray16', 'gray17', 'gray18', 'gray19', 'gray2', 'gray20', 'gray21', 'gray22', 'gray23', 'gray24', 'gray25', 'gray26', 'gray27', 'gray28', 'gray29', 'gray3',
        'gray30', 'gray31', 'gray32', 'gray33', 'gray34', 'gray35', 'gray36', 'gray37', 'gray38', 'gray39', 'gray4', 'gray40', 'gray41', 'gray42', 'gray43', 'gray44', 'gray45', 'gray46', 'gray47', 'gray48',
        'gray49', 'gray5', 'gray50', 'gray51', 'gray52', 'gray53', 'gray54', 'gray55', 'gray56', 'gray57', 'gray58', 'gray59', 'gray6', 'gray60', 'gray61', 'gray62', 'gray63', 'gray64', 'gray65', 'gray66',
        'gray67', 'gray68', 'gray69', 'gray7', 'gray70', 'gray71', 'gray72', 'gray73', 'gray74', 'gray75', 'gray76', 'gray77', 'gray78', 'gray79', 'gray8', 'gray80', 'gray81', 'gray82', 'gray83', 'gray84',
        'gray85', 'gray86', 'gray87', 'gray88', 'gray89', 'gray9', 'gray90', 'gray91', 'gray92', 'gray93', 'gray94', 'gray95', 'gray96', 'gray97', 'gray98', 'gray99', 'green', 'green1', 'green2', 'green3',
        'green4', 'greenyellow', 'grey', 'grey0', 'grey1', 'grey10', 'grey100', 'grey11', 'grey12', 'grey13', 'grey14', 'grey15', 'grey16', 'grey17', 'grey18', 'grey19', 'grey2', 'grey20', 'grey21', 'grey22',
        'grey23', 'grey24', 'grey25', 'grey26', 'grey27', 'grey28', 'grey29', 'grey3', 'grey30', 'grey31', 'grey32', 'grey33', 'grey34', 'grey35', 'grey36', 'grey37', 'grey38', 'grey39', 'grey4', 'grey40',
        'grey41', 'grey42', 'grey43', 'grey44', 'grey45', 'grey46', 'grey47', 'grey48', 'grey49', 'grey5', 'grey50', 'grey51', 'grey52', 'grey53', 'grey54', 'grey55', 'grey56', 'grey57', 'grey58', 'grey59',
        'grey6', 'grey60', 'grey61', 'grey62', 'grey63', 'grey64', 'grey65', 'grey66', 'grey67', 'grey68', 'grey69', 'grey7', 'grey70', 'grey71', 'grey72', 'grey73', 'grey74', 'grey75', 'grey76', 'grey77',
        'grey78', 'grey79', 'grey8', 'grey80', 'grey81', 'grey82', 'grey83', 'grey84', 'grey85', 'grey86', 'grey87', 'grey88', 'grey89', 'grey9', 'grey90', 'grey91', 'grey92', 'grey93', 'grey94', 'grey95',
        'grey96', 'grey97', 'grey98', 'grey99');

        static $table_count = 1;

        static $subgraph_number = 0;

        static $edge = array();

    public static function generateTable(array $param, $underline =array())
    {
        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2];
        $color = $param[3] ?? self::getColor($table_name);

        $db2 = Sgbd::sql(DB_DEFAULT);
        $db = Mysql::getDbLink($id_mysql_server, "EXPORT");

        $sql = "SELECT ROW_FORMAT as row_format, ENGINE as engine, TABLE_ROWS as table_rows
        FROM `INFORMATION_SCHEMA`.`TABLES` 
        WHERE TABLE_SCHEMA ='".$table_schema."' AND TABLE_NAME = '".$table_name."' AND TABLE_TYPE IN ('BASE TABLE', 'SYSTEM VERSIONED')";

        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res)) {
            $row_format = $ob->row_format;
            $engine = $ob->engine;
            $table_rows = $ob->table_rows;
        }


        $sql2 = "SELECT  COLUMN_NAME as colone, count(1) as cpt, group_concat(SEQ_IN_INDEX) as seq
        FROM information_schema.STATISTICS 
        WHERE TABLE_SCHEMA = '".$table_schema."' AND TABLE_NAME = '".$table_name."'  group by COLUMN_NAME;";

        $res2 = $db->sql_query($sql2);

        $INDEX = array();
        while($arr2 = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
            $INDEX[$arr2['colone']]['count'] = $arr2['cpt'];
            $INDEX[$arr2['colone']]['seq'] = $arr2['seq'];
        }

        $sql3 = "SELECT * FROM index_stats WHERE id_mysql_server = ".$id_mysql_server." AND table_schema='".$table_schema."' AND table_name = '".$table_name."'";

        $CARD = array();
        $res3 = $db2->sql_query($sql3);
        while($ob3 = $db2->sql_fetch_object($res3, MYSQLI_ASSOC)) {

            $total_size_index = self::format($ob3->size_for_table);

            if (empty($total_size_index)) {
                $total_size_index = "0";
            }

            $tmp = array();
            $tmp['columns'] = $ob3->columns;
            $tmp['size'] = $total_size_index;
            $tmp['R'] = $ob3->is_redundant;
            $tmp['U'] = $ob3->is_unused;

            $CARD[] = $tmp;
        }

        if (isset($table_rows))
        {
            $number_rows = "~".number_format($table_rows, 0, ',', ' ');
            if ($table_rows < self::MAX_ROWS_TO_REQUEST) {
                $number_rows = number_format(Table::getCount($param), 0, ',', ' ');
            }
        }
        else{
            $number_rows = "N/A";
        }

        $definitions = Table::getTableDefinition(array($id_mysql_server, $table_schema, $table_name));
        
        $return = '';
        // define color
        $return = "node[shape=none fontsize=8 ranksep=0 splines=true overlap=true];".PHP_EOL;
        

        $forground_color = '#000000';
        if (static::getBrightness($color) < 100) {
            $forground_color = '#FFFFFF';
        }

        //
        $return .= '  "'.$table_name.'"[ href="'.LINK.'table/mpd/'.$id_mysql_server.'/'.$table_schema.'/'.$table_name.'/"';
        $return .= 'tooltip="'.$table_schema.'.'.$table_name.'" 
        label =<<table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="4"><tr><td bgcolor="'.$color.'">
        <table BGCOLOR="#fafafa" BORDER="0" CELLBORDER="0" CELLSPACING="1" CELLPADDING="2">';
        $return .= '<tr><td PORT="title" colspan="3" bgcolor="'.$color.'"  align="center"><font color="'.$forground_color.'"><b>'.$table_name.'</b></font></td></tr>';

        if (empty($engine)) {
            //view
            $return .= '<tr><td colspan="3" bgcolor="grey" align="left">VIEW</td></tr>'.PHP_EOL;
        }
        else
        {
            $return .= '<tr><td colspan="3" bgcolor="grey" align="left">'.$engine.' ('.$row_format.')</td></tr>'.PHP_EOL;
        }
        
        $return .= '<tr><td colspan="3" bgcolor="grey" align="left">Total of <b>'.$number_rows.' </b>row(s)</td></tr>';

        $return .=
        '<tr>'
        .'<td bgcolor="#bbbbbb" align="left" title="'.__('Field').'">'.__('Field').'</td>'
        .'<td bgcolor="#bbbbbb" align="left">'.__('Type').'</td>'
        .'<td bgcolor="#bbbbbb" align="left">'.__('Key').'</td>'
        .'</tr>'.PHP_EOL;
        
        $line = 1;

        foreach ($definitions as $def) {

            $forground_color = '#000000';
            $bgcolor='bgcolor="'.$color.'"';
            if (empty($underline[$def['Field']])) {
                $bgcolor = 'bgcolor="#dddddd"';
            }
            else {
                $background_color = $underline[$def['Field']]['color'];
                $bgcolor = 'bgcolor="'.$background_color.'"';

                if (static::getBrightness($background_color) < 100) {
                    $forground_color = '#FFFFFF';
                }
            }

            if (strlen($def['Type']) > self::MAX_LENGTH){
                $def['Type'] = substr($def['Type'],0,self::MAX_LENGTH)."...";
            }

            $us = "";
            $ue = "";

            if ($def['Key'] === "PRI") {
                $us = "<u>";
                $ue = "</u>";
            }

            if (empty($def['Key'])) {
                if (! empty($INDEX[$def['Field']]))
                {
                    $def['Key'] = "IDX (".$INDEX[$def['Field']]['seq'].")";
                }
            }
            else{
                if (! empty($INDEX[$def['Field']])) {
                    $def['Key'] = $def['Key']." (".$INDEX[$def['Field']]['seq'].")";
                }
            }

            $return .=
                '<tr>'
                .'<td '.$bgcolor.' port="a'.$line.'" align="left" title="'.$def['Field'].'"><font color ="'.$forground_color.'">'.$us.''.$def['Field'].''.$ue.'</font></td>'
                .'<td '.$bgcolor.' align="left"><font color ="'.$forground_color.'">'.$us.''.$def['Type'].''.$ue.'</font></td>'
                .'<td '.$bgcolor.' port="d'.$line.'" align="left"><font color ="'.$forground_color.'">'.$us.''.$def['Key'].''.$ue.'&nbsp;</font></td>'
                .'</tr>'.PHP_EOL;
            $line++;
        }



        $bgindex = 'bgcolor="#bbbbbb"';
        $forground_color = '#000000';

        $return .= '<tr>'
        .'<td '.$bgindex.' colspan="2" align="center"><font color ="'.$forground_color.'"><b>'.__('Index').'</b></font></td>'
        .'<td '.$bgindex.' align="center"><font color ="'.$forground_color.'"><b>'.__('Size').'</b></font></td>'
        .'</tr>'.PHP_EOL;

        $bgindex = 'bgcolor="#dddddd"';

        foreach($CARD as $elem)
        {
            $b1 = "";
            $b2 = "";

            $extra = '';
            if (!empty($elem['R'])) {
                $extra .= 'R';
            }
            if (!empty($elem['U'])){
                $extra .= 'U';
            }
            if (! empty($extra))
            {
                $extra = '('.$extra.') ';
                $b1 = "<b>";
                $b2 = "</b>";
            }


            $return .= '<tr>'
            .'<td '.$bgindex.' colspan="2" align="left"><font color ="'.$forground_color.'">'.$extra.''.$elem['columns'].'</font></td>'
            .'<td '.$bgindex.' align="right"><font color ="'.$forground_color.'">'.$b1.$elem['size'].$b2.'</font></td>'
            .'</tr>'.PHP_EOL;

        }

        $return .= '</table>';
        $return .= '</td></tr></table>> ];'.PHP_EOL;

        return $return;
    }


    public static function getColor($string)
    {
        $color = self::$color[hexdec(substr(md5($string), 0, 2))];

        $h1 = substr(md5($string), 5, 2);
        $h2 = substr(md5($string), 10, 2);
        $h3 = substr(md5($string), 0, 2);

        $color = $h1.$h2.$h3;

        return "#".$color;
    }

    static public function generateStart($param=array())
    {
        $ret = 'digraph structs {rankdir=LR;  splines="compound"; margin="0.104,0"; fontname="arial" '.PHP_EOL; 
        //$ret = 'digraph structs {rankdir=LR; layout="sfdp"; splines="ortho"; fontname="arial" '.PHP_EOL; 
        $ret .= "labelloc=\"t\"; ".PHP_EOL;
        //$ret .= 'graph [pad="0.2", nodesep="0.1", ranksep="0.2"];'.PHP_EOL;
        $ret .= 'node [shape=none  fontname = "Arial"];'.PHP_EOL;
            //fwrite($fp, "nodesep=2;".PHP_EOL);
        return $ret;
    }
    
    static public function generateEnd($param = array())
    {
        $ret = "".PHP_EOL;
        //$ret .= '[arrowhead=none arrowtail=none arrowhead=none penwidth="3" ';
        //$ret .= 'fontname="arial" fontsize=8 edgeURL=""];'.PHP_EOL;
        $ret .= "}\n";
        return $ret;
    }

    /*
     * (PmaControl 2.0.64)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@68koncept.com>
     * @return boolean File_name where is locate the SVG
     * @package Controller
     * @since 2.0.64  First time this was introduced.
     * @description 
     * @access public
     *
     */

    static public function generateDot($reference, $graph)
    {
        $type = "svg";
        $type2 = "png";

        $dot_file = TMP."dot/".$reference.".dot";
        $file_name = TMP."dot/".$reference.".".$type;
        $file_name2 = TMP."dot/".$reference.".".$type2;

        file_put_contents($dot_file, $graph);

        $dot = 'cd '.TMP.'dot && dot -T'.$type.' '.$dot_file.' -o '.$file_name.'';
        //debug($dot);
        exec($dot);

        $dot2 = 'cd '.TMP.'dot && dot -T'.$type2.' '.$dot_file.' -o '.$file_name2.'';
        exec($dot2);

        //post treatment SVG
        self::removeBackground($file_name);
        self::replaceLinkImg($file_name);

        return $file_name;
    }

    static public function generateEdge($edge)
    {
        if (empty($edge['options'])){
            $edge['options'] = array();
        }

        $return = "".$edge['arrow'];
        $return .= '[tooltip="'.$edge['tooltip'].'" color="'.$edge['color'].'" penwidth="3" ';
        $return .= 'fontname="arial" fontsize=8 edgeURL="" ';
        foreach($edge['options'] as $key => $option) {
            $return .= $key.'="'.$option.'" ';
        }

        $return .= '];'.PHP_EOL;
        
        return $return;
    }

    static public function getBrightness($hex) {
        // returns brightness value from 0 to 255
        // strip off any leading #
        $hex = str_replace('#', '', $hex);
       
        $c_r = hexdec(substr($hex, 0, 2));
        $c_g = hexdec(substr($hex, 2, 2));
        $c_b = hexdec(substr($hex, 4, 2));
       
        return (($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000;
    }

    static public function removeBackground($file_name)
    {
        // Remove background generated by Dot
        // remove this polygon: <polygon fill="white" stroke="transparent" points="-4,4 -4,-2138 442,-2138 442,4 -4,4"></polygon>
        $svg = file_get_contents($file_name);
        $elem = Grabber::getTagContent($svg, '<polygon fill="white" stroke="transparent"', $strip = false);
        $svg = str_replace($elem, '', $svg);
        file_put_contents($file_name, $svg);
    }


    static public function replaceLinkImg($file_name)
    {
        // Remove background generated by Dot
        // remove this polygon: <polygon fill="white" stroke="transparent" points="-4,4 -4,-2138 442,-2138 442,4 -4,4"></polygon>
        $svg = file_get_contents($file_name);
        
        $image_server  = ROOT."/App/Webroot/image/dot/";
        $image_url = WWW_ROOT."image/icon/";

        $svg = str_replace($image_server, $image_url, $svg);
        file_put_contents($file_name, $svg);
    }



    static public function generateHiddenEdge($hidden_edge)
    {
        $ret = '';
        $ret .= "".$hidden_edge."[style=invis];".PHP_EOL;
        //$ret .= "".$hidden_edge.";".PHP_EOL;
        return $ret;
    }

    static public function openSubgraph($param)
    {
        self::$subgraph_number++;

        $ret = '';
        $ret .= "subgraph cluster_".self::$subgraph_number." {".PHP_EOL;
        
        foreach($param as $key => $elem) {
            $ret .= $key.'="'.$elem.'";'.PHP_EOL;
        }

        return $ret;
    }

    static public function closeSubgraph($param)
    {
        self::$subgraph_number++;

        $ret = '';
        $ret .= "}".PHP_EOL;
        return $ret;
    }

    static public function generateServer($server)
    { 
        /*
         * to be sure to insert image with add <?xml version="1.0" encoding="UTF-8" standalone="no"?> in top of SVG
         */
        $image_server = ROOT."/App/Webroot/image/dot/";

        $return = '';
        // define color

        //shape=plaintext
        $return = "node[shape=none fontsize=8 ranksep=10 splines=true overlap=true];".PHP_EOL;
        //$return = "node[shape=plaintext fontsize=8];".PHP_EOL;

        Debug::debug($server, "DEBUG TO REMOVE");
        
        $format = Format::getMySQLNumVersion($server['version'], $server['version_comment']);

        $fork = $format['fork'];
        $number = $format['number'];

        $forground_color = '#000000';
        if (static::getBrightness($server['color']) < 100) {
            $forground_color = '#FFFFFF';
        }

        $image_logo = strtolower($fork).'.svg';

        if (!empty($server['is_proxysql']) && $server['is_proxysql'] == "1" ) {
            $image_logo = 'proxysql.png';            
        }

        if (!empty($server['wsrep_on']) && strtolower($server['wsrep_on']) == "on" ) {
            $image_logo = 'galera.svg';
        }

        //
        $return .= '  "'.$server['id_mysql_server'].'"[ href="'.LINK.'MysqlServer/processlist/'.$server['id_mysql_server'].'/"';
        $return .= 'tooltip="'.$server['display_name'].'"
        shape=plaintext,label =<<table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="4">
        <tr><td port="target" bgcolor="'.$server['color'].'">
        
        <table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="0"><tr><td>

        <table BGCOLOR="#eafafa" BORDER="0" CELLBORDER="0" CELLSPACING="1" CELLPADDING="2">'.PHP_EOL;
        $return .= '<tr><td PORT="title" colspan="2" bgcolor="'.$server['color'].'">
        <font color="'.$forground_color.'"><b>'.$server['display_name'].'</b></font></td></tr>';


        
        $return .= '<tr><td bgcolor="#eeeeee" CELLPADDING="0" width="28" rowspan="2" port="from"><IMG SRC="'.$image_server.$image_logo.'" /></td>
        <td bgcolor="lightgrey" width="100" align="left">'.$fork.' : '.$number.'</td></tr>';
        $return .= '<tr><td bgcolor="lightgrey" width="100" align="left">'.$server['ip'].':'.$server['port'].'</td></tr>'.PHP_EOL;

        //$return .= '<tr><td colspan="2" bgcolor="lightgrey" align="left">'.__('Since')." : ".$server['date'].'</td></tr>'.PHP_EOL;

        if (empty($server['is_proxysql']) )
        {
            $return .= '<tr><td colspan="2" bgcolor="lightgrey" align="left">'.__('Time zone')." : ".$server['time_zone'].'</td></tr>'.PHP_EOL;
            $return .= '<tr><td colspan="2" bgcolor="lightgrey" align="left">'.__('Server ID')." : ".$server['server_id'].' - Auto Inc : '.$server['auto_increment_offset'].'/'.$server['auto_increment_increment'].'</td></tr>'.PHP_EOL;
            $return .= '<tr><td colspan="2" bgcolor="lightgrey" align="left">'.__('Binlog')." : ".$server['binlog_format'].'</td></tr>'.PHP_EOL;
            $return .= '<tr><td colspan="2" bgcolor="lightgrey" align="left">'.__('Read only')." : ".$server['read_only'].' - LSU : '.$server['log_slave_updates'].'</td></tr>'.PHP_EOL;
            

            $return .= '</table>'.PHP_EOL;
    
            $return .= '</td></tr><tr><td>';

            
            $return .= '<table BGCOLOR="#eafafa" BORDER="0" CELLBORDER="0" CELLSPACING="1" CELLPADDING="2">
            <tr>
                <td align="left" bgcolor="grey">Schema</td>
                <td bgcolor="grey">B</td>
                <td bgcolor="grey">R</td>
            </tr>'.PHP_EOL;
            
            foreach($server['mysql_database'] as $database)
            {
                $return .= '<tr>'.PHP_EOL;
                $return .= '<td bgcolor="darkgrey" align="left">'.$database.'</td>'.PHP_EOL;
                $return .= '<td bgcolor="darkgrey" align="center">'.'🛢'.'</td>'.PHP_EOL;
                $return .= '<td bgcolor="darkgrey" align="center">'.'🛢'.'</td>'.PHP_EOL;
                $return .= '</tr>'.PHP_EOL;

                if ($database == "eshop")
                {

                    $return .= '<tr><td colspan="3" bgcolor="green" align="left">🕷 '.'simulation#P#pt1{0,1}'.'</td></tr>'.PHP_EOL;
                    $return .= '<tr><td colspan="3" bgcolor="red" align="left">🕷 '.'simulation#P#pt2{0,1}'.'</td></tr>'.PHP_EOL;
                    $return .= '<tr><td colspan="3" bgcolor="lightgrey" align="left">🕷 '.'simulation#P#pt3{0,1}'.'</td></tr>'.PHP_EOL;
                    $return .= '<tr><td colspan="3" bgcolor="lightgrey" align="left">🕷 '.'simulation#P#pt4{0,1}'.'</td></tr>'.PHP_EOL;
                    
                }

            }
            $return .= '</table>'.PHP_EOL;
            
            $return .= '</td></tr>'.PHP_EOL;
            $return .= '</table>'.PHP_EOL;


            
            $return .= '</td></tr></table>> ];'.PHP_EOL;

            //$return .= '<tr><td colspan="2" bgcolor="lightgrey" align="left">'.__('Auto_increment')." : ".$server['time_zone'].'</td></tr>'.PHP_EOL;
        }elseif ($server['is_proxysql'] == "1")
        {
            
            //Debug::debug($server);
            //exit;
            $hostgroup = 0;
            $i = 0;
            foreach($server['proxysql_runtime_mysql_servers'] as $link)
            {
                $i++;
                
                if ($hostgroup != $link['hostgroup_id'])
                {
                    $port = crc32($link['hostgroup_id'].'::');
                    $return .= '<tr><td colspan="2" port="'.$port.'" bgcolor="grey" align="left"><font color="#000000">'.__('Host group').' : '.$link['hostgroup_id'].'</font></td></tr>'.PHP_EOL;
                }
                //⛯ PmaControl
                $hostgroup = $link['hostgroup_id'];
                $forground_color = 'white';
                //OVER RIDE STYLE
                //⚙🔥

                $extra = '';
                $bgcolor = Dot3::$config['PROXYSQL_'.$link['status']]['color'];
                $forground_color =Dot3::$config['PROXYSQL_'.$link['status']]['font'];
                foreach($server['proxy_connect_error'] as $hostname => $error)
                {
                    $extra = '';
                    if ($hostname == $link['hostname'].':'.$link['port'])
                    {
                        
                        //$forground_color = 'black';
                        //$bgcolor = 'darkgrey';
                        if ($link['status'] == "ONLINE")
                        {
                            $bgcolor = Dot3::$config['PROXYSQL_CONFIG']['color'];
                            $extra = ' ⚙🔥';
                        }
                        else{
                            $extra = ' 🔥';
                        }


                        break;
                    }
                }

                $port = crc32($link['hostgroup_id'].':'.$link['hostname'].':'.$link['port']);
                
                $return .= '<tr>';
                $return .= '<td colspan="2" bgcolor="'.$bgcolor.'" align="left" port="'.$port.'">';
                $return .= '<font color="'.$forground_color.'">'.$link['hostname'].':'.$link['port'].''.$extra.'</font>';
                $return .= '</td>';
                $return .= '</tr>'.PHP_EOL;
            }
            $return .= '</table>';
            
            
            $return .= '</td></tr>'.PHP_EOL;
            $return .= '</table>'.PHP_EOL;
            
            $return .= '</td></tr></table>> ];'.PHP_EOL;
        }
        
        
        // http://localhost/pmacontrol/image/icon/proxysql.png
        /*
        $return .=
        '<tr>'
        .'<td bgcolor="#bbbbbb" align="left" title="'.__('Field').'">'.__('Field').'</td>'
        .'<td bgcolor="#bbbbbb" align="left">'.__('Type').'</td>'
        .'<td bgcolor="#bbbbbb" align="left">'.__('Key').'</td>'
        .'</tr>'.PHP_EOL;
        
        $line = 1;
        */
    
        //databases
        return $return;
    }


    static function buildApp()
    {

        //TO DO
        $db = Sgbd::sql('proxysql_1');

        $sql ="select cli_host, srv_host,srv_port, hostgroup, user, count(1) as cpt, db as table_schema,sum(time_ms) as sum_time_ms  
        from stats_mysql_processlist WHERE command != 'Sleep' and hostgroup!=-1 group
        by cli_host,srv_host,srv_port,hostgroup, user, db ;";

        $res = $db->sql_query($sql);

        $data = array();
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $data[$arr['cli_host']][$arr['user']][$arr['table_schema']] = $arr;
        }   


        krsort($data);

        Debug::debug($data);

        $APP = '';
        foreach($data as $ip => $users)
        {
            self::$edge= array();

            $lines = '<table BGCOLOR="#eafafa" BORDER="0" CELLBORDER="0" CELLSPACING="1" CELLPADDING="2">'.PHP_EOL;
            $lines .=  '<tr><td width="150" bgcolor="grey" colspan="4"><font color="#ffffff"><b>'.'App ??'.'</b></font></td></tr>';
            $lines .=  '<tr><td width="150" bgcolor="lightgrey" colspan="4">'.$ip.'</td></tr>';

            $lines .= '<tr>';
            $lines .= '<td bgcolor="grey">'.__("User").'</td>';
            $lines .= '<td bgcolor="grey">'.__("Schema").'</td>';
            $lines .= '<td bgcolor="grey">'.__("Con").'</td>';
            $lines .= '<td bgcolor="grey">'.__("Ms").'</td>';
            $lines .= '</tr>'.PHP_EOL;

            foreach($users as $name_user => $dbs)
            {
                $nb_dbs = count($dbs);
                $lines .=  '<tr><td bgcolor="darkgrey" align="left" rowspan="'.$nb_dbs.'">'.$name_user.'</td>';
                $i = 0;

                ksort($dbs);
                foreach($dbs as $db_name => $elem)
                {
                    $i++;
                    if ($i != 1) {
                        $lines .= '<tr>';
                    }
                    $port_ori = crc32(json_encode($elem));
                    $port_dest = crc32($elem['hostgroup'].':'.$elem['srv_host'].':'.$elem['srv_port']);


                    $lines .= '<td bgcolor="darkgrey" align="left">'.$db_name.'</td>';
                    $lines .= '<td bgcolor="darkgrey" align="right">'.$elem['cpt'].'</td>';
                    $lines .= '<td bgcolor="darkgrey" align="right" port="'.$port_ori.'">'.$elem['sum_time_ms'].'</td>';
                    $lines .= '</tr>'.PHP_EOL;

                    
                    self::$edge[] = crc32($ip).':'.$port_ori.' -> 65:'.$port_dest;
                }
            }
            $lines .= '</table>'.PHP_EOL;
            $APP .= self::buildBox($lines, crc32($ip), "", "All", "grey").PHP_EOL;


            foreach(self::$edge as $edge)
            {
                $APP .= $edge.'[tooltip="OK" color="darkgrey" fontname="arial" fontsize=8 edgeURL="" arrowhead="none" penwidth="3" style="solid" arrowsize="1.5" ];'.PHP_EOL;
            }
        }




        return $APP;
    }

    static function buildBox($body, $id_box, $link, $display_name, $box_color)
    {

        $return = PHP_EOL;
        $return .= '  "'.$id_box.'"[ href="'.$link.'"';
        $return .= 'tooltip="'.$display_name.'"
        shape=plaintext,label =<<table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="4">
        <tr><td port="target" bgcolor="'.$box_color.'">
        
        <table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="0"><tr><td>';

        $return .= $body;
        $return .= '</td></tr></table>'.PHP_EOL;
        $return .= '</td></tr></table>> ];'.PHP_EOL;
        
        return $return;
    }


    static function format($bytes, $decimals = 2)
    {
        // && $bytes != 0
        if (empty($bytes)) {
            return "";
        }
        $sz = ' KMGTP';

        $factor = (int) floor(log($bytes) / log(1024));

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
    }




    static function startCluster($cluster_name, $type='galera', $nodes=array() )
    {
        if (! in_array($type, array('galera','groupe', 'xdb', 'ndb')))
        {
            throw new \Exception('Impossible to find this cluster');
        }


        $return = "subgraph cluster_cluster_cats {".PHP_EOL;

    }


    static function endClsuter()
    {



    }
}