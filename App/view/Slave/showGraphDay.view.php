<?php
// AJAX response for "Load previous day" on /slave/show. Renders one
// canvas per slave (one per day, but the action runs over a single
// day) and lets the shared `Slave::buildLagChartJs()` helper emit
// the Chart.js construction code — same dual-axis treatment as the
// initial-load chart so the relay_log_space right-side GB axis and
// the per-segment colouring stay in lockstep with /slave/show.
if (!empty($data['graphs'])) {
    foreach ($data['graphs'] as $slave) {
        $canvasId = 'myChart' . $slave['id_mysql_server'] . crc32(($slave['connection_name'] ?? '') . $slave['day']);
        ?>
        <div class="sv-chart-wrap"><canvas id="<?= $canvasId ?>"></canvas></div>
        <script>
<?= App\Controller\Slave::buildLagChartJs($slave) ?>
        </script>
        <?php
    }
}
