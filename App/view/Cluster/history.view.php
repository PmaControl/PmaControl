<?php
use \Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;
?>


<div >
<div style="float:left; padding-right:10px;"><?= FactoryController::addNode("MysqlServer", "menu", $data['param']); ?></div>

<!--enterprise -->
<?= FactoryController::addNode("Cluster", "replay", $data['param']); ?>
<!--enterprise -->
</div> 
<br >


    <div class="row">

        <!-- Control Panel -->
        <div class="col-md-2">


            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __("Controles") ?></h3>
                </div>

                <div style="padding:4px">
                <button class="btn btn-primary btn-block" onclick="first()">Début</button>
                <button class="btn btn-default btn-block" onclick="previous()">⟵ Précédent</button>
                <button class="btn btn-default btn-block" onclick="next()">Suivant ⟶</button>
                <button class="btn btn-danger btn-block" onclick="last()">Fin</button>
                <button class="btn btn-success btn-block" onclick="play()">Lecture</button>
                <button class="btn btn-warning btn-block" onclick="pause()">Pause</button>
                <select id="svgSelector" class="form-control" size="20"></select>
                </div>
            </div>
        </div>

        <!-- SVG Display Area -->
        <div class="col-md-10">
            <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">

                    <?= __("Cluster") ?>
                    <span id="svgDate">Date:</span></h3>
            </div>
            <div id="svg" class="mpd">

            
            <div id="svgContainer"></div>
        </div>


    </div>


<script>
    const data = <?php echo json_encode($data['svg']); ?>;
    let currentIndex = 0;
    let intervalId = null;

    function updateSVG() {
        if (data[currentIndex]) {
            document.getElementById('svgDate').textContent = "Date : " + data[currentIndex].date_inserted;
            document.getElementById('svgContainer').innerHTML = data[currentIndex].svg;
            document.getElementById('svgSelector').selectedIndex = currentIndex;
        }
    }

    function first() {
        currentIndex = 0;
        updateSVG();
    }

    function last() {
        currentIndex = data.length - 1;
        updateSVG();
    }

    function next() {
        if (currentIndex < data.length - 1) {
            currentIndex++;
            updateSVG();
        }
    }

    function previous() {
        if (currentIndex > 0) {
            currentIndex--;
            updateSVG();
        }
    }

    function play() {
        if (intervalId) return;
        intervalId = setInterval(() => {
            if (currentIndex < data.length - 1) {
                currentIndex++;
                updateSVG();
            } else {
                pause(); // stop at the end
            }
        }, 500);
    }

    function pause() {
        clearInterval(intervalId);
        intervalId = null;
    }

    // Remplir la liste déroulante
    const selector = document.getElementById('svgSelector');
    data.forEach((item, index) => {
        const opt = document.createElement('option');
        opt.text = item.date_inserted;
        opt.value = index;
        selector.appendChild(opt);
    });

    selector.addEventListener('change', function () {
        currentIndex = parseInt(this.value);
        updateSVG();
    });

    // Initial display
    updateSVG();
</script>

<?php
/*
    echo '<div style="float:left; border:#000 1px solid">';

    foreach($data['svg'] as $svg)
    {
        $svg['svg']
        echo $svg['date_inserted'] ;
    }
    echo '</div>';
    echo '<div style="clear:both"></div>';

    echo '</div>';
    echo '</div>';
*/