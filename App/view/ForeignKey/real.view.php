<?php
use App\Library\Security\CsrfRender;

$foreignKeyMutationInput = CsrfRender::hiddenInput($data, 'foreign_key_mutation');
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
$foreignKeyActionForm = static function (
    string $action,
    string $label,
    string $labelClass,
    array $fields
) use ($foreignKeyMutationInput, $escape): string {
    $html = '<form method="post" action="'.$action.'" style="display:inline">';
    $html .= $foreignKeyMutationInput;
    foreach ($fields as $name => $value) {
        $html .= '<input type="hidden" name="'.$escape($name).'" value="'.$escape($value).'">';
    }
    $html .= '<button type="submit" class="btn btn-link" style="border:0;background:transparent;padding:0">';
    $html .= '<big><span class="label '.$labelClass.'">'.$escape($label).'</span></big>';
    $html .= '</button></form>';

    return $html;
};
?>
<div >
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlServer", "menu", $data['param']); ?></div>
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlDatabase", "menu", $data['param']); ?></div>
  <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("ForeignKey", "menu", $data['param']); ?></div>
</div> 
<div style="clear:both"></div>
<br />
<?php
echo '<form method="post" action="'.LINK.'ForeignKey/import/" style="display:inline">';
echo $foreignKeyMutationInput;
echo '<input type="hidden" name="id_mysql_server" value="'.$escape($param[0]).'">';
echo '<input type="hidden" name="database" value="'.$escape($param[1]).'">';
echo '<button type="submit" class="btn btn-primary">'.__('Import foreign keys').'</button>';
echo '</form>';
?>
<br /><br />
<div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">

                <?= __("Real foreign keys") ?>
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
foreach($data['real_fk'] as $key => $fk ){

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
    . $foreignKeyActionForm(
        LINK.'ForeignKey/dropForeignKey/',
        'Remove foreign key',
        'label-danger',
        ['id' => $fk['id']]
    )
    . '</td>';
    echo '</tr>';

}

echo '</table>';

?>
</div>
</div>
