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

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<table class="table table-condensed table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>'.__("Top").'</th>';
echo '<th>'.__("Schema").'</th>';
echo '<th>'.__("Table").'</th>';
echo '<th>'.__("Field").'</th>';
echo '<th>'.__("ref_schema").'</th>';
echo '<th>'.__("ref_table").'</th>';
echo '<th>'.__("ref_field").'</th>';
echo '<th>'.__("Operation").'</th>';
echo '</tr>';

$i=0;
foreach($data['real_fk'] as $fk)
{
    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$fk['constraint_schema'].'</td>';
    echo '<td>'.$fk['constraint_table'].'</td>';
    echo '<td>'.$fk['constraint_column'].'</td>';
    echo '<td>'.$fk['referenced_schema'].'</td>';
    echo '<td>'.$fk['referenced_table'].'</td>';
    echo '<td>'.$fk['referenced_column'].'</td>';
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

foreach($data['virtual_fk'] as $key => $fk ){
    if (in_array($key, $data['real_fk'])){
        continue;
    }

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$fk['constraint_schema'].'</td>';
    echo '<td>'.$fk['constraint_table'].'</td>';
    echo '<td>'.$fk['constraint_column'].'</td>';
    echo '<td>'.$fk['referenced_schema'].'</td>';
    echo '<td>'.$fk['referenced_table'].'</td>';
    echo '<td>'.$fk['referenced_column'].'</td>';
    echo '<td>'
    . $foreignKeyActionForm(
        LINK.'ForeignKey/addForeignKey/',
        'Add foreign key',
        'label-success',
        ['id' => $fk['id']]
    )
    ."&nbsp;"
    . $foreignKeyActionForm(
        LINK.'ForeignKey/rmForeignKey/',
        'Remove virtual foreign key',
        'label-primary cursor',
        ['id' => $fk['id']]
    )
    . '</td>';
    echo '</tr>';

}

echo '</table>';
