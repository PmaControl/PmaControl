<?php

namespace App\Library;

class Post
{

    static public function getToPost(
)    {
        $ret = [];

        //debug($_POST);

        foreach ($_POST as $main => $elems) {
            foreach ($elems as $key => $val) {

                if (is_array($val)) {
                    $val = "[".implode(",",$val)."]";
                }


                $ret[] = $main.":".$key.":".(str_replace("/","[DS]", $val));
            }
        }

        return implode('/', $ret);
    }
}