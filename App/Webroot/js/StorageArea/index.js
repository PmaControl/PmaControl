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


$(document).ready(function () {
    $('.line-edit').editable();
});