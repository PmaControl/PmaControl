<?php

use App\Library\Display;

function alias_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

echo '<div style="margin-bottom:15px">';
echo '<a href="'.LINK.'alias/updateAlias/" class="btn btn-primary" style="font-size:12px; margin-right:8px"><span class="glyphicon glyphicon-refresh" style="font-size:12px"></span> Get aliases</a>';
$pendingAliasCount = count($data['pending_aliases'] ?? []);
echo '<button type="button" id="js-toggle-alias-form" class="btn btn-success" style="font-size:12px"><span class="glyphicon glyphicon-plus" style="font-size:12px"></span> Ajouter un alias <strong>('.$pendingAliasCount.')</strong></button>';
echo '</div>';

$hasPendingAliases = !empty($data['pending_aliases']);

echo '<div id="js-alias-form-panel" class="well" style="display:none">';
echo '<h4 style="margin-top:0">Alias proposes depuis slave/index</h4>';

if (!$hasPendingAliases) {
    echo '<p style="margin-bottom:0">'.__('No unresolved master found from slave/index').'</p>';
} else {
    echo '<table class="table table-condensed table-bordered table-striped" style="margin-bottom:0">';
    echo '<tr>';
    echo '<th>'.__('Alias à créer').'</th>';
    echo '<th>'.__('Vu sur').'</th>';
    echo '<th style="min-width:420px">'.__('Serveur lié').'</th>';
    echo '<th class="text-center">'.__('Action').'</th>';
    echo '</tr>';

    foreach ($data['pending_aliases'] as $pendingAlias) {
        $formId = 'alias-form-'.md5($pendingAlias['dns'].':'.$pendingAlias['port']);
        echo '<tr>';
        echo '<td><strong>'.alias_h($pendingAlias['dns']).':'.(int)$pendingAlias['port'].'</strong></td>';
        echo '<td>'.alias_h(implode(', ', $pendingAlias['sources'])).'</td>';
        echo '<td>';
        echo '<form id="'.alias_h($formId).'" action="'.LINK.'alias/index/" method="POST" style="margin:0">';
        echo '<input type="hidden" name="alias_dns[dns]" value="'.alias_h($pendingAlias['dns']).'" />';
        echo '<input type="hidden" name="alias_dns[port]" value="'.(int)$pendingAlias['port'].'" />';
        echo '<select name="alias_dns[id_mysql_server]" class="selectpicker" data-live-search="true" data-width="100%" title="'.alias_h(__('Choose a server')).'">';

        foreach ($pendingAlias['candidates'] as $candidate) {
            $selected = ((int)$pendingAlias['suggested_id_mysql_server'] === (int)$candidate['id']) ? ' selected="selected"' : '';

            echo '<option value="'.(int)$candidate['id'].'"'
                .$selected
                .' data-match-color="'.alias_h($candidate['match_color']).'"'
                .' data-tokens="'.alias_h($candidate['display_label'].' '.$candidate['hostname'].' '.$candidate['ip'].' '.$candidate['remote_host']).'"'
                .'>'.alias_h($candidate['display_label']).'</option>';
        }

        echo '</select>';
        echo '</form>';
        echo '</td>';
        echo '<td class="text-center">';
        echo '<button type="submit" form="'.alias_h($formId).'" class="btn btn-success btn-sm">'.__('Add alias').'</button>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

echo '</div>';

echo '<table class="table table-condensed table-bordered table-striped" id="table">';

echo '<tr>';
echo '<th>#</th>';
echo '<th>DNS</th>';
echo '<th>Port</th>';
echo '<th>'.__('Linked to').'</th>';
echo '<th>'.__('Source').'</th>';
echo '<th>'.__('Since').'</th>';
echo '<th>'.__('Action').'</th>';
echo '</tr>';

$i = 0;

foreach ($data['alia_dns'] as $alias) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.alias_h($alias['dns']).'</td>';
    echo '<td>'.(int)$alias['port'].'</td>';
    echo '<td>'.Display::srv($alias['id_mysql_server']);

    if (!empty($alias['is_from_ssh']) && (int)$alias['is_from_ssh'] === 1) {
        echo '<td><span class="label label-info">SSH</span></td>';
    } else {
        echo '<td><span class="label label-default">Manual / Auto</span></td>';
    }

    $date_start = explode(".", $alias['ROW_START'])[0];

    echo '<td>'.alias_h($date_start).'</td>';

    $msg = strip_tags(__('Delete this alias?'));

    echo '<td class="text-center">'
        .'<a href="'.LINK.'alias/delete/'.$alias['id'].'" '
        .'class="btn btn-danger btn-xs" '
        .'onclick="return confirm(\''.alias_h($msg).'\');">'
        .'<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>'
        .'</a>'
        .'</td>';

    echo '</tr>';
}

echo '</table>';

echo '<style>
.bootstrap-select .dropdown-menu li a[data-match-color] {
    transition: background-color 0.15s ease-in-out;
}
.bootstrap-select > .dropdown-toggle[data-match-color] {
    transition: background-color 0.15s ease-in-out;
}
</style>';

echo '<script>
(function () {
    var button = document.getElementById("js-toggle-alias-form");
    var panel = document.getElementById("js-alias-form-panel");
    var selects = document.querySelectorAll(".selectpicker");

    function applyAliasHighlight(select) {
        if (!select || !select.parentNode) {
            return;
        }

        var wrapper = select.parentNode.querySelector(".bootstrap-select");
        if (!wrapper) {
            return;
        }

        var buttonSelect = wrapper.querySelector(".dropdown-toggle");
        var options = select.options;
        var links = wrapper.querySelectorAll(".dropdown-menu.inner li a");

        for (var i = 0; i < links.length; i++) {
            var originalIndex = links[i].parentNode.getAttribute("data-original-index");
            if (originalIndex === null || typeof options[originalIndex] === "undefined") {
                continue;
            }

            var color = options[originalIndex].getAttribute("data-match-color");
            if (!color) {
                continue;
            }

            links[i].setAttribute("data-match-color", color);
            links[i].style.backgroundColor = color;
        }

        if (!buttonSelect) {
            return;
        }

        var selectedOption = options[select.selectedIndex];
        var selectedColor = selectedOption ? selectedOption.getAttribute("data-match-color") : "";
        buttonSelect.setAttribute("data-match-color", selectedColor || "");
        buttonSelect.style.backgroundColor = selectedColor || "";
    }

    if (!button || !panel) {
        return;
    }

    button.addEventListener("click", function () {
        panel.style.display = panel.style.display === "none" ? "block" : "none";

        if (panel.style.display !== "none" && window.jQuery) {
            window.jQuery(selects).selectpicker("refresh");
            for (var i = 0; i < selects.length; i++) {
                applyAliasHighlight(selects[i]);
            }
        }
    });

    if (window.jQuery) {
        window.jQuery(selects).on("loaded.bs.select refreshed.bs.select shown.bs.select", function () {
            applyAliasHighlight(this);
        });
        window.jQuery(selects).on("changed.bs.select", function () {
            applyAliasHighlight(this);
        });
    }
})();
</script>';
