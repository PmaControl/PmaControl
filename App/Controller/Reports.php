<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\Reports\BusinessReportCatalog;
use Glial\Synapse\Controller;

class Reports extends Controller
{
    public function index($param = [])
    {
        Debug::parseDebug($param);

        $routeReport = isset($param[0]) && is_scalar($param[0]) ? (string)$param[0] : 'overview';
        $selectedReport = isset($_GET['report']) && is_scalar($_GET['report']) ? (string)$_GET['report'] : $routeReport;
        $payload = BusinessReportCatalog::build($selectedReport, $_GET);

        if (isset($this->di['js']) && is_object($this->di['js'])) {
            $this->di['js']->addJavascript([
                'chart-4.5.1.umd.min.js?v=' . (@filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'chart-4.5.1.umd.min.js') ?: time()),
                'Reports/reports.js?v=' . (@filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'Reports' . DS . 'reports.js') ?: time()),
            ]);
        }

        $this->title = 'Reports / ' . (string)($payload['selected_report']['title'] ?? 'Overview');
        $this->ariane = ' > Dashboard > Reports';
        $this->set('payload', $payload);
    }
}
