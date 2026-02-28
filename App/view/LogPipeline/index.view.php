<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Pipeline logs (24h)</h3>
    </div>
    <div class="panel-body">
        <p>Visualisation type Kafka des logs collectés par SSH, puis agrégés par listener.</p>

        <div class="form-inline" style="margin-bottom: 15px;">
            <label for="server-select">Serveur :</label>
            <select id="server-select" class="form-control" style="margin-left: 8px;">
                <?php foreach (($data['servers'] ?? []) as $server): ?>
                    <option value="<?= (int) $server['id'] ?>"><?= htmlspecialchars($server['display_name'].' ('.$server['ip'].')', ENT_QUOTES) ?></option>
                <?php endforeach; ?>
            </select>
            <button id="reload-chart" class="btn btn-primary" style="margin-left: 8px;">Recharger</button>
        </div>

        <canvas id="log-pipeline-chart" height="120"></canvas>
    </div>
</div>

<script src="<?= WWW_ROOT ?>js/Chart.bundle.js"></script>
<script>
(function() {
    var chart;
    var ctx = document.getElementById('log-pipeline-chart').getContext('2d');
    var select = document.getElementById('server-select');
    var btn = document.getElementById('reload-chart');

    function draw(data) {
        if (chart) {
            chart.destroy();
        }

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels || [],
                datasets: data.datasets || []
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: true },
                scales: {
                    xAxes: [{ display: true }],
                    yAxes: [{ display: true, ticks: { beginAtZero: true } }]
                }
            }
        });
    }

    function load() {
        var id = select.value;
        fetch('<?= LINK ?>LogPipeline/api24h/' + id)
            .then(function(resp) { return resp.json(); })
            .then(function(json) { draw(json); })
            .catch(function() { draw({labels: [], datasets: []}); });
    }

    btn.addEventListener('click', load);
    if (select.value) {
        load();
    }
})();
</script>
