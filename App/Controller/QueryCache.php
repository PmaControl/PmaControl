<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use App\Library\Mysql;
use App\Library\Debug;

use App\Library\Extraction2;


/**
 * Class responsible for query cache workflows.
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
class QueryCache extends Controller {

/**
 * Render query cache state through `index`.
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
 * @example /fr/querycache/index
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

