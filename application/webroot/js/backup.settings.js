$(function() {
    var nbline = 1;
    var derline;
    nbline = $("tr.blah").length;
    minLine = nbline;

    derline = nbline;

    $("#add").click(function() {
        derline++;
        nbline++;
        var clone;

        clone = $("tr.blah:last-child").clone();
        clone.attr("id", "tr-" + derline);

        clone.find("select, input").each(function() {
            var matches = $(this).attr("id").match(/([\w\_]+\-)[0-9]+(-[\w\_]+)/);

            //backup_database[id_backup_storage_area][0]
            var matches2 = $(this).attr("name").match(/([\w\_]+\[)[0-9]+(\]\[[\w\_]+\])/);


            $(this).attr("id", matches[1] + derline + matches[2]);
            $(this).attr("name", matches2[1] + derline + matches2[2]);


            //console.log(matches2);
            //console.log($(this).attr("id"));
        });

        $("#table").append(clone);
        return false;
    });

    $("#table").on("click", "a.delete-line", function() {
        console.log(nbline);


        if (nbline > minLine) {
            $(this).closest("tr").remove();

            nbline--;
            $("#add").attr("disabled", false);
            if (nbline === 1) {
                $("input.delete-line").attr("disabled", true).removeClass("btBlueTest").addClass("btGrey");
            }
        }
        return false;
    });



});

$(function() {
    $(document).on("click", "input.server", function(e) {
        $(this).autocomplete("http://dba-tools.photobox.com/en/backup/getServerByName/ajax>true/", {
            mustMatch: true,
            autoFill: true,
            max: 100,
            minChars: 2,
            scrollHeight: 302,
            selectFirst: false,
            delay: 300
        }).result(function(event, data, formatted) {
            if (data)
            {
                
                $(this).parent().next().find("input").val(data[2]);
                $(this).closest('tr').find("input.auto").val(data[1]);
                $(this).parent().next().next().find("select").load("http://dba-tools.photobox.com/en/backup/getDatabaseByServer/" + data[1] + "/ajax>true/");
            }
        }
        );
    });
});

$(function() {
    $(document).on("click", "input.ip", function(e) {
        $(this).autocomplete("http://dba-tools.photobox.com/en/backup/getServerByIp/ajax>true/", {
            mustMatch: true,
            autoFill: true,
            max: 100,
            minChars: 2,
            scrollHeight: 302,
            selectFirst: false,
            delay: 300
        }).result(function(event, data, formatted) {
            if (data)
            {
                
                $(this).parent().prev().find("input").val(data[2]);
                $(this).closest('tr').find("input.auto").val(data[1]);
                $(this).parent().next().find("select").load("http://dba-tools.photobox.com/en/backup/getDatabaseByServer/" + data[1] + "/ajax>true/");
            }
        }
        );
    });
});


$(function() {
    $("#table tr.edit td.input").dblclick(function(e) {
        e.stopPropagation();      //<-------stop the bubbling of the event here
        var currentEle = $(this);
        var value = $(this).html();
        updateVal(currentEle, value);
    });
});

function updateVal(currentEle, value) {
    $(currentEle).html('<input style="width:40px" class="thVal" type="text" value="' + value + '" />');
    $(".thVal").focus();
    $(".thVal").keyup(function(event) {
        if (event.keyCode == 13) {
            $(currentEle).html($(".thVal").val().trim());
        }
    });

    $(document).click(function() { // you can use $('html')
        $(currentEle).html($(".thVal").val().trim());
    });
}