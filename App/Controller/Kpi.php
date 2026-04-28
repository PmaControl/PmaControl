<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\Security\CsrfGuard;
use App\Library\Kpi\KpiDaemonDrilldown;
use App\Library\Kpi\KpiProcessDrilldown;
use App\Library\Kpi\KpiServerDrilldown;
use Glial\Security\Csrf;
use Glial\Synapse\Controller;

class Kpi extends Controller
{
    public const KPI_PROCESS_KILL_CSRF_SCOPE = 'kpi.process.kill';

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

    public function daemon($param)
    {
        Debug::parseDebug($param);

        $daemonId = isset($param[0]) && is_numeric($param[0]) ? (int)$param[0] : 0;
        if ($daemonId <= 0) {
            set_flash('error', 'KPI', 'Invalid daemon id.');
            header('location: '.LINK.'daemon/index/');
            return;
        }

        $payload = KpiDaemonDrilldown::buildPayload($daemonId);
        if (empty($payload['daemon'])) {
            $warnings = $payload['warnings'] ?? [];
            $message = 'Daemon not found.';
            if (!in_array('Daemon not found.', $warnings, true) && !empty($warnings[0])) {
                $message = 'Daemon KPI unavailable: '.$warnings[0];
            }

            set_flash('error', 'KPI', $message);
            header('location: '.LINK.'daemon/index/');
            return;
        }

        $daemonName = $payload['daemon']['name'] ?? (string)$daemonId;
        $this->title = 'KPI daemon '.$daemonName;
        $this->ariane = ' > KPI > Daemon '.$daemonName;

        $chartVersion = @filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'chart-4.5.1.umd.min.js') ?: time();
        $kpiDaemonVersion = @filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'Kpi' . DS . 'daemon.js') ?: time();

        if (!empty($this->di['js']) && is_object($this->di['js'])) {
            $this->di['js']->addJavascript([
                'moment.js',
                'chartjs-adapter-moment.min.js',
                'chart-4.5.1.umd.min.js?v='.$chartVersion,
                'Kpi/daemon.js?v='.$kpiDaemonVersion,
            ]);

            $this->di['js']->code_javascript(
                'window.kpiDaemonCharts = '.json_encode([
                    'duration' => $payload['duration_series'],
                    'delay' => $payload['delay_series'],
                    'maxDelayMs' => $payload['max_delay_ms'],
                ], JSON_UNESCAPED_SLASHES).';'
            );
        }

        $this->set('data', ['payload' => $payload]);
    }

    public function process($param)
    {
        Debug::parseDebug($param);

        $pid = isset($param[0]) && is_numeric($param[0]) ? (int)$param[0] : 0;
        if (($param[1] ?? '') === 'kill') {
            $this->killProcess($pid);
            return;
        }

        $payload = KpiProcessDrilldown::buildPayload($pid);
        if (!empty($payload['not_found'])) {
            http_response_code(404);
        }

        $this->title = 'KPI process '.$pid;
        $this->ariane = ' > KPI > Process '.$pid;

        $this->set('data', [
            'payload' => $payload,
            'kill_csrf_field' => Csrf::DEFAULT_FIELD,
            'kill_csrf_token' => Csrf::issueToken($_SESSION, self::KPI_PROCESS_KILL_CSRF_SCOPE),
        ]);
    }

    public static function evaluateProcessKillRequest(array $post, array $server, array $session, array $params = []): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::KPI_PROCESS_KILL_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return [
                'status' => $guard['status'],
                'body' => $guard['body'],
                'headers' => $guard['headers'],
                'pid' => null,
                'start_time_ticks' => null,
            ];
        }

        $pidValue = (string)($params[0] ?? $post['pid'] ?? '');
        if (!ctype_digit($pidValue) || !KpiProcessDrilldown::validPid((int)$pidValue)) {
            return ['status' => 400, 'body' => 'Invalid process id', 'headers' => [], 'pid' => null, 'start_time_ticks' => null];
        }

        $postPid = (string)($post['pid'] ?? '');
        if ($postPid !== '' && $postPid !== $pidValue) {
            return ['status' => 400, 'body' => 'Process id mismatch', 'headers' => [], 'pid' => null, 'start_time_ticks' => null];
        }

        $startTimeTicks = (string)($post['start_time_ticks'] ?? '');
        if ($startTimeTicks === '' || !ctype_digit($startTimeTicks)) {
            return ['status' => 400, 'body' => 'Invalid process identity', 'headers' => [], 'pid' => null, 'start_time_ticks' => null];
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'pid' => (int)$pidValue,
            'start_time_ticks' => (int)$startTimeTicks,
        ];
    }

    private function killProcess(int $pid): void
    {
        $this->view = false;
        $this->layout_name = false;

        $outcome = self::evaluateProcessKillRequest($_POST, $_SERVER, $_SESSION, [$pid]);
        foreach ($outcome['headers'] as $name => $value) {
            header($name.': '.$value);
        }

        if ($outcome['status'] !== 200) {
            if (function_exists('set_flash')) {
                set_flash('error', 'KPI process', $outcome['body']);
            }
            header('Location: '.LINK.'kpi/process/'.$pid);
            return;
        }

        $payload = KpiProcessDrilldown::buildPayload((int)$outcome['pid']);
        if (($payload['context']['type'] ?? '') !== 'worker' || empty($payload['kill']['available'])) {
            if (function_exists('set_flash')) {
                set_flash('caution', 'KPI process', $payload['kill']['reason'] ?? 'Kill is not available for this process.');
            }
            header('Location: '.LINK.'kpi/process/'.(int)$outcome['pid']);
            return;
        }

        $result = Worker::killByPid((int)$outcome['pid'], (int)$outcome['start_time_ticks']);
        if (function_exists('set_flash')) {
            set_flash($result['killed'] ? 'success' : 'caution', 'KPI process', $result['message']);
        }

        header('Location: '.LINK.'kpi/process/'.(int)$outcome['pid']);
    }
}
