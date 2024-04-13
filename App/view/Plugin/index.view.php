<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<div class="btn-group" role="group" aria-label="Default button group">
    <a href="'.LINK.'plugin/index" type="button" class="btn btn-primary';
if (!isset($param[0])) echo ' active';
echo '" style="font-size:12px"> <i class="fa fa-server" aria-hidden="true" style="font-size:14px"></i> ' . __("All") . '</a>
    <a href="'.LINK.'plugin/index/installed" type="button" class="btn btn-primary';
if (isset($param[0]) && $param[0] == 'installed') echo ' active';
echo '" style="font-size:12px"> <i class="fa fa-server" aria-hidden="true" style="font-size:14px"></i> ' . __("Installed") . '</a>
    <a href="'.LINK.'plugin/index/toupdate" type="button" class="btn btn-primary';
if (isset($param[0]) && $param[0] == 'toupdate') echo ' active';
echo '" style="font-size:12px"> <i class="fa fa-server" aria-hidden="true" style="font-size:14px"></i> ' . __("ToUpdate") . '</a>
</div>';
echo '<div>&nbsp;</div>';

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
            if ($line2["est_actif"]==1)
            {
                $newdata2[$key]["CurrentVersion"]=$key2;
            }

            endforeach;

            foreach ($newdata as $key => $line):
                $newArray[$key] = $newdata[$key];
                $newArray[$key]["Version"] = $newdata2[$key]["Version"];
                $newArray[$key]["OldVersion"] = $newdata2[$key]["OldVersion"];
                
                if (isset( $newdata2[$key]["CurrentVersion"]))
                {
                    $newArray[$key]["CurrentVersion"] = $newdata2[$key]["CurrentVersion"];
                }
                else
                {
                    $newArray[$key]["CurrentVersion"] = "";
                }
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
                <img src='<?= $line2['image'] ?>' height="100" width="100">
            </div>
            <div class="col-md-10" style="padding:10px">
                <div class="row">
                    <div class="col-md-8" style="padding:10px">
                        <b><a href='<?php echo $line2["fichier"] ?>'><?= $key2 ?> <?= $line2['Version'] ?></a></b>
                        <?php foreach ($line2["OldVersion"] as $key3 => $line3):
                            if ($line2['version'] != $line3) echo $line3." ";
                        endforeach;

                        if ($line2["est_actif"] <= 0) {
                            echo '<a href="'.LINK.'plugin/install/'.$line2["id"].'" type="button" class="btn btn-primary" style="font-size:12px"> <i class="fa fa-cloud-download" aria-hidden="true"></i> Installation </a>';
                        }
                        else
                        {
                            if ((!empty($line2["CurrentVersion"])) && ($line2["CurrentVersion"]<$line2["Version"])) {
                                echo '<a href="'.LINK.'plugin/update/'.$line2["id"].'" type="button" class="btn btn-primary" style="font-size:12px"> <i class="fa fa-wrench" aria-hidden="true"></i> Mise à jour </a>';
                            }
                            if ((!empty($line2["CurrentVersion"])) && ($line2["CurrentVersion"]==$line2["Version"])) {
                                echo '<i class="fa fa-fire" aria-hidden="true"></i> Installé </a>';
                            }

                            echo '<a href="'.LINK.'plugin/remove/'.$line2["id"].'" type="button" class="btn btn-primary" style="font-size:12px"> <i class="icon-remove"></i> Remove </a>';
                        }
                        ?>
                    </div>
                    <div class="col-md-4" style="padding:10px">
                        <p align="right"><?= $line2['date_installation'] ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6" style="padding:10px">
                        <?= $line2['auteur'] ?>
                    </div>
                    <div class="col-md-6" style="padding:10px">
                        <p align="right"><?= $line2['type_licence'] ?><p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style="padding:10px">
                        <?= $line2['description'] ?>
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
