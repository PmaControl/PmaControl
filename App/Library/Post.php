<?php

namespace App\Library;

/**
 * Class responsible for post workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Post
{

/**
 * Retrieve post state through `getToPost`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $filter Input value for `filter`.
 * @phpstan-param mixed $filter
 * @psalm-param mixed $filter
 * @return mixed Returned value for getToPost.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getToPost()
 * @example /fr/post/getToPost
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getToPost($filter= array())
    {
        $ret = [];

        //debug($_POST);

        foreach ($_POST as $main => $elems) {
            foreach ($elems as $key => $val) {

                if (is_array($val)) {
                    $val = "[".implode(",", $val)."]";
                }

                $ret[] = $main.":".$key.":".(str_replace("/", "[DS]", $val));
            }
        }

        return implode('/', $ret);
    }
}
