<?php

use Glial\Html\Form\Form;
?>
<span id="loading" class="fa-stack fa-fw fa-lg text-danger" style="display:none; position:fixed; top:50%; left:50%;">
    <i class="fa fa-spinner fa-5x fa-spin"></i>
</span>

<form action="" method="post">
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <?php
            //debug($data['scan']);


            $status['open']     = "check";
            $status['closed']   = "close";
            $status['filtered'] = "filter";
            echo '<thead>';
            echo '<tr>';
            echo '<th width="5%" rowspan="2"><input id="check-all" type="checkbox" name="vehicle" value="1"></th>';
            echo '<th width="5%" rowspan="2">'.__('Top').'</th>';
            echo '<th width="15%" rowspan="2">'.__('Hostname').'</th>';
            echo '<th width="5%" rowspan="2">'.__('IP').'</th>';
            //echo '<th rowspan="2">'.__('MAC').'</th>';
            echo '<th width="5%" rowspan="2">'.__('Type').'</th>';
            echo '<th width="5%" rowspan="2">'.__('Latency').'</th>';
            echo '<th width="5%" rowspan="2">'.__('Refresh').'</th>';
            echo '<th width="50%" colspan="'.count($data['port']).'">'.__('Port').'</th>';
            echo '<th width="5%" rowspan="2">'.__('Monitoring').'</th>';
            echo '</tr>';

            echo '<tr>';
            foreach ($data['port'] as $port => $elems) {
                echo '<th title="'.$elems.'">'.$port.'</th>';
            }
            echo '</tr>';
            echo '</thead>';
            $i = 0;



            echo '<tbody>';
            foreach ($data['scaned'] as $line) {
                $is_mysql = false;

                $ports = json_decode($line['data'], true);

                $list_port = [];
                $style     = "";

                $is_linux = false;

                if (!empty($ports['3306'])) {

                    if (!in_array($line['ip'], $data['ip'])) {
                        $style    = "background-color:#dff0d8";
                        $is_mysql = true;
                    }
                }

                if (!empty($ports['22'])) {
                    $is_linux = true;
                }



                echo '<tr>';
                echo '<td width="5%" style="'.$style.'">'.($is_mysql ? '<input type="checkbox" name="vehicle" class="check-box">' : '').'</td>';
                echo '<td width="5%" style="'.$style.'">'.$i.'</td>';
                echo '<td width="5%" style="'.$style.'">'.$line['ip'].'</td>';
                echo '<td width="5%" style="'.$style.'">'.$line['ip'].'</td>';
                //echo '<td style="'.$style.'">Mac</td>';


                if ($is_linux) {
                    $os = "linux";
                } else {
                    $os = "windows";
                }

                echo '<td style="'.$style.'"><i style="font-size:16px" class="fa fa-'.$os.'"></i></td>';
                echo '<td style="'.$style.'">'.$line['ms'].' ms</td>';
                echo '<td style="'.$style.'">'.$line['date'].' ms</td>';


                foreach ($data['port'] as $port => $elems) {
                    if (!empty($ports[$port])) {
                        echo '<td width="5%" style="'.$style.'"><i style="font-size:16px" class="fa fa-'.$status['open'].'"></i></td>';
                    } else {
                        echo '<td width="5%" style="'.$style.'">&nbsp;</td>';
                    }
                }


                echo '<td style="'.$style.'">';
                if ($is_mysql) {
                    echo '<a href="'.LINK.'mysql/add/mysql_server:ip:'.$line['ip'].'/mysql_server:port:3306/" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus" style="font-size:12px"></span> '.__("Add").'</a>';
                    //echo '<a href="">'.__("Add").'</a>';
                }
                echo '</td>';

                echo '</tr>';
            }
            echo '</tbody>';
            ?>
        </table>
    </div>

</form>
<button type="button" class="btn btn-primary"><i style="font-size:14px" class="fa fa-share fa-flip-vertical"></i>
    <?= __("Add all these servers to monitoring") ?></button>

<?php
echo Form::select("none", "range", $data['select'], "", array("data-width" => "auto"));
?>
<button id="mask" href="<?= LINK ?>scan/refresh/" role="button" type="submit" class="btn btn-primary"><i style="font-size:14px" class="fa fa-refresh fa-spin"></i> <?= __("Refresh scan") ?></button>
