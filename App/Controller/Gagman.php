<?php

use \Glial\Synapse\Controller;
use App\Library\Extraction;

class Gagman extends Controller {

    public function index($param) {


        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));
        $this->di['js']->addJavascript(array("moment.js", "Chart.min.js"));


        $db = $this->di['db']->sql(DB_DEFAULT);



        Extraction::setDb($db);



        $res = Extraction::extract(array("status::com_insert"), array(), $_GET['ts_variable']['date']);








        if (!empty($data['graph'])) {

            foreach ($data['graph'] as $value) {

                if (empty($old_date) && $_GET['ts_variable']['derivate'] == "1") {

                    $old_date = $value['date'];
                    $old_value = $value['value'];
                    continue;
                } elseif ($_GET['ts_variable']['derivate'] == "1") {

                    $datetime1 = strtotime($old_date);
                    $datetime2 = strtotime($value['date']);

                    $secs = $datetime2 - $datetime1; // == <seconds between the two times>
//echo $datetime1. ' '.$datetime2 . ' : '. $secs." ".$value['value'] ." - ". $old_value." => ".($value['value']- $old_value)/ $secs."<br>";

                    $derivate = round(($value['value'] - $old_value) / $secs, 2);

                    if ($derivate < 0) {
                        $derivate = 0;
                    }

                    $val = $derivate;

//$points[] = "{ x: " . $datetime2 . ", y :" . $derivate . "}";
                } else {
                    $val = $value['value'];
                }



                $point[] = "{ x: new Date('" . $value['date'] . "'), y: " . $val . "}";

                $dates[] = $value['date'];

                $old_date = $value['date'];
                $old_value = $value['value'];
            }
        }



        $this->di['js']->code_javascript('
var ctx = document.getElementById("myChart").getContext("2d");


var myChart = new Chart(ctx, {
    type: "line",
    data: {
        datasets: [{
            label: "' . $name . '",
            data: [' . $points . '],
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

                    max: new Date("' . date('Y-m-d H:i:s') . '"),
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



');
    }

}
