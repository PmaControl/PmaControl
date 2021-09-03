<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>


<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Accounts') ?></h3>
    </div>
    <table class="table table-condensed table-bordered table-striped" id="table">
        <tr>
            <th><?= __("Server") ?></th>
            <th><?= __("IP") ?></th>
            <th><?= __("Port") ?></th>
            <th><?= __("User") ?></th>
            <th><?= __("Host") ?></th>
            <th><?= __("Password") ?></th>
            <th><?= __("Is super") ?></th>
        </tr>
        <?php
        $account_without_password = 0;

        foreach ($data as $id => $servers) {
            foreach ($servers['account'] as $account) {

                $style = '';
                if (empty($account['password'])) {
                    $style = 'background-color:rgb(217, 83, 79,0.7); color:#000';
                    $account_without_password++;
                }

                echo '<tr>';
                echo '<td style="'.$style.'">'.$servers['display_name']."</td>";
                echo '<td style="'.$style.'">'.$servers['ip']."</td>";
                echo '<td style="'.$style.'">'.$servers['port']."</td>";
                echo '<td style="'.$style.'">'.$account['user']."</td>";
                echo '<td style="'.$style.'">'.$account['host']."</td>";
                echo '<td style="'.$style.'">'.$account['password']."</td>";
                echo '<td style="'.$style.'">'.$account['Super_priv']."</td>";
                echo '</tr>';
            }



            echo '<tr>';
            echo '<td colspan="7" style="border-bottom:1px solid #333"></td>';
            echo '</tr>';
        }
        ?>

    </table>
</div>

<?php
echo __('Number of account without password:').$account_without_password;
