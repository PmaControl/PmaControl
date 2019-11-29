<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\I18n\I18n;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;

class Test extends Controller {

    public function testGoogleTranslate2($param) {
        Debug::parseDebug($param);


        I18n::SetDefault("en");
        I18n::load("en");

        $res = I18n::get_answer_from_google("Bienvenue, veuillez vous identifier\nContacter le créateur de l'application", "fr");


        Debug::debug($res);

        $this->assertEquals($res[0], "Welcome, please login");
        $this->assertEquals($res[1], "Contact the creator of the application");

        I18n::SetDefault("fr");
        I18n::load("fr");

        $res = I18n::get_answer_from_google("storage area\nProduct Version", "en");

        print_r($res);

        $this->assertEquals($res[0], "zone de stockage");
        $this->assertEquals($res[1], "Version du produit");
    }

}
