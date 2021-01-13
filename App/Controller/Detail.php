<?php
//https://nagix.github.io/chartjs-plugin-streaming/samples/interactions.html

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Display;
use App\Library\Debug;
use \Glial\Sgbd\Sgbd;

class Detail extends Controller
{

    static function format($bytes, $decimals = 2)
    {
        // && $bytes != 0
        if (empty($bytes)) {
            return "";
        }
        $sz = ' KMGTP';

        $factor = (int) floor(log($bytes) / log(1024));

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
    }

    public function index($param)
    {
        $this->di['js']->addJavascript(array("moment.js", "Chart.bundle.js", "hammer.min.js", "chartjs-plugin-zoom.js")); //, "hammer.min.js", "chartjs-plugin-zoom.js")
        $db = Sgbd::sql(DB_DEFAULT);


        $id_mysql_server = $param[0];
    }

    public function graph($param)
    {

        $db = Sgbd::sql(DB_DEFAULT);

        // in case of no id_mysql_server set, we relaod the page with the fist one
        if (empty($param[0]))
        {
            $sql ="SELECT min(id) as id_mysql_server FROM mysql_server";

            $res = $db->sql_query($sql);
            while($ob = $db->sql_fetch_object($res))
            {
                $link = LINK.$this->getClass().'/'.__FUNCTION__.'/'.$ob->id_mysql_server.'/';
                header('location: '.$link);
                exit;
            }

        }

        $id_mysql_server = $param[0];
        Debug::parseDebug($param);

        //$this->di['js']->addJavascript(array("moment.js", "Chart.bundle.js")); //, "hammer.min.js", "chartjs-plugin-zoom.js")
        $this->di['js']->addJavascript(array("moment.js", "chart.min.js", "chartjs-plugin-crosshair.js"));
        $slaves = Extraction::extract(array("status::com_select"), array(1), "1 hour", true, true);
        //$slaves2 = Extraction::extract(array("status::com_select"), array(1), "1 hour", true, true);
        //Debug::$debug = true;
        //$slave = array_merge($slaves1,$slaves2);
        $color  = array("orange" => "rgb(255, 159, 64)",
            "blue" => "rgb(54, 162, 235)",
            "red" => "rgb(255, 99, 132)",
            "yellow" => "rgb(255, 205, 86)",
            "green" => "rgb(75, 192, 192)",
            "purple" => "rgb(153, 102, 255)",
            "grey" => "rgb(201, 203, 207)"
        );

        $alpha      = 0.2;
        $background = array("orange" => "rgba(255, 159, 64, $alpha)",
            "blue" => "rgba(54, 162, 235, $alpha)",
            "red" => "rgba(255, 99, 132, $alpha)",
            "yellow" => "rgba(255, 205, 86, $alpha)",
            "green" => "rgba(75, 192, 192, $alpha)",
            "purple" => "rgba(153, 102, 255, $alpha)",
            "grey" => "rgba(201, 203, 207, $alpha)"
        );

        $graph   = array();
        $tooltip = "var agregat = []\n";
        $i       = 0;

        $data['legend'] = array();
        foreach ($slaves as $slave) {
            //Debug::debug($slave);


            $coul = next($color);
            $back = next($background);


            $slave['color'] = $coul;
            $data['legend'][] = $slave;
            // id_ts_variable

            $graph[] = '{
                label: "'.Display::ts_variable($slave['id_ts_variable']).'",
                data: ['.$slave['graph'].'],
                borderColor: "'.$coul.'",
                fill:true,
                pointBackgroundColor: "'.$back.'",
                borderWidth: 2,
                pointRadius: 0,
                lineTension: 0,
                backgroundColor: "'.$back.'",
                interpolate: true,
                showLine: true,
            }';

            //$tooltip .= 'agregat["'.$i.'"] = " -'."\t".'Min : '.self::format($slave['min']).' - Max : '.self::format($slave['max']).' - Avg : '
            //    .' '.self::format($slave['avg']).' -'."\t".'Std : '.round(sqrt($slave['std']), 2).'"'."\n";

            $tooltip .= 'agregat["'.$i.'"] = " -'."\t".'Min : '.round($slave['min'],0).' - Max : '.round($slave['max'],0).' - Avg : '
                .' '.round($slave['avg'],2).' -'."\t".'Std : '.round(sqrt($slave['std']), 2).'"'."\n";




            $i++;
        }

        //debug($tooltip);

        $y_access = '';
        if (false) {
            $y_access = ",yAxes: [{
                ticks:
                {
                    callback: function(value, index, values){
                        return FileConvertSize(value)
                    },
                }
            }]";
        }

        $zoom = '';
        if (false)
        {
            $zoom = '            zoom: {
              enabled: true,                                      // enable zooming
              zoomboxBackgroundColor: "rgba(66,133,244,1)",     // background color of zoom box
              zoomboxBorderColor: "#48F",                         // border color of zoom box
              zoomButtonText: "Reset Zoom",                       // reset zoom button text
              zoomButtonClass: "reset-zoom",                      // reset zoom button class
            },
            callbacks: {
              beforeZoom: function(start, end) {                  // called before zoom, return false to prevent zoom
                return true;
              },
              afterZoom: function(start, end) {                   // called after zoom
              }
            }';
        }

// //..' -  Max : '.self::format($slave['max']).' - Avg : '.self::format($slave['avg']).' - Std : '.$slave['std'].'"
        $this->di['js']->code_javascript('
"use strict";

function FileConvertSize(aSize){

    return Math.round((aSize + Number.EPSILON) * 100) / 100;
    aSize = Math.abs(parseInt(aSize, 10));
    var def = [[1, "octets"], [1024, "ko"], [1024*1024, "Mo"], [1024*1024*1024, "Go"], [1024*1024*1024*1024, "To"]];
    for(var i=0; i<def.length; i++){
            if(aSize<def[i][0]) return (aSize/def[i-1][0]).toFixed(2)+" "+def[i-1][1];
    }
}

'.$tooltip.'


$(".toggle").click(function() {

    var item_selected = $(this).text();
    var activate_all = false;

    if ($(this).hasClass("selected"))
    {
        activate_all = true;
        $(this).removeClass( "selected" );
        $(".toggle").closest("tr").removeClass("fadeout");
    }
    else
    {
        $(".toggle").removeClass( "selected").closest("tr").addClass("fadeout");
        $(this).addClass( "selected" ).closest("tr").removeClass("fadeout");
    }

    myChart.data.datasets.forEach(function(ds) {
        console.log(ds);

        if (activate_all)
        {
            ds.hidden = false;
        }
        else
        {
            ds.hidden = true;
            if (ds.label  == item_selected)
            {
                ds.hidden = false;
            }
        }

    });
  myChart.update();
});

var ctx = document.getElementById("myChart2").getContext("2d");

var myChart = new Chart(ctx, {
    type: "line",
    data: {
        datasets: ['.implode(",", $graph).']
    },

options:
    {

        animation: {
            duration: 0
        },
        hover: {
            animationDuration: 0
        },
        responsiveAnimationDuration: 0,
        responsive: true,

        plugins: {
          crosshair: {
            line: {
              color: "#aaa",        // crosshair line color
              width: 2,             // crosshair line width
              dashPattern: [1, 1]   // crosshair line dash pattern
            },
            sync: {
              enabled: true,            // enable trace line syncing with other charts
              group: 1,                 // chart group
              suppressTooltips: false   // suppress tooltips when showing a synced tracer
            },
            '.$zoom.'
          }
        },

        //end plugin
        bezierCurve: false,
        title: {
            display: true,
            text: "Top Command Counters",
            position: "top",
            padding: "0"
        },
        hover: {
            mode: "index",
            intersect: false
	    },
        tooltips: {
            enabled: true,
            mode: "interpolate",
            intersect: false,


            callbacks: {
                title: function(a, d) {
                    return a[0].xLabel.format("dddd D MMMM YYYY HH:mm:ss")
                },
                label: function(tooltipItem, data) {
                    var label = " "+data.datasets[tooltipItem.datasetIndex].label || "";
                    if (label) {
                        label += " : ";
                    }
                    label += FileConvertSize(tooltipItem.yLabel);
                    /* label += agregat[tooltipItem.datasetIndex]; */
                    return label;
                }
            }
        },
        pointDot: false,
        legend: {
            position: "top",

            labels: {
                generateLabels: function(chart) {
                  var data = chart.data;
                  return Chart.helpers.isArray(data.datasets) ? data.datasets.map(function(dataset, i) {
                    return {
                        text: dataset.label,
                        /* text: dataset.label + agregat[i], */
                        /* text: dataset.label + " (max : " + Chart.helpers.max(dataset.data).toLocaleString() + " max : " + Chart.helpers.max(dataset.data).toLocaleString() + ")", */
                        fillStyle: (!Chart.helpers.isArray(dataset.backgroundColor) ? dataset.backgroundColor : dataset.backgroundColor[0]),
                        hidden: !chart.isDatasetVisible(i),
                        lineCap: dataset.borderCapStyle,
                        lineDash: dataset.borderDash,
                        lineDashOffset: dataset.borderDashOffset,
                        lineJoin: dataset.borderJoinStyle,
                        lineWidth: dataset.borderWidth,
                        strokeStyle: dataset.borderColor,
                        pointStyle: dataset.pointStyle,

                        // Below is extra data used for toggling the datasets
                        datasetIndex: i
                    };
                  }, this) : [];
                },
            },


        },
        scales:
        {
            xAxes: [
                {
                    type: "time",
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: "Date",
                    },
                    distribution: "linear",
                    time: {
                        tooltipFormat: "dddd YYYY-MM-DD, HH:mm:ss",
                        displayFormats: {
                            minute: "HH:mm"
                        }
                    }
                }]
                '.$y_access.'
        },

    }
});

');
        $this->set('data', $data);


    }
}


/*
 * 
 * 
 * WITH t as (
SELECT 
  a.`id_mysql_server`, 
  a.`id_ts_variable`, 
  '' as connection_name, 
  a.`date`, 
   GREATEST(CAST(a.`value` as SIGNED) - CAST(LAG(a.`value`) OVER W AS SIGNED),0 )/( TIME_TO_SEC(TIMEDIFF(a.date,lag(a.date) OVER W))) as value,
   a.`value` as gg
FROM 
  `ts_value_general_int` a 
  INNER JOIN `ts_date_by_server` b on a.`date` = b.`date` AND a.`id_mysql_server` = b.`id_mysql_server` 
  INNER JOIN `ts_variable` c ON a.`id_ts_variable` = c.id AND b.`id_ts_file` = c.`id_ts_file` 
WHERE 
  id_ts_variable = 484 
  AND a.id_mysql_server IN (1) 
  AND a.`date` between "2020-10-25 01:55:59" and "2020-10-25 05:19:54"
  AND a.`date` <= now() 
  AND a.`date` <= now() 
  WINDOW W AS (    ORDER BY   a.date ))
select 
  id_mysql_server, 
  id_ts_variable, 
  max(`date`), 
  avg(`value`) é
from 
  t 
group by 
  UNIX_TIMESTAMP(`date`) DIV 10;
 */