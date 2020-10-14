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



        $slaves = Extraction::extract(array("status::com_select" ), array(130), "10 minute", true, true);
        //$slaves2 = Extraction::extract(array("status::com_select"), array(1), "1 hour", true, true);


        Debug::$debug = true;
      

        //$slave = array_merge($slaves1,$slaves2);

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
                label: "'.Display::srvjs($slave['id_mysql_server']).''.$slave['id_ts_variable'].'",
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

var ctx = document.getElementById("myChart2").getContext("2d");


var myChart = new Chart(ctx, {
    type: "line",
    data: {
        datasets: ['.$graph[0].',
            {
                label: "p226db1-c2m2p484222",
                data: [{ x: new Date("2020-10-09 04:42:03"), y: 2933},{ x: new Date("2020-10-09 04:42:13"), y: 3027},{ x: new Date("2020-10-09 04:42:23"), y: 3011},{ x: new Date("2020-10-09 04:42:33"), y: 3128},{ x: new Date("2020-10-09 04:42:43"), y: 3076},{ x: new Date("2020-10-09 04:42:54"), y: 3305},{ x: new Date("2020-10-09 04:43:03"), y: 2316},{ x: new Date("2020-10-09 04:43:13"), y: 1875},{ x: new Date("2020-10-09 04:43:23"), y: 2015},{ x: new Date("2020-10-09 04:43:33"), y: 1923},{ x: new Date("2020-10-09 04:43:43"), y: 1832},{ x: new Date("2020-10-09 04:43:54"), y: 1996},{ x: new Date("2020-10-09 04:44:03"), y: 1560},{ x: new Date("2020-10-09 04:44:14"), y: 1943},{ x: new Date("2020-10-09 04:44:24"), y: 1796},{ x: new Date("2020-10-09 04:44:34"), y: 1860},{ x: new Date("2020-10-09 04:44:44"), y: 1981},{ x: new Date("2020-10-09 04:44:54"), y: 1837},{ x: new Date("2020-10-09 04:45:05"), y: 1762},{ x: new Date("2020-10-09 04:45:15"), y: 2111},{ x: new Date("2020-10-09 04:45:24"), y: 1665},{ x: new Date("2020-10-09 04:45:34"), y: 1958},{ x: new Date("2020-10-09 04:45:44"), y: 1872},{ x: new Date("2020-10-09 04:45:54"), y: 1792},{ x: new Date("2020-10-09 04:46:05"), y: 1930},{ x: new Date("2020-10-09 04:46:14"), y: 1876},{ x: new Date("2020-10-09 04:46:24"), y: 1841},{ x: new Date("2020-10-09 04:46:34"), y: 2081},{ x: new Date("2020-10-09 04:46:44"), y: 1835},{ x: new Date("2020-10-09 04:46:54"), y: 1841},{ x: new Date("2020-10-09 04:47:05"), y: 2010},{ x: new Date("2020-10-09 04:47:15"), y: 1869},{ x: new Date("2020-10-09 04:47:25"), y: 1855},{ x: new Date("2020-10-09 04:47:35"), y: 1885},{ x: new Date("2020-10-09 04:47:45"), y: 2111},{ x: new Date("2020-10-09 04:47:56"), y: 2106},{ x: new Date("2020-10-09 04:48:05"), y: 1742},{ x: new Date("2020-10-09 04:48:15"), y: 1933},{ x: new Date("2020-10-09 04:48:25"), y: 1852},{ x: new Date("2020-10-09 04:48:35"), y: 1811},{ x: new Date("2020-10-09 04:48:46"), y: 2169},{ x: new Date("2020-10-09 04:48:55"), y: 1660},{ x: new Date("2020-10-09 04:49:06"), y: 1817},{ x: new Date("2020-10-09 04:49:15"), y: 1724},{ x: new Date("2020-10-09 04:49:25"), y: 1708},{ x: new Date("2020-10-09 04:49:35"), y: 1573},{ x: new Date("2020-10-09 04:49:46"), y: 1900},{ x: new Date("2020-10-09 04:49:56"), y: 1677},{ x: new Date("2020-10-09 04:50:06"), y: 1671},{ x: new Date("2020-10-09 04:50:17"), y: 2017},{ x: new Date("2020-10-09 04:50:27"), y: 1838},{ x: new Date("2020-10-09 04:50:36"), y: 1633},{ x: new Date("2020-10-09 04:50:46"), y: 1816},{ x: new Date("2020-10-09 04:50:56"), y: 1757},{ x: new Date("2020-10-09 04:51:06"), y: 1666},{ x: new Date("2020-10-09 04:51:16"), y: 1736},{ x: new Date("2020-10-09 04:51:26"), y: 1674},{ x: new Date("2020-10-09 04:51:36"), y: 1703}],
                borderColor: "rgb(255, 99, 132)",
                fill:true,
                pointBackgroundColor: "rgba(255, 99, 132, 0.1)",
                borderWidth: 2,
                pointRadius: 0,
                lineTension: 0,
                backgroundColor: "rgba(255, 99, 132, 0.1)",
                interpolate: true,
                showLine: true,
            }]
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
              enabled: true,            // enable trace line syncing with other charts
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