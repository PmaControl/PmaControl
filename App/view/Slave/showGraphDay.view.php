<?php
if (!empty($data['graphs'])) {
    foreach ($data['graphs'] as $slave) {
        $canvasId = 'myChart'.$slave['id_mysql_server'].crc32(($slave['connection_name'] ?? '').$slave['day']);
        ?>
        <div class="sv-chart-wrap"><canvas id="<?= $canvasId ?>"></canvas></div>
        <script>
(function() {
    Chart.defaults.plugins.legend.display = false;

    var canvas = document.getElementById("<?= $canvasId ?>");
    if (!canvas) return;
    var existing = Chart.getChart(canvas);
    if (existing) existing.destroy();
    var ctx = canvas.getContext("2d");

    var chart = new Chart(ctx, {
        type: "line",
        data: {
            datasets: [{
                label: "<?= __('Second behind source') ?>",
                data: [<?= $slave['graph'] ?>],
                borderColor: "#16285a",
                backgroundColor: "rgba(22,40,90,0.3)",
                fill: true,
                borderWidth: 1,
                pointRadius: 1,
                tension: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: "Replication : <?= $slave['day'] ?>",
                    position: "top",
                    padding: 0
                }
            },
            scales: {
                x: {
                    type: "time",
                    display: true,
                    title: {
                        display: true,
                        text: "Date"
                    },
                    time: {
                        tooltipFormat: "dddd YYYY-MM-DD, HH:mm:ss",
                        displayFormats: {
                            minute: "HH:mm"
                        }
                    },
                    min: new Date("<?= $slave['day'] ?> 00:00:00"),
                    max: new Date("<?= $slave['day'] ?> 23:59:59")
                },
                y: {
                    min: 0,
                    title: {
                        display: true,
                        text: "Second behind source"
                    }
                }
            }
        }
    });
})();
        </script>
        <?php
    }
}
