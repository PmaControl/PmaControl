<?php



$doublon = array();



foreach($data as $line)
{
   // if (! in_array($line['msg'],$doublon))
   // {
    //    $doublon[] = $line['msg'];
        echo "

        <div class=\"notification " . $line['type_error'] . "\">
            <div class=\"notification-inner\">
                <div class=\"notification-message\">
                    <div class=\"title\"><strong>" . $line['title'] . "</strong></div>
                    <div class=\"msg\">" . $line['msg'] . "</div>
                </div>
            </div>
            <a class=\"notification-close\" href=\"#\" onclick=\"$(this).parent().fadeOut(250, function() { $(this).css({opacity:0}).animate({height: 0}, 100, function() { $(this).remove(); }); }); return false;\">";
        echo __("Close");
        echo "</a>
        </div>";
        
        
 //   }
}