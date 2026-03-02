<?php

\Glial\Synapse\FactoryController::addNode("ProxySQL", "menu", $data['param']);

$current_human = str_replace('_', ' ', $data['current']);
$back_link = LINK.'ProxySQL/config/'.$data['id_proxysql_server'].'/'.$data['current'].'/';

echo '&nbsp;&nbsp;&nbsp;'; 
echo '<div class="btn-group" role="group" aria-label="Default button group">';

foreach ($data['menu'] as $elems => $sql)
{
  $key_menu = str_replace(' ', '_', $elems);

  $active = "";
  if ($key_menu == $data['current']){
    $active = " active";
  }
  echo '<a href="'.LINK.'ProxySQL/addLine/'.$data['id_proxysql_server'].'/'.$key_menu.'" type="button" class="btn btn-primary'.$active.'">'.ucfirst(strtolower($elems)).'</a>';
}


echo '</div>';

if (isset($data['is_addline_allowed']) && $data['is_addline_allowed'] === false) {
    return;
}


echo '<br><br>';
echo '<div class="panel panel-primary">';
echo '<div class="panel-heading">';
echo '<h3 class="panel-title">'.__('Add a line').' : <b>'.$data['table_name'].'</b> <small>('.$current_human.')</small></h3>';
echo '</div>';
echo '<div class="panel-body">';

echo '<form action="" method="post">';
echo '<div class="row">';

$editable_column_found = false;

foreach ($data['columns'] as $column) {
    $column_name = $column['name'];
    $column_type = $column['type'];

    $is_required = !empty($column['notnull'])
        && $column['default'] === null
        && empty($column['autoincrement']);

    if (!empty($column['autoincrement'])) {
        continue;
    }

    $editable_column_found = true;

    $value = $data['post'][$column_name] ?? ($column['default'] ?? '');

    echo '<div class="col-md-6" style="margin-bottom:15px;">';
    echo '<label for="proxysql_addline_'.$column_name.'">'.$column_name;
    if ($is_required) {
        echo ' <span style="color:#d9534f">*</span>';
    }
    echo '</label>';

    if (!empty($column['is_select'])) {
        echo '<select class="form-control" id="proxysql_addline_'.$column_name.'" name="proxysql_addline['.$column_name.']"';
        if ($is_required) {
            echo ' required';
        }
        echo '>';

        if (!$is_required) {
            echo '<option value="">--</option>';
        }

        foreach ($column['enum_values'] as $enum_value) {
            $selected = ((string) $value === (string) $enum_value) ? ' selected' : '';
            echo '<option value="'.htmlspecialchars($enum_value, ENT_QUOTES, 'UTF-8').'"'.$selected.'>'
                .htmlspecialchars($enum_value, ENT_QUOTES, 'UTF-8').'</option>';
        }

        echo '</select>';
    } else {
        $input_type = !empty($column['is_numeric']) ? 'number' : 'text';

        echo '<input class="form-control" type="'.$input_type.'" id="proxysql_addline_'.$column_name.'" '
            .'name="proxysql_addline['.$column_name.']" '
            .'value="'.htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8').'"';

        if ($is_required) {
            echo ' required';
        }

        echo '>';
    }

    echo '<small class="text-muted">'.__('Type').' : '.$column_type;
    if (!empty($column['pk'])) {
        echo ' | PK';
    }
    if ($column['default'] !== null) {
        echo ' | '.__('Default').' : '.htmlspecialchars((string) $column['default'], ENT_QUOTES, 'UTF-8');
    }
    echo '</small>';
    echo '</div>';
}

echo '</div>';

if (!$editable_column_found) {
    echo '<div class="alert alert-warning">'.__('No editable field found for this table').'.</div>';
}

echo '<hr>';
echo '<button class="btn btn-success"'.($editable_column_found ? '' : ' disabled').'>'.__('Insert').'</button> ';
echo '<a href="'.$back_link.'" class="btn btn-default">'.__('Back').'</a>';
echo '</form>';

echo '</div>';
echo '</div>';
