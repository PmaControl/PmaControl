<?php
echo '<a href="'.LINK.'ForeignKey/autoDetect/'.$param[0].'/'.$param[1].'/" role="button" class="btn btn-primary">'.__('Auto generate virtual foreign keys').'</a>';
?>
<br /><br />
<div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">

                <?= __("Virtual foreign keys") ?>
                </h3>
        </div>
        <div>

<?php

echo '<table class="table table-condensed table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>'.__("Top").'</th>';
echo '<th>'.__("Schema").'</th>';
echo '<th>'.__("Table").'</th>';
echo '<th>'.__("Field").'</th>';
echo '<th>'.__("ref_schema").'</th>';
echo '<th>'.__("ref_table").'</th>';
echo '<th>'.__("ref_field").'</th>';
echo '<th>'.__("Date generated").'</th>';
echo '<th>'.__("Operation").'</th>';
echo '</tr>';
$i=0;
foreach($data['virtual_fk'] as $key => $fk ){

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$fk['constraint_schema'].'</td>';
    echo '<td>'.$fk['constraint_table'].'</td>';
    echo '<td>'.$fk['constraint_column'].'</td>';
    echo '<td>'.$fk['referenced_schema'].'</td>';
    echo '<td>'.$fk['referenced_table'].'</td>';
    echo '<td>'.$fk['referenced_column'].'</td>';
    echo '<td>'.$fk['date_inserted'].'</td>';
    echo '<td>'
    . '<a href="'.LINK.'ForeignKey/addForeignKey/'.$fk['id'].'"><big><span class="label label-success">'.__("Add real foreign key").'</span></big></a>'
            ."&nbsp;"
    . '<a href="'.LINK.'ForeignKey/rmForeignKey/'.$fk['id'].'"><big><span class="label label-primary cursor">'.__("Remove virtual foreign key").'</span></big><a/>'
    . '</label>'
    . '</td>';
    echo '</tr>';

}

echo '</table>';

?>
</div>
</div>