<?php
use App\Controller\Query;
?>

<div class="container" style="width:100%">
  <div class="row">

    <!-- Normalized Query -->
    <div class="col-md-6">
      <h4>Requête Normalisée (digest)</h4>
      <div style="padding:10px; background:#fafafa; border:1px solid #ddd">
        <?php echo \SqlFormatter::format($data['digest_text']); ?>
      </div>
    </div>

    <!-- Real Example Query -->
    <div class="col-md-6">
      <h4>Exemple réel observé</h4>
      <div style="padding:10px; background:#fff; border:1px solid #ddd">
        <?php echo \SqlFormatter::format($data['sql_text']); ?>
      </div>
    </div>

  </div>

  <br>

  <!-- EXPLAIN CLASSIQUE -->
  <div class="panel panel-primary">
      <div class="panel-heading">
          <h3 class="panel-title"><?= __("Explain") ?></h3>
      </div>
      <div>

<?php
echo '<table class="table table-condensed table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>id</th>';
echo '<th>select_type</th>';
echo '<th>table</th>';
echo '<th>possible_keys</th>';
echo '<th>key</th>';
echo '<th>key_len</th>';
echo '<th>ref</th>';
echo '<th>rows</th>';
echo '<th>filtered</th>';
echo '<th>extra</th>';
echo '</tr>';

foreach($data['explain'] as $explain)
{
    if (! empty($data['alias'][$explain['table']]['table']))
    {
      $alias = $data['alias'][$explain['table']]['table'] ." (".$explain['table'].")";
    }
    else{
      $alias =  $explain['table'];
    }

    echo '<tr>';
    echo '<td>'.$explain['id'].'</td>';
    echo '<td>'.$explain['select_type'].'</td>';
    echo '<td>'.$alias.'</td>';
    echo '<td>'.$explain['possible_keys'].'</td>';
    echo '<td>'.$explain['key'].'</td>';
    echo '<td>'.$explain['key_len'].'</td>';
    echo '<td>'.$explain['ref'].'</td>';
    echo '<td>'.$explain['rows'].'</td>';
    echo '<td>'.$explain['filtered'].'</td>';
    echo '<td>'.$explain['Extra'].'</td>';
    echo '</tr>';
}

echo '</table>';
?>
      </div>
  </div>


<!-- EXPLAIN JSON INTERPRÉTÉ + SHOW INDEX CARDINALITY -->
<?php if (!empty($data['explain_json'])): ?>

<div class="panel panel-info" style="margin-top:20px;">
  <div class="panel-heading">
    <h3 class="panel-title">Explain (JSON Interprété)</h3>
  </div>
  <div class="panel-body">

<?php
    $pretty = Query::parseExplainJson($data['explain_json']);

    echo '<table class="table table-condensed table-bordered table-striped">';
    echo '<tr>
            <th>Table</th>
            <th>Access Type</th>
            <th>Rows</th>
            <th>Filtered</th>
            <th>Possible Keys</th>
            <th>Key Used</th>
            <th>Cost</th>
          </tr>';

    foreach ($pretty as $row) {

        $tbl = $row['table'];

        echo '<tr>';
        echo '<td>'.$row['table'].'</td>';
        echo '<td>'.$row['access_type'].'</td>';

        $rowsStyle = ((int)$row['rows'] > 500000) ? 'color:red;font-weight:bold' :
                     ((int)$row['rows'] > 50000 ? 'color:#d78400' : '');
        echo '<td style="'.$rowsStyle.'">'.$row['rows'].'</td>';

        echo '<td>'.$row['filtered'].'</td>';
        echo '<td>';
        
        //$row['possible_keys'];
        
        echo '</td>';
        echo '<td style="font-weight:bold">'.$row['key'].'</td>';

        $costStyle = ($row['cost'] > 200000) ? 'color:red;font-weight:bold' : '';
        echo '<td style="'.$costStyle.'">'.$row['cost'].'</td>';
        echo '</tr>';


        // Sous tableau CARDINALITY
        if (!empty($data['index_info'][$tbl])) {
            
            echo '<tr><td colspan="7" style="padding:0;background:#fafafa">';
            echo '<table class="table table-condensed" style="margin:0;border:0;font-size:12px">';
            echo '<tr style="background:#f0f0f0">
                    <th style="width:180px">Index</th>
                    <th>Column</th>
                    <th>Seq</th>
                    <th>Cardinality</th>
                    <th>Unique</th>
                  </tr>';

            foreach ($data['index_info'][$tbl] as $idx) {

                $style = ($idx['cardinality'] <= 10 ? 'color:#d78400;font-weight:bold' : '');

                echo '<tr>';
                echo '<td>'.$idx['index_name'].'</td>';
                echo '<td>'.$idx['column_name'].'</td>';
                echo '<td>'.$idx['seq'].'</td>';
                echo '<td style="'.$style.'">'.$idx['cardinality'].'</td>';
                echo '<td>'.($idx['non_unique'] ? 'NO' : '<b>YES</b>').'</td>';
                echo '</tr>';
            }

            echo '</table>';
            echo '</td></tr>';
        }
    }

    echo '</table>';
?>

  </div>
</div>

<?php endif; ?>

</div>