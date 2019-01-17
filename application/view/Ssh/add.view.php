<?php

use Glial\Html\Form\Form;
?>
<form action="" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Add a ssh key') ?></h3>
        </div>
        <div class="well">
            <div class="row">

                <div class="col-md-4">
                    <?= __("Name") ?> 
                    <?= Form::input("ssh_key", "name", array("class" => "form-control", "placeholder" => "Name of this key ssh (to remember)")) ?>
                </div>
                <div class="col-md-4"><?= __("User") ?>
                    <?= Form::input("ssh_key", "user", array("class" => "form-control", "placeholder" => "User who is linked with this publickey")) ?>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4"></div>
            </div>
            <div class="row">
                <div class="col-md-5">Public key
                    <textarea name="public_key" id="key_pub" class="form-control" rows="15" placeholder="ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCwvGxb+mqTxWQKH95S6Fe6wMvIbvaxbPu4mTpjVX/eVBFdsJfyiuUZKWlwHC0ydU4AxR26Xj81D2zAMmp9JgzHn6ziGCzCIPCqWLA2a7woY/uCx6q2kHVqk6wgYpvLwpWSrCmbHIWzhb50rBzGY+XD7VvnDlqujqR4htyiYoAU2qrWfNEs5NseGEcQaiRMHe57lw2UTXGbj3Ked+h+n/XngRLV4D01DzaQZ8k45dREe32rUmJZJ3hvE3FI57ICEnVtnrQ8+lQrAoYP0jnYT7eXcIvjHDgyMXKc7fEAyp3b2QG+4J/HxL6K+elFJErLQ2yQlDR9afadnTsBJxFBA2/6yx42Lrp0pMprxKOvhSiMKNiDrP73Jt7d8Z5Z89YN+414Vo2M9713O54IB5H2r88qtdY4fuLzK4d4V39vz6ii5H2aEXIJVsbafLCn/qzbjp7IpoqvuB/3Smp2XW2RnWcZB1NY6diTQkS3MKpblDJILv5UtKN9RCyhRmRHFIM5RyTN21Euuei5bX6WhvEsL7jGo6JDmnXi3tzdAeTUbhPgOd2lX4LECBg9wbhzsezN47S6IGf+72sD/6BCJewKCZ8iheM34pEewDJdUSrg06LDLOr1TrRfaoV1qSsWNDtJVrfae/NTo4oKggxNkkDFkfeHm1pBej37dbMqzDVsKcNoCw== nicolas.martin@france.com"></textarea>
                </div>
                <div class="col-md-5">Private key
                    <textarea name="private_key" id="key_priv" class="form-control" rows="15" placeholder="-----BEGIN RSA PRIVATE KEY-----
MIIJKQIBAAKCAgEAsLxsW/pqk8VkCh/eUuhXusDLyG72sWz7uJk6Y1V/3lQRXbCX
8orlGSlpcBwtMnVOAMUdul4/NQ9swDJqfSYMx5+s4hgswiDwqliwNmu8KGP7gseq
tpB1apOsIGKby8KVkqwpmxyFs4W+dKwcxmPlw+1b5w5aro6keIbcomKAFNqq1nzR
ARBfL+AUEEZKjkK1o3vfzEhYL8nO+zpMzv2TMcbTumw+jjHC+DzKtUILBo/LjjkC
wyWKva6QArS125itvIMT5pUW6X72RgWByKIUzCJrR+HzWO9zl8FQQeRlZjtCp+9C
7HwMPiKH4upN2FfwWXSEa+NyYFUuNyjOCdbrRpgX0FfChE4XFklSNhMXdKMu
-----END RSA PRIVATE KEY-----"></textarea>
                </div>
                <div class="col-md-2">
                    Generating key :<br /><br />
                    <a href="<?= LINK ?>ssh/generate/rsa/2048" type="button" class="btn btn-info link">RSA 2048</a>
                    <a href="<?= LINK ?>ssh/generate/rsa/4096" type="button" class="btn btn-primary link">RSA 4096</a>
                    <br /><br />
                    <a href="<?= LINK ?>ssh/generate/ecdsa/256" type="button" class="btn btn-info link">ecdsa 256</a>
                    <a href="<?= LINK ?>ssh/generate/dsa/1024" type="button" class="btn btn-info link">DSA 1024</a>
                    <br /><br />
                    <a href="<?= LINK ?>ssh/generate/ed25519/256" type="button" class="btn btn-info link">ed25519 256</a>
                </div>
            </div>
            <br />
            <button type="submit" class="btn btn-primary">Add ssh key</button>
        </div>
    </div>
</form>