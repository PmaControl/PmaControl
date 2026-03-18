<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use App\Controller\Listener;
use App\Controller\MaxScale;
use \App\Library\Table;
use \App\Library\Format;
use \App\Library\Ofuscate;
use \App\Controller\Dot3;
use \Glial\Sgbd\Sgbd;

use \Glial\Extract\Grabber;
/**
 * Class responsible for graphviz workflows.
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
class Graphviz
{
    private const OFFLINE_RED = '#FF0000';
    private static string $lastGenerateDotError = '';
    // en dessous de MAX_ROWS_TO_REQUEST on va faire un select count(1) pour avoir le nombre de ligne exacte dans la table
    const MAX_ROWS_TO_REQUEST = 10000;

    //max char for type, to prevent really big table with enum
    const MAX_LENGTH = 25;
/**
 * Stores `$color` for color.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
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

/**
 * Stores `$table_count` for table count.
 *
 * @var int
 * @phpstan-var int
 * @psalm-var int
 */
        static $table_count = 1;

/**
 * Stores `$subgraph_number` for subgraph number.
 *
 * @var int
 * @phpstan-var int
 * @psalm-var int
 */
        static $subgraph_number = 0;

/**
 * Stores `$edge` for edge.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $edge = array();

    private static function openHtmlLikeLabel(string $targetPort, string $borderColor, string $innerBackground = '#eafafa'): string
    {
        return 'shape=plaintext,label =<<table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="4">' . PHP_EOL
            . '    <tr>' . PHP_EOL
            . '      <td port="' . $targetPort . '" bgcolor="' . $borderColor . '">' . PHP_EOL
            . '        <table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="0">' . PHP_EOL
            . '          <tr>' . PHP_EOL
            . '            <td>' . PHP_EOL
            . '              <table BGCOLOR="' . $innerBackground . '" BORDER="0" CELLBORDER="0" CELLSPACING="1" CELLPADDING="2">' . PHP_EOL;
    }

    private static function closeHtmlLikeLabel(bool $withBracket = true): string
    {
        $suffix = '</table>>';
        if ($withBracket) {
            $suffix .= ' ];';
        }

        return '              </table>' . PHP_EOL
            . '            </td>' . PHP_EOL
            . '          </tr>' . PHP_EOL
            . '        </table>' . PHP_EOL
            . '      </td>' . PHP_EOL
            . '    </tr>' . PHP_EOL
            . '  ' . $suffix;
    }

    private static function htmlRow(array $cells, int $indentLevel = 7): string
    {
        $indent = str_repeat('  ', $indentLevel);
        $cellIndent = str_repeat('  ', $indentLevel + 1);
        $row = $indent . '<tr>' . PHP_EOL;
        foreach ($cells as $cell) {
            $row .= $cellIndent . $cell . PHP_EOL;
        }
        $row .= $indent . '</tr>' . PHP_EOL;

        return $row;
    }

    private static function isOfflineServer(array $server): bool
    {
        return isset($server['mysql_available'])
            && (string)$server['mysql_available'] === '0'
            && empty($server['is_sst_receiver']);
    }

    private static function isGreyBackground(string $background): bool
    {
        $normalized = strtolower(trim($background));

        return in_array($normalized, [
            'grey',
            'gray',
            'lightgrey',
            'lightgray',
            'darkgrey',
            'darkgray',
            '#eeeeee',
            '#dddddd',
            '#cccccc',
            '#bbbbbb',
            '#aaaaaa',
        ], true);
    }

    private static function resolveOfflineRowColors(array $server, string $background, string $fontColor): array
    {
        if (!self::isOfflineServer($server) || self::isGreyBackground($background)) {
            return [$background, $fontColor];
        }

        return [self::OFFLINE_RED, '#ffffff'];
    }

/**
 * Handle graphviz state through `generateTable`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $param Route parameters forwarded by the router.
 * @phpstan-param array $param
 * @psalm-param array $param
 * @param mixed $underline Input value for `underline`.
 * @phpstan-param mixed $underline
 * @psalm-param mixed $underline
 * @return mixed Returned value for generateTable.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generateTable()
 * @example /fr/graphviz/generateTable
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function generateTable(array $param, $underline =array())
    {
        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2];
        $color = $param[3] ?? self::getColor($table_name);

        $db2 = Sgbd::sql(DB_DEFAULT);
        $db = Mysql::getDbLink($id_mysql_server, "EXPORT");

        // a déplacer dans la partie App
        $sql = "SELECT ROW_FORMAT as row_format, ENGINE as engine, TABLE_ROWS as table_rows
        FROM `INFORMATION_SCHEMA`.`TABLES` 
        WHERE TABLE_SCHEMA ='".$table_schema."' AND TABLE_NAME = '".$table_name."' AND TABLE_TYPE IN ('BASE TABLE', 'SYSTEM VERSIONED')";

        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res)) {
            $row_format = $ob->row_format;
            $engine = $ob->engine;
            $table_rows = $ob->table_rows;
        }

        // idem
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

        /*
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
        }*/

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
        
        $colspan = 2;
        $return .= '<tr><td PORT="title" colspan="'.$colspan.'" bgcolor="'.$color.'"  align="center"><font color="'.$forground_color.'"><b>'.$table_name.'</b></font></td></tr>';

        if (empty($engine)) {
            //view
            $return .= '<tr><td colspan="'.$colspan.'" bgcolor="grey" align="left">VIEW</td></tr>'.PHP_EOL;
        }
        else
        {
            $return .= '<tr><td colspan="'.$colspan.'" bgcolor="grey" align="left">'.$engine.' ('.$row_format.')</td></tr>'.PHP_EOL;
        }
        
        $return .= '<tr><td colspan="'.$colspan.'" bgcolor="grey" align="left">Total of <b>'.$number_rows.' </b>row(s)</td></tr>';

        $return .=
        '<tr>'
        .'<td bgcolor="#bbbbbb" align="left" title="'.__('Field').'">'.__('Field').'</td>'
        .'<td bgcolor="#bbbbbb" align="left">'.__('Type').'</td>'
      //  .'<td bgcolor="#bbbbbb" align="left">'.__('Key').'</td>'
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
                //.'<td '.$bgcolor.' align="left"><font color ="'.$forground_color.'">'.$us.''.$def['Type'].''.$ue.'</font></td>'
                .'<td '.$bgcolor.' port="d'.$line.'" align="left"><font color ="'.$forground_color.'">'.$us.''.$def['Type'].''.$ue.'</font></td>'
               // .'<td '.$bgcolor.' port="d'.$line.'" align="left"><font color ="'.$forground_color.'">'.$us.''.$def['Key'].''.$ue.'&nbsp;</font></td>'
                .'</tr>'.PHP_EOL;
            $line++;
        }



        $bgindex = 'bgcolor="#bbbbbb"';
        $forground_color = '#000000';


        if (! empty($CARD))
        {

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
        }

        $return .= '</table>';
        $return .= '</td></tr></table>> ];'.PHP_EOL;

        return $return;
    }


/**
 * Retrieve graphviz state through `getColor`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @return mixed Returned value for getColor.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getColor()
 * @example /fr/graphviz/getColor
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function getColor($string)
    {
        $color = self::$color[hexdec(substr(md5($string), 0, 2))];

        $h1 = substr(md5($string), 5, 2);
        $h2 = substr(md5($string), 10, 2);
        $h3 = substr(md5($string), 0, 2);

        $color = $h1.$h2.$h3;

        return "#".$color;
    }

/**
 * Handle graphviz state through `generateStart`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for generateStart.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generateStart()
 * @example /fr/graphviz/generateStart
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function generateStart($param=array())
    {
        //margin="0.104,0.0'.$rand.'";
        $ret = 'digraph structs {rankdir=LR;  splines="compound";  fontname="arial" '.PHP_EOL; 
        //$ret = 'digraph structs {rankdir=LR; layout="sfdp"; splines="ortho"; fontname="arial" '.PHP_EOL; 
        $ret .= "labelloc=\"t\"; ".PHP_EOL;
        //$ret .= 'graph [pad="0.2", nodesep="0.1", ranksep="0.2"];'.PHP_EOL;
        $ret .= 'node [shape=none  fontname = "Arial"];'.PHP_EOL;
            //fwrite($fp, "nodesep=2;".PHP_EOL);

        return $ret;
    }
    
/**
 * Handle graphviz state through `generateEnd`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for generateEnd.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generateEnd()
 * @example /fr/graphviz/generateEnd
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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
        self::$lastGenerateDotError = '';
        $type = "svg";
        $type2 = "png";

        $dot_file = TMP."dot/".$reference.".dot";
        $file_name = TMP."dot/".$reference.".".$type;
        $file_name2 = TMP."dot/".$reference.".".$type2;

        file_put_contents($dot_file, $graph);
        usleep(500);

        //hidding warnings
        //$dot = 'cd '.TMP.'dot && dot -T'.$type.' '.$dot_file.' -o '.$file_name.'';
        $dot = 'cd '.TMP.'dot && dot -T'.$type.' '.$dot_file.' -o '.$file_name.' 2>&1';
        

        $output_svg = array();
        $result_svg = 0;
        exec($dot, $output_svg, $result_svg);
        $svgErrorOutput = trim(implode("\n", $output_svg));

        if ($result_svg !== 0) {
            $fallbacks = array('svg', 'svg:svg', 'svg:cairo');
            foreach ($fallbacks as $fallback) {
                if ($fallback === $type) {
                    continue;
                }

                //fix error one day
                //$dot_try = 'cd '.TMP.'dot && dot -T'.$type2.' '.$dot_file.' -o '.$file_name2.'';
                $dot_try = 'cd '.TMP.'dot && dot -T'.$fallback.' '.$dot_file.' -o '.$file_name.' 2>&1';
                $output_try = array();
                $result_try = 0;
                exec($dot_try, $output_try, $result_try);
                if ($result_try === 0) {
                    $type = $fallback;
                    $svgErrorOutput = '';
                    break;
                }

                $tryOutput = trim(implode("\n", $output_try));
                if ($tryOutput !== '') {
                    $svgErrorOutput = $tryOutput;
                }
            }
        }

        $dot2 = 'cd '.TMP.'dot && dot -T'.$type2.' '.$dot_file.' -o '.$file_name2.' 2>&1';
        $output_png = array();
        $result_png = 0;
        exec($dot2, $output_png, $result_png);

        if ($result_png !== 0) {
            $fallbacks = array('png', 'png:png', 'png:cairo');
            foreach ($fallbacks as $fallback) {
                if ($fallback === $type2) {
                    continue;
                }

                $dot_try = 'cd '.TMP.'dot && dot -T'.$fallback.' '.$dot_file.' -o '.$file_name2.' 2>&1';
                $output_try = array();
                $result_try = 0;
                exec($dot_try, $output_try, $result_try);
                if ($result_try === 0) {
                    $type2 = $fallback;
                    break;
                }
            }
        }

        //post treatment SVG (only when we have a readable SVG file)
        if (file_exists($file_name) && substr($file_name, -4) === '.svg') {
            $firstBytes = file_get_contents($file_name, false, null, 0, 8);
            if ($firstBytes !== false && strncmp($firstBytes, "\x89PNG\r\n\x1a\n", 8) === 0) {
                self::$lastGenerateDotError = 'Graphviz fallback produced PNG instead of SVG.';
                return $file_name;
            }

            self::removeBackground($file_name);
            self::replaceLinkImg($file_name);
        }

        if ($result_svg !== 0 && $svgErrorOutput !== '') {
            self::$lastGenerateDotError = $svgErrorOutput;
        }

        return $file_name;
    }

    public static function getLastGenerateDotError(): string
    {
        return self::$lastGenerateDotError;
    }

/**
 * Handle graphviz state through `generateEdge`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $edge Input value for `edge`.
 * @phpstan-param mixed $edge
 * @psalm-param mixed $edge
 * @return mixed Returned value for generateEdge.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generateEdge()
 * @example /fr/graphviz/generateEdge
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function generateEdge($edge)
    {
        if (empty($edge['options'])){
            $edge['options'] = array();
        }

        if (!empty($edge['tooltip']))
        {
            $edge['options']['tooltip'] = $edge['tooltip'];
        }

        if (empty($edge['color']))
        {
            $edge['color'] = "#0000ff";
        }

        $return = "".$edge['arrow'];
        $return .= '[color="'.$edge['color'].'" penwidth="3" ';
        $return .= 'fontname="arial" fontsize=8 ';
        foreach($edge['options'] as $key => $option) {
            $return .= $key.'="'.$option.'" ';
        }

        $return .= '];'.PHP_EOL;

        // Trick to split double arrow in noth direction
        $return .= 'node[shape=none fontsize=8 ranksep=10 splines=true overlap=true];'.PHP_EOL;

        return $return;
    }

/**
 * Retrieve graphviz state through `getBrightness`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $hex Input value for `hex`.
 * @phpstan-param mixed $hex
 * @psalm-param mixed $hex
 * @return mixed Returned value for getBrightness.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getBrightness()
 * @example /fr/graphviz/getBrightness
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getBrightness($hex) {
        // returns brightness value from 0 to 255
        // strip off any leading #

        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $hex)) {
            throw new \Exception("Erreur : '$hex' n'est pas une couleur HEX valide.");
        }

        $hex = str_replace('#', '', $hex);
       
        $c_r = hexdec(substr($hex, 0, 2));
        $c_g = hexdec(substr($hex, 2, 2));
        $c_b = hexdec(substr($hex, 4, 2));
        
        return (($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000;
    }

/**
 * Delete graphviz state through `removeBackground`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $file_name Input value for `file_name`.
 * @phpstan-param mixed $file_name
 * @psalm-param mixed $file_name
 * @return void Returned value for removeBackground.
 * @phpstan-return void
 * @psalm-return void
 * @see self::removeBackground()
 * @example /fr/graphviz/removeBackground
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function removeBackground($file_name)
    {
        if (!is_string($file_name) || $file_name === '' || !file_exists($file_name)) {
            return;
        }

        $svg = file_get_contents($file_name);
        if ($svg === false || $svg === '') {
            return;
        }

        // Remove the white background polygon generated by dot without using the
        // legacy HTML parser, which corrupts XML/SVG payloads.
        $patterns = array(
            '/<polygon\b[^>]*\bfill="white"[^>]*\bstroke="transparent"[^>]*><\/polygon>/i',
            '/<polygon\b[^>]*\bstroke="transparent"[^>]*\bfill="white"[^>]*><\/polygon>/i',
            '/<polygon\b[^>]*\bfill="white"[^>]*\bstroke="transparent"[^>]*\/>/i',
            '/<polygon\b[^>]*\bstroke="transparent"[^>]*\bfill="white"[^>]*\/>/i',
        );

        $cleaned = preg_replace($patterns, '', $svg);
        if (is_string($cleaned) && $cleaned !== $svg) {
            file_put_contents($file_name, $cleaned);
        }
    }

    /**
     * Remplace les liens d'images dans un fichier SVG.
     *
     * Cette fonction lit un fichier SVG, remplace les chemins d'images générés 
     * par Dot par de nouveaux chemins d'images, puis réécrit le fichier SVG 
     * avec les modifications.
     *
     * @param string $file_name Le chemin du fichier SVG à modifier.
     * @return void
     */

    static public function replaceLinkImg($file_name)
    {
        if (!is_string($file_name) || $file_name === '' || !file_exists($file_name)) {
            return;
        }

        $svg = file_get_contents($file_name);
        if ($svg === false || $svg === '') {
            return;
        }

        $svg = self::postProcessSvgMarkup($svg);
        file_put_contents($file_name, $svg);
    }

    public static function postProcessSvgMarkup($svg)
    {
        if (!is_string($svg) || $svg === '') {
            return $svg;
        }

        $image_server  = ROOT."/App/Webroot/image/dot/";
        $image_url = WWW_ROOT."image/icon/";
        $symbols = array();
        $symbolMap = array();

        if (!preg_match_all('/<image\b([^>]*?)\s(?:xlink:href|href)="([^"]+)"([^>]*?)\/>/i', $svg, $matches, PREG_SET_ORDER)) {
            return str_replace($image_server, $image_url, $svg);
        }

        foreach ($matches as $match) {
            $href = $match[2];
            $diskPath = self::resolveGraphvizImagePath($href);

            if ($diskPath === null || !file_exists($diskPath)) {
                continue;
            }

            $extension = strtolower((string) pathinfo($diskPath, PATHINFO_EXTENSION));
            if ($extension === 'svg') {
                if (empty($symbolMap[$diskPath])) {
                    $iconSvg = file_get_contents($diskPath);
                    if ($iconSvg === false || $iconSvg === '') {
                        continue;
                    }

                    $symbolId = 'pmac-icon-'.md5($diskPath);
                    $symbol = self::buildSvgSymbol($iconSvg, $symbolId);
                    if ($symbol === '') {
                        continue;
                    }

                    $symbols[] = $symbol;
                    $symbolMap[$diskPath] = $symbolId;
                }
                continue;
            }

            $dataUri = self::buildImageDataUri($diskPath);
            if ($dataUri === '') {
                continue;
            }

            $quotedHref = preg_quote($href, '/');
            $svg = preg_replace(
                '/(<image\b[^>]*?\s(?:xlink:href|href)=")'.$quotedHref.'(")/i',
                '$1'.$dataUri.'$2',
                $svg
            );
        }

        if (!empty($symbolMap)) {
            $symbols = array_values(array_unique($symbols));
            $svg = self::injectSvgSymbols($svg, implode('', $symbols));

            $svg = preg_replace_callback(
                '/<image\b([^>]*?)\s(?:xlink:href|href)="([^"]+)"([^>]*?)\/>/i',
                function ($matches) use ($symbolMap) {
                    $href = $matches[2];
                    $diskPath = self::resolveGraphvizImagePath($href);
                    if ($diskPath === null || empty($symbolMap[$diskPath])) {
                        return $matches[0];
                    }

                    $attributes = trim($matches[1].' '.$matches[3]);
                    $attributes = preg_replace('/\s(?:xlink:href|href)="[^"]*"/i', '', ' '.$attributes);
                    $attributes = trim((string) $attributes);

                    if ($attributes !== '') {
                        $attributes = ' '.$attributes;
                    }

                    return '<use xlink:href="#'.$symbolMap[$diskPath].'"'.$attributes.'/>';
                },
                $svg
            );
        }

        $svg = str_replace($image_server, $image_url, $svg);
        return $svg;
    }

    public static function buildSvgDownloadDataUri($svg)
    {
        $svg = self::postProcessSvgMarkup($svg);
        if (!is_string($svg) || $svg === '') {
            return '';
        }

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    private static function buildSvgSymbol($iconSvg, $symbolId)
    {
        if (!preg_match('/<svg\b([^>]*)>(.*)<\/svg>/is', $iconSvg, $matches)) {
            return '';
        }

        $attributes = $matches[1];
        $innerSvg = trim($matches[2]);
        $viewBox = '';

        if (preg_match('/\bviewBox="([^"]+)"/i', $attributes, $viewBoxMatches)) {
            $viewBox = trim($viewBoxMatches[1]);
        } elseif (preg_match('/\bwidth="([0-9.]+)(?:px)?"/i', $attributes, $widthMatches)
            && preg_match('/\bheight="([0-9.]+)(?:px)?"/i', $attributes, $heightMatches)) {
            $viewBox = '0 0 '.$widthMatches[1].' '.$heightMatches[1];
        }

        $viewBoxAttribute = $viewBox !== '' ? ' viewBox="'.$viewBox.'"' : '';

        return '<symbol id="'.$symbolId.'"'.$viewBoxAttribute.'>'.$innerSvg.'</symbol>';
    }

    private static function resolveGraphvizImagePath($href)
    {
        if (!is_string($href) || $href === '') {
            return null;
        }

        $candidates = array();
        $basename = basename(parse_url($href, PHP_URL_PATH) ?: $href);

        if ($href[0] === '/' && file_exists($href)) {
            $candidates[] = $href;
        }

        if (str_starts_with($href, WWW_ROOT)) {
            $relative = substr($href, strlen(WWW_ROOT));
            $candidates[] = ROOT.'/App/Webroot/'.$relative;
        }

        if (str_starts_with($href, '/image/')) {
            $candidates[] = ROOT.'/App/Webroot'.$href;
        }

        if ($basename !== '') {
            $candidates[] = ROOT.'/App/Webroot/image/dot/'.$basename;
            $candidates[] = ROOT.'/App/Webroot/image/icon/'.$basename;
        }

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function buildImageDataUri($diskPath)
    {
        if (!is_string($diskPath) || $diskPath === '' || !file_exists($diskPath)) {
            return '';
        }

        $content = file_get_contents($diskPath);
        if ($content === false || $content === '') {
            return '';
        }

        $extension = strtolower((string) pathinfo($diskPath, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => '',
        };

        if ($mime === '') {
            return '';
        }

        return 'data:'.$mime.';base64,'.base64_encode($content);
    }

    private static function injectSvgSymbols($svg, $symbolsMarkup)
    {
        if ($symbolsMarkup === '') {
            return $svg;
        }

        if (strpos($svg, '<defs>') !== false) {
            return preg_replace('/<defs>/i', '<defs>'.$symbolsMarkup, $svg, 1);
        }

        return preg_replace('/<svg\b([^>]*)>/i', '<svg$1><defs>'.$symbolsMarkup.'</defs>', $svg, 1);
    }



/**
 * Handle graphviz state through `generateHiddenEdge`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $hidden_edge Input value for `hidden_edge`.
 * @phpstan-param mixed $hidden_edge
 * @psalm-param mixed $hidden_edge
 * @return mixed Returned value for generateHiddenEdge.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generateHiddenEdge()
 * @example /fr/graphviz/generateHiddenEdge
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function generateHiddenEdge($hidden_edge)
    {
        $ret = '';
        $ret .= "".$hidden_edge."[style=invis];".PHP_EOL;
        //$ret .= "".$hidden_edge.";".PHP_EOL;
        return $ret;
    }

/**
 * Handle graphviz state through `openSubgraph`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for openSubgraph.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::openSubgraph()
 * @example /fr/graphviz/openSubgraph
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle graphviz state through `closeSubgraph`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for closeSubgraph.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::closeSubgraph()
 * @example /fr/graphviz/closeSubgraph
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function closeSubgraph($param)
    {
        self::$subgraph_number++;

        $ret = '';
        $ret .= "}".PHP_EOL;
        return $ret;
    }

/**
 * Handle graphviz state through `generateServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $server Input value for `server`.
 * @phpstan-param mixed $server
 * @psalm-param mixed $server
 * @return mixed Returned value for generateServer.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generateServer()
 * @example /fr/graphviz/generateServer
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function generateServer($server)
    { 


        /*
         * to be sure to insert image with add <?xml version="1.0" encoding="UTF-8" standalone="no"?> in top of SVG
         */
        $image_server = ROOT."/App/Webroot/image/dot/";
        //$image_server = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_ADDR'].LINK."image/dot/";

        $return = '';
        // define color

        //shape=plaintext
        $return = "node[shape=none fontsize=8 ranksep=10 splines=true overlap=true];".PHP_EOL;
        //$return = "node[shape=plaintext fontsize=8];".PHP_EOL;

        //Debug::debug($server, "DEBUG TO REMOVE");
        
        $format = Format::getMySQLNumVersion($server['version'] ?? '', $server['version_comment'] ?? '');

        $fork = $format['fork'] ?? '';
        $number = $format['number'] ?? '';
        $isSingleStore = !empty($server['is_single_store']) && (string) $server['is_single_store'] === '1';

        if ($isSingleStore) {
            $fork = 'SingleStore';
        }

        $isVipServer = !empty($server['is_vip']) && (string)$server['is_vip'] === "1";
        $version_label = trim($fork.' : '.$number);
        if ($version_label === ':') {
            $version_label = $fork !== '' ? $fork : $number;
        }
        if (!empty($server['version_label_override'])) {
            $version_label = $server['version_label_override'];
        }

        //to move in dot3
        if (!empty($server['wsrep_cluster_status']) && strtolower((string)$server['wsrep_cluster_status']) === "non-primary")
        {
            $server['color'] = "#FFFF00"; // import this from legend
        }

        $forground_color = '#000000';
        if (static::getBrightness($server['color']) < 128) {
            $forground_color = '#FFFFFF';
        }

        $image_logo = strtolower($fork).'.svg';
        if ($image_logo === '.svg' || $image_logo === 'sql.svg') {
            $image_logo = 'mysql.svg';
        }

        if ($isVipServer) {
            $image_logo = 'vip.svg';
            $version_label = 'VIP';
        }

        if (!empty($server['is_proxysql']) && $server['is_proxysql'] == "1" ) {
            $image_logo = 'proxysql.png';            
        }

        if (!empty($server['is_maxscale']) && $server['is_maxscale'] == "1" ) {
            $image_logo = 'maxscale.png';            
        }

        if (!$isVipServer && Dot3::isMysqlRouterNode($server)) {
            $image_logo = 'router.svg';
            $version_label = 'MySQL Router';
        }


        if (!empty($server['wsrep_on']) && strtolower((string)$server['wsrep_on']) == "on" ) {
            //$image_logo = 'galera.svg';
        }
        
        if (self::isOfflineServer($server)) {
            $server['color'] = self::OFFLINE_RED;
        }


        //
        $return .= '  "'.$server['id_mysql_server'].'"[ href="'.LINK.'MysqlServer/processlist/'.$server['id_mysql_server'].'/"';
        $return .= 'tooltip="'.$server['display_name'].'"'.PHP_EOL;
        $return .= self::openHtmlLikeLabel(Dot3::TARGET, $server['color']);
        $return .= self::htmlRow([
            '<td PORT="title" colspan="2" bgcolor="'.$server['color'].'"><font color="'.$forground_color.'"><b>'.$server['display_name'].'</b></font></td>',
        ]);

        $return .= self::htmlRow([
            '<td bgcolor="#eeeeee" CELLPADDING="0" width="28" rowspan="2" port="from"><IMG SCALE="TRUE" SRC="'.$image_server.$image_logo.'" /></td>',
            '<td bgcolor="lightgrey" width="100" align="left">'.$version_label.'</td>',
        ]);

        if ($isVipServer)
        {
            $server['port_real'] = trim((string)($server['vip_dns_port'] ?? $server['port_real'] ?? $server['port'] ?? ''));
            $server['ip_real'] = trim((string)($server['vip_dns_ip'] ?? $server['ip_real'] ?? $server['ip'] ?? ''));
        }

        $displayAddress = ' ' . Ofuscate::ip($server['ip_real'] ?? '') . ':' . ($server['port_real'] ?? '');

        if ($server['display_name'] === "garb")
        {
            $server['ip_real'] = "N/A";
            $displayAddress = ' N/A:' . ($server['port_real'] ?? '3306');
        }
        else {
            $endpoint = trim((string)($server['ip_real'] ?? '')) . ':' . trim((string)($server['port_real'] ?? ''));

            if (preg_match('/^(\d{1,3}\.){3}\d{1,3}:\d{1,5}$/', $endpoint)) {
                $tunnelDestination = Dot3::getTunnel([$endpoint]);

                if (!empty($tunnelDestination)) {
                    $displayAddress = ' 🔀' . $tunnelDestination;
                }
            }
        }

        //country there
        $return .= self::htmlRow([
            '<td bgcolor="lightgrey" width="100" align="left">' . $displayAddress . '</td>',
        ]);

        //$return .= '<tr><td colspan="2" bgcolor="lightgrey" align="left">'.__('Since')." : ".$server['date'].'</td></tr>'.PHP_EOL;



        if (empty($server['is_proxysql']) && empty($server['is_maxscale']) && empty($server['is_proxy'])   )
        {

            $time_zone = $server['time_zone'] ?? 'N/A';
            if ($time_zone === "SYSTEM") {
                $time_zone = $time_zone . " (".($server['system_time_zone'] ?? 'N/A').")";
            }

            if (!isset($server['system_time_zone'])) {
                $server['system_time_zone'] = 'N/A';
            }

            if ($server['display_name'] !== "garb")
            {

                if ($isVipServer) {
                    $vipActive = trim((string)($server['vip_active_label'] ?? 'N/A'));
                    if ($vipActive === '') {
                        $vipActive = 'N/A';
                    }

                    $vipPrevious = trim((string)($server['vip_previous_label'] ?? 'N/A'));
                    if ($vipPrevious === '') {
                        $vipPrevious = 'N/A';
                    }

                    $vipLastSwitch = trim((string)($server['vip_last_switch'] ?? 'N/A'));
                    if ($vipLastSwitch === '') {
                        $vipLastSwitch = 'N/A';
                    }

                    $return .= self::htmlRow([
                        '<td colspan="2" bgcolor="lightgrey" align="left" port="'.Dot3::VIP_ACTIVE_PORT.'">IP active : '.$vipActive.'</td>',
                    ]);
                    $return .= self::htmlRow([
                        '<td colspan="2" bgcolor="lightgrey" align="left" port="'.Dot3::VIP_PREVIOUS_PORT.'">IP previous : '.$vipPrevious.'</td>',
                    ]);
                    $return .= self::htmlRow([
                        '<td colspan="2" bgcolor="lightgrey" align="left">Date last switch : '.$vipLastSwitch.'</td>',
                    ]);
                }
                else
                {

                $is_single_store = $isSingleStore || strtolower((string)$fork) === 'singlestore';

                $return .= self::htmlRow([
                    '<td colspan="2" bgcolor="lightgrey" align="left">'.__('Time zone')." : ".$time_zone.' </td>',
                ]);

                if (!$is_single_store) {
                    // 🇫🇷
                    $server_id = $server['server_id'] ?? 'N/A';
                    $auto_increment_offset = $server['auto_increment_offset'] ?? 'N/A';
                    $auto_increment_increment = $server['auto_increment_increment'] ?? 'N/A';

                    $return .= self::htmlRow([
                        '<td colspan="2" bgcolor="lightgrey" align="left">'.__('Server ID')." : ".$server_id.' - Auto Inc : '.$auto_increment_offset.'/'.$auto_increment_increment.'</td>',
                    ]);

                    $debug = '';
                    //Debug::$debug = true;

                    //force le refresh du DOT
                    if (Debug::$debug === true) {
                        $rand = rand(1,100);
                        $debug  = ' (Debug : '.$rand.')';
                    }

                    $ROW = '';
                    $binlog_format = strtolower((string)($server['binlog_format'] ?? ''));
                    if ($binlog_format === "row")
                    {
                        $ROW = "(".($server['binlog_row_image'] ?? 'N/A').")";
                    }

                    $binlog_display = $server['binlog_format'] ?? 'N/A';
                    $return .= self::htmlRow([
                        '<td colspan="2" bgcolor="lightgrey" align="left">'.__('Binlog')." : ".$binlog_display.' '.$ROW.$debug.'</td>',
                    ]);

                    if (empty($server['log_slave_updates']))
                    {
                        //Debug::debug($server, "SERVER");
                        //die();
                        //return "";
                    }

                    $read_only = strtolower((string)($server['read_only'] ?? ''));
                    if ($read_only === "on" || $read_only === "1")
                    {
                        $server['read_only'] = '✅ ON';
                    }
                    elseif ($read_only === "off" || $read_only === "0")
                    {
                        $server['read_only'] = 'OFF';
                    }
                    else {
                        $server['read_only'] = 'N/A';
                    }

                    if (($server['log_slave_updates'] ?? '') === "OFF")
                    {
                        $server['log_slave_updates'] = "⚫ OFF";
                    }

                    if (empty($server['log_slave_updates'])) {
                        $server['log_slave_updates'] = 'N/A';
                    }

                    $return .= self::htmlRow([
                        '<td colspan="2" bgcolor="lightgrey" align="left">'.__('Read only')." : ".$server['read_only'].' - LSU : '.$server['log_slave_updates'].'</td>',
                    ]);
                }
                

                //NODE GALERA
                //A déplacer dans DOT quoi que ?
                if (!empty($server['wsrep_on']) && strtolower($server['wsrep_on']) == "on" ) {

                    if (!empty($server['galera_status_override'])) {
                        $server['wsrep_local_state_comment'] = $server['galera_status_override'];
                    }

                    if ($server['wsrep_local_state_comment'] != "Synced") {

                            if ($server['wsrep_local_state'] === "2") { // Donnor / desync

                                if ($server['wsrep_desync'] === "ON"){
                                    $server['wsrep_local_state_comment'] = "Desynced - Desync : ".$server['wsrep_desync'];
                                }
                                else{
                                    $server['wsrep_local_state_comment'] = "Donor - Desync : ".$server['wsrep_desync'];
                                }
                            }

                        $comment = "<b>".trim($server['wsrep_local_state_comment'])."</b>";
                    }
                    else
                    {
                        $comment = $server['wsrep_local_state_comment'];
                    }

                    if ($server['wsrep_cluster_status'] !== "Primary")
                    {
                        $server['wsrep_cluster_status'] = "<b>".trim($server['wsrep_cluster_status'])."</b>";
                    }


                    if (!empty($server['galera_status_override'])) {
                        $status = $server['wsrep_cluster_status'].' (<b>'.$server['wsrep_local_state_comment'].'</b>)';
                    } elseif ($server['mysql_available'] === "0") {
                        $status = '<b>Offline</b>';
                    } else {
                        $status = $server['wsrep_cluster_status'].' ('.$comment.')';
                    }

                    $return .= self::htmlRow([
                        '<td colspan="2" bgcolor="lightgrey" align="left">'.__('Status')." : ".$status.'</td>',
                    ]);
                }
                }
            }
            
            
            

            /*
            $return .= '<tr><td>';
            $return .= '<table BGCOLOR="#eafafa" BORDER="0" CELLBORDER="0" CELLSPACING="1" CELLPADDING="2">
            <tr>
                <td align="left" bgcolor="grey">Schema</td>
                <td bgcolor="grey">B</td>
                <td bgcolor="grey">R</td>
            </tr>'.PHP_EOL;
            

            
            if (! empty($server['mysql_database']))
            {
                $i = 1;
                foreach($server['mysql_database'] as $database)
                {
                    if (in_array($database, array("NONE", 'sys')))
                    {
                        continue;
                    }

                    $i++;
                    $return .= '<tr>'.PHP_EOL;
                    $return .= '<td bgcolor="darkgrey" align="left">'.$database.'</td>'.PHP_EOL;
                    $return .= '<td bgcolor="darkgrey" align="center">'.'🛢'.'</td>'.PHP_EOL;
                    $return .= '<td bgcolor="darkgrey" align="center">'.'🛢'.'</td>'.PHP_EOL;
                    $return .= '</tr>'.PHP_EOL;

                    if ($database == "sakila")
                    {
                        $return .= '<tr><td colspan="3" bgcolor="green" align="left">🕷 '.'simulation#P#pt1{0,1}'.'</td></tr>'.PHP_EOL;
                        $return .= '<tr><td colspan="3" bgcolor="red" align="left">🕷 '.'simulation#P#pt2{0,1}'.'</td></tr>'.PHP_EOL;
                        $return .= '<tr><td colspan="3" bgcolor="lightgrey" align="left">🕷 '.'simulation#P#pt3{0,1}'.'</td></tr>'.PHP_EOL;
                        $return .= '<tr><td colspan="3" bgcolor="lightgrey" align="left">🕷 '.'simulation#P#pt4{0,1}'.'</td></tr>'.PHP_EOL;
                    }

                    if ($i > 5)
                    {
                        break;
                    }
                }
            }
            else
            {
                
            } 

            $return .= '</table>'.PHP_EOL;
            $return .= '</td></tr>'.PHP_EOL;
            /***** */



        }elseif (!empty($server['is_proxysql']) && $server['is_proxysql'] == "1")
        {
            
            //Debug::debug($server,"CORRESPONDANCE");
            //exit;
            $hostgroup = 0;
            $i = 0;

            if (! empty($server['mysql_galera_hostgroups']))
            {
                $correspondance_hg = Dot3::getHostGroup($server['mysql_galera_hostgroups']);
            }

            if(! empty($server['mysql_replication_hostgroups']))
            {
                $correspondance_hg = Dot3::getHostGroup($server['mysql_replication_hostgroups']);

            }
            
            if (! empty($server['mysql_group_replication_hostgroups']))
            {
                $correspondance_hg = Dot3::getHostGroup($server['mysql_group_replication_hostgroups']);
            }

            //Debug::debug($correspondance_hg, "HG");
            
            $max_writer = 0;
            if (isset($server['mysql_galera_hostgroups'][0]['max_writers'])){
                $max_writer = $server['mysql_galera_hostgroups'][0]['max_writers'];
            }

            foreach($server['mysql_servers'] as $link)
            {
                $i++;
                

                if (empty($correspondance_hg[$link['hostgroup_id']]))
                {
                    //$this->logger->warning("Impossible to link this hostgroup : ".$link['hostgroup_id']);
                    continue;
                }


                if ($hostgroup != $link['hostgroup_id'])
                {
                    $max = "";
                    if ($correspondance_hg[$link['hostgroup_id']] === "writer" && ! empty($max_writer))
                    {
                        $max = " (max : ". $max_writer.")";
                    }

                    $port = crc32($link['hostgroup_id'].'::');
                    $return .= '<tr><td colspan="2" port="'.$port.'" bgcolor="#aaaaaa" align="left"><font color="#000000">'.__('Host group').' : <b>'.$correspondance_hg[$link['hostgroup_id']].'</b> '.$max.'</font></td></tr>'.PHP_EOL;
                }
                //⛯ PmaControl
                $hostgroup = $link['hostgroup_id'];
                $forground_color = 'white';
                //OVER RIDE STYLE
                //⚙🔥

                $extra = '';
                $bgcolor = Dot3::$config['PROXYSQL_'.$link['status']]['color'];
                $forground_color = Dot3::$config['PROXYSQL_'.$link['status']]['font'];

                if (! empty($server['proxy_connect_error'])) {
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
                }

                $port = crc32($link['hostgroup_id'].':'.$link['hostname'].':'.$link['port']);
                
                [$bgcolor, $forground_color] = self::resolveOfflineRowColors($server, $bgcolor, $forground_color);

                $return .= '<tr>';
                $return .= '<td colspan="2" bgcolor="'.$bgcolor.'" align="left" port="'.$port.'">';
                $return .= '<font color="'.$forground_color.'">'.$link['hostname'].':'.$link['port'].''.$extra.'</font>';
                $return .= '</td>';
                $return .= '</tr>'.PHP_EOL;
            }
            




        }elseif (Dot3::isMysqlRouterNode($server))
        {
            $background = Dot3::$config['SERVER_CONFIG']['background'];
            $color = Dot3::$config['SERVER_CONFIG']['color'];

            $routesPayload = Dot3::decodeMysqlRouterJson($server, 'mysqlrouter_routes');
            $metadataConfig = Dot3::decodeMysqlRouterJson($server, 'mysqlrouter_metadata_config');
            $matchedRoute = Dot3::resolveMysqlRouterRouteForServer($server);

            $routes = $routesPayload['items'] ?? [];
            if (!empty($matchedRoute)) {
                $routes = array($matchedRoute);
            }

            if (!empty($routes)) {
                foreach ($routes as $route) {
                    $routeName = $route['route'] ?? $route['name'] ?? $route['id'] ?? 'route';
                    $bindAddress = $route['bindAddress'] ?? $route['bind_address'] ?? '0.0.0.0';
                    $bindPort = $route['bindPort'] ?? $route['bind_port'] ?? '';
                    $destinations = $route['destinations_payload']['items'] ?? [];

                    [$routeBackground, $routeFontColor] = self::resolveOfflineRowColors($server, '#00B33C', '#ffffff');

                    $return .= '<tr>';
                    $return .= '<td colspan="2" bgcolor="'.$routeBackground.'" align="left">';
                    $return .= '<font color="'.$routeFontColor.'">🛣 '.$routeName.' : '.$bindAddress.':'.$bindPort.'</font>';
                    $return .= '</td>';
                    $return .= '</tr>'.PHP_EOL;

                    foreach ($destinations as $destination) {
                        if (is_array($destination)) {
                            $destinationAddress = $destination['address'] ?? $destination['hostname'] ?? 'n/a';
                            $destinationPort = $destination['port'] ?? '';
                        } else {
                            $destinationAddress = (string) $destination;
                            $destinationPort = '';
                        }

                        $destinationKey = $destinationAddress.':'.$destinationPort;
                        $destinationDetails = $server['mysqlrouter_route_destinations'][$destinationKey] ?? array();
                        $destinationGraphPort = $destinationDetails['graph_port'] ?? crc32((string)($server['id_mysql_server'] ?? '').':'.$destinationAddress.':'.$destinationPort);
                        $destinationRole = strtoupper((string)($destinationDetails['role'] ?? ''));
                        $destinationBgColor = '#EAF7EE';
                        $destinationFontColor = '#0f172a';
                        $destinationIcon = '📖';

                        if ($destinationRole === 'PRIMARY') {
                            $destinationBgColor = '#008000';
                            $destinationFontColor = '#ffffff';
                            $destinationIcon = '✍️';
                        } elseif ($destinationRole === 'REPLICA') {
                            $destinationBgColor = '#00B33C';
                            $destinationFontColor = '#ffffff';
                        }

                        [$destinationBgColor, $destinationFontColor] = self::resolveOfflineRowColors($server, $destinationBgColor, $destinationFontColor);

                        $return .= '<tr>';
                        $return .= '<td colspan="2" bgcolor="'.$destinationBgColor.'" align="left" port="'.$destinationGraphPort.'">';
                        $return .= '<font color="'.$destinationFontColor.'">'.$destinationIcon.' '.$destinationAddress.':'.$destinationPort.'</font>';
                        $return .= '</td>';
                        $return .= '</tr>'.PHP_EOL;
                    }
                }
            } else {
                [$warningBgColor, $warningFontColor] = self::resolveOfflineRowColors($server, '#FFBF00', '#000000');

                $return .= '<tr>';
                $return .= '<td colspan="2" bgcolor="'.$warningBgColor.'" align="left">';
                $return .= '<font color="'.$warningFontColor.'">MySQL Router routes : no payload collected</font>';
                $return .= '</td>';
                $return .= '</tr>'.PHP_EOL;
            }

            /*
            if (!empty($metadataConfig)) {
                foreach ($metadataConfig as $metadataName => $metadata) {
                    $nodes = $metadata['nodes'] ?? [];
                    $return .= '<tr>';
                    $return .= '<td colspan="2" bgcolor="'.$background.'" align="left">';
                    $return .= '<font color="'.$color.'">📦 Metadata : '.$metadataName.' ('.count($nodes).' node(s))</font>';
                    $return .= '</td>';
                    $return .= '</tr>'.PHP_EOL;
                }
            }*/

            
        }elseif ($server['is_maxscale'] == "1")
        {

            $background = Dot3::$config['SERVER_CONFIG']['background'];
            $color = Dot3::$config['SERVER_CONFIG']['color'];
            
            $maxscale = MaxScale::rewriteJson($server);

           

            $ret_max = Dot3::resolveMaxScaleConnection($maxscale,  $server['ip_real'].":".$server['port_real']);

            if (empty($ret_max[$server['ip_real'].":".$server['port_real']]))
            {
                $return .= '<tr>';
                $return .= '<td colspan="2" bgcolor="'.'#FF0000'.'" align="left">';
                $return .= '<font color="'.'#ffffff'.'"> Impossible to match Maxcale Admin</font>';
                $return .= '</td>';
                $return .= '</tr>'.PHP_EOL;

                $return .= '<tr>';
                $return .= '<td colspan="2" bgcolor="'.'#FF0000'.'" align="left">';
                $return .= '<font color="'.'#ffffff'.'">[ Check it here ]</font>';
                $return .= '</td>';
                $return .= '</tr>'.PHP_EOL;


                $services = ['listeners', 'services','monitors','servers'];

                foreach($services as $service)
                {
                    if (empty($server['maxscale_'.$service])) {
                        $icon = '✖️';
                        $bgcolor ='#FF0000';
                    }
                    else{
                        $icon = '✅';
                        $bgcolor ='#008000';
                    }

                    $return .= '<tr>';
                    $return .= '<td colspan="2" bgcolor="'.$bgcolor.'" align="left">';
                    $return .= '<font color="'.'#ffffff'.'">MaxScale '.$service." : ".$icon;
                    


                    $return .= '</font>';
                    $return .= '</td>';
                    $return .= '</tr>'.PHP_EOL;
                }





                

            }
            else
            {

                
                $max = $ret_max[$server['ip_real'].":".$server['port_real']];

                //Debug::debug(maxScale::removeArraysDeeperThan($max,3), "MAX");

                if ($max['service']['state'] === 'Started'){
                    $icone = '✅';
                }
                else{
                    $icone = '⛔';
                }

                if (empty($server['mysql_available'])){
                    $icone = "⛔";
                }

                [$background, $color] = self::resolveOfflineRowColors($server, $background, $color);

                $return .= '<tr>';
                $return .= '<td colspan="2" bgcolor="'.$background.'" align="left">';
                $return .= '<font color="'.$color.'">'.$icone.' Router : '.$max['service']['router'].' ('
                .$max['service']['statistics']['active_operations'] ."/".$max['service']['statistics']['connections'].')</font>';
                $return .= '</td>';
                $return .= '</tr>'.PHP_EOL;




                foreach($max['monitor'] as $module_name => $module)
                {
                    if ($module['state'] === 'Running'){
                        $icone = '✅';
                    }
                    else{
                        $icone = '⛔';
                    }

                    if (empty($server['mysql_available'])){
                        $icone = "⛔";
                    }

                    [$background, $color] = self::resolveOfflineRowColors($server, $background, $color);

                    $return .= '<tr>';
                    $return .= '<td colspan="2" bgcolor="'.$background.'" align="left">';
                    $return .= '<font color="'.$color.'">'.$icone.' Module : '.$module['module'].'</font>';
                    $return .= '</td>';
                    $return .= '</tr>'.PHP_EOL;
                }


                // Read-write-listener ✅
                // Read-write-split ✅
                // Total connections : 434
                // Monitor : galeramon ✅

                // Master, Synced, Running   => Down
                // 10.68.68.233:3306 (10)
                // Slave, Synced, Running
                // 10.68.68.231:3306 (10)
                // 10.68.68.232:3306 (10)


                foreach($max['servers'] as $server_ip_port => $elem)
                {
                    //valeur possible :
                    /*
                    Master — le serveur est le maître (primary) dans la réplication. 
                    Slave — le serveur est un esclave (réplica). 
                    Running — indication que le serveur est accessible / actif sous le monitor. (souvent combiné avec Master/Slave, ex. “Master, Running”) 
                    Synced — dans les environnements Galera / cluster, pour indiquer qu’il est synchronisé. Par exemple “Slave, Synced, Running” comme valeur combinée dans un exemple de list servers. 
                    Draining — l’état de « drainage » : le serveur est en train de se vider, c’est-à-dire qu’il n’accepte plus de nouvelles connexions mais les connexions existantes peuvent continuer. 
                    Drained — l’état où le serveur a été complètement drainé (plus de connexions restantes). 
                    Maintenance — le serveur est en maintenance, donc non éligible pour de nouvelles connexions. 
                    Down — le serveur est hors ligne ou injoignable selon le monitor. Dans les scénarios de failover, le monitor peut marquer un serveur “Down”. 
                    */

                    $states = explode(",", $elem['state']);
                    foreach($states as $key => $state)
                    {
                        $states[$key] = trim($state);
                    }


                    if (in_array("Running", $states))
                    {

                        $hasGaleraMon = false;

                        foreach ($max['monitor'] as $item) {
                            if (isset($item['module']) && $item['module'] === 'galeramon') {
                                $hasGaleraMon = true;
                                break; // on peut sortir dès qu'on trouve
                            }
                        }


                        if ($hasGaleraMon)
                        {
                            if (in_array("Synced", $states))
                            {

                                $background = Dot3::$config['MAXSCALE_RUNNING']['background'];
                                $color = Dot3::$config['MAXSCALE_RUNNING']['color'];

                                if (in_array("Master", $states)){
                                    $background = "#008000";
                                }
                                else{
                                    $background = "#00B33C";
                                }
                                //$color="#333333";
                            }
                            else{
                                $background = Dot3::$config['MAXSCALE_UNSYNC']['background'];
                                $color = Dot3::$config['MAXSCALE_UNSYNC']['color'];
                            }
                        }
                        else{

                                if (in_array("Master", $states)){
                                    $background = "#008000";
                                }
                                else{
                                    $background = "#00B33C";
                                }

                            //$background = Dot3::$config['MAXSCALE_RUNNING']['background'];
                            $color = Dot3::$config['MAXSCALE_RUNNING']['color'];
                        }

                    }

                    if (in_array("Down", $states))
                    {
                        $background = Dot3::$config['MAXSCALE_DOWN']['background'];
                        $color = Dot3::$config['MAXSCALE_DOWN']['color'];
                    }

                    $icone = "⛔";
                    if (in_array("Master", $states))
                    {
                        $icone = "✍️";
                    }

                    if (in_array("Slave", $states))
                    {
                        $icone = "📖";
                    }

                    if (empty($server['mysql_available']))
                    {
                        $icone = "⛔";
                        $elem['connections'] = 0;
                        $background = Dot3::$config['MAXSCALE_DOWN']['background'];
                        $color = Dot3::$config['MAXSCALE_DOWN']['color'];
                    }


                    $port = crc32($server['ip_real'].':'.$server['port_real'].':'.$server_ip_port);


                    [$background, $color] = self::resolveOfflineRowColors($server, $background, $color);

                    $return .= '<tr>';
                    $return .= '<td colspan="2" bgcolor="'.$background.'" align="left" port="'.$port.'">';
                    $return .= '<font color="'.$color.'">'.$icone.' '.$server_ip_port.' ('.$elem['statistics']['connections'].'/'.$elem['statistics']['max_connections'].')</font>';
                    $return .= '</td>';
                    $return .= '</tr>'.PHP_EOL;
                }
            }

            

            //rgb(125, 208, 18) => color arrow maxscale
        }


        $return .= self::closeHtmlLikeLabel().PHP_EOL;
        
        
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


/**
 * Handle graphviz state through `buildBox`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $body Input value for `body`.
 * @phpstan-param mixed $body
 * @psalm-param mixed $body
 * @param int $id_box Input value for `id_box`.
 * @phpstan-param int $id_box
 * @psalm-param int $id_box
 * @param mixed $link Input value for `link`.
 * @phpstan-param mixed $link
 * @psalm-param mixed $link
 * @param mixed $display_name Input value for `display_name`.
 * @phpstan-param mixed $display_name
 * @psalm-param mixed $display_name
 * @param mixed $box_color Input value for `box_color`.
 * @phpstan-param mixed $box_color
 * @psalm-param mixed $box_color
 * @return mixed Returned value for buildBox.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::buildBox()
 * @example /fr/graphviz/buildBox
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function buildBox($body, $id_box, $link, $display_name, $box_color)
    {

        $return = PHP_EOL;
        $return .= '  "'.$id_box.'"[ href="'.$link.'"';
        $return .= 'tooltip="'.$display_name.'"
        shape=plaintext,label =<<table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="4">
        <tr><td port="'.Dot3::TARGET.'" bgcolor="'.$box_color.'">
        <table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="0"><tr><td>';

        $return .= $body;
        $return .= '</td></tr></table>'.PHP_EOL;
        $return .= '</td></tr></table>> ];'.PHP_EOL;
        
        return $return;
    }


/**
 * Handle graphviz state through `format`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $bytes Input value for `bytes`.
 * @phpstan-param mixed $bytes
 * @psalm-param mixed $bytes
 * @param mixed $decimals Input value for `decimals`.
 * @phpstan-param mixed $decimals
 * @psalm-param mixed $decimals
 * @return mixed Returned value for format.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::format()
 * @example /fr/graphviz/format
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

/**
 * Handle `startCluster`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $type Input value for `type`.
 * @phpstan-param mixed $type
 * @psalm-param mixed $type
 * @param mixed $elems Input value for `elems`.
 * @phpstan-param mixed $elems
 * @psalm-param mixed $elems
 * @return mixed Returned value for startCluster.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @example startCluster(...);
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function startCluster($type, $elems )
    {
        $crc32 = crc32(json_encode($elems));

        if (! in_array($type, array('galera','segment', 'group', 'xdb', 'ndb', 'innodb')))
        {
            throw new \Exception('Impossible to find this cluster');
        }

        $return = PHP_EOL;
        $return .= "subgraph cluster_cluster_".$crc32." {".PHP_EOL;
        //$return .= 'label = <<b>Galera : '.$cluster_name.'</b>>;'.PHP_EOL;


        $return .= "penwidth = 4;".PHP_EOL;
        $return .= 'fontname = "Arial";'.PHP_EOL;
        $return .= 'fontsize=8;'.PHP_EOL;
        
        
        
        return $return;
    }


/**
 * Handle `endCluster`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for endCluster.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example endCluster(...);
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function endCluster()
    {
        $return = "}".PHP_EOL;
        return $return;
    }

    private static function getColorByTheme($theme, $fallback)
    {
        if (!empty(Dot3::$config[$theme]['color'])) {
            return Dot3::$config[$theme]['color'];
        }

        return $fallback;
    }

    static function generateInnoDBCluster($all_innodb_cluster)
    {
        $return = '';

        foreach ($all_innodb_cluster as $cluster) {
            $return .= self::startCluster('innodb', $cluster);

            $stateColor = self::getColorByTheme((string)($cluster['config'] ?? 'INNODB_CLUSTER_OK'), '#2f855a');
            $background = self::diluerCouleur($stateColor, 88);
            $groupName = trim((string)($cluster['group_name'] ?? ''));
            $image_server  = ROOT."/App/Webroot/image/dot/";

            $return .= 'label =<<table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="4">
            <tr><td port="'.Dot3::TARGET.'" bgcolor="'.'#000000'.'">
            
            <table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="0"><tr><td>
    
            <table BGCOLOR="#eafafa" BORDER="0" CELLBORDER="0" CELLSPACING="1" CELLPADDING="2">'.PHP_EOL;
            $return .= '<tr><td PORT="title" colspan="2" bgcolor="'.'#000000'.'">
            <font color="'.'#FFFFFF'.'"><b>'.htmlspecialchars((string)$cluster['name'], ENT_QUOTES, 'UTF-8').'</b></font></td></tr>';
            $return .= '<tr><td bgcolor="#eeeeee" CELLPADDING="0" width="28" rowspan="2" port="from"><IMG SRC="'.$image_server.'gr.svg" /></td>
            <td bgcolor="lightgrey" width="100" align="left">Nodes available : <b>'.(int)$cluster['node_online'].'/'.(int)$cluster['members'].'</b></td></tr>';
            $return .= '<tr><td bgcolor="lightgrey" width="100" align="left">Mode : <b>'.htmlspecialchars((string)$cluster['mode'], ENT_QUOTES, 'UTF-8').'</b></td></tr>'.PHP_EOL;


            $return .= '</table>';
            $return .= '</td></tr></table>';
            $return .= '</td></tr></table>>';
            $return .= 'tooltip = "InnoDB Cluster : '.addslashes((string)$cluster['name']).'";'.PHP_EOL;
            $return .= "rank = same;".PHP_EOL;
            $return .= 'penwidth = 4;'.PHP_EOL;
            $return .= 'color = "'.$stateColor.'";'.PHP_EOL;
            $return .= 'style = filled;'.PHP_EOL;
            $return .= 'fillcolor = "'.$background.'"'.PHP_EOL;
            $return .= 'href = "'.LINK.'InnoDBCluster/index/'.urlencode((string)$groupName).'";'.PHP_EOL;

            $roleBuckets = array(
                'PRIMARY' => array(),
                'SECONDARY' => array(),
            );

            foreach ($cluster['node'] as $idMysqlServer => $node) {
                $role = strtoupper((string)($node['member_role'] ?? 'SECONDARY'));
                if (!isset($roleBuckets[$role])) {
                    $roleBuckets[$role] = array();
                }
                $roleBuckets[$role][] = $idMysqlServer;
            }

            $subgroups = array(
                'PRIMARY' => array(
                    'label' => 'Primary',
                    'color' => '#1b5e20',
                    'fillcolor' => '#e8f5e9',
                ),
                'SECONDARY' => array(
                    'label' => 'Replica',
                    'color' => '#1565c0',
                    'fillcolor' => '#e3f2fd',
                ),
            );

            foreach ($subgroups as $role => $style) {
                if (empty($roleBuckets[$role])) {
                    continue;
                }

                $return .= 'subgraph cluster_innodb_'.crc32((string)$cluster['id_cluster'].'_'.$role).' {'.PHP_EOL;
                $return .= 'label = "'.$style['label'].'";'.PHP_EOL;
                $return .= 'penwidth = 2;'.PHP_EOL;
                $return .= 'color = "'.$style['color'].'";'.PHP_EOL;
                $return .= 'style = "rounded,filled";'.PHP_EOL;
                $return .= 'fillcolor = "'.$style['fillcolor'].'";'.PHP_EOL;
                $return .= 'fontsize = 8;'.PHP_EOL;
                $return .= 'fontname = "Arial";'.PHP_EOL;

                foreach ($roleBuckets[$role] as $idMysqlServer) {
                    $return .= $idMysqlServer.';'.PHP_EOL;
                }

                $return .= '}'.PHP_EOL;
            }

            $return .= self::endCluster();
        }

        return $return;
    }

/**
 * Handle `generateGalera`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $all_galera Input value for `all_galera`.
 * @phpstan-param mixed $all_galera
 * @psalm-param mixed $all_galera
 * @return mixed Returned value for generateGalera.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example generateGalera(...);
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function generateGalera($all_galera)
    {
        //Debug::debug($all_galera, "ALL GALERA");
        $return = '';

        foreach($all_galera as $galera)
        {
            // need to know availibility of all node

            //Debug::debug($galera);
            //start cluster
            $return .= self::startCluster( "galera", $galera );
            
            $image_server  = ROOT."/App/Webroot/image/dot/";

            $return .= 'label =<<table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="4">
            <tr><td port="'.Dot3::TARGET.'" bgcolor="'.'#000000'.'">
            
            <table BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="0"><tr><td>
    
            <table BGCOLOR="#eafafa" BORDER="0" CELLBORDER="0" CELLSPACING="1" CELLPADDING="2">'.PHP_EOL;
            $return .= '<tr><td PORT="title" colspan="2" bgcolor="'.'#000000'.'">
            <font color="'.'#FFFFFF'.'"><b>'.$galera['name'].'</b></font></td></tr>';

            $return .= '<tr><td bgcolor="#eeeeee" CELLPADDING="0" width="28" rowspan="2" port="from"><IMG SCALE="TRUE" SRC="'.$image_server."galera.svg".'" /></td>
            <td bgcolor="lightgrey" width="100" align="left">'.'Nodes available'.' : <b>'.$galera['node_available'].'/'.$galera['members'].'</b> - '.$galera['wsrep_provider_version'].'</td></tr>';
            $return .= '<tr><td bgcolor="lightgrey" width="100" align="left">'.'Galera Version : '.$galera['galera_version'].' - Worker : '.$galera['wsrep_slave_threads'].'</td></tr>'.PHP_EOL;


            $return .= '<tr><td bgcolor="lightgrey" align="left" colspan="2">wsrep_sst_method'.' : '.$galera['sst_method'].'</td></tr>";'.PHP_EOL;

            $return .= "</table>";
            $return .= "</td></tr></table>";
            $return .= "</td></tr></table>>";

            $return .= 'tooltip = "Galera : '.$galera['name'].'";'.PHP_EOL;
            $return .= "rank = same;".PHP_EOL;
            $return .= "penwidth = 4;".PHP_EOL;


            //Debug::debug($galera['config']);
            
            $background = self::diluerCouleur(Dot3::$config[$galera['config']]['color'], 90);
            

            $return .= 'color = "'.Dot3::$config[$galera['config']]['color'].'";'.PHP_EOL; // Bordure verte
            $return .= "style = filled;".PHP_EOL; // Active le remplissage
            $return .= 'fillcolor = "'.$background.'"'.PHP_EOL; 
            $return .= 'href = "'.LINK.'GaleraCluster/view/'.$galera['id_cluster'].'";'.PHP_EOL;

            //$return .= 'ranksep = "1"'.PHP_EOL; 
            //$return .= 'nodesep = "1"'.PHP_EOL; 


            $display_segment = false;
            if (count($galera['node']) == 2) {
                $display_segment = true;
            }

            ksort($galera['node']);

            foreach($galera['node'] as $segment => $nodes)
            {


                //if ($display_segment === true){
                    $return .= self::startCluster( "segment", $nodes );
                    
                    $theme = $galera['segment'][$segment]['theme'];
                    $color = Dot3::$config[$theme]['color'];
                    $background = self::diluerCouleur($color, 60);

                    $return .= 'color = "'.$color.'";'.PHP_EOL; 

                    
                    
                    $return .= "style = dashed;".PHP_EOL;
                    $return .= 'tooltip = "Segment number : '.$segment.'"'.PHP_EOL; // pourquoi ca n'est pas pris en compte ???
                    $return .= 'label = "Segment number : '.$segment.'"'.PHP_EOL;
                    $return .= 'fillcolor = "'.$background.'"'.PHP_EOL; 

                    $return .= 'ordering="out";'.PHP_EOL; 
                    
                    
                    $return .= 'href = "'.LINK.'GaleraCluster/view/'.$galera['id_cluster'].'";'.PHP_EOL;
                //}

                foreach($nodes as $id_mysql_server => $node ) {
                    $return .= $id_mysql_server.";".PHP_EOL;
                }

                //Debug::debug($nodes, "NODES");

                //if ($display_segment === true){
                    $return .= self::endCluster();
                //}
            }
            //Debug::debug($galera);


            //end cluster
            $return .= self::endCluster();
        }

        return $return;
    }

/**
 * Handle `diluerCouleur`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $hex Input value for `hex`.
 * @phpstan-param mixed $hex
 * @psalm-param mixed $hex
 * @param mixed $percent Input value for `percent`.
 * @phpstan-param mixed $percent
 * @psalm-param mixed $percent
 * @return mixed Returned value for diluerCouleur.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example diluerCouleur(...);
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function diluerCouleur($hex, $percent) {
        // Assurez-vous que le format hexadécimal est valide
        if (strlen($hex) != 7 || $hex[0] != '#') {
            return 'Format de couleur invalide.';
        }
    
        // Convertir les composantes hexadécimales en valeurs décimales
        $r = hexdec(substr($hex, 1, 2));
        $g = hexdec(substr($hex, 3, 2));
        $b = hexdec(substr($hex, 5, 2));
    
        // Calculer la nouvelle couleur en augmentant la luminosité
        $nouveau_r = min(255, $r + (255 - $r) * $percent / 100);
        $nouveau_g = min(255, $g + (255 - $g) * $percent / 100);
        $nouveau_b = min(255, $b + (255 - $b) * $percent / 100);
    
        // Reconvertir les valeurs RGB en hexadécimal
        $nouveau_hex = sprintf("#%02x%02x%02x", $nouveau_r, $nouveau_g, $nouveau_b);
    
        return $nouveau_hex;
    }
}
