/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getReadableFileSizeString(fileSizeInBytes) {
    var i = -1;
    var byteUnits = [' ko', ' Mo', ' Go', ' To', 'Po', 'Eo', 'Zo', 'Yo'];
    do {
        fileSizeInBytes = fileSizeInBytes / 1024;
        i++;
    } while (fileSizeInBytes >= 1024);

    return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];
};



$("#mysql_server-id").change(function () {
    id_mysql_server = $(this).val();
    
    //alert(id_mysql_server);
   
    $("#variables-max_binlog_size").load(GLIAL_LINK + "Binlog/getMaxBinlogSize/" + id_mysql_server + "/ajax>true/", function (result) {
        $("#variables-max_binlog_size").val(getReadableFileSizeString(result));
        $("#variables-file_binlog_size").val(result);
        
    });
});