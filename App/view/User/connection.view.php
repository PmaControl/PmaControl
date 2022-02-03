<?php
// <input autocomplete="off" class="textfield" type="text" name="usernameInput" id="usernameInput" size="26" onkeypress="eturn checkEnter(event);">
use Glial\Html\Form\Form;
?>

<div class="container">
    <div class="row">
        <div class="span2 offset2" style="margin-top: 100px;">
            <!-- Session lost information -->
            <!-- Authentication failed -->
            <!-- Login form -->
            <div class="well" style="text-align: center;">
                <h3 style="margin-bottom: 20px;"><?= __("Bienvenue, veuillez vous identifier", "fr") ?></h3>
                <form id="loginForm" name="loginForm" method="post" action="">
                    <input type="hidden" name="loginForm" value="loginForm">

                    <div class="row">
                        <div class="col-md-6"><?= __("Login") ?></div>
                        <div class="col-md-6"><?= Form::input("user_main", "login", array("class" => "form-control", "autocomplete" => "false", "autocomplete" => "off")) ?></div>

                    </div>


                    <div class="row">
                        <div class="col-md-6"><?= __("Password") ?></div>
                        <div class="col-md-6"><?=
                            Form::input("user_main", "password",
                                array("type" => "password", "class" => "form-control", "autocomplete" => "false", "autocomplete" => "off", "autocomplete" => "new-password"))
                            ?>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12">&nbsp;</div>
                    </div>

                    
                    <div class="row">
                        <div class="col-md-12">
                            <input id="login" type="submit" name="login" value="Connexion" class="btn btn-primary btn-large">
                            <input id="Reset" type="reset" name="Reset" value="Réinitialiser" class="btn btn-large btn-default">


                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12">&nbsp;</div>
                    </div>
                </form>

                <p>
                    <a href="https://github.com/PmaControl/PmaControl/issues" target="_BLANK">
                        <i class="icon-envelope" style="margin-right: 5px;"></i><?= __("Contact the creator of the application / Report a bug") ?>
                    </a>
                </p>
            </div>

            <?php
            if (!LDAP_CHECK) {
                ?>
                <p>
                    <a href="<?= LINK ?>user/register/"><?= __("Sign up, it's free !") ?></a>
                    (<a href="<?= LINK ?>user/lost_password/"><?= __("password forgotten") ?></a>)
                </p>
                <?php
                /**/
            }
            ?>


        </div>
    </div>
</div><!-- End of row -->
</div>