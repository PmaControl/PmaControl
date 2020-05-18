<?php

//https://nagix.github.io/chartjs-plugin-streaming/samples/interactions.html
namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Display;
use App\Library\Debug;

class PostMortem extends Controller
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

    public function item($param)
    {
        Debug::parseDebug($param);

        //$db = Sgbd::sql(DB_DEFAULT);

        $this->di['js']->addJavascript(array("moment.js", "chart.min.js", "chartjs-plugin-crosshair.js"));



        $slaves = Extraction::extract(array("status::memory_used"), array(9, 1), array("2019-02-19 05:40:00", "2020-02-20 18:00:00"), true, true);


        $color = array("orange" => "rgb(255, 159, 64)",
            "blue" => "rgb(54, 162, 235)",
            "red" => "rgb(255, 99, 132)",
            "yellow" => "rgb(255, 205, 86)",
            "green" => "rgb(75, 192, 192)",
            "purple" => "rgb(153, 102, 255)",
            "grey" => "rgb(201, 203, 207)"
        );

        
        $alpha = 0.1;
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
        foreach ($slaves as $slave) {
            Debug::debug($slave);

            $coul = next($color);
            $back = next($background);

            $label = 'server'.$slave['id_mysql_server'];

            $graph[] = '{
                label: "'.Display::srvjs($slave['id_mysql_server']).'",
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


            $tooltip .= 'agregat["'.$i.'"] = " -'."\t".'Min : '.self::format($slave['min']).' - Max : '.self::format($slave['max']).' - Avg : '
                .' '.self::format($slave['avg']).' -'."\t".'Std : '.round(sqrt($slave['std']), 2).'"'."\n";

            $i++;
        }




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