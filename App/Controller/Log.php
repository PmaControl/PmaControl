<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Debug;
use App\Library\Security\CsrfGuard;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

/**
 * Class responsible for log workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Log extends Controller {


    const LOG_DIRECTORY=TMP."log/";

    const SIZE_MAX = 30 * 1024 * 1024;

    const EXT_OLD = ".1";
    const EXT_LOG = ".log";

/**
 * Stores `$logger` for logger.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $logger;

/**
 * Render log state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/log/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index() {

        // http://eonasdan.github.io/bootstrap-datetimepicker/  <= date time picker

        $this->di['js']->addJavascript(array('moment.js', 'bootstrap-datetimepicker.js'));

        $this->di['js']->code_javascript(
                "$(function () {
            $('#datetimepicker1').datetimepicker({sideBySide:true,format:'YYYY-MM-DD HH:mm:ss'});
        });

        $(function () {
            $('#datetimepicker2').datetimepicker({sideBySide:true,format:'YYYY-MM-DD HH:mm:ss'});
        });

        ");

        $indexRequest = self::evaluateIndexRequest($_GET, $_SERVER);
        if ($indexRequest['status'] === 405) {
            $this->view = false;
            $this->layout_name = false;
            self::sendIndexError($indexRequest['status'], $indexRequest['body'], $indexRequest['headers']);
            return;
        }

        $data = array();
        $data['log'] = array();
        $data['filters'] = $indexRequest['filters'];

        if (!headers_sent()) {
            header('Cache-Control: private, no-store');
        }

        if ($indexRequest['filters']['ready']) {
            $data['log'] = Extraction::display(
                $indexRequest['filters']['ts_variables'],
                $indexRequest['filters']['mysql_server_ids'],
                array($indexRequest['filters']['date_start'], $indexRequest['filters']['date_end']),
                true
            );
        }


        $this->set('data', $data);
    }

    public static function evaluateIndexRequest(array $get, array $server): array
    {
        if (CsrfGuard::isPost($server)) {
            return self::buildIndexOutcome(405, 'Method Not Allowed', ['Allow' => 'GET']);
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'filters' => self::normalizeIndexFilters($get),
        ];
    }

    public static function normalizeIndexFilters(array $get): array
    {
        $dateStart = self::normalizeDateTime($get['ts']['date_start'] ?? null);
        $dateEnd = self::normalizeDateTime($get['ts']['date_end'] ?? null);
        $mysqlServerIds = self::normalizePositiveIds($get['mysql_server']['id'] ?? null);
        $tsVariables = self::normalizeTsVariables($get['ts_variable']['id'] ?? null);

        return [
            'mysql_server_ids' => $mysqlServerIds,
            'ts_variables' => $tsVariables,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'ready' => $mysqlServerIds !== [] && $tsVariables !== [] && $dateStart !== '' && $dateEnd !== '',
        ];
    }

    public static function normalizePositiveIds($selection): array
    {
        $ids = [];
        foreach (self::flattenSelection($selection) as $value) {
            if (!ctype_digit($value) || (int) $value < 1) {
                continue;
            }

            $id = (int) $value;
            if (!in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        return array_slice($ids, 0, 200);
    }

    public static function normalizeTsVariables($selection): array
    {
        $variables = [];
        foreach (self::flattenSelection($selection) as $value) {
            $variable = strtolower($value);
            if (!preg_match('/\A[a-z0-9_]+(?:::[a-z0-9_]+)?\z/', $variable)) {
                continue;
            }

            if (!in_array($variable, $variables, true)) {
                $variables[] = $variable;
            }
        }

        return array_slice($variables, 0, 200);
    }

    private static function flattenSelection($selection): array
    {
        if ($selection === null) {
            return [];
        }

        if (is_array($selection)) {
            $values = [];
            foreach ($selection as $value) {
                $values = array_merge($values, self::flattenSelection($value));
            }
            return $values;
        }

        if (!is_scalar($selection)) {
            return [];
        }

        $selection = trim((string) $selection);
        if ($selection === '') {
            return [];
        }

        if ($selection[0] === '[' && substr($selection, -1) === ']') {
            $selection = substr($selection, 1, -1);
        }

        return array_values(array_filter(array_map('trim', explode(',', $selection)), static fn($value): bool => $value !== ''));
    }

    private static function normalizeDateTime($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        $date = trim((string) $value);
        $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $date);
        if ($dateTime === false || $dateTime->format('Y-m-d H:i:s') !== $date) {
            return '';
        }

        return $date;
    }

    private static function buildIndexOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'filters' => self::normalizeIndexFilters([]),
        ];
    }

    private static function sendIndexError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $message;
    }

/**
 * Prepare log state through `before`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for before.
 * @phpstan-return void
 * @psalm-return void
 * @see self::before()
 * @example /fr/log/before
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function before($param)
    {
        $monolog       = new Logger("Aspirateur");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

/**
 * Handle log state through `rotate`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for rotate.
 * @phpstan-return void
 * @psalm-return void
 * @see self::rotate()
 * @example /fr/log/rotate
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function rotate($param)
    {
        Debug::parseDebug($param);

        $files = glob(self::LOG_DIRECTORY."*".self::EXT_LOG);
        foreach($files as $file)
        {
            $file_size = filesize($file);
            if ($file_size > self::SIZE_MAX)
            {
                $previous_file = $file.self::EXT_OLD;

                $this->logger->notice("log rotate on file : $file [".self::formatFileSize($file_size)."]");

                if (file_exists($previous_file)){
                    unlink($previous_file);
                    Debug::debug("unlink $previous_file");
                }
                
                Debug::debug("rename $file TO $previous_file");
                rename($file, $file.self::EXT_OLD);
            }
        }
    }

/**
 * Handle log state through `formatFileSize`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $size Input value for `size`.
 * @phpstan-param mixed $size
 * @psalm-param mixed $size
 * @return mixed Returned value for formatFileSize.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::formatFileSize()
 * @example /fr/log/formatFileSize
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function formatFileSize($size) {
        if ($size < 1024) {
            return $size . ' octets';
        } else if ($size < 1024 * 1024) {
            return round($size / 1024, 2) . ' Ko';  // Kilo-octets
        } else if ($size < 1024 * 1024 * 1024) {
            return round($size / (1024 * 1024), 2) . ' Mo';  // Méga-octets
        } else {
            return round($size / (1024 * 1024 * 1024), 2) . ' Go';  // Giga-octets
        }
    }
}
