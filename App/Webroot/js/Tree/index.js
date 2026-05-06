/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



/*
 var $outer = $(".line-edit");
 
 $outer.dblclick(function () {
 
 $(this).removeClass("line-edit");
 var text = $.trim($(this).text());
 
 $(this).empty();
 $(this).append('<input value="' + text + '" class="form-control input-sm"></input>');
 $(this).unbind('dblclick');
 
 });*/
$.fn.editable.defaults.mode = 'inline';

(function () {
    function initLineEdit(context) {
        var $context = context ? $(context) : $(document);
        var $elements = $context.is('.line-edit') ? $context : $context.find('.line-edit');

        $elements.each(function () {
            var $element = $(this);

            if ($element.data('editable')) {
                return;
            }

            $element.editable({
                params: function (params) {
                    // Issue #790: in bootstrap-editable's params callback,
                    // `this` is the Editable widget instance, NOT the DOM
                    // element. Reading the CSRF attributes via $(this) on
                    // the widget therefore returned undefined and the token
                    // was never attached to the POST — /tree/update answered
                    // 403 "Invalid CSRF token" on every save. Use the
                    // closure-captured $element (the actual <td>) instead.
                    var csrfToken = $element.data('csrf-token');
                    var csrfField = $element.data('csrf-field') || '_csrf_token';

                    if (csrfToken) {
                        params[csrfField] = csrfToken;
                    }

                    return params;
                }
            });
        });
    }

    window.pmacontrolInitLineEdit = initLineEdit;

    $(document).ready(function () {
        initLineEdit(document);
    });
})();
