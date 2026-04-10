<?php
if (!empty($data['graphs'])) {
    foreach ($data['graphs'] as $slave) {
        $canvasId = 'myChart'.$slave['id_mysql_server'].crc32($slave['day']);
        $varName  = 'myChart'.$slave['id_mysql_server'].crc32($slave['connection_name']);
        ?>
        <div>&nbsp;</div>
        <canvas style="width: 100%; height: 150px;" id="<?= $canvasId ?>" height="150" width="1600"></canvas>
        <script>
(function() {
    Chart.defaults.plugins.legend.display = false;

    var ctx = document.getElementById("<?= $canvasId ?>").getContext("2d");

    var <?= $varName ?> = new Chart(ctx, {
        type: "line",
        data: {
            datasets: [{
                label: "<?= __('Second behind source') ?>",
                data: [<?= $slave['graph'] ?>],
                borderWidth: 1,
                pointRadius: 1,
                tension: 0
            }]
        },
        options: {
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
                    ticks: {
                        beginAtZero: false
                    },
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
