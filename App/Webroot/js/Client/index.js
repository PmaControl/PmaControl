/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(".is_monitored").change(function () {
    var checkbox = $(this);
    var idClient = checkbox.attr("data-id");
    var isMonitored = checkbox.is(":checked") ? 1 : 0;
    var previousState = !checkbox.is(":checked");
    var csrfField = checkbox.attr("data-csrf-field") || "_csrf_token";
    var csrfToken = checkbox.attr("data-csrf-token");
    var payload = {
        id: idClient,
        is_monitored: isMonitored
    };

    if (csrfToken) {
        payload[csrfField] = csrfToken;
    }

    $.ajax({
        url: GLIAL_LINK + 'client/toggleMonitoring/ajax:true',
        method: 'POST',
        dataType: 'json',
        data: payload
    }).done(function (response) {
        if (!response || response.success !== true) {
            checkbox.prop("checked", previousState);
        }
    }).fail(function () {
        checkbox.prop("checked", previousState);
    });
});
