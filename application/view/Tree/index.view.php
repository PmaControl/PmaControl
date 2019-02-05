<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;
?>

<form action="" method="post">

    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title"><?= __('Menu Settings') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-4">

                    Select menu <a href="#" data-toggle="popover" title="<?= __("Select menu") ?>" data-content="<ul><li><?= __("The menu can be different for each group of user") ?></li><li><?= __("There is one menu for loged user and one other everybody") ?></li></ul>">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </a>
                    <?= Form::select("menu", "id", $data['liste_menu'], "10", array("class" => "form-control")) ?>

                </div>
                <div class="col-md-4">
                    <br />
                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok" style="font-size:12px"></span> <?= __('Save') ?></button>

                </div>
                <div class="col-md-4">

                </div>
            </div>
            <div class="row">&nbsp;</div>
        </div>
    </div>

</form>

<form action="" method="post">

    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title"><?= __('Main Menu') ?></h3>
        </div>

        <table class="table table-condensed table-bordered table-striped">


            <tr>
                <th><?= __('id') ?></th>
                <th><?= __('id_parent') ?></th>
                <th><?= __('bg') ?></th>
                <th><?= __('bd') ?></th>
                <th><?= __('icon') ?></th>
                <th><?= __('title') ?></th>
                <th><?= __('url') ?></th>
                <th><?= __('actions') ?></th>
                <th><?= __('active') ?></th>

            </tr>
            <?php
            $total = 0;

            $elems = count($data['menu']) * 2;

            foreach ($data['menu'] as $line):
                ?>



                <tr>
                    <td><?= $line['id'] ?>



                    </td>
                    <td><?= $line['parent_id'] ?></td>
                    <td><?= $line['bg'] ?></td>
                    <td><?= $line['bd'] ?></td>
                    <td><?php \Glial\Synapse\FactoryController::addNode("tree", "getCountFather", array($data['id_menu'], $line['id'])); ?><?= $line['icon'] ?></td>
                    <td class="line-edit" data-name="title" data-pk="<?= $line['id'] ?>" data-type="text" data-url="<?= LINK ?>tree/update" data-title="Enter URL"><?= $line['title'] ?></td>
                    <td class="line-edit" data-name="url" data-pk="<?= $line['id'] ?>" data-type="text" data-url="<?= LINK ?>tree/update" data-title="Enter URL"> <?= $line['url'] ?></td>
                    <td>

                        <a href="<?= LINK ?>tree/add/<?= $data['id_menu'] ?>/<?= $line['id'] ?>" role="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></a>
                        <a role="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span></a>

                        <a href="<?= LINK ?>tree/left/<?= $data['id_menu'] ?>/<?= $line['id'] ?>" role="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-triangle-left" aria-hidden="true"></span></a>
                        <a href="<?= LINK ?>tree/down/<?= $data['id_menu'] ?>/<?= $line['id'] ?>" role="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></a>
                        <a href="<?= LINK ?>tree/up/<?= $data['id_menu'] ?>/<?= $line['id'] ?>" role="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-triangle-top" aria-hidden="true"></span></a>
                        - <a href="<?= LINK ?>tree/delete/<?= $data['id_menu'] ?>/<?= $line['id'] ?>" role="button" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>
                    </td>
                    <td class="line-edit" data-name="active" data-pk="<?= $line['id'] ?>" data-type="text" data-url="<?= LINK ?>tree/update" data-title="Enter URL"><?= $line['active'] ?></td>

                </tr>


                <?php
                $total += $line['bg'] + $line['bd'];
            endforeach;
            ?>
        </table>

    </div>

<!--<i class="fa fa-list-alt" aria-hidden="true"></i>
    -->
    <!--<button href="<?= LINK ?>Tree/add/1/NULL" role="button" class="btn btn-success"><span class="glyphicon glyphicon-triangle-plus" aria-hidden="true"></span> Update menu</button>-->
    <a href="<?= LINK ?>Tree/add/1/NULL" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-triangle-plus" aria-hidden="true"></span> Add root node</a>

</form>
<?php
echo "total : ".$total."<br />";

$total2 = ($elems * ($elems + 1)) / 2;

echo "total2 : ".$total2."\n";
