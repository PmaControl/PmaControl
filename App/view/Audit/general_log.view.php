<div class="well">

    <form action="" method="get">

        <?php
        \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "auto")));
        ?>



        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>
