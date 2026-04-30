<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\ChartPayload;
use App\Library\Debug;
use App\Library\Display;
use App\Library\Extraction;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for chartjs workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Chartjs extends Controller
{
/*
 *
 * lineBasic(int id_mysql_server, array ts_variable, mixed time )
 * Liste de paramètres
 * @param (int) id_mysql_server : id du serveur mysql
 * @param (array) ts_variable : list of metrics to chart example  array("status::com_select", "status::com_insert")
 * @param (mixed) date : if string interval according to : https://mariadb.com/kb/en/date-and-time-units/
 *                       if array should be equal to 2 with first date min and date max
 * @param (array) color : colors to use for graph
 * @param (array) options
 * 
 *
 */
    public function lineBasic($param)
    {


        $db = Sgbd::sql(DB_DEFAULT);

        // in case of no id_mysql_server set, we relaod the page with the fist one
        if (empty($param[0])) {
            $sql = "SELECT min(id) as id_mysql_server FROM mysql_server";
            $res = $db->sql_query($sql);
            while ($ob  = $db->sql_fetch_object($res)) {
                $link = LINK.$this->getClass().'/'.__FUNCTION__.'/'.$ob->id_mysql_server.'/';
                header('location: '.$link);
                exit;
            }
        }

        $id_mysql_server = $param[0];
        $ts_variable = $param[1];
        $date = $param[2];
        $chart_name = "chart".uniqid();


        Debug::parseDebug($param);

        //$this->di['js']->addJavascript(array("moment.js", "Chart.bundle.js")); //, "hammer.min.js", "chartjs-plugin-zoom.js")
        $this->di['js']->addJavascript(array("moment.js", "chart.min.js", "chartjs-plugin-crosshair.js", "formatters.js"));
        $slaves = Extraction::extract(array("status::com_select", "status::com_insert", "status::com_update", "status::com_delete"), array($id_mysql_server), $date, true, true);


        $legacyChart = ChartPayload::legacyExtraction(
            $slaves,
            static function (array $slave): string {
                return Display::ts_variable($slave['id_ts_variable']);
            },
            ['alpha' => 0.2]
        );
        $graph          = $legacyChart['datasets_js'];
        $tooltip        = $legacyChart['tooltip_js'];
        $data['legend'] = $legacyChart['legend'];

        //debug($tooltip);

        $y_access = '';
        if (false) {
            $y_access = ",yAxes: [{
                ticks:
                {
                    callback: function(value, index, values){
                        return PmaFormat.number(value)
                    },
                }
            }]";
        }

        $zoom = '';
        if (false) {
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

    '.$chart_name.'.data.datasets.forEach(function(ds) {
        
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
  '.$chart_name.'.update();
});

var ctx = document.getElementById("'.$chart_name.'").getContext("2d");

var '.$chart_name.' = new Chart(ctx, {
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
                    label += PmaFormat.number(tooltipItem.yLabel);
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
