/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$("#mysql_server-id").change(function () {
    data = $(this).val();
    $("#spider-database").load(GLIAL_LINK + "common/getDatabaseByServer/" + data + "/ajax>true/",
            function () {
                $('#spider-database').selectpicker('refresh');
            });
});


$("#mysql_server-database").change(function () {
    data = $(this).val();

    splited = data.split("-");
    id_mysql_server = splited[0];
    database = splited[1];


    alert(id_mysql_server);

    $("#mysql_server-table").load(GLIAL_LINK + "common/getTableByServerAndDatabase/" + id_mysql_server + "/" + database + "/ajax>true/",
            function () {
                $('#spider-table').selectpicker('refresh');
            });
});

