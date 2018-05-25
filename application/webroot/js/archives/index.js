/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$('.server').on('change', function () {
    
    data = $(this).val();
    var numberline = $(this).attr("id").match(/[\w\_]+\-([0-9]+)\-[\w\_]+/)[1];
    var id = '#mysql_server-' + numberline + '-database';
    
    $(id).load(GLIAL_LINK + "common/getDatabaseByServer/" + data + "/ajax>true/",
            function () {
                $(id).selectpicker('refresh');
            }
    );
});