<?php

//https://nagix.github.io/chartjs-plugin-streaming/samples/interactions.html
namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\ChartPayload;
use App\Library\Extraction;
use App\Library\Display;
use App\Library\Debug;
use App\Library\Format;
use App\Library\PostMortem\PostMortemReport;

/**
 * Class responsible for post mortem workflows.
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
class PostMortem extends Controller
{

    public function index($param)
    {
        Debug::parseDebug($param);

        $serverId = PostMortemReport::normalizeServerId((array)$param);
        $payload = PostMortemReport::buildPayload($serverId, $_GET);

        $this->title = $serverId > 0 ? 'Post-mortem server '.$serverId : 'Post-mortem';
        $this->ariane = ' > Tools > Post-mortem';
        $this->set('data', ['payload' => $payload]);
    }

/**
 * Handle post mortem state through `format`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $bytes Input value for `bytes`.
 * @phpstan-param mixed $bytes
 * @psalm-param mixed $bytes
 * @param mixed $decimals Input value for `decimals`.
 * @phpstan-param mixed $decimals
 * @psalm-param mixed $decimals
 * @return mixed Returned value for format.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::format()
 * @example /fr/postmortem/format
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function format($bytes, $decimals = 2)
    {
        // && $bytes != 0
        if (empty($bytes)) {
            return "";
        }

        return Format::bytes($bytes, $decimals);
    }

/**
 * Handle `item`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for item.
 * @phpstan-return void
 * @psalm-return void
 * @example item(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function item($param)
    {
        Debug::parseDebug($param);

        //$db = Sgbd::sql(DB_DEFAULT);

        $this->di['js']->addJavascript(array("moment.js", "chart.min.js", "chartjs-plugin-crosshair.js"));



        $slaves = Extraction::extract(array("status::memory_used"), array(1), "10 minutes", true, true);


        $legacyChart = ChartPayload::legacyExtraction(
            $slaves,
            static function (array $slave): string {
                return Display::srvjs($slave['id_mysql_server']);
            },
            [
                'alpha' => 0.1,
                'aggregate_formatter' => [self::class, 'format'],
            ]
        );
        $graph   = $legacyChart['datasets_js'];
        $tooltip = $legacyChart['tooltip_js'];




// //..' -  Max : '.self::format($slave['max']).' - Avg : '.self::format($slave['avg']).' - Std : '.$slave['std'].'"
        $this->di['js']->code_javascript('
"use strict";

function FileConvertSize(aSize){
    aSize = Math.abs(parseInt(aSize, 10));
    var def = [[1, "octets"], [1024, "ko"], [1024*1024, "Mo"], [1024*1024*1024, "Go"], [1024*1024*1024*1024, "To"]];
    for(var i=0; i<def.length; i++){
            if(aSize<def[i][0]) return (aSize/def[i-1][0]).toFixed(2)+" "+def[i-1][1];
    }
}


'.$tooltip.'
var ctx = document.getElementById("myChart2").getContext("2d");


var myChart = new Chart(ctx, {
    type: "line",
    data: {
        datasets: ['.implode(",", $graph).']
    },

options:
    {

        plugins: {
          crosshair: {
            line: {
              color: "#aaa",        // crosshair line color
              width: 2,             // crosshair line width
              dashPattern: [1, 1]   // crosshair line dash pattern
            },
            sync: {
              enabled: false,            // enable trace line syncing with other charts
              group: 1,                 // chart group
              suppressTooltips: false   // suppress tooltips when showing a synced tracer
            },
            zoom: {
              enabled: true,                                      // enable zooming
              zoomboxBackgroundColor: "rgba(66,133,244,0.2)",     // background color of zoom box
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
            }
          }
        },



        //end plugin
        bezierCurve: false,
        title: {
            display: true,
            text: "Memory used",
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


            callbacks: {
                label: function(tooltipItem, data) {
                    var label = " "+data.datasets[tooltipItem.datasetIndex].label || "";
                    if (label) {
                        label += " : ";
                    }
                    label += FileConvertSize(tooltipItem.yLabel);
                    label += agregat[tooltipItem.datasetIndex];



                    return label;
                }
            }
        },
        pointDot: false,
        scales:
        {
            xAxes: [{
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
                }],
            yAxes: [{

                    ticks:
                    {

                        callback: function(value, index, values){

                            return FileConvertSize(value)
                        },
                    }

                }]
        }
    }
});
');









        /*
          $this->di['js']->code_javascript('
          var ctx = document.getElementById("myChart").getContext("2d");


          var myChart = new Chart(ctx, {
          type: "line",
          data: {
          datasets: [{
          label: "'.$name.'",
          data: ['.$points.'],
          borderWidth: 1,
          pointRadius :0,
          lineTension: 0

          },
          ]
          },
          options: {
          bezierCurve: false,
          title: {
          display: true,
          text: " ",
          position: "top",
          padding: "0"
          },
          pointDot : false,
          scales: {
          xAxes: [{

          type: "time",
          display: true,
          scaleLabel: {
          display: true,
          labelString: "Date",
          },
          distribution: "linear",
          time: {

          max: new Date("'.date('Y-m-d H:i:s').'"),
          tooltipFormat: "dddd YYYY-MM-DD, HH:mm:ss",
          displayFormats: {
          minute: "dddd YYYY-MM-DD, HH:mm"
          }

          }

          }],
          yAxes: [{


          scaleLabel: {
          display: true,
          labelString: "Queries by second",

          }

          }]
          }
          }
          });



          '); */
    }
}
