<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Ariane;
use \App\Library\Display;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for layout workflows.
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
class Layout extends Controller
{

/**
 * Handle layout state through `header`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $title Input value for `title`.
 * @phpstan-param mixed $title
 * @psalm-param mixed $title
 * @return void Returned value for header.
 * @phpstan-return void
 * @psalm-return void
 * @see self::header()
 * @example /fr/layout/header
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function header($title)
    {
        $this->set('GLIALE_TITLE', $title);
    }

/**
 * Handle layout state through `footer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for footer.
 * @phpstan-return void
 * @psalm-return void
 * @see self::footer()
 * @example /fr/layout/footer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function footer()
    {

    }

/**
 * Handle layout state through `headerPma`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for headerPma.
 * @phpstan-return void
 * @psalm-return void
 * @see self::headerPma()
 * @example /fr/layout/headerPma
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function headerPma($param)
    {
        $title        = $param[0];
        $data['auth'] = $this->di['auth']->getAccess();
        $this->set('data', $data);
        $this->set('GLIALE_TITLE', $title);
    }

/**
 * Handle layout state through `footerPma`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for footerPma.
 * @phpstan-return void
 * @psalm-return void
 * @see self::footerPma()
 * @example /fr/layout/footerPma
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function footerPma()
    {
        $data['auth'] = $this->di['auth']->getAccess();

        if ($data['auth'] !== 1) {
            $user         = $this->di['auth']->getuser();
            $data['name'] = $user->firstname." ".$user->name." (".$user->email.")";
        }
        $this->set('data', $data);
    }

/**
 * Handle layout state through `headerPmacontrol`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for headerPmacontrol.
 * @phpstan-return void
 * @psalm-return void
 * @see self::headerPmacontrol()
 * @example /fr/layout/headerPmacontrol
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function headerPmacontrol($param)
    {
        $title = $param[0];
        $this->set('GLIALE_TITLE', $title);
    }

/**
 * Handle layout state through `footerPmacontrol`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for footerPmacontrol.
 * @phpstan-return void
 * @psalm-return void
 * @see self::footerPmacontrol()
 * @example /fr/layout/footerPmacontrol
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function footerPmacontrol()
    {
    }

/**
 * Handle layout state through `ariane`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for ariane.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::ariane()
 * @example /fr/layout/ariane
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function ariane($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $ariane = new Ariane($db);
        $body   = $ariane->buildAriane($this->getMethod());

        $body = Display::icon($body);
        $data = $body;
        return $data;
    }

/**
 * Retrieve layout state through `getMethod`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getMethod.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getMethod()
 * @example /fr/layout/getMethod
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

    /**
     *
     * @deprecated
     * @since 2.0.30
     */
    private function replaceIndex($method)
    {
        $elems    = explode("::", $method);
        $elems[1] = "index";

        return implode("::", $elems);
    }

    /**
     *
     * @deprecated
     * @since 2.0.30
     */
    public function title($params)
    {
        Debug::debug($params);

        $param      = \Glial\Synapse\FactoryController::getRootNode();
        $controller = $param[0];
        $method     = $param[1];
        $this->view = false;
        $db         = Sgbd::sql(DB_DEFAULT);
        $sql        = "SELECT * FROM menu where `class`='".$controller."' AND `method` = '".$method."' ORDER BY group_id ASC LIMIT 1";
        $res        = $db->sql_query($sql);

        while ($data['title'] = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            return $data['title']['icon']." ".__($data['title']['title']);
        }
        echo $data['title']['icon']." ".__($data['title']['title']);
    }
}

