<?php

use \glial\synapse\Controller;


class History extends Controller {

	public $module_group = "Administration";

	function index() {
		$this->title = __("Who we are?");
		$this->ariane = "> " . $this->title;
	}

	function admin_history() {
		$module['picture'] = "administration/Intranet.png";
		$module['name'] = __("History");
		$module['description'] = __("Control's activity on system");


		if (from() !== "administration.controller.php")
		{
			$this->title = __("Historical");
			$this->ariane = "> <a href=\"" . LINK . "administration/\">" . __("Administration") . "</a> > " . $this->title;
			$this->layout_name = "admin";


			$this->javascript = array("jquery-1.4.4.min.js");
			$this->di['js']->code_javascript[] = '$(document).ready(function()
		{
			$("#paradigm_all").click(function()
			{
				var checked_status = this.checked;
				$(".select_all").each(function()
				{
					this.checked = checked_status;
				});
			});
		});';

			$data = array();

			(empty($_GET['id_user_main']) ? $data['id_user_main'] = "" : $data['id_user_main'] = $_GET['id_user_main']);
			(empty($_GET['id_history_action']) ? $data['id_history_action'] = "" : $data['id_history_action'] = $_GET['id_history_action']);
			(empty($_GET['page'])) ? $data['page'] = 1 : $data['page'] = $_GET['page'];

			if ($_SERVER['REQUEST_METHOD'] == "POST")
			{
				if (!empty($_POST['id_history_etat']))
				{
					foreach ($_POST['id_history_etat'] as $key => $value)
					{
						history::revert_history(substr($key, 3));
						header("location: " . LINK . __CLASS__ . "/" . __FUNCTION__ . "/id_user_main:" . $data['id_user_main'] . '/id_history_action:' . $data['id_history_action'] . "/page:" . $data['page']);
						exit;
					}
				}


				if (!empty($_POST['filter']))
				{

					debug($_POST);

					(empty($_POST['history_main']['id_user_main'])) ? $data['id_user_main'] = -1 : $data['id_user_main'] = $_POST['history_main']['id_user_main'];
					(empty($_POST['history_main']['id_history_action'])) ? $data['id_history_action'] = -1 : $data['id_history_action'] = $_POST['history_main']['id_history_action'];

					header("location: " . LINK . __CLASS__ . "/" . __FUNCTION__ . "/id_user_main:" . $data['id_user_main'] . '/id_history_action:' . $data['id_history_action'] . "/page:" . $data['page']);
					exit;
				}
			}

			$start = HISTORY_ELEM_PER_PAGE * ( $data['page'] - 1 );
			$sql1 = "SELECT a.id, a.param, a.date, a.id_history_action,a.id_history_etat, a.type, c.title, c.point,b.name, a.id_user_main, b.firstname,d.iso , e.name as table_name, a.line ";
			$sql2 = "SELECT count(1) as cpt ";

			$sql = "
			FROM history_main a
			INNER JOIN user_main b ON a.id_user_main = b.id
			INNER JOIN history_action c ON c.id = a.id_history_action
			INNER JOIN history_table e ON e.id = a.id_history_table
			INNER JOIN geolocalisation_country d ON b.id_geolocalisation_country = d.id
			WHERE 1=1 ";

			(empty($data['id_history_action']) || $data['id_history_action'] == -1) ? : $sql .=" AND a.id_history_action = " . $data['id_history_action'] . " ";
			(empty($data['id_user_main']) || $data['id_user_main'] == -1) ? : $sql .=" AND a.id_user_main = " . $data['id_user_main'] . " ";
			$sql .= " ORDER BY a.id DESC";


			$res = $db->sql_query($sql2 . $sql);
			$data['count'] = $db->sql_to_array($res);
//*****************************pagination

			if ($data['count'][0]['cpt'] != 0)
			{
				include_once(LIB . "pagination.lib.php");

//url, curent page, nb item max , nombre de lignes, nombres de pages
				$pagination = new pagination(LINK . __CLASS__ . '/' . __FUNCTION__ . '/id_user_main:' . $data['id_user_main'] . '/id_history_action:' . $data['id_history_action'], $data['page'], $data['count'][0]['cpt'], HISTORY_ELEM_PER_PAGE, HISTORY_NB_PAGE_TO_DISPLAY_MAX);

				$tab = $pagination->get_sql_limit();
				$pagination->set_alignment("left");
				$pagination->set_invalid_page_number_text(__("Please input a valid page number!"));
				$pagination->set_pages_number_text(__("pages of"));
				$pagination->set_go_button_text(__("Go"));
				$pagination->set_first_page_text("« " . __("First"));
				$pagination->set_last_page_text(__("Last") . " »");
				$pagination->set_next_page_text("»");
				$pagination->set_prev_page_text("«");
				$data['pagination'] = $pagination->print_pagination();

				$limit = " LIMIT " . $tab[0] . "," . $tab[1] . " ";
				$data['i'] = $tab[0] + 1;
//*****************************pagination end

				$res = $db->sql_query($sql1 . $sql . $limit);
				$data['text'] = $db->sql_to_array($res);
			}

			$sql = "SELECT distinct a.id_user_main,b.name, b.firstname, d.iso FROM history_main a
		INNER JOIN user_main b ON a.id_user_main = b.id
		INNER JOIN geolocalisation_country d ON b.id_geolocalisation_country = d.id
		ORDER BY b.name, b.firstname";
			$res = $db->sql_query($sql);
			$i = 0;
			$data['user'][$i]['libelle'] = __("Select all");
			$data['user'][$i]['id'] = -1;
			$i++;
			while ($ob = $db->sql_fetch_object($res)) {

				$data['user'][$i]['libelle'] = $ob->firstname . " " . $ob->name;
				$data['user'][$i]['id'] = $ob->id_user_main;
				$i++;
			}


			$sql = "SELECT distinct a.id_history_action, b.title
		FROM history_main a
		INNER JOIN history_action b ON a.id_history_action = b.id
		ORDER BY b.title asc";
			$res = $db->sql_query($sql);
			$i = 0;
			$data['history_action'][$i]['libelle'] = __("Select all");
			$data['history_action'][$i]['id'] = -1;
			$i++;
			while ($ob = $db->sql_fetch_object($res)) {
				$data['history_action'][$i]['libelle'] = __($ob->title);
				$data['history_action'][$i]['id'] = $ob->id_history_action;
				$i++;
			}

			$sql = "SELECT count(1) as cpt FROM history_main";
			$res = $db->sql_query($sql);
			$data['cpt'] = $db->sql_to_array($res);

			$this->set("data", $data);
		}
		return $module;
	}

	function patch() {
		$sql = "SELECT * FROM species_pictures_photo";
		$res = $db->sql_query($res);
		While ($ob = $db->sql_fetch_array($res)) {
			$tab = History::compare("", $ob);
			$data['history_main']['date'] = $ob->date;
			$data['history_main']['data'] = json_encode($tab);
			$db->sql_save($data['history_main']);
		}
	}

	function get_table_with_history() {

		$sql = "select * from `history_table`";
		$res = $db->sql_query($sql);


		$data = array();

		while ($ob = $db->sql_fetch_object($res)) {
			$data[] = $ob->name;
		}

		return implode(",", $data);
	}

	function one_shoot_fix_param() {

		$this->layout_name = false;

		$sql = "SELECT id,param FROM history_main WHERE id > 223439";

		$res = $db->sql_query($sql);

		$i = 0;
		while ($ob = $db->sql_fetch_object($res)) {


			$sql = "UPDATE history_main SET param = '". $ob->param."' WHERE id ='".$ob->id."'";
			mysql_query($sql) or die (mysql_error());
			//echo $sql."\n";


			$i++;

			if ($i % 100 == 0)
			{
				echo "ligne : " . $i . "\n";
			}
		}

		die;
	}

	function insert($tables, $line, $param, $id_history_action, $id_user_main, $type_query) {

		$sql = "SELECT * FROM history_table WHERE name ='" . $tables . "'";
		$res = $db->sql_query($sql);
		$number = $db->sql_num_rows($res);
		if ($number == 0)
		{
			die("ERROR : history : " . $number);
		}
		$ob = $db->sql_fetch_object($res);

		$table = array();
		$table['history_main']['id_history_table'] = $ob->id;

		$table['history_main']['id_history_action'] = $id_history_action;
		$table['history_main']['id_history_etat'] = 1;
		$table['history_main']['line'] = $line;
		$table['history_main']['param'] = $param;
		$table['history_main']['date'] = date("Y-m-d H:i:s");
		$table['history_main']['type'] = $type_query;

		if (is_null($id_user_main))
		{
			$table['history_main']['id_user_main'] = $_COOKIE['IdUser'];
		}
		else
		{
			$table['history_main']['id_user_main'] = $id_user_main;
		}



		if (!$db->sql_save($table))
		{
			debug($db->sql_error());

			die("probleme enregistrement history");
		}
	}

	function compare($tab_from = array(), $tab_to) {
		$tab_update = array_intersect_key($tab_from, $tab_to);
		foreach ($tab_update as $key => $value)
		{
			if ($tab_from[$key] != $tab_to[$key])
			{
				$update[$key] = $tab_to[$key];
				$update2[$key] = $tab_from[$key];
			}
		}
		foreach ($tab_to as $key => $value)
		{
			if (!isset($tab_update[$key]))
			{
				$add[$key] = $value;
			}
		}
		foreach ($tab_from as $key => $value)
		{
			if (!isset($tab_update[$key]))
			{
				$del[$key] = $value;
			}
		}

		$finale = array();
		empty($add) ? "" : $finale['add'] = $add;
		empty($delete) ? "" : $finale['delete'] = $del;
		empty($update) ? "" : $finale['update'] = $update;

		empty($update2) ? "" : $finale2['update'] = $update2;

		$param['up'] = $finale;
		empty($finale2) ? $param['down'] = array() : $param['down'] = $finale2;

		return serialize($param);
	}

	function display_action($elem) {


//debug($elem);
		//2012-08-03 22:24:42 <= *********************************** last good


		if (!empty($elem['param']))
		{
			$data = unserialize($elem['param']);

			switch ($elem['id_history_action'])
			{

				case 5:
//debug($data);

					if (!empty($data['down']['update']['text']) && !empty($data['up']['update']['text']))
					{


//$text = '<img class="'.$data['down']['add']['source'].'" width="18" height="12" border="0" src="'.IMG.'main/1x1.png" /> ';
						$text = $data['down']['update']['text'] . "<br />";
						$text .= __("Was replaced by :") . "<br />";
						$text .= $data['up']['update']['text'] . "<br />";
//echo "<pre>".print_r($data)."<pre>";
					}

					break;

				case 6:

					if (!empty($data['up']['add']['source']))
					{
						$sql = "SELECT text FROM `translation_" . $data['up']['add']['source'] . "` WHERE `key` = '" . $data['up']['add']['key'] . "'";
						$res = $db->sql_query($sql);
						$ob = $db->sql_fetch_object($res);

						$table_lang = explode("_", $elem['table_name']);

						$text = '<img class="' . $data['up']['add']['source'] . '" width="18" height="12" border="0" src="' . IMG . 'main/1x1.png" /> ';
						$text .= $ob->text . '<hr />';
						$text .= __("Has been translated in:") . '<hr />';

						$text .= '<img class="' . $table_lang[1] . '" width="18" height="12" border="0" src="' . IMG . 'main/1x1.png" /> ';
						$text .= $data['up']['add']['text'];
					}
//debug($data);


					break;

				default:

					$text = __("This renderer is not implemented yet");

					break;
			}
		}

		(empty($text)) ? $text = "Problem on alimentation of history or wrong \$id_history_action" : $text = $text;

		return $text;
	}

	function revert_history($id_history_main) {
		$sql = "SELECT a.*,b.name FROM history_main a
			INNER JOIN history_table b ON a.id_history_table = b.id
			WHERE a.id=" . $id_history_main . "";
		$res = $db->sql_query($sql);

		$ob = $db->sql_fetch_object($res);


		$ob->id_history_etat = intval($ob->id_history_etat);

		if ($ob->id_history_etat === 1)
		{
			$data_to_take = "down";
		}
		else if ($ob->id_history_etat === 3)
		{
			$data_to_take = "up";
		}
		else
		{
			die("Error revert_history must be 1 or 3, 2 => impossible to revert value(" . $ob->id_history_etat . ")");
		}


		$data = unserialize($ob->param);

		switch ($ob->type)
		{
			case 'UPDATE':

				$filed = array();


				foreach ($data[$data_to_take]['update'] as $key => $value)
				{
					$field[] = $key . " = '" . $value . "'";
				}

				if (count($field) == 1)
				{
					$fields = $field[0];
				}
				else
				{
					$fields = implode(",", $field);
				}

				$sql = "UPDATE " . $ob->name . " SET " . $fields . " WHERE id =" . $ob->line . "";
				$db->sql_query($sql);

				break;


			case 'INSERT':
				($ob->id_history_etat == 1) ? $id_history_etat = 3 : $id_history_etat = 1;
				$sql = "UPDATE " . $ob->name . " SET id_history_etat = '" . $id_history_etat . "' WHERE id ='" . $ob->line . "'";
				$db->sql_query($sql);

				break;

			case 'DELETE':
				($ob->id_history_etat == 1) ? $id_history_etat = 1 : $id_history_etat = 3;
				$sql = "UPDATE " . $ob->name . " SET id_history_etat = " . $id_history_etat . " WHERE id =" . $ob->line . "";
				$db->sql_query($sql);

				break;
		}

		($ob->id_history_etat == 1) ? $type_query = 'DELETE' : $type_query = 'INSERT';
		$sql = "INSERT INTO history_main (`id_history_table`, `line` , `id_user_main`, `id_history_action`, `param`, `date`, `id_history_etat`, `type`)
			VALUES (" . $ob->id_history_table . ", " . $ob->line . ", " . $GLOBALS['_SITE']['IdUser'] . ", 7, '" . $ob->param . "', now(), 2, '" . $type_query . "'	)";

		$db->sql_query($sql);

		($ob->id_history_etat === 1) ? $id_history_etat = 3 : $id_history_etat = 1;

		$sql = "UPDATE history_main SET id_history_etat =" . $id_history_etat . " WHERE id=" . $id_history_main . "";
		$db->sql_query($sql);
	}

	static function get_img_type_query($type_query) {
		switch ($type_query)
		{
			case 'UPDATE':
				return '<img src="' . IMG . 'type_query/stock_3d_normals_double_sided.png" alt="UPDATE" title="UPDATE" height="16" width="16" />';
				break;

			case 'INSERT':
				return '<img src="' . IMG . 'type_query/bs_tblInsertRow.gif" alt="INSERT" title="INSERT" height="16" width="16" />';
				break;


			case 'DELETE':
				return '<img src="' . IMG . 'type_query/deleteRow.gif" alt="DELETE" title="DELETE" height="16" width="16" />';
				break;
		}
	}

	function one_shoot_fix_supplier() {



		$sql = "SELECT id,date_created FROM species_picture_in_wait";

		$res = $db->sql_query($sql);

		$i = 0;
		while ($ob = $db->sql_fetch_object($res)) {


			$table['history_main']['id_history_table'] = 9;
			$table['history_main']['id_history_action'] = 3;
			$table['history_main']['id_history_etat'] = 1;
			$table['history_main']['line'] = $ob->id;
			$table['history_main']['param'] = "";
			$table['history_main']['date'] = $ob->date_created;
			$table['history_main']['id_user_main'] = 9;
			$table['history_main']['type'] = "INSERT";

			if (!$db->sql_save($table))
			{
				debug($table);
				die("problem !");
			}

			$i++;

			if ($i % 1000 == 0)
			{
				echo "ligne : " . $i . "\n";
			}
		}



	}

}