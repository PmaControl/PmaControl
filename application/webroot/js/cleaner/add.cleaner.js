/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$("#cleaner_main-id_mysql_server").change(function () {
    data = $(this).val();
    
    $(this).parent().parent().next().find("select").load(GLIAL_LINK+"cleaner/getDatabaseByServer/" + data + "/ajax>true/");

    $(".table").find("select.schema").each(function () {
        $(this).load(GLIAL_LINK+"cleaner/getDatabaseByServer/" + data + "/ajax>true/");
    });
});

$("#cleaner_main-database").change(function () {
    data = $(this).val();

    var optionSelected = $(this).find("option:selected");
    var valueSelected = optionSelected.val();
    //var textSelected   = optionSelected.text();

    $(".table").find("select.schema").each(function () {
        $(this).val(valueSelected);
    });
    
    server = $("#cleaner_main-id_mysql_server").val();
    schema = data;
    
    $(".table").find("select.tables").each(function () {
        $(this).load(GLIAL_LINK+"cleaner/getTableByDatabase/" + schema + "/id_mysql_server:" + server + "/ajax>true/");
    });
    $("#cleaner_main-main_table").load(GLIAL_LINK+"cleaner/getTableByDatabase/" + schema + "/id_mysql_server:" + server + "/ajax>true/");
});

$(".table").on('change','.constraint.schema', function() {
    data = $(this).val();
    server = $("#cleaner_main-id_mysql_server").val();

    $(this).parents('.cleaner-line').find(".constraint.tables").load(GLIAL_LINK+"cleaner/getTableByDatabase/" + data + "/id_mysql_server:" + server + "/ajax>true/");
});

$(".table").on('change','.constraint.tables', function() {
    table = $(this).val();
    server = $("#cleaner_main-id_mysql_server").val();
    schema = $(this).parents('.cleaner-line').find(".constraint.schema").val();
    $(this).parents('.cleaner-line').find(".constraint.column").load(GLIAL_LINK+"cleaner/getColumnByTable/" + table + "/id_mysql_server:" + server + "/schema:" + schema + "/ajax>true/");
});

$(".table").on('change','.referenced.schema', function() {
    data = $(this).val();
    server = $("#cleaner_main-id_mysql_server").val();

    $(this).parents('.cleaner-line').find(".referenced.tables").load(GLIAL_LINK+"cleaner/getTableByDatabase/" + data + "/id_mysql_server:" + server + "/ajax>true/");
});

$(".table").on('change','.referenced.tables', function() {
    data = $(this).val();
    server = $("#cleaner_main-id_mysql_server").val();
    schema = $(this).parents('.cleaner-line').find(".referenced.schema").val();
    $(this).parents('.cleaner-line').find(".referenced.column").load(GLIAL_LINK+"cleaner/getColumnByTable/" + data + "/id_mysql_server:" + server + "/schema:" + schema + "/ajax>true/");
});

$("#cleaner_foreign_key-referenced_table").change(function () {
    data = $(this).val();
    server = $("#cleaner_main-id_mysql_server").val();
    schema = $("#cleaner_foreign_key-referenced_schema").val();
    $("#cleaner_foreign_key-referenced_column").load(GLIAL_LINK+"cleaner/getColumnByTable/" + data + "/id_mysql_server:" + server + "/schema:" + schema + "/ajax>true/");
});

$(function () {
    var nbline = 1;
    var derline;
    nbline = $(".cleaner-line").length;
    minLine = nbline;
    derline = nbline;

    $("#add").click(function () {
        derline++;
        nbline++;
        var clone;

        clone = $(".cleaner-line").last().clone();
        clone.attr("id", "cleaner-line-" + derline);

        clone.find("select, input").each(function () {
            var matches = $(this).attr("id").match(/([\w\_]+\-)[0-9]+(-[\w\_]+)/);
            var matches2 = $(this).attr("name").match(/([\w\_]+\[)[0-9]+(\]\[[\w\_]+\])/);

            $(this).attr("id", matches[1] + derline + matches[2]);
            $(this).attr("name", matches2[1] + derline + matches2[2]);
        });

        //$(".table").append(clone);

        $(".cleaner-line:last-child").after(clone);

        if (nbline > 1)
        {
            $(".delete-row").attr("disabled", false).removeClass("btn-default").addClass("btn-danger");
        }

        return false;
    });

    $(".table").on("click", ".delete-row", function () {
        if (nbline > minLine) {
            $(this).closest(".cleaner-line").remove();
            nbline--;
            if (nbline === 1) {
                $(".delete-row").attr("disabled", true).removeClass("btn-danger").addClass("btn-default");
            }
        }
        return false;
    });
});