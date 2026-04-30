<?php

use App\Library\Security\CsrfRender;
use App\Library\Html;

use Glial\Html\Form\Form;

$sshSaveCsrfField = CsrfRender::field($data, 'ssh_save');
$sshSaveCsrfToken = CsrfRender::token($data, 'ssh_save');
$sshSaveCsrfInput = '<input type="hidden" name="'.$sshSaveCsrfField.'" value="'.$sshSaveCsrfToken.'">';
$sessionSshKey = is_array($_SESSION['ssh_key'] ?? null) ? $_SESSION['ssh_key'] : [];
$dataSshKey = is_array($data['ssh_key'] ?? null) ? $data['ssh_key'] : [];
$sshPublicKeyValue = Html::escape($dataSshKey['public_key'] ?? $sessionSshKey['public_key'] ?? '');
$sshPrivateKeyValue = Html::escape($dataSshKey['private_key'] ?? $sessionSshKey['private_key'] ?? '');
?>
<form action="" method="post">
    <?= $sshSaveCsrfInput ?>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Add a ssh key') ?></h3>
        </div>
        <div class="well">
            <div class="row">

                <div class="col-md-4">
                    <?= __("Name") ?> 
                    <?= Form::input("ssh_key", "name", array("class" => "form-control", "placeholder" => "Name of this key ssh (to remember)")) ?>
                    <?= Form::input("ssh_key", "id", array("type" => "hidden")) ?>
                </div>
                <div class="col-md-4"><?= __("User") ?>
                    <?= Form::input("ssh_key", "user", array("class" => "form-control", "placeholder" => "User who is linked with this publickey")) ?>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4"></div>
            </div>
            <div class="row">
                <div class="col-md-5">Public key
                    <textarea name="ssh_key[public_key]" id="ssh_key-key_pub" class="form-control" rows="15" placeholder="ssh-rsa AAAA... user@example.com"><?= $sshPublicKeyValue ?></textarea>
                </div>
                <div class="col-md-5">Private key
                    <textarea name="ssh_key[private_key]" id="ssh_key-key_priv" class="form-control" rows="15" placeholder="Paste the private key generated for this account"><?= $sshPrivateKeyValue ?></textarea>
                </div>
                <?php
                unset($_SESSION['ssh_key']['public_key']);
                unset($_SESSION['ssh_key']['private_key']);
                ?>
                <div class="col-md-2">
                    Generating key :<br /><br />
                    <a href="<?= LINK ?>ssh/generate/rsa/2048" type="button" class="btn btn-info link">RSA 2048</a>
                    <a href="<?= LINK ?>ssh/generate/rsa/4096" type="button" class="btn btn-primary link">RSA 4096</a>
                    <br /><br />
                    <a href="<?= LINK ?>ssh/generate/ecdsa/256" type="button" class="btn btn-info link">ECDSA 256</a>
                    <a href="<?= LINK ?>ssh/generate/dsa/1024" type="button" class="btn btn-info link">DSA 1024</a>
                    <br /><br />
                    <a href="<?= LINK ?>ssh/generate/ed25519/256" type="button" class="btn btn-info link">ED25519 256</a>
                </div>
            </div>
            <br />

            <?php
            if (!empty($_GET['ssh_key']['id'])) {
                echo '<button type="submit" class="btn btn-primary">Edit ssh key</button>';
            } else {
                echo '<button type="submit" class="btn btn-primary">Add ssh key</button>';
            }
            ?>
        </div>
    </div>
</form>
