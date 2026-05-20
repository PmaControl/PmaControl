<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;

/**
 * Class responsible for error web workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class ErrorWeb extends Controller {

/**
 * Handle error web state through `error404`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for error404.
 * @phpstan-return void
 * @psalm-return void
 * @see self::error404()
 * @example /fr/errorweb/error404
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function error404() {
        $this->layout_name = 'default';

        $this->title = __("Error 404");
        $this->ariane = " > " . $this->title;

        //$this->javascript = array("");
    }

/**
 * Handle error web state through `message`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for message.
 * @phpstan-return void
 * @psalm-return void
 * @see self::message()
 * @example /fr/errorweb/message
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function message($param) {

        $data['title'] = $param[0];
        $data['msg'] = $param[1];
        $data['color'] = $param[2];

        $this->set('data', $data);
    }

}

