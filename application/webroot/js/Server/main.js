/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



$(".general_log").change(function () {
    if (this.checked) {
        //Do stuff
        //alert("gg");

        $.get({url: GLIAL_LINK + 'server/toggleGeneralLog/' + $(this).attr("data-id") + '/true'});
    } else
    {
        $.get({url: GLIAL_LINK + 'server/toggleGeneralLog/' + $(this).attr("data-id") + '/false'});
    }
});