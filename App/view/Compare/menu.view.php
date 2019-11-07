<div class="well">
    <?php
    echo ' <div class="btn-group" role="group" aria-label="Default button group">';

    foreach ($data['menu'] as $key => $elem) {

        if ($_GET['menu'] == $key) {
            $color = "btn-info";
        } else {
            $color = "btn-primary";
        }

        $disable ='';
        if ($elem['count'] == 0) {
            $disable = 'disabled="disabled"';
        }

        echo '<a href="'.$elem['url'].'" type="button" class="btn '.$color.'" style="font-size:12px" '.$disable.'>'
        .' '.$elem['icone'].' '.__($elem['name']).' ('.$elem['count'].')</a>';
    }
    echo '</div>';
    ?>
</div>