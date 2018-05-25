<div class="well">
    <div class="btn-group" role="group" aria-label="Default button group">
        <?php
        $class = ("listStorage" === $data['menu']) ? 'btn-primary' : 'btn-default';
        echo '<a href="'.LINK.'StorageArea/listStorage" type="button" class="btn '.$class.'">'
        .'<span class="glyphicon glyphicon-list-alt" style="font-size:12px" aria-hidden="true"></span> '.__('Listing').'</a>';
        $class = ("add" === $data['menu']) ? 'btn-primary' : 'btn-default';
        echo '<a href="'.LINK.'StorageArea/add" type="button" class="btn '.$class.'">'
        .'<span class="glyphicon glyphicon-plus" style="font-size:12px" aria-hidden="true"></span> '.__('Add').'</a>';
        $class = ("edit" === $data['menu']) ? 'btn-primary' : 'btn-default';
        echo '<span type="button" class="btn '.$class.'">'
        .'<span class="glyphicon glyphicon-stats" style="font-size:12px" aria-hidden="true"></span> '.__('Edit').'</span>';
        ?>
    </div>
</div>
