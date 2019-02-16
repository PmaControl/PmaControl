<?php

use Glial\Html\Form\Form;

function getBadge($status)
{

    switch ($status) {
        case "ERROR":
            $color = "danger";
            break;

        case "INTERRUPTED":
            $color = "default";
            break;

        case "SUCCESS":
            $color = "success";
            break;

        case "WARNING":
            $color = "warning";
            break;


        case "RUNNING":
            $color = "primary";
            break;


        default:
            break;
    }

    return $color;
}
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Listing') ?></h3>
    </div>


    <table class="table table-bordered table-striped" id="table">
        <tr>
            <th><?= __('Top') ?></th>
            <th><?= __('class') ?></th>
            <th><?= __('method') ?></th>
            <th><?= __('param') ?></th>
            <th><?= __('date start') ?></th>
            <th><?= __('date end') ?></th>
            <th><?= __('pid') ?></th>
            <th><?= __('status') ?></th>
            <th><?= __('log') ?></th>            
        </tr>

        <?php
        foreach ($data['jobs'] as $job):
            $i++;
            ?>

            <tr>
                <td><?= $i ?></td>
                <td><?= $job['class'] ?></td>
                <td><?= $job['method'] ?></td>
                <td><pre><?php print_r(json_decode($job['param'], true)); ?></pre></td>
                <td><?= $job['date_start'] ?></td>
                <td><?= $job['date_end'] ?></td>
                <td><?= $job['pid'] ?></td>
                <td><big><span class="label label-<?= getBadge($job['status']) ?>"><?= $job['status'] ?></span></big></td>
                <td><?= $job['log_msg'] ?></td>
            </tr>

            <?php
        endforeach;
        ?>

    </table>