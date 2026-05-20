<?php

namespace App\Controller;

use App\Library\Compare\SchemaCompareEngine;
use App\Library\Security\CompareMainSelection;
use App\Library\SelectorOptions;
use Glial\I18n\I18n;
use Glial\Synapse\Controller;

class Compare extends Controller
{
    use \App\Library\Filter;

    public function index($params)
    {
        $this->title = __("Compare");

        $indexRequest = self::evaluateIndexRequest($_GET, $_SERVER);
        if ($indexRequest['status'] !== 200) {
            $this->view = false;
            $this->layout_name = false;
            self::sendCompareIndexError($indexRequest['status'], $indexRequest['body'], $indexRequest['headers']);
            return;
        }

        $selection = $indexRequest['selection'];
        CompareMainSelection::applyToGet($selection);
        $selectionIsValid = true;

        if (CompareMainSelection::isComplete($selection)) {
            $out = $this->compareEngine()->checkConfig(
                $selection[CompareMainSelection::SERVER_ORIGINAL],
                $selection[CompareMainSelection::DATABASE_ORIGINAL],
                $selection[CompareMainSelection::SERVER_COMPARE],
                $selection[CompareMainSelection::DATABASE_COMPARE]
            );

            if ($out !== true) {
                $extra = "";
                foreach ($out as $msg) {
                    $extra .= "<br />" . __($msg);
                }

                $msg = I18n::getTranslation(__("Please correct your paramaters !") . $extra);
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);
                $selectionIsValid = false;
            }
        }

        $this->di['js']->addJavascript([
            "jquery-latest.min.js",
            "jquery.browser.min.js",
            "jquery.autocomplete.min.js",
            "bootstrap-select.min.js",
            "compare/index.js",
        ]);

        $data['listdb1'] = [];
        if ($selectionIsValid && $selection[CompareMainSelection::SERVER_ORIGINAL] !== null) {
            $select1 = $this->getDatabaseByServer([$selection[CompareMainSelection::SERVER_ORIGINAL]]);
            $data['listdb1'] = $select1['databases'];
        }

        $data['listdb2'] = [];
        if ($selectionIsValid && $selection[CompareMainSelection::SERVER_COMPARE] !== null) {
            $select1 = $this->getDatabaseByServer([$selection[CompareMainSelection::SERVER_COMPARE]]);
            $data['listdb2'] = $select1['databases'];
        }

        $data['display'] = false;

        if ($selectionIsValid && count($data['listdb2']) !== 0 && count($data['listdb1']) !== 0) {
            if ($selection[CompareMainSelection::DATABASE_ORIGINAL] !== null && $selection[CompareMainSelection::DATABASE_COMPARE] !== null) {
                $data['resultat'] = $this->compareEngine()->analyse(
                    $selection[CompareMainSelection::SERVER_ORIGINAL],
                    $selection[CompareMainSelection::DATABASE_ORIGINAL],
                    $selection[CompareMainSelection::SERVER_COMPARE],
                    $selection[CompareMainSelection::DATABASE_COMPARE]
                );

                $data['display'] = true;

                $this->di['log']->warning(
                    '[Compare] ' . $selection[CompareMainSelection::SERVER_ORIGINAL] . ":" .
                    $selection[CompareMainSelection::DATABASE_ORIGINAL] . " vs " .
                    $selection[CompareMainSelection::SERVER_COMPARE] . ":" .
                    $selection[CompareMainSelection::DATABASE_COMPARE] . "(" . $_SERVER["REMOTE_ADDR"] . ")"
                );
            }
        }

        $this->set('data', $data);
    }

    public static function evaluateIndexRequest(array $get, array $server): array
    {
        return CompareMainSelection::evaluate($get, $server);
    }

    public function menu($param)
    {
        $data['menu']['TABLE']['name'] = __("Tables");
        $data['menu']['TABLE']['icone'] = '<i class="fa fa-table"></i>';
        $data['menu']['VIEW']['name'] = __("Views");
        $data['menu']['VIEW']['icone'] = '<i class="fa fa-eye"></i>';
        $data['menu']['TRIGGER']['name'] = __("Triggers");
        $data['menu']['TRIGGER']['icone'] = '<i class="fa fa-random"></i>';
        $data['menu']['FUNCTION']['name'] = __("Functions");
        $data['menu']['FUNCTION']['icone'] = '<i class="fa fa-cog"></i>';
        $data['menu']['PROCEDURE']['name'] = __("Routines");
        $data['menu']['PROCEDURE']['icone'] = '<i class="fa fa-cogs"></i>';
        $data['menu']['EVENT']['name'] = __("Events");
        $data['menu']['EVENT']['icone'] = '<i class="fa fa-calendar"></i>';

        if (empty($_GET['menu'])) {
            $_GET['menu'] = "TABLE";
        }

        $tmp = json_decode(json_encode($param[0]), true);

        foreach ($tmp['resultat'] as $key => $tab) {
            $data['menu'][$key]['count'] = count($tab);
            $data['menu'][$key]['url'] = LINK . 'compare/index/' . $this->generateGet() . 'menu:' . $key;
        }

        $this->set('data', $data);
        return $data;
    }

    public function generateGet(): string
    {
        $selection = CompareMainSelection::normalize($_GET);
        if ($selection === null) {
            return "";
        }

        $route = CompareMainSelection::toRoute($selection);
        return $route === "" ? "" : $route . "/";
    }

    public function getObjectDiff($param): void
    {
        $indexRequest = self::evaluateIndexRequest($_GET, $_SERVER);
        if ($indexRequest['status'] !== 200 || !CompareMainSelection::isComplete($indexRequest['selection'])) {
            $this->view = false;
            $this->layout_name = false;
            self::sendCompareIndexError(
                $indexRequest['status'] === 200 ? 400 : $indexRequest['status'],
                $indexRequest['status'] === 200 ? 'Invalid compare selection' : $indexRequest['body'],
                $indexRequest['headers']
            );
            return;
        }

        $selection = $indexRequest['selection'];
        $data = json_decode(json_encode($param[0]), true);
        $menu = (string)($_GET['menu'] ?? 'TABLE');
        if (!isset($data['resultat'][$menu]) || !is_array($data['resultat'][$menu])) {
            $this->view = false;
            $this->layout_name = false;
            self::sendCompareIndexError(400, 'Invalid compare menu');
            return;
        }

        $data['resultat'][$menu] = $this->compareEngine()->compareDiffForMenu(
            $selection[CompareMainSelection::SERVER_ORIGINAL],
            $selection[CompareMainSelection::SERVER_COMPARE],
            $menu,
            $selection[CompareMainSelection::DATABASE_ORIGINAL],
            $selection[CompareMainSelection::DATABASE_COMPARE],
            $data['resultat'][$menu]
        );

        $this->set('data', $data);
        $this->set('menu', $param[1] ?? null);
    }

    public function getDatabaseByServer($param)
    {
        if (IS_AJAX) {
            $this->layout_name = false;
        }

        $idMysqlServer = $param[0];
        $dbToGetDb = $this->compareEngine()->getDbLinkFromId($idMysqlServer);

        $data['databases'] = SelectorOptions::databaseNamesFromConnection($dbToGetDb);

        $this->set("data", $data);
        return $data;
    }

    private static function sendCompareIndexError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }

        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

    private function compareEngine(): SchemaCompareEngine
    {
        return new SchemaCompareEngine();
    }
}
