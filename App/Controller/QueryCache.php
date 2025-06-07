<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use App\Library\Mysql;
use App\Library\Debug;

use App\Library\Extraction2;


class QueryCache extends Controller {

    public function index($param)
    {
        Debug::parseDebug($param);

        $data= array();
        $data['cache'] = Extraction2::display(array("qcache_free_blocks","com_select", "qcache_free_memory", "qcache_hits", "qcache_inserts",
         "qcache_lowmem_prunes","qcache_not_cached","qcache_queries_in_cache","qcache_total_blocks"));

        $data['variable'] = Extraction2::display(array("query_cache_limit","query_cache_min_res_unit", "query_cache_size", "query_cache_type", 
         "query_cache_wlock_invalidate"));

        Debug::debug($data);




        $this->set('data', $data);
    }



}
