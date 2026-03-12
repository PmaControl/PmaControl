<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;

/**
 * Class responsible for format workflows.
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
class Format extends Controller
{

/**
 * Render format state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/format/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param)
    {

        $this->title = '<i class="fa fa-wpforms" aria-hidden="true"></i> '.__("Format SQL");

        if ($_SERVER['REQUEST_METHOD'] === "POST") {


            $md5            = md5($_POST['sql']);
            $_SESSION[$md5] = $_POST['sql'];

            header("location: ".LINK.$this->getClass()."/".__FUNCTION__."/".$md5);
        }

        $data = array();

        if (!empty($param[0])) {

            if (!empty($_SESSION[$param[0]])) {
                $data['sql'] = $_SESSION[$param[0]];

                $data['$queries'] = \SqlFormatter::splitQuery($data['sql']);

                foreach ($data['$queries'] as $query) {
                    $data['sql_formated'][] = \SqlFormatter::format($query);
                }
            }
            
            $this->set('data', $data);
        }
    }

/**
 * Handle format state through `base64url_encode`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $data Input value for `data`.
 * @phpstan-param array<int|string,mixed> $data
 * @psalm-param array<int|string,mixed> $data
 * @return mixed Returned value for base64url_encode.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::base64url_encode()
 * @example /fr/format/base64url_encode
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function base64url_encode($data)
    {
        return strtr(base64_encode($val), '+/=', '-_,');
        //return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

/**
 * Handle format state through `base64url_decode`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $data Input value for `data`.
 * @phpstan-param array<int|string,mixed> $data
 * @psalm-param array<int|string,mixed> $data
 * @return mixed Returned value for base64url_decode.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::base64url_decode()
 * @example /fr/format/base64url_decode
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function base64url_decode($data)
    {
        return base64_decode(strtr($val, '-_,', '+/='));
        //return base64_decode(strtr($data, '-_', '+/').str_repeat('=', 3 - ( 3 + strlen($data)) % 4));
    }
}
/*
 * select if(`performance_schema`.`threads`.`PROCESSLIST_ID` is null,substring_index(`performance_schema`.`threads`.`NAME`,'/',-1),
 * concat(`performance_schema`.`threads`.`PROCESSLIST_USER`,'@',`performance_schema`.`threads`.`PROCESSLIST_HOST`)) AS `user`,
 * sum(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`COUNT_STAR`) AS `total`,
 * `sys4`.`format_time`(sum(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`SUM_TIMER_WAIT`)) AS `total_latency`,
 * `sys4`.`format_time`(min(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`MIN_TIMER_WAIT`)) AS `min_latency`,
 * `sys4`.`format_time`(avg(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`AVG_TIMER_WAIT`)) AS `avg_latency`,
 * `sys4`.`format_time`(max(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`MAX_TIMER_WAIT`)) AS `max_latency`,
 * `performance_schema`.`events_waits_summary_by_thread_by_event_name`.`THREAD_ID` AS `thread_id`,
 * `performance_schema`.`threads`.`PROCESSLIST_ID` AS `processlist_id`
 * from (`performance_schema`.`events_waits_summary_by_thread_by_event_name`
 * left join `performance_schema`.`threads` on(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`THREAD_ID` = `performance_schema`.`threads`.`THREAD_ID`))
 * where `performance_schema`.`events_waits_summary_by_thread_by_event_name`.`EVENT_NAME` like 'wait/io/file/%'
 * and `performance_schema`.`events_waits_summary_by_thread_by_event_name`.`SUM_TIMER_WAIT` > 0
 * group by `performance_schema`.`events_waits_summary_by_thread_by_event_name`.`THREAD_ID`,`performance_schema`.`threads`.`PROCESSLIST_ID`,
 * if(`performance_schema`.`threads`.`PROCESSLIST_ID` is null,substring_index(`performance_schema`.`threads`.`NAME`,'/',-1),
 * concat(`performance_schema`.`threads`.`PROCESSLIST_USER`,'@',`performance_schema`.`threads`.`PROCESSLIST_HOST`))
 * order by sum(`performance_schema`.`events_waits_summary_by_thread_by_event_name`.`SUM_TIMER_WAIT`) desc
 */
