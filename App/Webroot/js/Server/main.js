/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(".general_log").change(function () {
    id_mysql_server = $(this).attr("data-id");
    url = GLIAL_LINK + 'server/toggleGeneralLog/' + id_mysql_server;

    if (this.checked) {
        $.get(url + '/true/');
    } else
    {
        $.get(url + '/false/');
    }
});