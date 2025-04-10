<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div class="well">
    <div style="margin: 0 auto 0 auto; width:500px">
        <span style="color:#000; font-size: 48px">
            <i class="fa fa-database fa-lg"></i>
            <?= SITE_NAME ?>
        </span><br />
        <span class="badge badge-info" style="font-variant: small-caps; font-size: 20px; vertical-align: middle; background-color: #4384c7">
            <?= SITE_VERSION ?> (<?= SITE_LAST_UPDATE ?>)</span>
    </div>
</div>
<br />

<h3><?= __('Product') ?></h3>
<ul>
    <li><?= __('Product Version:') ?> <b><?= SITE_NAME ?> <?= SITE_VERSION ?></b> (Build <?= $data['build'] ?>)</li>
    <?php
    /*
    <li><?= __('Lisense:') ?> <b><a href="http://www.gnu.org/licenses/gpl-3.0.fr.html">GNU GPL v3</a></b></li>
    <li><?= __('Made in') ?> <b>🇫🇷 <?= __('FRANCE') ?></b></li>
    */
    ?>
</ul>

<h3><?= __('Dependencies') ?></h3>
<ul>

    <li>PHP: <b><?= $data['php'] ?></b></li>
    <li>MySQL / MariaDB / Percona Server <b><?= $data['mysql'] ?></b></li>
    <li>GraphViz: <b><?= $data['graphviz'] ?></b></li>
    <li>MySQL-sys: <b>v1.5.0</b> (<a href="https://github.com/Esysteme/mysql-sys">Esysteme/mysql-sys</a>
        <?= __('forked from') ?> <a href="https://github.com/mysql/mysql-sys">mysql/mysql-sys</a>)</li>
    <li><?= __('Kernel :') ?> <b><?= $data['kernel'] ?></b></li>
    <li>GNU/Linux: <b><?= $data['os'] ?></b></li>
    <li>Time zone: (session)<b> <?= $data['time_zone'] ?></b></li>
    <li>Time zone: (global)<b> <?= $data['global_time_zone'] ?></b></li>
    <li>System time zone :<b> <?= $data['system_time_zone'] ?></b></li>
    <li>now() :<b> <?= $data['now'] ?></b></li>
    <li>Date (PHP) :<b> <?= $data['now'] ?></b></li>
</ul>

<h3><?= __('Powered by') ?></h3>
<ul>
    <li><img src="<?= IMG ?>main/esysteme.jpg" height="32" width="32" /><b>68Koncept</b> (<a href="http://www.68koncept.com">www.68koncept.com</a>)</li>
    <li><?= __('Author :') ?> <b>Aurélien LEQUOY</b></li>
    <!--<li>Email : <b><a href="mailto:pmacontrol@esysteme.com">pmacontrol@esysteme.com</a></b></li>-->
</ul>

<h3><?= __('Credits') ?></h3>
<ul>
    <li>Stéphane VAROQUIE</li>
    <li>Mark LEITH</li>
    <li>Serge FREZEFOND</li>
    <li>Olivier DAISINI</li>
    <li>Dimitri KRAVTCHUK</li>
    <li>Philippe BEAUMONT</li>
</ul>
