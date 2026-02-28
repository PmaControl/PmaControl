<?php

echo '<div class="row">';
echo '<div class="col-md-12">';
echo '<div class="panel panel-default">';
echo '<div class="panel-heading"><h3 class="panel-title">Kafka-like log stream (24h) - server #'.$data['id_mysql_server'].'</h3></div>';
echo '<div class="panel-body">';
echo '<canvas id="logIngestion24h" style="width: 100%; height: 420px;"></canvas>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

$labels = json_encode($data['labels']);
$total = json_encode($data['total']);
$error = json_encode($data['error']);
$warning = json_encode($data['warning']);
$critical = json_encode($data['critical']);

$this->di['js']->code_javascript('
const ctxLogIngestion24h = document.getElementById("logIngestion24h").getContext("2d");

new Chart(ctxLogIngestion24h, {
    type: "line",
    data: {
        labels: '.$labels.',
        datasets: [
            {
                label: "Total events",
                data: '.$total.',
                borderColor: "#3e95cd",
                pointRadius: 1,
                fill: false,
            },
            {
                label: "Error",
                data: '.$error.',
                borderColor: "#e74c3c",
                pointRadius: 1,
                fill: false,
            },
            {
                label: "Warning",
                data: '.$warning.',
                borderColor: "#f39c12",
                pointRadius: 1,
                fill: false,
            },
            {
                label: "Critical",
                data: '.$critical.',
                borderColor: "#8e44ad",
                pointRadius: 1,
                fill: false,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            xAxes: [{
                display: true,
            }],
            yAxes: [{
                display: true,
                ticks: {
                    beginAtZero: true,
                    precision: 0,
                }
            }]
        }
    }
});
');
