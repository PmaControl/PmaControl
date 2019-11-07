
$("#mysql_cluster-id").change(function () {
    data = $(this).val();

    $("#mysql_cluster-database").load(GLIAL_LINK + "CheckDataOnCluster/getDatabasesByServers/" + data + "/ajax>true/", function () {
        $("#mysql_cluster-database").selectpicker("refresh");
    });
});