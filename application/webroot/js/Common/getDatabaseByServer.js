/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$('select[data-link="db"]').on('change', function () {
    data = $(this).val();
    var cible = $(this).attr("data-target");
    var id = '#' + cible;
    $(id).load(GLIAL_LINK + "common/getDatabaseByServer/mysql_database/id/" + data + "/ajax>true/",
            function () {
                $(id).selectpicker('refresh');
            }
    );
});
