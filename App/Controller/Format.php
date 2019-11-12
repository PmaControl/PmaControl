<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;

class Format extends Controller {

    public function index($param) {

        $this->title = '<i class="fa fa-wpforms" aria-hidden="true"></i> ' . __("Format SQL");



        if ($_SERVER['REQUEST_METHOD'] === "POST") {


            $md5 = md5($_POST['sql']);
            $_SESSION[$md5] = $_POST['sql'];

            header("location: " . LINK .$this->getClass(). "/" . __FUNCTION__ . "/" . $md5);
        }

        if (!empty($param[0])) {


            $data['sql'] = $_SESSION[$param[0]];

            $queries = \SqlFormatter::splitQuery($_SESSION[$param[0]]);

            foreach ($queries as $query) {
                $data['sql_formated'][] = \SqlFormatter::format($query);
            }

            $this->set('data', $data);
        }
    }

    private function base64url_encode($data) {
        return strtr(base64_encode($val), '+/=', '-_,');
        //return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64url_decode($data) {
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