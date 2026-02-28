<?php

$chart_id = 'log-ingestion-24h-'.uniqid();

$labels = array();
$levels = array('DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY');
$matrix = array();

foreach ($levels as $level) {
    $matrix[$level] = array();
}

foreach ($data['series'] as $row) {
    $labels[$row['bucket_hour']] = $row['bucket_hour'];
}

$labels = array_values($labels);

foreach ($levels as $level) {
    foreach ($labels as $label) {
        $matrix[$level][$label] = 0;
    }
}

foreach ($data['series'] as $row) {
    $matrix[$row['level']][$row['bucket_hour']] = (int) $row['total'];
}

$datasets = array();
$colors = array(
    'DEBUG' => '#9E9E9E',
    'INFO' => '#2196F3',
    'NOTICE' => '#00BCD4',
    'WARNING' => '#FFC107',
    'ERROR' => '#F44336',
    'CRITICAL' => '#E91E63',
    'ALERT' => '#9C27B0',
    'EMERGENCY' => '#000000',
);

foreach ($levels as $level) {
    $datasets[] = array(
        'label' => $level,
        'data' => array_values($matrix[$level]),
        'borderColor' => $colors[$level],
        'backgroundColor' => $colors[$level],
        'fill' => false,
        'pointRadius' => 2,
        'tension' => 0,
    );
}

echo '<div class="panel panel-primary">';
echo '<div class="panel-heading"><h3 class="panel-title">'.__('Automatic log ingestion (last 24h)').' #'.$data['id_mysql_server'].'</h3></div>';
echo '<div class="panel-body">';
echo '<canvas id="'.$chart_id.'" height="90"></canvas>';
echo '</div>';
echo '</div>';

?>
<script>
(function () {
    var ctx = document.getElementById(<?php echo json_encode($chart_id); ?>).getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: <?php echo json_encode($datasets); ?>
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                title: {
                    display: true,
                    text: '24h log flow (Kafka-like timeline)'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'events'
                    }
                }
            }
        }
    });
})();
</script>
