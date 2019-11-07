<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Date\Date;

class Test2 extends Controller {

    public function test() {
        $this->view = false;


        $graph = new Alom\Graphviz\Digraph('G');


        $graph
                ->set('rankdir', 'LR')
                ->node("ETAI-117", array('label' => '<<table border="0" cellborder="0" cellspacing="0" cellpadding="1" bgcolor="white"><tr><td bgcolor="black" color="white" align="center" title="ETAI-117" href="/pmacontrol/en/monitoring/query/370/"><font color="white">ETAI-117</font></td></tr><tr><td bgcolor="grey" align="left">10.23.1.117:3306</td></tr><tr><td bgcolor="grey" align="left">MariaDB : 5.5.42</td></tr>
<tr><td bgcolor="grey" align="left">Uptime : 7 days and 15:19:10</td></tr><tr><td bgcolor="grey" align="left">(2015-06-04 11:00:21) : CEST</td></tr><tr><td bgcolor="grey" align="left">Binlog format : ROW</td></tr><tr><td bgcolor="grey" align="left">
<table border="0" cellborder="0" cellspacing="2" cellpadding="2"><tr><td bgcolor="#eeeeee">M</td><td bgcolor="#eeeeee">S</td><td bgcolor="#dddddd" align="left">Databases</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of checksum" href="/pmacontrol/en/mysql/mpd/ETAI-117/checksum">checksum</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of import" href="/pmacontrol/en/mysql/mpd/ETAI-117/import">import</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of information_schema" href="/pmacontrol/en/mysql/mpd/ETAI-117/information_schema">information_schema</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of mysql" href="/pmacontrol/en/mysql/mpd/ETAI-117/mysql">mysql</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of performance_schema" href="/pmacontrol/en/mysql/mpd/ETAI-117/performance_schema">performance_schema</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail">portail</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150410" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150410">portail20150410</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150423" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150423">portail20150423</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150506" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150506">portail20150506</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150511" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150511">portail20150511</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150512" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150512">portail20150512</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150604\nfghsfgh\n wdfgwdfg" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150604">portail20150604</td></tr>
</table>
</td></tr>
</table>>', 'fontname' => 'Monospace', 'fontsize' => '9', 'color' => 'green', 'shape' => 'box',))
                ->node("ETAI-118", array('label' => '<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white"><tr><td bgcolor="black" color="white" align="center" title="ETAI-117" href="/pmacontrol/en/monitoring/query/370/"><font color="white">ETAI-118</font></td></tr><tr><td bgcolor="grey" align="left">10.23.1.117:3306</td></tr><tr><td bgcolor="grey" align="left">MariaDB : 5.5.42</td></tr>
<tr><td bgcolor="grey" align="left">Uptime : 7 days and 15:19:10</td></tr><tr><td bgcolor="grey" align="left">(2015-06-04 11:00:21) : CEST</td></tr><tr><td bgcolor="grey" align="left">Binlog format : ROW</td></tr><tr><td bgcolor="grey" align="left">
<table border="0" cellborder="0" cellspacing="2" cellpadding="2"><tr><td bgcolor="#eeeeee">M</td><td bgcolor="#eeeeee">S</td><td bgcolor="#dddddd" align="left">Databases</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of checksum" href="/pmacontrol/en/mysql/mpd/ETAI-117/checksum">checksum</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of import" href="/pmacontrol/en/mysql/mpd/ETAI-117/import">import</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of information_schema" href="/pmacontrol/en/mysql/mpd/ETAI-117/information_schema">information_schema</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of mysql" href="/pmacontrol/en/mysql/mpd/ETAI-117/mysql">mysql</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of performance_schema" href="/pmacontrol/en/mysql/mpd/ETAI-117/performance_schema">performance_schema</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail">portail</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150410" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150410">portail20150410</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150423" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150423">portail20150423</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150506" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150506">portail20150506</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150511" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150511">portail20150511</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150512" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150512">portail20150512</td></tr>
<tr><td bgcolor="#eeeeee">-</td><td bgcolor="#eeeeee">-</td><td bgcolor="#dddddd" align="left" title="MPD of portail20150604\nfghsfgh\n wdfgwdfg" href="/pmacontrol/en/mysql/mpd/ETAI-117/portail20150604">portail20150604</td></tr>
</table>
</td></tr>
</table>>', 'fontname' => 'Monospaced 13', 'fontsize' => '8', 'color' => 'green', 'shape' => 'rect', "penwidth" => "3"))
                ->edge(array('ETAI-117', 'ETAI-118'), array("arrowsize" => "1.5", "penwidth" => "3", "fontname" => "arial", "fontsize" => "8", "color" => "green"))
        ;
        $dot = $graph->render(1);


        /*
         * Arial
         * Verdana
         * Monospace
         */

        $fp = fopen(TMP . "gg" . '.dot', "w");
        fwrite($fp, $dot);
        fclose($fp);


        $data['dot'] = $dot;


        if (file_exists(TMP . 'gg.svg')) {
            unlink(TMP . 'gg.svg');
        }

        $data['cmd'] = 'dot -T' . 'svg' . ' ' . TMP . "gg" . '.dot -o ' . TMP . 'gg.svg 2>&1';

        $data['error'] = exec($data['cmd']);

        $this->set('data', $data);
    }

    function getTable() {
        $color = ['blue', 'red', 'green'];

        if (!in_array($data['color'], $color)) {
            throw new Exception("PMACLI-085 Impossible to get the color !");
        }
        fwrite($fp, "\t node [color=" . $data['color'] . "];" . PHP_EOL);
        fwrite($fp, '  "' . $data['id_mysql_server'] . '" [style="" penwidth="3" fillcolor="yellow" fontname="arial" label =<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white"><tr><td bgcolor="black" color="white" align="center" href="' . LINK . 'monitoring/query/' . str_replace('_', '-', $data['hostname']) . '/' . '"><font color="white">' . str_replace('_', '-', $data['hostname']) . '</font></td></tr><tr><td bgcolor="grey" align="left">' . $data['ip'] . ':' . $data['port'] . '</td></tr>');
        fwrite($fp, '<tr><td bgcolor="grey" align="left">' . $data['version'] . '</td></tr>' . PHP_EOL);
        fwrite($fp, '<tr><td bgcolor="grey" align="left">Uptime : ' . Date::secToTime($data['uptime']) . '</td></tr>');
        fwrite($fp, '<tr><td bgcolor="grey" align="left">(' . $data['date'] . ') : ' . $data['timezone'] . '</td></tr>');
        fwrite($fp, '<tr><td bgcolor="grey" align="left">Binlog format : ' . $data['binlog_format'] . '</td></tr>');
//fwrite($fp, '<tr><td bgcolor="red" align="left">Date : <b>' . $ob->date.'</b></td></tr>');
        // DATABASES


        fwrite($fp, '</table>> ];' . PHP_EOL);
    }

    function getLabel() {
        $data['hostname'] = 'test';
        $data['ip'] = "192.168.1.1";
        $data['port'] = "3306";
        $data['version'] = "MariaDB 10.0.19";
        $data['uptime'] = 154;
        $data['timezone'] = 'UTC';
        $data['binlog_format'] = 'ROW';
        $data['date'] = "14:02:21";


        $label = '<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white">'
                . '<tr>'
                . '<td bgcolor="black" color="white" align="center" href="' . LINK . 'monitoring/query/' . str_replace('_', '-', $data['hostname']) . '/' . '"><font color="white">' . str_replace('_', '-', $data['hostname']) . '</font></td>'
                . '</tr>'
                . '<tr>'
                . '<td bgcolor="grey" align="left">' . $data['ip'] . ':' . $data['port'] . '</td>'
                . '</tr>';

        $label .= '<tr><td bgcolor="grey" align="left">' . $data['version'] . '</td></tr>'
                . '<tr><td bgcolor="grey" align="left">Uptime : ' . Date::secToTime($data['uptime']) . '</td></tr>'
                . '<tr><td bgcolor="grey" align="left">(' . $data['date'] . ') : ' . $data['timezone'] . '</td></tr>'
                . '<tr><td bgcolor="grey" align="left">Binlog format : ' . $data['binlog_format'] . '</td></tr>';

        // DATABASES

        $label .= '</table>>';

        return $label;
    }

    function getDatabase() {
        
    }

    function timeOut() {
        $this->view = false;

        $ret = \Glial\Cli\SetTimeLimit::run("Test2", "hello", array("fgchfdg", "dfgdfg"), 2);

        if (empty($ret)) {
            echo "script under timeout and successful\n";
        } elseif (is_int($ret)) {
            echo "Error in script !\n";
        } elseif (is_array($ret)) {
            echo "timeout !\n";
            debug($ret);
        } else {
            $this->di['log']->emergency("PMA-CTRL ANORMAL CASE !", $ret);
        }
    }

    function hello($param) {

        $this->view = false;

        echo "Hello Boy !\n";

        sleep(1000);
        print_r($param);

        exit(1);
        //throw new \Execption("DFGSHWFSXGH");
    }

}
