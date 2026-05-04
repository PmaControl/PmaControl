<?php
/**
 * Issue #742 diagnostic view. Self-contained HTML, no layout, no CSS.
 * Renders one canvas per replica with a tiny inline script that draws the
 * lag sparkline directly on the 2D context.
 *
 * @var array<int, array{id_mysql_server:int|string, connection_name:string, values:array<int,string>}> $rows
 */

$rows = $rows ?? [];

echo "<!DOCTYPE html>\n";
echo "<html><head><meta charset=\"utf-8\"><title>Slave sparkline test (#742)</title></head><body>\n";
echo '<h1>Sparkline diagnostic test</h1>'."\n";
echo '<p>One canvas per replica, no CSS, no Chart.js. If the line draws here '
    .'but not on /slave/index, the bug is in /slave/index layout.</p>'."\n";

if (empty($rows)) {
    echo '<p><strong>No data — Extraction returned 0 rows.</strong></p>'."\n";
} else {
    echo '<p>Rendering '.count($rows).' canvas(es). Border added for visibility.</p>'."\n";
    echo "<table border=\"1\">\n";
    echo "<tr><th>server</th><th>conn</th><th>points</th><th>min</th><th>max</th><th>canvas (160x17)</th><th>canvas (160x60)</th></tr>\n";

    $scriptBlocks = [];
    foreach ($rows as $row) {
        $sid     = (int) $row['id_mysql_server'];
        $conn    = (string) $row['connection_name'];
        $values  = $row['values'];
        $count   = count($values);
        $numeric = array_filter($values, static fn ($v) => $v !== 'null');
        $min     = $numeric ? min(array_map('floatval', $numeric)) : 'n/a';
        $max     = $numeric ? max(array_map('floatval', $numeric)) : 'n/a';
        $idSmall = 'spark_'.$sid.'_'.crc32($conn).'_s';
        $idTall  = 'spark_'.$sid.'_'.crc32($conn).'_t';
        $valuesJs = '[' . implode(',', $values) . ']';

        echo "<tr>";
        echo "<td>".$sid."</td>";
        echo "<td>".htmlspecialchars($conn === '' ? '(default)' : $conn)."</td>";
        echo "<td>".$count."</td>";
        echo "<td>".(is_string($min) ? $min : (string) $min)."</td>";
        echo "<td>".(is_string($max) ? $max : (string) $max)."</td>";
        echo "<td><canvas id=\"".$idSmall."\" width=\"160\" height=\"17\" style=\"border:1px solid red\"></canvas></td>";
        echo "<td><canvas id=\"".$idTall."\" width=\"160\" height=\"60\" style=\"border:1px solid red\"></canvas></td>";
        echo "</tr>\n";

        $scriptBlocks[] = '__draw("'.$idSmall.'",'.$valuesJs.');';
        $scriptBlocks[] = '__draw("'.$idTall .'",'.$valuesJs.');';
    }
    echo "</table>\n";

    echo "<script>\n";
    echo "function __draw(id, d) {\n";
    echo "  var c = document.getElementById(id);\n";
    echo "  if (!c || !c.getContext) { console.warn('no canvas', id); return; }\n";
    echo "  var ctx = c.getContext('2d');\n";
    echo "  var w = c.width, h = c.height;\n";
    echo "  ctx.clearRect(0, 0, w, h);\n";
    echo "  var n = d.length, mn = null, mx = null;\n";
    echo "  for (var i = 0; i < n; i++) { var v = d[i]; if (v === null) continue; if (mn === null || v < mn) mn = v; if (mx === null || v > mx) mx = v; }\n";
    echo "  if (mn === null) { mn = 0; mx = 0; }\n";
    echo "  var rng = mx - mn;\n";
    echo "  function px(i) { return n > 1 ? (i / (n - 1)) * (w - 1) : 0; }\n";
    echo "  function py(v) { if (v === null) return null; if (rng <= 0) return Math.round((h - 1) / 2); return (h - 1) - ((v - mn) / rng) * (h - 1); }\n";
    echo "  ctx.beginPath(); ctx.moveTo(0, h);\n";
    echo "  var started = false;\n";
    echo "  for (var i = 0; i < n; i++) { var y = py(d[i]); if (y === null) continue; var x = px(i); if (!started) { ctx.lineTo(x, h); ctx.lineTo(x, y); started = true; } else { ctx.lineTo(x, y); } }\n";
    echo "  ctx.lineTo(w, h); ctx.closePath();\n";
    echo "  ctx.fillStyle = 'rgba(22,40,90,0.3)'; ctx.fill();\n";
    echo "  ctx.beginPath(); started = false;\n";
    echo "  for (var i = 0; i < n; i++) { var y = py(d[i]); if (y === null) { started = false; continue; } var x = px(i); if (!started) { ctx.moveTo(x, y); started = true; } else { ctx.lineTo(x, y); } }\n";
    echo "  ctx.strokeStyle = 'rgba(0,0,0,1)'; ctx.lineWidth = 1; ctx.stroke();\n";
    echo "  console.log('drew', id, 'n=', n, 'min=', mn, 'max=', mx);\n";
    echo "}\n";
    foreach ($scriptBlocks as $line) {
        echo $line."\n";
    }
    echo "</script>\n";
}

echo "</body></html>\n";
