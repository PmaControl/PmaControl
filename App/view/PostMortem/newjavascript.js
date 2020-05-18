var myChart = new Chart(ctx, {
    type: "line",
    data: {
        datasets: [{
                label: "' . $name . '",
                data: [' . $points . '],
                borderWidth: 1,
                pointRadius: 0,
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
        pointDot: false,
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