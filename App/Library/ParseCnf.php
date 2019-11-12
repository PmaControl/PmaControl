<?php

namespace App\Library;

class ParseCnf
{
    static $var_to_merge = array("replicate_annotate_row_events", "replicate_ignore_db", "replicate_rewrite_db", "replicate_do_db", "replicate_do_table", "replicate_events_marked_for_skip",
        "replicate_ignore_table", "replicate_wild_do_table", "replicate_wild_ignore_table");

    static function parseCnf($path, $parsed = array())
    {

        $path_parts = pathinfo($path);

        if ($path_parts['extension'] === "cnf") {
            $new_path = $path;
        } else {
            $new_path = rtrim($path, "/")."/*.cnf";
        }


        $files = glob($new_path);


        foreach ($files as $file) {
            echo $file."\n";
            $myfile = file($file);


            $cnf         = array();
            $include_dir = array();


            foreach ($myfile as $line) {
                $comment_removed   = explode('#', $line)[0];
                $comment_removed_t = trim($comment_removed);

                if (!empty($comment_removed_t)) {
                    if (substr($comment_removed_t, 0, 11) === "!includedir") {
                        $include_dir[] = trim(str_replace("!includedir", "", $comment_removed_t));
                        continue;
                    }

                    $cnf[] = $comment_removed_t;
                }
            }

            $pure_cnf = implode("\n", $cnf);

            $for_split = preg_replace("/\[\w+\-?\d?\.?\d?\]/s", "###$0", $pure_cnf);

            $sections = explode('###', $for_split);

            unset($sections[0]);


            foreach ($sections as $section) {
                $lines        = explode("\n", trim($section));
                $section_name = trim($lines[0], "[]");

                unset($lines[0]);

                foreach ($lines as $line) {
                    $options = explode("=", $line);

                    $var = trim($options[0]);

                    $pos = strpos($var, '.');

                    $sub = false;
                    if ($pos !== false) {
                        $sub   = true;
                        $elems = explode('.', $var);

                        //$var = end($elems);
                        unset($elems[key($elems)]);

                        $connection_name = implode('.', $elems); // if we have dot in connection_name

                        $var = str_replace("-", "_", $var); //to have all var write with _ instead of -
                    } else {

                        $var = str_replace("-", "_", $var); //to have all var write with _ instead of -
                        if (in_array($var, self::$var_to_merge)) {
                            $connection_name = "";
                            $sub             = true;
                        }
                    }


                    unset($options[0]);

                    $val = trim(implode('=', $options)); // in case of '=' in password
                    if (empty($val)) {
                        $val = 1;
                    }

                    $val = trim(trim($val, '"'), "'");  // remove quote and double quotes


                    if ($sub === true) {
                        $parsed[$section_name][$var][$connection_name][] = $val;
                    } else {
                        $parsed[$section_name][$var][] = $val;
                    }
                }
            }

            foreach ($include_dir as $path) {
                $parsed = self::parseCnf($path, $parsed);
            }
        }
        return $parsed;
    }

    static function getCnf($mycnf)
    {

        $parsed = self::parseCnf($mycnf);

        $ret = array();

        foreach ($parsed as $section => $tab_var) {

            foreach ($tab_var as $var => $tab_val) {

                if (in_array($var, self::$var_to_merge)) {
                    foreach ($tab_val as $connection_name => $tab_options) {
                        $val                                   = implode(',', $tab_options);
                        $ret[$section][$var][$connection_name] = $val;
                    }
                } else {
                    $ret[$section][$var] = end($tab_val);
                }
            }
        }

        return $ret;
    }
}
