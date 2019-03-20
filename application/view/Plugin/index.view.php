 
<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

echo '<div class="well center">';

echo '<form class="form-inline" action="" method="post">';
echo ' <div class="form-group" role="group" aria-label="Default button group">';
echo '<nav aria-label="Page navigation">';
echo '<ul class="pagination">';
echo ' <li><button type="submit" class="btn btn-primary"> <i class="fa fa-list-ol" style="font-size:18px" aria-hidden="true"></i> ' . __("All") . '</button></li>';
echo ' <li><button type="submit" class="btn btn-primary"> <i class="fa fa-fire" style="font-size:18px" aria-hidden="true"></i> ' . __("Installed") . '</button></li>';
echo ' <li><button type="submit" class="btn btn-primary"> <i class="fa fa-wrench" style="font-size:18px" aria-hidden="true"></i> ' . __("ToUpdate") . '</button></li>';
echo ' <li><button type="submit" class="btn btn-primary"> <i class="fa fa-cloud-download" style="font-size:18px" aria-hidden="true"></i> ' . __("Available") . '</button></li>';
echo '</ul>';
echo '</nav>';
echo '</div>';
echo '</form>';

echo '</div>';



$i=0;
if (!is_null($data))
{
    ?>
    <div class="row">
        <div class="col-md-1">
            &nbsp;
        </div>
        <div class="col-md-10">
        <?php
        $newArray = array();

        foreach ($data as $key => $line):

            ksort($line);

            foreach ($line as $key2 => $line2):

            $newdata[$key]=$line2;
            $newdata2[$key]["Version"]=$key2;
            $newdata2[$key]["OldVersion"][]=$key2;

            endforeach;

            foreach ($newdata as $key => $line):
                $newArray[$key]=$newdata[$key];
                $newArray[$key]["Version"]=$newdata2[$key]["Version"];
                $newArray[$key]["OldVersion"]=$newdata2[$key]["OldVersion"];
            endforeach;

        endforeach;

        foreach ($newArray as $key2 => $line2):
        $i++;
        ?>


        <?php if ($i%2==0): ?>
        <div class="row" style="background: #bbb; border:#ddd 1px solid;">
        <?php else: ?>
        <div class="row" style="background: #eee; border:#ddd 1px solid;">
        <?php endif; ?>
            <div class="col-md-2" style="padding:10px">
                <img src='<?= $line2['Picture'] ?>' height="100" width="100">
            </div>
            <div class="col-md-10" style="padding:10px">
                <div class="row">
                    <div class="col-md-8" style="padding:10px">
                        <b><a href='<?php echo $line2["URL"] ?>'><?= $key2 ?> <?= $line2['Version'] ?></a></b>
                        <?php foreach ($line2["OldVersion"] as $key3 => $line3):
                            if ($line2['Version'] != $line3) echo $line3." ";
                        endforeach;
                        ?>
                        <i class="fa fa-cloud-download" aria-hidden="true"></i>
                        <i class="fa fa-wrench" aria-hidden="true"></i>
                        <i class="fa fa-fire" aria-hidden="true"></i>
                    </div>
                    <div class="col-md-4" style="padding:10px">
                        <p align="right"><?= $line2['CreationDate'] ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6" style="padding:10px">
                        <?= $line2['Contributor'] ?>
                    </div>
                    <div class="col-md-6" style="padding:10px">
                        <p align="right"><?= $line2['LicenceType'] ?><p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style="padding:10px">
                        <?= $line2['Description'] ?>
                    </div>
                </div>
            </div>
        </div>

        <div>&nbsp;</div>
        <?php
        endforeach;
        ?>
        </div>
        <div class="col-md-1">
            &nbsp;
        </div>
    </div>
    <?php
}
else
{
    ?>
    <div class="notification error">
        <div class="notification-inner">
            <div class="notification-message">
                <div class="title"><strong>Error</strong></div>
                <div class="msg">No plugin found</div>
            </div>
        </div>
        <a class="notification-close" href="#" onclick="$(this).parent().fadeOut(250, function() { $(this).css({opacity:0}).animate({height: 0}, 100, function() { $(this).remove(); }); }); return false;">Close</a>
    </div>
    <?php
}
