<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\Kpi\KpiServerDrilldown;
use Glial\Synapse\Controller;

class Kpi extends Controller
{
    public function server($param)
    {
        Debug::parseDebug($param);

        $serverId = isset($param[0]) && is_numeric($param[0]) ? (int)$param[0] : 0;
        if ($serverId <= 0) {
            set_flash('error', 'KPI', 'Invalid server id.');
            header('location: '.LINK.'server/main/');
            return;
        }

        $payload = KpiServerDrilldown::buildPayload($serverId);
        if (empty($payload['server'])) {
            set_flash('error', 'KPI', 'Server not found.');
            header('location: '.LINK.'server/main/');
            return;
        }

        $this->title = 'KPI server '.$serverId;
        $this->ariane = ' > KPI > Server '.$serverId;

        $chartVersion = @filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'chart-4.5.1.umd.min.js') ?: time();
        $kpiServerVersion = @filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'Kpi' . DS . 'server.js') ?: time();

        if (!empty($this->di['js']) && is_object($this->di['js'])) {
            $this->di['js']->addJavascript([
                'moment.js',
                'chartjs-adapter-moment.min.js',
                'chart-4.5.1.umd.min.js?v='.$chartVersion,
                'Kpi/server.js?v='.$kpiServerVersion,
            ]);

            $this->di['js']->code_javascript(
                'window.kpiServerCharts = '.json_encode([
                    'timeline' => $payload['timeline'],
                    'sparklines' => $payload['sparklines'],
                ], JSON_UNESCAPED_SLASHES).';'
            );
        }

        $this->set('data', ['payload' => $payload]);
    }
}
