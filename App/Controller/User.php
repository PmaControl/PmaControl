<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;


class User extends Controller {

    use \Glial\Neuron\MailBox\MailBox;

    public $module_group = "Users & access management";
    public $method_administration = array("user", "roles");

    function before($param) {
        
    }

    function after($param) {
        if (!IS_CLI) {

            //$this->di['js']->addJavascript(array("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"));
        }
    }

    function index() {
        //$this->di['js']->addJavascript(array("jquery-latest.min.js"));
        $this->title = __("Members");
        $this->ariane = "> " . $this->title;

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id, a.firstname, a.name, b.id_country, a.email,b.libelle, a.id_group, date_last_connected
			FROM user_main a
			INNER JOIN geolocalisation_city b ON b.id = a.id_geolocalisation_city
			WHERE a.is_valid =  '1'
			LIMIT 100";

        $res = $db->sql_query($sql);
        $data['user'] = $db->sql_to_array($res);


        $sql30 = "SELECT * from `group` order by name";

        $res30 = $db->sql_query($sql30);

        $data['group'] = array();

        while ($ob = $db->sql_fetch_object($res30)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = __($ob->name);
            $data['group'][] = $tmp;
        }

        $this->set("data", $data);
    }

    function is_logged() {

        die(); // voir dans le boot.php

        global $_SITE;

        $_SITE['IdUser'] = -1;
        $_SITE['id_group'] = 1;

        if (!empty($_COOKIE['IdUser']) && !empty($_COOKIE['Passwd'])) {
            $sql = "select * from user_main where id = '" . $db->sql_real_escape_string($_COOKIE['IdUser']) . "'";
            $res = $db->sql_query($sql);

            debug("wdxfrgwdfgwdfg");
            die();


            if ($db->sql_num_rows($res) == 1) {
                $ob = $db->sql_fetch_object($res);

                debug($ob);

                if ($ob->password === $_COOKIE['Passwd']) {
                    $_SITE['IdUser'] = $_COOKIE['IdUser'];
                    $_SITE['Name'] = $ob->name;
                    $_SITE['FirstName'] = $ob->firstname;
                    $_SITE['id_group'] = $ob->id_group;

                    $GLOBALS['_SITE']['id_group'] = $_SITE['id_group'];

                    $sql = "UPDATE user_main SET date_last_connected = now() where id='" . $db->sql_real_escape_string($_SITE['IdUser']) . "'";
                    $db->sql_query($sql);
                }
            }
        }


        $this->set("_SITE", $_SITE);
    }

    function block_newsletter() {
        //Vous Ã¯Â¿Â½tes maintenant abonnÃ¯Â¿Â½ Ã¯Â¿Â½ la lettre d'information.
        //Veuillez renseigner le champ correctement...
        //include_once("class/mail.lib.php");
        $_MSG = "";

        if (!empty($_POST['newsletter'])) {
            if (mail::IsSyntaxEmail($_POST['newsletter'])) {
                $sql = "select * from UserNewsLetter where Email = '" . $db->sql_real_escape_string($_POST['newsletter']) . "'";
                $res = sql::sql_query($sql);


                if ($db->sql_num_rows($res) != 0) {
                    $_MSG = __("You are removed from our newslettter");
                    $sql = "DELETE FROM UserNewsLetter where Email = '" . $db->sql_real_escape_string($_POST['newsletter']) . "'";
                    sql::sql_query($sql);
                } else {
                    $sql = "INSERT INTO UserNewsLetter SET 
					Email = '" . $db->sql_real_escape_string($_POST['newsletter']) . "', 
					IP='" . $_SERVER['REMOTE_ADDR'] . "', 
					UserAgent='" . $_SERVER['HTTP_USER_AGENT'] . "', 
					DateInserted=now()";

                    sql::sql_query($sql);

                    $_MSG = __("Your Email has been added !");
                }
            } else {

                $_MSG = __("Your Email is not valid !");
            }
        }
    }

    function city() {
        /*
          [path] => en/user/city/
          [q] => paris
          [limit] => 10
          [timestamp] => 1297207840432
          [lg] => en
          [url] => user/city/

         */


        $this->layout_name = false;


        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "SELECT libelle, id FROM geolocalisation_city WHERE libelle LIKE '" . $db->sql_real_escape_string($_GET['q']) . "%'
		AND id_geolocalisation_country='" . $db->sql_real_escape_string($_GET['country']) . "' ORDER BY libelle LIMIT 0,100";
        $res = $db->sql_query($sql);
        $data = $db->sql_to_array($res);
        $this->set("data", $data);
    }

    function author() {
        /*
          [path] => en/user/city/
          [q] => paris
          [limit] => 10
          [timestamp] => 1297207840432
          [lg] => en
          [url] => user/city/
         */


        $this->layout_name = false;

        $sql = "SELECT firstname, name, id FROM species_author WHERE name LIKE '" . $db->sql_real_escape_string($_GET['q']) . "%' OR firstname LIKE '" . $db->sql_real_escape_string($_GET['q']) . "%'
		ORDER BY name, firstname LIMIT 0,100";
        $res = $db->sql_query($sql);
        $data = $db->sql_to_array($res);
        $this->set("data", $data);
    }

    function register() {
        $this->title = __("Registration");
        $this->ariane = "> <a href=\"" . LINK . "user/\">" . __("Members") . "</a> > " . $this->title;

        //$this->di['js']->addJavascript(array("http://www.estrildidae.net/js/jquery.1.3.2.js", "jquery.autocomplete.min.js"));
        $this->di['js']->addJavascript(array("jquery.browser.min.js", "jquery.autocomplete.min.js"));

        $this->di['js']->code_javascript('$("#user_main-id_geolocalisation_city-auto").autocomplete("' . LINK . 'user/city/none>none", {
		extraParams: {
			country: function() {return $("#user_main-id_geolocalisation_country").val();}
		},
        mustMatch: true,
        autoFill: true,
        max: 100,
        scrollHeight: 302,
        delay:0
		});
		$("#user_main-id_geolocalisation_city-auto").result(function(event, data, formatted) {
			if (data)
				$("#user_main-id_geolocalisation_city").val(data[1]);
		});
		$("#user_main-id_geolocalisation_country").change( function() 
		{
			$("#user_main-id_geolocalisation_city-auto").val("");
			$("#user_main-id_geolocalisation_city").val("");
		} );

		');


        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "SELECT id, libelle from geolocalisation_country where libelle != '' order by libelle asc";
        $res = $db->sql_query($sql);

        $data = array();
        $data['geolocalisation_country'] = $db->sql_to_array($res);

        $this->set('data', $data);

        if (!empty($_POST['user_main'])) {

            if (!empty($_COOKIE['IdUser'])) {

                $msg = I18n::getTranslation(__("You are already registered under the account id : ") . $_COOKIE['IdUser']);
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);
                header("location: " . WWW_ROOT);
                exit;
            }

            $data = array();
            $data['user_main'] = $_POST['user_main'];
            $data['user_main']['login'] = $data['user_main']['email'];
            $data['user_main']['ip'] = $_SERVER['REMOTE_ADDR'];
            $data['user_main']['date_last_login'] = date("Y-m-d H:i:s");
            $data['user_main']['date_last_connected'] = date("Y-m-d H:i:s");
            $data['user_main']['date_created'] = date("c");
            $data['user_main']['key_auth'] = sha1(uniqid());
            $data['user_main']['name'] = mb_convert_case($data['user_main']['name'], MB_CASE_UPPER, "UTF-8");
            $data['user_main']['id_group'] = 2;

            $password_non_hashed = $data['user_main']['password'];

            $data['user_main']['password'] = $this->di['auth']->hash_password($data['user_main']['login'], $data['user_main']['password']);
            $_POST['user_main']['password2'] = $this->di['auth']->hash_password($data['user_main']['login'], $data['user_main']['password']);

            //to set uppercase to composed name like 'Jean-Louis'
            $firstname = str_replace("-", " - ", $data['user_main']['firstname']);
            $firstname = mb_convert_case($firstname, MB_CASE_TITLE, "UTF-8");

            $data['user_main']['firstname'] = str_replace(" - ", "-", $firstname);

            if (!$db->sql_save($data)) {

                $error = $db->sql_error();
                $_SESSION['ERROR'] = $error;

                $title = I18n::getTranslation(__("Registration error"));
                $msg = I18n::getTranslation(__("One or more problem came when you try to register your account, please verify your informations"));

                set_flash("error", $title, $msg);

                unset($_POST['user_main']['password']);
                unset($_POST['user_main']['password2']);

                $ret = array();

                foreach ($_POST['user_main'] as $var => $val) {
                    $ret[] = "user_main:" . $var . ":" . urlencode(html_entity_decode($val));
                }

                $param = implode("/", $ret);

                header("location: " . LINK . "user/register/" . $param);
                exit;
            } else {


                $subject = __("Confirm your registration on ") . SITE_NAME;

                $msg = __('Hello') . ' ' . $data['user_main']['firstname'] . ' ' . $data['user_main']['name'] . ' !<br />
				' . __('Thank you for registering on ') . ' <a href="' . SITE_URL . '">' . SITE_NAME . '</a><br />
				<br />
				' . __("To finalise your registration, please click on the confirmation link below. Once you've done this, your registration will be complete.") . '<br />
				' . __('Please') . ' <a href="' . 'http://' . $_SERVER['SERVER_NAME'] . LINK . 'user/confirmation/' . $data['user_main']['email'] . "/" . $data['user_main']['key_auth'] . '"> ' . __('click here') . '</a> ' . __('to confirm your registration
				or copy and paste the following URL into your browser:') . '
				' . 'http://' . $_SERVER['SERVER_NAME'] . LINK . 'user/confirmation/' . $data['user_main']['email'] . '/' . $data['user_main']['key_auth'] . '<br />
                <br />
				' . __('Many thanks');


                $msg = I18n::getTranslation($msg);

                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

                // En-tetes additionnels
                $headers .= 'To: ' . $data['user_main']['firstname'] . ' ' . $data['user_main']['name'] . ' <' . $data['user_main']['email'] . '>' . "\r\n";
                $headers .= 'From: ' . SITE_NAME . ' <noreply@pmacontrol.com>' . "\r\n";
                //$headers .= 'Cc: anniversaire_archive@example.com' . "\r\n";
                //$headers .= 'Bcc: anniversaire_verif@example.com' . "\r\n";

                mail($data['user_main']['email'], $subject, $msg, $headers) or die("error mail");
                mail("aurelien.lequoy@gmail.com", "New user on " . SITE_NAME,
                        "Firstname : " . $data['user_main']['firstname'] . "\n"
                        . "Lastname : " . $data['user_main']['name'] . "\n"
                        . "Email : " . $data['user_main']['email'] . "\n");


                $msg = __('Welcome! You are now registered as a member.') . "<br/>";
                $msg .= __("In a few seconds you'll receive an email from our system with the link of validation of your account. Remember to configure your account preferences. Hope you can enjoy our services.") . "<br /><br />";
                $msg .= __("Thank you for registering on " . SITE_NAME . " !") . "<br/>";

                $msg = I18n::getTranslation($msg);
                $title = I18n::getTranslation(__("New user account created !"));
                set_flash("success", $title, $msg);


                $this->login($data['user_main']['login'], $password_non_hashed);

                header("location: " . LINK . ROUTE_DEFAULT);
                exit;
            }
        }
    }

    function lost_password() {
        $this->di['js']->addJavascript(array("jquery-latest.min.js"));

        $db = Sgbd::sql(DB_DEFAULT);


        $this->title = __("Password forgotten ?");
        $this->ariane = "> <a href=\"" . LINK . "user/\">" . __("Members") . "</a> > " . $this->title;

        if (!empty($_POST['user_main']['email'])) {

            $sql = "SELECT * FROM user_main WHERE email='" . $db->sql_real_escape_string($_POST['user_main']['email']) . "'";

            $res = $db->sql_query($sql);

            if ($db->sql_num_rows($res) === 0) {

                $title = I18n::getTranslation(__("Error"));
                $msg = I18n::getTranslation(__("This email does not exist in our database"));
                set_flash("error", $title, $msg);

                $ret = array();
                foreach ($_POST['user_main'] as $var => $val) {
                    $ret[] = "user_main:" . $var . ":" . urlencode($val);
                }

                $param = implode("/", $ret);

                header("location: " . LINK . "user/lost_password/" . $param);
                exit;
            } else {

                $ob = $db->sql_fetch_object($res);

                $recover = array();
                $recover['user_main']['id'] = $ob->id;
                $recover['user_main']['key_auth'] = sha1(uniqid());
                if (!$db->sql_save($recover)) {
                    die('problem with set key_auth');
                }

                $subject = __("Instructions to Recover your password on : ") . " " . SITE_NAME . "";
                $msg = __('Hello') . ' ' . $ob->firstname . ' ' . $ob->name . ' !<br />
				<br />
				' . __("To finalise of recover your password, please click on the following link :") . '<br />
				' . 'http://' . $_SERVER['SERVER_NAME'] . '/en/' . 'user/password_recover/' . $ob->email . '/' . $recover['user_main']['key_auth'] . '<br />
                <br />
				' . __('Many thanks');

                $subject = I18n::getTranslation($subject);
                $msg = I18n::getTranslation($msg);

                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

                // En-tetes additionnels
                $headers .= 'To: ' . $ob->firstname . ' ' . $ob->name . ' <' . $ob->email . '>' . "\r\n";
                $headers .= 'From: Contact <noreply@estrildidae.com>' . "\r\n";
                //$headers .= 'Cc: anniversaire_archive@example.com' . "\r\n";
                //$headers .= 'Bcc: anniversaire_verif@example.com' . "\r\n";


                mail($ob->email, $subject, $msg, $headers) or die("error mail");

                $title = I18n::getTranslation(__("Instructions sent !"));
                $msg = I18n::getTranslation(__("In a few seconds you'll receive an email from our system with the informations to recover your password."));
                set_flash("success", $title, $msg);


                header("location: " . LINK . "user/lost_password/");
                exit;
            }
        }
    }

    function password_recover($param) {
        $db = Sgbd::sql(DB_DEFAULT);


        $this->title = __("Recover your password");
        $this->ariane = "> <a href=\"" . LINK . "user/\">" . __("Members") . "</a> > " . $this->title;

        $sql = "SELECT * FROM user_main WHERE email='" . $db->sql_real_escape_string($param[0]) . "'
			AND key_auth='" . $db->sql_real_escape_string($param[1]) . "'";

        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) === 0) {
            $title = I18n::getTranslation(__("Error"));
            $msg = I18n::getTranslation(__("This link to recover your password is not valid anymore. Make a new request."));
            set_flash("error", $title, $msg);

            header("location: " . LINK . "user/lost_password/" . $param);
            exit;
        } else {
            if ($_SERVER['REQUEST_METHOD'] == "POST") {

                $ob = $db->sql_fetch_object($res);

                $recover = array();
                $recover['user_main']['id'] = $ob->id;
                $recover['user_main']['password'] = $_POST['user_main']['password'];


                if ($db->sql_save($recover)) {
                    $tmp = array();
                    $tmp['user_main']['id'] = $ob->id;
                    $tmp['user_main']['key_auth'] = "";
                    $tmp['user_main']['password'] = $this->di['auth']->hash_password($ob->login, $_POST['user_main']['password']);
                    $_POST['user_main']['password2'] = $this->di['auth']->hash_password($ob->login, $_POST['user_main']['password']);


                    $password_non_hash = $_POST['user_main']['password'];

                    if (!$db->sql_save($tmp)) {
                        $error = $db->sql_error();
                        print_r($error);
                        print_r($tmp);

                        die('problem with delete key_auth');
                    }

                    /*
                      debug($ob->login);
                      debug($password_non_hash);
                      exit;
                     */
                    $this->login($ob->login, $password_non_hash);


                    $title = I18n::getTranslation(__("Success"));
                    $msg = I18n::getTranslation(__("Your password has been updated successfully"));

                    set_flash("success", $title, $msg);
                    header("location: " . LINK . ROUTE_DEFAULT);
                    exit;
                } else {
                    $error = $db->sql_error();
                    $_SESSION['ERROR'] = $error;

                    $title = I18n::getTranslation(__("Error"));
                    $msg = I18n::getTranslation(__("One or more problem came when you try to update your password, please verify your informations"));
                    set_flash("error", $title, $msg);

                    header("location: " . LINK . "user/password_recover/" . $param[0] . "/" . $param[1]);
                    exit;
                }
            }
        }
    }

    function block_last_registered() {


        $sql = "select a.name, a.firstname, lower(b.iso) as iso, a.date_created, a.id from user_main a
		INNER JOIN geolocalisation_country b ON a.id_geolocalisation_country = b.id
		where 1=1 order by date_created DESC LIMIT 10";
        $res = $db->sql_query($sql);
        $data = $db->sql_to_array($res);
        $this->set("data", $data);
    }

    function block_last_online() {


        $sql = "select a.name, a.firstname, lower(b.iso) as iso, a.date_last_connected, a.id from user_main a
		INNER JOIN geolocalisation_country b ON a.id_geolocalisation_country = b.id
		where is_valid ='1' order by date_last_connected DESC LIMIT 10";
        $res = $db->sql_query($sql);
        $data = $db->sql_to_array($res);
        $this->set("data", $data);
    }

    function admin_user() {
        $module = array();
        $module['picture'] = "administration/ico-users.gif";
        $module['name'] = __("Users");
        $module['description'] = __("Manage users who can access");

        return $module;
    }

    function confirmation($data) {
        $db = Sgbd::sql(DB_DEFAULT);


        $sql = "SELECT * FROM user_main WHERE email = '" . $db->sql_real_escape_string($data[0]) . "'";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) == 1) {
            $ob = $db->sql_fetch_object($res);

            if (($ob->key_auth == $data[1]) && !empty($ob->key_auth)) {
                $type = "success";
                $title = "New user account confirmed !";
                $msg = "Your registration is now complete !";

                $sql = "UPDATE user_main SET is_valid = 1, key_auth ='',id_group=2  WHERE email = '" . $db->sql_real_escape_string($data[0]) . "'";
                $db->sql_query($sql);

                function login($login, $password) {
                    $_POST['user_main']['login'] = $login;
                    $_POST['user_main']['password'] = $password;
                    $_SERVER['REQUEST_METHOD'] = "POST";

                    $ret = $this->di['auth']->authenticate();
                    $id_user = $this->di['auth']->getIdUserTriingLogin();

                    if (!empty($id_user)) {
                        $this->log($id_user, $ret);
                    }
                }

                $this->login($ob->login, $ob->password);
            } else {
                $type = "error";
                $title = "Error";
                $msg = "This confirmation is not valid anymore !";
            }
        } else {
            $type = "error";
            $title = "Error";
            $msg = "This account doesn't exist anymore !";
        }


        $title = I18n::getTranslation(__($title));
        $msg = I18n::getTranslation(__($msg));

        //unset($_SESSION['msg_flash']);
        set_flash($type, $title, $msg);


        header("location: " . LINK . ROUTE_DEFAULT);
        exit;
    }

    private function log($id_user, $success) {

        $db = Sgbd::sql(DB_DEFAULT);

        $data = array();
        $data['user_main_login']['id_user_main'] = $id_user;
        $data['user_main_login']['date'] = date("Y-m-d H:i:s");
        $data['user_main_login']['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $data['user_main_login']['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['user_main_login']['is_logged'] = (int) $success;

        if (!$gg = $db->sql_save($data)) {
            var_dump($success);
            debug($db->error);
            debug($data);
            die();
        }
    }

    public function profil($param) {

        $db = Sgbd::sql(DB_DEFAULT);
        $this->layout_name = "admin";

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (!empty($_POST['shoutbox']['text'])) {
                $data = array();
                $data['shoutbox'] = $_POST['shoutbox'];
                $data['shoutbox']['id_user_main'] = $user->id;
                $data['shoutbox']['id_user_main__box'] = $db->sql_real_escape_string($param[0]);
                $data['shoutbox']['date'] = date("Y-m-d H:i:s");
                $data['shoutbox']['id_history_etat'] = 1;

                if (!$db->sql_save($data)) {
                    debug($db->sql_error());
                    die("problem to save msg en shoutbox");
                }

                header("location: " . LINK . "user/profil/" . $param[0]);
                exit;
            }
        }
        $this->data['id'] = $db->sql_real_escape_string($param[0]);

        $sql = "SELECT  a.id_user_main, a.date, a.text, name,firstname, c.iso, b.id
			FROM shoutbox a
			INNER JOIN user_main b ON a.id_user_main = b.id
			INNER JOIN geolocalisation_country c ON c.id = b.id_geolocalisation_country
			WHERE a.id_history_etat=1
			AND id_user_main__box = " . $db->sql_real_escape_string($param[0]) . "
			ORDER BY a.date asc";

        $res = $db->sql_query($sql);
        $this->data['shoutbox'] = $db->sql_to_array($res);

        $sql = "SELECT * FROM user_main a
		INNER JOIN geolocalisation_country b ON a.id_geolocalisation_country = b.id
		INNER JOIN geolocalisation_city c ON a.id_geolocalisation_city = c.id
		
where a.id ='" . $db->sql_real_escape_string($param[0]) . "'";
        $res = $db->sql_query($sql);

        $user = $db->sql_to_array($res);
        $this->data['user'] = $user[0];

        $this->title = $this->data['user']['firstname'] . ' ' . $this->data['user']['name'];
        $this->ariane = "> <a href=\"" . LINK . "user/\">" . __("Members") . "</a> > " . $this->title;

        $this->data['name'] = $this->title;

        $sql = "SELECT title, id, point FROM history_action WHERE point !=0 ORDER BY title";
        $res = $db->sql_query($sql);

        $this->data['actions'] = $db->sql_to_array($res);

        $sql = "SELECT d.id, COUNT( d.point ) AS points, point
FROM history_main c
LEFT JOIN history_action d ON d.id = c.id_history_action
WHERE c.id_user_main =  '" . $db->sql_real_escape_string($param[0]) . "' and d.point != 0
GROUP BY d.id";
        $res = $db->sql_query($sql);
        $tab_point = $db->sql_to_array($res);


        foreach ($tab_point as $line) {
            $this->data['points'][$line['id']] = $line['points'];
        }

        $this->set("data", $this->data);
    }

    function user_main() {
        /*
          [path] => en/user/city/
          [q] => paris
          [limit] => 10
          [timestamp] => 1297207840432
          [lg] => en
          [url] => user/city/
         */

        $this->layout_name = false;


        $sql = "SELECT name, firstname, id FROM user_main WHERE
			firstname != 'BOT'
			AND id_group > 1
			AND firstname LIKE '" . $db->sql_real_escape_string($_GET['q']) . "%' 
			OR name LIKE '" . $db->sql_real_escape_string($_GET['q']) . "%' 
		ORDER BY firstname,name LIMIT 0,100";
        $res = $db->sql_query($sql);
        $data = $db->sql_to_array($res);
        $this->set("data", $data);
    }

    function settings($param) {

        $this->data['request'] = $param[0];

        if (!empty($param[1])) {
            $this->data['item'] = $param[1];
        } else {
            $this->data['item'] = '';
        }

        $this->layout_name = "admin";

        $sql = "SELECT * FROM user_main a
			LEFT JOIN user_settings b ON a.id = b.id_user_main
			WHERE a.id='" . $user->id . "'";

        $res = $db->sql_query($sql);
        $data = $db->sql_to_array($res);
        $this->data['user'] = $data[0];

        $this->title = __("Settings");
        $this->ariane = "> <a href=\"" . LINK . "user/\">" . __("Members") . "</a> > "
                . '<a href="' . LINK . 'user/' . $user->id . '">' . $this->data['user']['firstname'] . ' ' . $this->data['user']['name'] . '</a>'
                . ' > '
                . $this->title;

        switch ($this->data['request']) {
            case 'main':

                break;
        }


        $this->set("data", $this->data);
    }

    function photo($param) {

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            
        }
    }

    private function get_new_mail() {


        $sql = "SELECT count(1) as cpt FROM mailbox_main
			WHERE id_user_main__box = '" . $user->id . "'
				AND id_user_main__to = '" . $user->id . "'
					AND id_mailbox_etat =2
					AND id_history_etat = 1";

        $res = $db->sql_query($sql);
        $data = $db->sql_to_array($res);
        return $data[0]["cpt"];
    }

    function send_confirmation() {

        include_once(LIBRARY . "Glial/user/user.php");

        glial\user::get_user_not_confirmed();

        exit;
    }

    function connection() {
        $this->di['js']->addJavascript(array("jquery-latest.min.js"));


        $this->title = __("Log on");
        $this->ariane = "> <a href=\"" . LINK . "user/\">" . __("Members") . "</a> > " . $this->title;


        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            if ($this->di['auth']->authenticate()) {


                $id_user = $this->di['auth']->getIdUserTriingLogin();

                if (!empty($id_user)) {
                    $this->log($id_user, true);
                }

                $title = I18n::getTranslation(__("Login successful !"));
                $msg = I18n::getTranslation(__("Congratulations, you have successfully logged in."));

                set_flash("success", $title, $msg);
                header("Location: " . LINK . ROUTE_DEFAULT);
                exit;
            } else {

                $id_user = $this->di['auth']->getIdUserTriingLogin();

                if (!empty($id_user)) {
                    $this->log($id_user, false);
                }


                $msg = I18n::getTranslation(__("Your login information was incorrect. Please try again."));
                $title = I18n::getTranslation(__("Invalid login / password !"));

                set_flash("error", $title, $msg);
            }

            header("Location: " . LINK . "user/connection/");
            exit;
        }
    }

    function logout() {
        $this->di['auth']->logout();

        header("Location: " . LINK . "user/connection/");
        exit;
    }

    function updateGroup() {

        $roles = $this->di['acl']->getAlias();
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "DELETE FROM `group`";
        $db->sql_query($sql);

        foreach ($roles as $id => $role) {
            $sql = "INSERT INTO `group` (id, name) VALUES ('" . $id . "','" . $db->sql_real_escape_string($role) . "');";

            $db->sql_query($sql);
        }

        //$db->sql_multi_query($sql);
    }

    private function login($login, $password) {
        $_POST['user_main']['login'] = $login;
        $_POST['user_main']['password'] = $password;
        $_SERVER['REQUEST_METHOD'] = "POST";

        $ret = $this->di['auth']->authenticate();
        $id_user = $this->di['auth']->getIdUserTriingLogin();

        if (!empty($id_user)) {
            $this->log($id_user, $ret);
        }
    }

    public function update_idgroup() {
        $this->layout = false;
        $this->view = false;


        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            $db = Sgbd::sql(DB_DEFAULT);

            foreach ($_POST['user_main'] as $id_user_main => $value) {

                $user_main = [];
                $user_main['user_main']['id'] = $id_user_main;
                $user_main['user_main']['id_group'] = $value['id_group'];

                $yes = $db->sql_save($user_main);

                if (!$yes) {

                    $msg = I18n::getTranslation(__("Impossible to update the group for these users !") . $extra);
                    $title = I18n::getTranslation(__("Error"));
                    set_flash("error", $title, $msg);

                    header('location: ' . LINK . "user/index");
                }
            }

            $msg = I18n::getTranslation(__("The group of these users has been updated") . $extra);
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);


            header('location: ' . LINK . 'user/index/');
        }
    }

}
