<?php

use App\Library\Display;

?>

<div class="row" style="border:#ccc 1px solid; margin-left:0; margin-right:0">
    <div class="col-md-9">
        <div style="width:100%; height-max:500px"><canvas style="width:100%; height-min: 200px; height-max: 500px;" id="myChart2"></canvas></div>
    </div>

    <div class="col-md-3" style="padding-left:0">
        <div class="graph-legend">
            <div class="custom-scrollbars" style="position: relative; overflow: hidden; width: 100%; height: auto; min-height: 100%; max-height: 100%;">
                <div style="position: relative; overflow: scroll; margin-right: -12px; margin-bottom: -12px; min-height: calc(100% + 12px); max-height: calc(100% + 12px);" class="view">
                    <div class="graph-legend-content graph-legend-table ">
                        <table class="graph-legend">
                            <colgroup>
                                <col style="width: 100%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th style="text-align: left;"></th>
                                    <th class="pointer">min</th>
                                    <th class="pointer">max</th>
                                    <th class="pointer">avg</th>
                                    <th class="pointer">std</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                $i =1;
                                foreach ($data['legend'] as $legend) {
                                    $i++;
                                    if ($i % 2 === 0)
                                    {
                                        $under = 'graph-underline';
                                    }else{
                                        $under = '';
                                    }
                                    echo '<tr class="graph-legend-series '.$under.'">';

                                    echo '<td style="text-align: left;">
    <i class="fa fa-minus pointer" style="color: '.$legend['color'].';"></i>
    <a class="graph-legend-alias pointer toggle" title="read_rnd_next">'.Display::ts_variable($legend['id_ts_variable']).'</a>
    </td>';

                                    echo '<td class="graph-legend-value min">'.$legend['min'].'</td>
    <td class="graph-legend-value max">'.$legend['max'].'</td>
    <td class="graph-legend-value avg">'.$legend['avg'].'</td>
    <td class="graph-legend-value avg">'.$legend['std'].'</td>
    ';
                                    echo '<tr>';
                                }
                                ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>