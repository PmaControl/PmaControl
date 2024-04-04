<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
class Translation extends Controller
{
    public $module_group = "Other";

    private $logger;

    function index()
    {
        $this->title  = __("Translations");
        $this->ariane = "> <a href=\"\">".__("Administration")."</a> > ".$this->title;
    }

    function admin_translation()
    {



        //include_once APP_DIR.DS."controller".DS."History.controller.php";

        $this->title  = __("Translations");
        $this->ariane = "> <a href=\"\">".__("Administration")."</a> > ".$this->title;

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['field-to-update'])) {
                $_POST['field-to-update'] = mb_substr($_POST['field-to-update'], 0, -1);

                $data_to_update = explode(";", $_POST['field-to-update']);

                foreach ($data_to_update as $key) {

                    $key_extrated = explode("-", $key);

                    $data['translation_'.$_POST['none']['id_to']]['id']             = $key_extrated[1];
                    $data['translation_'.$_POST['none']['id_to']]['text']           = $_POST[$key];
                    $data['translation_'.$_POST['none']['id_to']]['translate_auto'] = 0;

                    $db->set_history_type(5);
                    $db->sql_save($data);
                }
            }
        }

        $count = 0;

        $db = Sgbd::sql(DB_DEFAULT);

        $tables = $db->getListTable("table");

        $lg_available = explode(",", LANGUAGE_AVAILABLE);

        foreach ($tables['table'] as $table) {
            if (mb_strstr($table, 'translation_')) {

                list(, $suffixe ) = explode("_", $table);

                if (!in_array($suffixe, $lg_available)) {
                    continue;
                }

                /*
                  $sql = "SELECT count(1) as cpt FROM `" . $table[0] . "` WHERE text =''";

                  $res2 = $db->sql_query($sql);
                  $data['count'] = $db->sql_to_array($res2);
                  //echo $data['count'][0]['cpt']." - ";
                  $count += $data['count'][0]['cpt'];
                 */
                $var                      = explode("_", $table);
                $data['country'][$var[1]] = 0;

                $sql                             = "SELECT count(1) as cpt FROM `".$table."` WHERE text !='' AND translate_auto = 1";
                $res3                            = $db->sql_query($sql);
                $data['count_auto']              = $db->sql_to_array($res3);
                $data['translate_auto'][$var[1]] = $data['count_auto'][0]['cpt'];
            }
        }

        if (\Glial\Synapse\Basic::from() !== "administration.controller.php") {
            if (empty($_GET['alpha'])) {
                $data['alpha'] = 1;
            } else {
                $data['alpha'] = $_GET['alpha'];
            }

            if (empty($_GET['type'])) {
                $data['type'] = 1;
            } else {
                $data['type'] = $_GET['type'];
            }

            if (empty($_GET['page'])) {
                $data['page'] = 0;
            } else {
                $data['page'] = $_GET['page'];
            }

            empty($_GET['from']) ? $data['from'] = 'en' : $data['from'] = $_GET['from'];
            empty($_GET['to']) ? $data['to']   = I18n::Get() : $data['to']   = $_GET['to'];

            $this->javascript                  = array("jquery-1.4.2.min.js");
            $this->di['js']->code_javascript[] = '
	function trim11 (str) {
		str = str.replace(/^\s+/, "");
		for (var i = str.length - 1; i >= 0; i--) {
			if (/\S/.test(str.charAt(i))) {
				str = str.substring(0, i + 1);
				break;
			}
		}
		return str;
	}

	$("textarea.translation").each(function(i, item) {
		console.log(i, item);
		$(item).css("height",item.scrollHeight);
	});

	$("textarea.translation").keyup(function() {
		$(this).css("height",10);
		$(this).css("height",this.scrollHeight);
	});

	$("textarea.translation").change(function() {

		$(this).css("height",10);
		$(this).val(trim11($(this).val()));

		$(this).css("height",this.scrollHeight);
	});

	$("#none-id_from").change(function() {
		  document.location="'.LINK.$this->getClass().'/'.__FUNCTION__.'/type:'.$data['type'].'/alpha:'.$data['alpha'].'/from:"+$(this).val()+"/to:'.$data['to'].' "
		  // display based on the value
	});

	$("#none-id_to").change(function() {
		  document.location="'.LINK.$this->getClass().'/'.__FUNCTION__.'/type:'.$data['type'].'/alpha:'.$data['alpha'].'/from:'.$data['from'].'/to:"+$(this).val()
		  // display based on the value
	});

    $("textarea.val2").change(function() {
        $("#none-field-to-update").val($(this).attr("name")+";"+$("#none-field-to-update").val());
	});';

            $i = 0;
            foreach ($data['country'] as $key => $value) {

                /*
                  $key2 = $key;
                  if (strstr($key, '-'))
                  {
                  $tmp2 = explode("-", $key);
                  //$key2 = $tmp2[0] . '-' . $tmp2[1];
                  } */

                $data['geolocalisation_country'][$i]['libelle'] = ucfirst(I18n::$languages[$key]);
                $data['geolocalisation_country'][$i]['id']      = $key;
                $i++;
            }

            $sql1 = "SELECT a.id as aid, b.id as bid, a.key,a.file_found,a.line_found, a.source, a.text as atext, b.text as btext, a.translate_auto as auto1, b.translate_auto as auto2 ";
            $sql2 = "SELECT count(1) as cpt ";

            $sql = " FROM  `translation_".$data['from']."` a
			INNER JOIN `translation_".$data['to']."` b ON a.key = b.key
			WHERE a.source != '".$data['to']."'";

            if ($data['alpha'] != "1") {
                $sql .= " AND a.text LIKE '".$data['alpha']."%' ";
            }
            if ($data['type'] == "4") {
                $sql .= " AND b.text ='' ";
            }
            if ($data['type'] == "2") {
                $sql .= " AND b.translate_auto=1 ";
            }
            if ($data['type'] == "3") {
                $sql .= " AND b.translate_auto=0 ";
            }
            $sql .= " order by a.text asc";

            $res           = $db->sql_query($sql2.$sql);
            $data['count'] = $db->sql_to_array($res);
            //*****************************pagination

            if ($data['count'][0]['cpt'] != 0) {
                include_once(LIB."pagination.lib.php");

                //url, curent page, nb item max , nombre de lignes, nombres de pages
                $pagination = new pagination(LINK.$this->getClass().'/'.__FUNCTION__.'/type:'.$data['type'].'/alpha:'.$data['alpha'].'/from:'.$data['from'].'/to:'.$data['to'],
                    $data['page'], $data['count'][0]['cpt'], TRANSLATION_ELEM_PER_PAGE, TRANSLATION_NB_PAGE_TO_DISPLAY_MAX);

                $pagination->set_alignment("left");
                $pagination->set_invalid_page_number_text(__("Please input a valid page number!"));
                $pagination->set_pages_number_text(__("pages of"));
                $pagination->set_go_button_text(__("Go"));
                $pagination->set_first_page_text("« ".__("First"));
                $pagination->set_last_page_text(__("Last")." »");
                $pagination->set_next_page_text("»");
                $pagination->set_prev_page_text("«");
                $tab                = $pagination->get_sql_limit();
                $data['pagination'] = $pagination->print_pagination();
                $limit              = " LIMIT ".$tab[0].",".$tab[1]." ";
                $data['i']          = $tab[0] + 1;
                //*****************************pagination end

                $res          = $db->sql_query($sql1.$sql.$limit);
                $data['text'] = $db->sql_to_array($res);
            }

            $this->set("data", $data);
        }
    }

    function delete_tmp_files()
    {
        $cmd = "cd ".TMP."translations; rm *.csv";
        shell_exec($cmd);

        $cmd = "cd ".TMP."translations; ls | grep csv | wc -l";
        $res = shell_exec($cmd);

        if ($res == 0) {

            $title = I18n::getTranslation(__("Confirmation"));
            $msg   = I18n::getTranslation(__("The CSV files have been deleted"));

            set_flash("success", $title, $msg);
        } else {

            $title = I18n::getTranslation(__("Error"));
            $msg   = I18n::getTranslation(__("The CSV file haven't been deleted.")."<br />".__("Look")." chmod !");

            set_flash("success", $title, $msg);
        }

        header("location: ".LINK.$this->getClass()."/admin_translation/");
        exit;
    }

    function delete_table_cach()
    {
        $sql = "SHOW TABLES";
        $res = $db->sql_query($sql);

        while ($table = $db->sql_fetch_array($res)) {
            if (mb_stristr($table[0], 'translation_')) {
                if ($table[0] == "translation_main") {
                    continue;
                }

                $val = explode("_", $table[0]);

                $sql = "REPLACE INTO translation_main (`key`, `source`,`destination` ,`text`,`date_inserted`, `date_updated`, `translate_auto`, `file_found`, `line_found`)
				SELECT `key`, `source`,'".$val[1]."',`text`,`date_inserted`, `date_updated`, `translate_auto`, `file_found`, `line_found` FROM `".$table[0]."`
				WHERE `text` != '' and `source` != '".$val[1]."'";

                $db->sql_query($sql);
                //echo $sql . "<br />";

                $sql = "TRUNCATE TABLE `".$table[0]."`";
                $db->sql_query($sql);
            }
        }

        $title = I18n::getTranslation(__("Confirmation"));
        $msg   = I18n::getTranslation(__("The tables cach have been truncated"));

        set_flash("success", $title, $msg);

        header("location: ".LINK.$this->getClass()."/admin_translation/");
        exit;
    }

    function getNew($param)
    {

        $languages = explode(',', LANGUAGE_AVAILABLE);

        Debug::parseDebug($param);

        Debug::debug($languages, "LANGUAGE_AVAILABLE");

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.`key`, a.`text`, a.`language` from translation_glial a
LEFT JOIN translation_google b ON a.`key` = b.`key`
WHERE b.`key` IS NULL
GROUP by a.`text`, a.`language`;";

        Debug::sql($sql);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            foreach ($languages as $target_language) {

                if ($ob->language != $target_language) {

                    Debug::debug($target_language, 'target_language');
                    Debug::debug($ob->language, 'source_language');

                    $text_translated = $this->askApiGoogle(array($ob->language, $target_language, $ob->text));

                    if ($text_translated)
                    {
                        // insert
                        $sql2 = "INSERT INTO `translation_google` (`key`, `source_language`,`source_text` , `target_language`,`target_text` )"
                            ."VALUES ('".$ob->key."', '".$ob->language."', '".$db->sql_real_escape_string($ob->text)."','".$target_language."','".$db->sql_real_escape_string($text_translated)."');";

                        Debug::sql($sql2);

                        $db->sql_query($sql2);

                        Debug::debug($text_translated, "text_translated");

                        usleep(rand(1000000,2000000));
                    }

                }
            }
        }
    }

    function askApiGoogle($param)
    {
        $from   = $param[0];
        $source = $param[1];
        $string = $param[2];

        $url = "https://www.googleapis.com/language/translate/v2/"
            ."?key=".GOOGLE_API_KEY
            ."&source=".$from
            ."&target=".$source
            ."&q=".urlencode($string);

        Debug::debug($url);

        $handle   = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);     //We want the result to be saved into variable, not printed out
        $response = curl_exec($handle);
        curl_close($handle);

        Debug::debug(json_decode($response, true));
        $data = json_decode($response, true);

        if (!empty($data['data']['translations'][0]['translatedText'])) {
            return trim($data['data']['translations'][0]['translatedText']);
        } else {
            return false;
        }

        
    }

    function settings($param)
    {

        Debug::parseDebug($param);

        $languages = explode(',', LANGUAGE_AVAILABLE);

        $data['language'] = array();
        foreach ($languages as $lang) {

            $tmp                = array();
            $tmp['id']          = $lang;
            $tmp['libelle']     = '<img src="" width="18px" height="12px"> '.' '.I18n::$languagesUTF8[$lang]." - ".__(ucwords(I18n::$languages[$lang]));
            $data['language'][] = $tmp;

            Debug::debug($tmp);
        }

        Debug::debug($data);
    }


    /*
    * (PmaControl 3.0)<br/>
    * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
    * @return boolean Success
    * @package Controller
    * @description log error 
    * @access public
    */

    public function before($param)
    {
        $monolog = new Logger("Translation");
        $handler = new StreamHandler(LOG_FILE, Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

}