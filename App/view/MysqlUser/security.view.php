<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<p><a class="btn btn-primary showdiff" role="button"><?= __('Show only security issue') ?></a></p>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Accounts') ?></h3>
    </div>
    <table class="table table-condensed table-bordered table-striped" id="table">
        <tr>
            <th><?= __("Top") ?></th>
            <th><?= __("Server") ?></th>
            <th><?= __("IP") ?></th>
            <th><?= __("Port") ?></th>
            <th><?= __("User") ?></th>
            <th><?= __("Host") ?></th>
            <th><?= __("Password") ?></th>
            <th><?= __("Plugin") ?></th>
            <th><?= __("Is super") ?></th>
        </tr>
        <?php
        $account_without_password = 0;
        $account_with_unsecure_plugin = 0;

        $i = 0;
        foreach ($data as $id => $servers) {
            foreach ($servers['account'] as $account) {

                $i++;
                $style = '';
                $hide = ' class="to_hide"';

                if (empty($account['Password']) && $account['Plugin'] !== "unix_socket") {
                    $style = 'background-color:rgb(217, 83, 79,0.7); color:#000';
                    $account_without_password++;
                    $hide = '';
                }

                if ($account['Plugin'] === "mysql_old_password")
                {
                    $style = 'background-color:rgb(0,0,0,0.7); color:#fff';
                    $account_with_unsecure_plugin++;
                    $hide = '';
                }

                if ((empty($account['Password'])  && $account['User'] === "mariadb.sys" && $account['Host'] === "localhost" ))
                {
                    $style = '';
                    $account_without_password--;
                    $hide = ' class="to_hide"';
                }

                /* to debug 
                if ($account['User']  === "pmacontrol")
                {
                    $style = 'background-color:rgb(0,0,0,0.7); color:#fff';
                    $hide = '';
                }
                /**** */

                echo '<tr'.$hide.'>';
                echo '<td style="'.$style.'">'.$i."</td>";
                echo '<td style="'.$style.'">'.$servers['display_name']."</td>";
                echo '<td style="'.$style.'">'.$servers['ip']."</td>";
                echo '<td style="'.$style.'">'.$servers['port']."</td>";
                echo '<td style="'.$style.'">'.$account['User']."</td>";
                echo '<td style="'.$style.'">'.$account['Host']."</td>";
                echo '<td style="'.$style.'">'.$account['Password']."</td>";
                echo '<td style="'.$style.'">'.$account['Plugin']."</td>";
                echo '<td style="'.$style.'">'.$account['Super_priv']."</td>";
                echo '</tr>';
            }


        }
        ?>

    </table>
</div>

<?php
echo __('Number of account without password:').$account_without_password . "<br>";
echo __('Number of account with unsecure plugin:').$account_with_unsecure_plugin;
