<?php


/*
//echo "<h3 class=\"item\">Signalétique</h3>";
echo "<form action=\"\" method=\"post\">";
echo "<table class=\"form\" width=\"100%\">";
echo "<tr>";
echo "<td class=\"first\">".__("Email")." :</td>";
echo "<td>".input("user_main","email")."</td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan=\"2\" class=\"td_bouton\"><br/><input class=\"button btBlueTest overlayW btMedium\" type=\"submit\" value=\"Valider\" /> <input class=\"button btBlueTest overlayW btMedium\" type=\"reset\" value=\"Effacer\" /></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
*/

use App\Library\Security\CsrfRender;
use Glial\Html\Form\Form;

$userLostPasswordCsrfField = CsrfRender::field($data, 'user_lost_password');
$userLostPasswordCsrfToken = CsrfRender::token($data, 'user_lost_password');

?>

<div class="container">
    <div class="row">
        <div class="span4 offset4" style="margin-top: 100px;">
            <!-- Session lost information -->
            <!-- Authentication failed -->
            <!-- Login form -->
            <div class="well" style="text-align: center;">
                <h3 style="margin-bottom: 3px;"><?=__("Forgot password ?") ?></h3>
                <form id="loginForm" name="loginForm" method="post" action="" class="form-horizontal">
                    <input type="hidden" name="loginForm" value="loginForm">
                    <input type="hidden" name="<?=$userLostPasswordCsrfField?>" value="<?=$userLostPasswordCsrfToken?>">

                    <table class="table" style="margin-top: 7px;">
                        <tbody>
                            <tr>
                                <td style="text-align:right;"><?=__("Email")?>                                </td>
                                <td><?=Form::input("user_main","email",array("class" => "form-control")) ?>
                                </td>
                            </tr>

                            <tr>
                                <td style="text-align:right;">
                                    <input id="login" type="submit" name="login" value="<?=__("Validate") ?>" class="btn btn-primary btn-large">
                                </td>
                                <td><input id="Reset" type="reset" name="Reset" value="<?=__("Delete") ?>" class="btn btn-large">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>

            </div>
        </div>
    </div><!-- End of row -->
</div>
