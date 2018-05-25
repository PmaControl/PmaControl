<?php

use \Glial\Synapse\Controller;
use \App\Library\Ariane;

class Layout extends Controller
{

    function header($title)
    {
        $this->set('GLIALE_TITLE', $title);
    }

    function footer()
    {

    }

    function headerPma($title)
    {

        $data['auth'] = $this->di['auth']->getAccess();

        $this->set('data', $data);
        $this->set('GLIALE_TITLE', $title);
    }

    function footerPma()
    {


        $data['auth'] = $this->di['auth']->getAccess();

        if ($data['auth'] !== 1) {
            $user         = $this->di['auth']->getuser();
            $data['name'] = $user->firstname." ".$user->name." (".$user->email.")";
        }
        $this->set('data', $data);
    }

    public function ariane($param)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $title = array();
        if (!empty($param[1])) {
            $title = array($param[1]);
        }

        $ariane = new Ariane($db);
        $body   = $ariane->buildAriane($this->getMethod());

        if (count($body) === 0) {
            $body = $ariane->buildAriane($this->replaceIndex($this->getMethod()));
        }

        $root = array('<a href="'.WWW_ROOT.'"><span class="glyphicon glyphicon glyphicon-home"></span> '.__("Home").'</a>');


        if (trim(strtolower(strip_tags(end($body)))) === trim(strtolower(strip_tags(end($title))))) {
            $title = array();
        }


        $breadcrumb     = array_merge($root, $body, $title);
        $data['ariane'] = $this->buildHtml($breadcrumb);

        $this->set('data', $data);
    }

    private function getMethod()
    {
        $elems = explode("/", $_GET['glial_path']);

        $class = '';
        if (!empty($elems[1])) {
            $class = $elems[1];
        }
        $method = '';
        if (!empty($elems[2])) {
            $method = $elems[2];
        }

        return $class."::".$method;
    }

    private function buildHtml($arr)
    {
        return implode(" > ", $arr);
    }

    private function replaceIndex($method)
    {

        $elems = explode("::", $method);

        $elems[1] = "index";

        return implode("::", $elems);
    }
}