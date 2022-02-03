/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$("#data_main-id_mysql_server__original").change(function () {
    data = $(this).val();
    $("#compare_main-database__original").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
       function(){
	$('#compare_main-database__original').selectpicker('refresh');
    });
   
});

$("#compare_main-id_mysql_server__compare").change(function () {
    data = $(this).val();
    $("#compare_main-database__compare").load(GLIAL_LINK+"common/getDatabaseByServer/" + data + "/ajax>true/",
    function() {
	$('#compare_main-database__compare').selectpicker('refresh');
    });
});
