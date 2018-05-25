<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 *
 * 15% moins bien lors du chargement par rapport à une sauvegarde générer avec mysqldump
 * le temps de load peut être optimisé
 */

use Glial\Synapse\Controller;

class Deploy extends Controller
{

    public function index()
    {

        $this->title = '<i class="fa fa-arrows-alt" aria-hidden="true"></i> '.__("Deploy");

        //$this->di['js']->addJavascript(array("bootstrap-tooltip.js"));


        
        $this->di['js']->code_javascript('$(function () {  $(\'[data-toggle="popover"]\').popover({trigger:"hover"}) });');
        $this->di['js']->code_javascript('
            $(\'[data-toggle="popover"]\').each(function(index, element) {
    var contentElementId = $(element).data().target;
    var contentHtml = $(contentElementId).html();
    $(element).popover({
        content: contentHtml,
        trigger:"hover",
        html:true
    });
});');
        
    }
}