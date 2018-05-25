<?php
if (count($data['resultat'][$_GET['menu']]) != 0) {

    if ($_GET['menu'] == 'TABLE'):
        ?>
        <div class="well" style="border-left-color: #d9534f;   border-left-width: 5px;">
            <p><b>IMPORTANT !!!</b></p>
            This tool will not handle a case when the field was renamed. It will generate 2 queries, one to drop the column with the old name and one to create column with the new name, so if there is a data in the dropped column, it will be lost.
        </div>
        <?php
    endif;

    //echo '<div class="well">';
    echo '<table class="table table-condensed">';
    echo '<tr>';
    echo '<th><a href="#" class="btn btn-default" style="width:100%"><span style="font-size:12px" class="glyphicon glyphicon-list-alt"></span> Tables</a></th>';
    echo '<th><a href="#" class="btn btn-default" style="width:100%">Orginal</a></th>';
    echo '<th><a href="#" class="btn btn-default" style="width:100%">Compare</a></th>';
    echo '<th><a href="#" class="btn btn-default" style="width:100%">Equality</a></th>';
    echo '<th><a href="#" id="vers" class="btn btn-primary" style="width:100%"><span style="font-size:12px" class="glyphicon glyphicon-arrow-left"></span> <span style="font-size:12px" class="glyphicon glyphicon-arrow-right"></span> '.__("Switch").'</a></th>';

    echo '</tr>';


    foreach ($data['resultat'][$_GET['menu']] as $tablename => $table) {

        if (!empty($table[0])) {
            $class_ori = "glyphicon glyphicon-ok";
        } else {
            $class_ori = "glyphicon glyphicon-remove";
        }

        if (!empty($table[1])) {
            $class_cmp = "glyphicon glyphicon-ok";
        } else {
            $class_cmp = "glyphicon glyphicon-remove";
        }

        if (empty($table['script'])) {
            $class_eq = "glyphicon glyphicon-ok";
        } else {
            $class_eq = "glyphicon glyphicon-remove";
        }

        echo '<tr>';
        echo '<td>'.$tablename.'</td>';

        echo '<td><span class="'.$class_ori.' danger"></span></td>';
        echo '<td><span class="'.$class_cmp.'"></span></td>';
        echo '<td><span class="'.$class_eq.'"></span></td>';
        echo '<td class="vers2">';

        if (count($table['script']) != 0) {
            $queries = implode(';', $table['script']).";";
            echo SqlFormatter::format($queries);
        }
        echo '</td>';

        echo '<td style="display:none" class="vers1">';
        if (count($table['script2']) != 0) {
            $queries = implode(';', $table['script2']).";";
            echo SqlFormatter::format($queries);
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';



    if ($_GET['menu'] == 'TABLE'):
        ?>
        <div class="well" style="border-left-color: #5cb85c;   border-left-width: 5px;">
            <p><b>Some features :</b></p>
            <ul>
                <li><code>AUTO_INCREMENT</code> value is ommited during the comparison and in resulting <code>CREATE TABLE</code> sql</li>
                <li>Fields with definitions like <code>(var)char (255) NOT NULL default ''</code> and <code>(var)char (255) NOT NULL</code> are treated as equal, the same for <code>(big|tiny)int NOT NULL default 0;</code></li>
                <li><code>IF NOT EXISTS</code> is automatically added to the resulting sql CREATE TABLE statement.</li>
                <li>Fields updating queries always come before key modification ones for each table.</li>
            </ul>
            <p><b>Not implemented yet :</b></p>
            <ul>
                <li>This tool even does not try to insert or re-order fields in the same order as in the original table. Does order matter?</li>
                <li>This tools don't take engine, charset and collation in consideration.</li>
            </ul>
        </div>
        <?php
        echo '<textarea class="form-control" style="height:500px">';

        foreach ($data['resultat'][$_GET['menu']] as $tablename => $table) {
            if (count($table['script']) != 0) {
                $queries = implode(';', $table['script']).";";
                $queries = str_replace(";",";\n", $queries);
                echo $queries."\n";
            }
            
        }


        echo '</textarea>';




    endif;
}
?>

<!-- view -->



<?php
/*
if (!empty($data['VIEW'])) {

    //echo '<div class="well">';
    echo '<table class="table table-condensed">';
    echo '<tr>';
    echo '<th><a href="#" class="btn btn-default" style="width:100%"><span style="font-size:12px" class="glyphicon glyphicon-list-alt"></span> Views</a></th>';
    echo '<th><a href="#" class="btn btn-default" style="width:100%">Orginal</a></th>';
    echo '<th><a href="#" class="btn btn-default" style="width:100%">Compare</a></th>';
    echo '<th><a href="#" class="btn btn-default" style="width:100%">Equality</a></th>';
    echo '<th><a href="#" id="vers" class="btn btn-primary" style="width:100%"><span style="font-size:12px" class="glyphicon glyphicon-arrow-left"></span> <span style="font-size:12px" class="glyphicon glyphicon-arrow-right"></span> '.__("Switch").'</a></th>';
    echo '</tr>';

    foreach ($data['VIEW'] as $tablename => $table) {

        if (!empty($table[0])) {
            $class_ori = "glyphicon glyphicon-ok";
        } else {
            $class_ori = "glyphicon glyphicon-remove";
        }

        if (!empty($table[1])) {
            $class_cmp = "glyphicon glyphicon-ok";
        } else {
            $class_cmp = "glyphicon glyphicon-remove";
        }

        if (empty($table['script'])) {
            $class_eq = "glyphicon glyphicon-ok";
        } else {
            $class_eq = "glyphicon glyphicon-remove";
        }

        echo '<tr>';
        echo '<td>'.$tablename.'</td>';

        echo '<td><span class="'.$class_ori.' danger"></span></td>';
        echo '<td><span class="'.$class_cmp.'"></span></td>';
        echo '<td><span class="'.$class_eq.'"></span></td>';
        echo '<td class="vers2">';

        if (count($table['script']) != 0) {
            $queries = implode(';', $table['script']).";";
            echo SqlFormatter::format($queries);
        }
        echo '</td>';

        echo '<td style="display:none" class="vers1">';
        if (count($table['script2']) != 0) {
            $queries = implode(';', $table['script2']).";";
            echo SqlFormatter::format($queries);
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
 */