<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Library\Available;
use App\Library\Mysql;
use App\Library\Display;

function slave_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function slave_random_hop_emoji()
{
    static $emojis = ['🎲', '🛰️', '🚇', '🪃', '🔀', '🌉', '🛸', '🎯'];

    return $emojis[array_rand($emojis)];
}

function slave_render_real_endpoint(array $slave, array $data)
{
    $localEndpoint = trim((string)$slave['master_host']).':'.(int)$slave['master_port'];
    $tunnel = $data['tunnel_details'][$localEndpoint] ?? null;

    if (empty($tunnel['remote'])) {
        return slave_h($localEndpoint);
    }

    $path = [];
    $hops = $tunnel['hops'] ?? [];
    $count = count($hops);

    for ($idx = 0; $idx < $count; $idx++) {
        $path[] = slave_h($hops[$idx]);

        if ($idx < $count - 1) {
            $path[] = slave_random_hop_emoji();
        }
    }

    return '<span data-html="true" data-toggle="tooltip" data-placement="top" title="'.implode(' ', $path).'">'.slave_h($tunnel['remote']).'</span>';
}

function slave_get_real_endpoint_from_local(string $host, int $port, array $data): string
{
    $localEndpoint = trim($host).':'.$port;
    $tunnel = $data['tunnel_details'][$localEndpoint] ?? null;

    if (!empty($tunnel['remote'])) {
        return (string)$tunnel['remote'];
    }

    return $localEndpoint;
}

function display_db($dbs)
{
    if (empty($dbs)) {
        $nb = 0;
    } else {
        $nb = count(explode(",", $dbs));

        echo '<span  data-html="true" data-toggle="tooltip" data-placement="right" title="'.str_replace(',', '<br />', $dbs).'">';

        if ($nb >= 5) {
            echo $nb." * ";
            $nb = 1;
        }

        echo str_repeat('<i class="fa fa-database fa-lg" style="font-size:14px"></i> ', $nb);
        echo '</span>';
    }
}
echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());

//have to put this in an other place and only if required
//\Glial\Synapse\FactoryController::addNode("Alias", "updateAlias", array());



echo '</div>';

echo '<table class="table table-condensed table-bordered table-striped" >'."\n";

echo '<tr>'."\n";
echo '<th>'.__("Top").'</th>'."\n";
//echo '<th>' . __("ID Serveur") . '</th>';
echo '<th>'.__("Master").'</th>'."\n";
echo '<th>'.__("Slave").'</th>'."\n";
echo '<th>'.__("Connection name").'</th>'."\n";
echo '<th colspan="2">'.__("Second behind master").'</th>'."\n";
echo '<th>'."Io running".'</th>'."\n";
echo '<th>'."Sql running".'</th>'."\n";
echo '<th>'."io error".'</th>'."\n";
echo '<th>'."Sql error".'</th>'."\n";
echo '<th>'."Replicate do db".'</th>'."\n";
echo '<th>'."Replicate ignore db".'</th>'."\n";
echo '<th>'.__("Date").'</th>'."\n";
echo '</tr>'."\n";

//debug($data);

$i = 0;

foreach ($data['slave'] as $slaves) {


    foreach ($slaves as $connect_name => $slave) {

        if (!empty($data['info_server'][$slave['id_mysql_server']]['']['is_proxysql']) && $data['info_server'][$slave['id_mysql_server']]['']['is_proxysql'] === "1") {
            continue;
        }

        $i++;

        echo '<tr>'."\n";
        echo '<td>'.$i.'</td>'."\n";
        //echo '<td>' .  . '</td>';

        $uniq            = $slave['master_host'].':'.$slave['master_port'];
        $id_mysql_server = Mysql::getIdFromDns($uniq);
        $realEndpoint = slave_render_real_endpoint($slave, $data);

        $id_master = null;
        if (!empty($data['server']['master'][$uniq]['id'])) {
            $id_master = $data['server']['master'][$uniq]['id'];
        } elseif ($id_mysql_server) {
            $id_master = $id_mysql_server;
        }

        $class = "";
        if ($id_master !== null) {
            $masterAvailable = $data['info_server'][$id_master][''][Available::MYSQL_AVAILABLE] ?? null;
            if ($masterAvailable === "0" || ! Available::getMySQL($id_master)) {
                $class = "pma pma-danger";
            }
        }

        echo '<td class="'.$class.'">';

        //if (Mysql::getMaster($id_mysql_server))

        if ($id_mysql_server) {
            echo Display::srv($id_mysql_server, true, LINK.'MysqlServer/main/'.$id_mysql_server.'/');
        } else {

            //updateAlias
            echo $realEndpoint.' <a href="'.LINK.'Mysql/add/mysql_server:ip:'.$slave['master_host'].'/mysql_server:port:'.$slave['master_port'].'" type="button" class="btn btn-default btn-xs">Add this server to monitoring</a>';
        }

        echo '</td>'."\n";

        $class = "";
        if (! Available::getMySQL($slave['id_mysql_server'])) {
            $class = "pma pma-danger";
        }elseif(! Available::getMySQL($slave['id_mysql_server']))
        {
            $class = "pma pma-warning";
        }

        echo '<td class="'.$class.'">';

        echo Display::srv($slave['id_mysql_server'], true, LINK.'MysqlServer/main/'.$slave['id_mysql_server'].'/');
        echo '</td>'."\n";

        echo '<td>';

        if (empty($connect_name)) {
            $disp = '<i>['.__("default").']</i>';
        } else {
            $disp = $connect_name;
        }

        echo '<a href="'.LINK.'slave/show/'.$slave['id_mysql_server'].'/'.$connect_name.'/">'.$disp.'</a>';

        //echo " ".$data['server']['idgraph'][$slave['id_mysql_server']][$connect_name];
        echo '</td>'."\n";
        //echo '<td>'.$slave['seconds_behind_master'].'</td>';

        if ($slave['seconds_behind_master'] !== "0" && $slave['seconds_behind_master'] !== "") {
            $class = "pma pma-warning";
        } else {
            $class = "";
        }

        $nullDash = '<span style="color:#999" title="NULL">—</span>';

        echo '<td class="'.$class.'">';
        if ($slave['seconds_behind_master'] === "") {
            echo $nullDash;
        } else {
            echo $slave['seconds_behind_master'];
        }

        // Lag stats (max / avg / std over 1 h) are populated client-side by
        // the indexGraphs AJAX call to keep the initial page render fast.
        $graphKey = $slave['id_mysql_server'].'|'.$connect_name;
        echo ' <span class="slave-lagstats" data-graph-key="'.htmlspecialchars($graphKey, ENT_QUOTES, 'UTF-8').'"></span>';
        echo '</td>'."\n";

        $canvasId = 'myChart'.$slave['id_mysql_server'].crc32($connect_name);
        echo '<td class="'.$class.'" style="padding:0;">'."\n";
        echo ' <div class="slave-sparkline-cell" data-graph-key="'.htmlspecialchars($graphKey, ENT_QUOTES, 'UTF-8').'" style="width:160px; height:17px; display:inline-block; position:relative; vertical-align:bottom;">'."\n"
            .'  <i class="slave-sparkline-spinner fa fa-spinner fa-spin" style="font-size:12px;color:#999;line-height:17px;"></i>'."\n"
            .'  <canvas width="160" height="17" style="width:160px;height:17px;display:none;vertical-align:bottom;" id="'.$canvasId.'"></canvas>'."\n"
            .'</div>'."\n";
        echo '</td>'."\n";

        $class = "";
        if ($slave['slave_io_running'] === "No") {
            $class = 'pma pma-primary';
        } else if ($slave['slave_io_running'] === "Connecting") {
            $class = 'pma pma-warning';
        }

        echo '<td class="'.$class.'">';
        echo $slave['slave_io_running'];
        echo '</td>'."\n";

        $class = "";
        if ($slave['slave_sql_running'] === "No") {
            $class = 'pma pma-primary';
        }

        echo '<td class="'.$class.'">';
        echo $slave['slave_sql_running'];
        echo '</td>'."\n";

        //echo '<td>'.$slave['last_io_error'].'</td>';

        $class = "";
        if ($slave['last_io_errno'] !== "0") {
            $class = 'pma pma-danger';
        }
        echo '<td  class="'.$class.'">';

        if (!empty($slave['last_io_error'])) {
            // Escape — error messages contain `"`, `<`, etc. that would
            // otherwise terminate the title attribute early and leak the
            // tail of the SQL into raw HTML, displacing the errno number.
            echo '<a href="#" data-toggle="tooltip" data-placement="right" title="'.htmlspecialchars((string) $slave['last_io_error'], ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars((string) $slave['last_io_errno']).'</a>';
        }
        echo '</td>'."\n";

        $class = "";
        if ($slave['last_sql_errno'] !== "0") {
            $class = 'pma pma-danger';
        }
        echo '<td  class="'.$class.'">';

        if (!empty($slave['last_sql_error'])) {
            echo '<a href="#" data-toggle="tooltip" data-placement="right" title="'.htmlspecialchars((string) $slave['last_sql_error'], ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars((string) $slave['last_sql_errno']).'</a>';
        }
        echo '</td>'."\n";

        echo '<td>';
        display_db($slave['replicate_do_db']);
        echo '</td>'."\n";

        echo '<td>';
        display_db($slave['replicate_ignore_db']);
        echo '</td>'."\n";

        echo '<td>'.$slave['date'].'</td>'."\n";
        echo '</tr>'."\n";
    }
}

echo '</table>'."\n";

// Sparkline + lag-stats are fetched after the page renders so the initial
// HTML hits the browser before the 1 h history UNION over ts_value_slave_*
// completes. Endpoint is server-side cached with a 10 s TTL, so reloads
// inside that window are near-instant.
$graphsUrl = LINK.'slave/indexGraphs/ajax:true/';
?>
<script>
(function(){
    var GRAPHS_URL = <?= json_encode($graphsUrl) ?>;

    function drawSpark(canvas, data){
        if (!canvas || !canvas.getContext) { return; }
        var ctx = canvas.getContext('2d');
        var w = canvas.width, h = canvas.height;
        ctx.clearRect(0, 0, w, h);
        var n = data.length, mx = 0;
        for (var i = 0; i < n; i++) { var v = data[i]; if (v !== null && v > mx) mx = v; }
        if (mx <= 0) { mx = 1; }
        function px(i){ return n > 1 ? (i / (n - 1)) * (w - 1) : 0; }
        function py(v){ if (v === null) return null; return (h - 1) - (v / mx) * (h - 1); }
        // filled area
        ctx.beginPath(); ctx.moveTo(0, h);
        var started = false;
        for (var j = 0; j < n; j++){
            var y = py(data[j]);
            if (y === null) continue;
            var x = px(j);
            if (!started){ ctx.lineTo(x, h); ctx.lineTo(x, y); started = true; }
            else { ctx.lineTo(x, y); }
        }
        ctx.lineTo(w, h); ctx.closePath();
        ctx.fillStyle = 'rgba(22,40,90,0.3)'; ctx.fill();
        // stroke
        ctx.beginPath(); started = false;
        for (var k = 0; k < n; k++){
            var yy = py(data[k]);
            if (yy === null){ started = false; continue; }
            var xx = px(k);
            if (!started){ ctx.moveTo(xx, yy); started = true; }
            else { ctx.lineTo(xx, yy); }
        }
        ctx.strokeStyle = 'rgba(0,0,0,1)'; ctx.lineWidth = 1; ctx.stroke();
    }

    function fmtStat(v){
        if (v === null || v === undefined) {
            return '<span style="color:#999" title="NULL">—</span>';
        }
        return v;
    }

    function renderRow(key, entry){
        var cells = document.querySelectorAll('.slave-sparkline-cell[data-graph-key="' + key.replace(/"/g, '\\"') + '"]');
        for (var i = 0; i < cells.length; i++){
            var cell = cells[i];
            var spinner = cell.querySelector('.slave-sparkline-spinner');
            if (spinner) { spinner.style.display = 'none'; }
            var canvas = cell.querySelector('canvas');
            if (canvas){
                canvas.style.display = '';
                if (entry && entry.values && entry.values.length){
                    drawSpark(canvas, entry.values);
                }
            }
        }
        var statSpans = document.querySelectorAll('.slave-lagstats[data-graph-key="' + key.replace(/"/g, '\\"') + '"]');
        for (var s = 0; s < statSpans.length; s++){
            var span = statSpans[s];
            if (!entry) { span.innerHTML = ''; continue; }
            var mx = entry.max, av = entry.avg, sd = entry.std;
            if (mx === null && av === null && sd === null) { span.innerHTML = ''; continue; }
            span.innerHTML = ' (max : ' + fmtStat(mx) + ' / avg : ' + fmtStat(av) + ' / std : ' + fmtStat(sd) + ')';
        }
    }

    function clearAllSpinners(){
        var cells = document.querySelectorAll('.slave-sparkline-cell');
        for (var i = 0; i < cells.length; i++){
            var spinner = cells[i].querySelector('.slave-sparkline-spinner');
            if (spinner) { spinner.style.display = 'none'; }
        }
    }

    function loadGraphs(){
        var xhr = new XMLHttpRequest();
        xhr.open('GET', GRAPHS_URL, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onreadystatechange = function(){
            if (xhr.readyState !== 4) return;
            if (xhr.status < 200 || xhr.status >= 300) { clearAllSpinners(); return; }
            var payload;
            try { payload = JSON.parse(xhr.responseText); }
            catch(e) { clearAllSpinners(); return; }
            var byKey = (payload && payload.by_key) || {};
            // Render every cell on the page (even when key has no data — hides spinner).
            var seen = {};
            for (var k in byKey){ if (byKey.hasOwnProperty(k)){ renderRow(k, byKey[k]); seen[k] = true; } }
            var cells = document.querySelectorAll('.slave-sparkline-cell[data-graph-key]');
            for (var i = 0; i < cells.length; i++){
                var key = cells[i].getAttribute('data-graph-key');
                if (!seen[key]) { renderRow(key, null); }
            }
        };
        xhr.send();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadGraphs);
    } else {
        loadGraphs();
    }
})();
</script>
<?php
