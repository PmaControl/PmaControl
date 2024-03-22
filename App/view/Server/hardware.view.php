<?php

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';


echo '<table class="table table-condensed table-bordered table-striped" id="table">';


echo '<tr>';

echo '<th>' . __("Top") . '</th>';
echo '<th>' . __("ID") . '</th>';
echo '<th>' . __("Available SSH") . '</th>';
echo '<th>' . __("Name") . '</th>';
echo '<th>' . __("Hostname") . '</th>';
echo '<th>' . __("User") . '</th>';
echo '<th>' . __("IP") . '</th>';


echo '<th>' . __("Operations system") . '</th>';
echo '<th>' . __("Product name") . '</th>';
echo '<th>' . __("Arch") . '</th>';
echo '<th>' . __("Kernel") . '</th>';
echo '<th>' . __("Processor") . '</th>';
echo '<th>' . "Mhz" . '</th>';

echo '<th>' . __("Memory") . '</th>';
echo '<th>Swapiness</th>';
//echo '<th title="0.75*CPU*GHZ + 0.5 Memory Go">' . __("Indice") . '</th>';
echo '</tr>';


$i = 0;

    foreach ($data['servers'] as $server) {
        $i++;


        $hardware = $data['hardware'][$server['id']][''];


        /*
          if (empty($server['operating_system'])) {
          continue;
          } */

        $style = "";
        if (empty($data['service_ssh'][$server['id']]['']['ssh_available']) && $server['is_monitored'] == "1") {
            $style = 'background-color:#d9534f; color:#FFFFFF';
        }


        $hardware['cpu_thread_count'] = $hardware['cpu_thread_count'] ?? "n/a";

        echo '<tr class="alternate">';
        echo '<td style="' . $style . '">' . $i . '</td>';
        echo '<td style="' . $style . '">' . $server['id'] . '</td>';
        echo '<td style="' . $style . '">';
        echo '<span class="glyphicon ' . ($data['service_ssh'][$server['id']]['']['ssh_available'] == 1 ? "glyphicon-ok" : "glyphicon-remove") . '" aria-hidden="true"></span>';
        echo '</td>';
        echo '<td style="' . $style . '">' . $server['display_name'] . '</td>';
        echo '<td style="' . $style . '">' . $hardware['hostname'] . '</td>';
        echo '<td style="' . $style . '">' . "root" . '</td>';
        echo '<td style="' . $style . '">' . $server['ip'] . ':' . $server['ssh_port'] . '</td>';
        echo '<td style="' . $style . '">';

        if (!empty($hardware['distributor'])) {
            echo '<img src="' . IMG . '/os/' . strtolower($hardware['distributor']) . '.png" alt="[' . $hardware['distributor'] . ']" title="' . $hardware['distributor'] . '" style="width:16px;height:16px;vertical-align:middle;"> ';
        }


        echo $hardware['os'] . '</td>';
        echo '<td style="' . $style . '">' . $hardware['product_name'] . '</td>';
        $class = ("i686" == $hardware['arch']) ? "error" : "";
        echo '<td style="' . $style . '" class="' . $class . '">' . $hardware['arch'] . '</td>';
        echo '<td style="' . $style . '">' . $hardware['kernel'] . '</td>';
        echo '<td style="' . $style . '">' . $hardware['cpu_thread_count'] . '</td>';
        echo '<td style="' . $style . '">' . $hardware['cpu_frequency'] . '</td>';
        echo '<td style="' . $style . '">' . $hardware['memory'] . '</td>';
        echo '<td style="' . $style . '">' . $hardware['swapiness'] . ' </td>';
        //echo '<td style="' . $style . '">' . round(0.75 * $server['processor'] * ($server['cpu_mhz'] / 1024) + 0.5 * ($server['memory_kb'] / 1024 / 1024), 2) . '</td>';


        echo '</tr>';
    }

echo '</table>';

