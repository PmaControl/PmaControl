<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 *
 * 15% moins bien lors du chargement par rapport à une sauvegarde générer avec mysqldump
 * le temps de load peut être optimisé
 */

namespace App\Controller;

use \App\Library\Debug;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for deploy workflows.
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
class Deploy extends Controller
{

/**
 * Render deploy state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/deploy/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index()
    {
        $outcome = self::evaluateIndexRequest($_SERVER);
        if ($outcome['status'] !== 200) {
            $this->view = false;
            $this->layout_name = false;
            self::sendDeployError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $this->title = '<i class="fa fa-arrows-alt" aria-hidden="true"></i> '.__("Deploy");

        //$this->di['js']->addJavascript(array("bootstrap-tooltip.js"));
        $this->di['js']->code_javascript('$(function () {  $(\'[data-toggle="popover"]\').popover({trigger:"hover"}) });');
        $this->di['js']->code_javascript('
            $(\'[data-toggle="popover"]\').each(function(index, element) {
    var contentElementId = $(element).data().target;
    var contentHtml = $(contentElementId).html();
    $(element).popover({
        content: contentHtml,
        trigger:"hover",
        html:true
    });
});');
    }

    public static function evaluateIndexRequest(array $server): array
    {
        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        if ($method !== 'GET' && $method !== 'HEAD') {
            return self::buildIndexOutcome(405, 'Method Not Allowed', ['Allow' => 'GET, HEAD']);
        }

        return self::buildIndexOutcome(200, '');
    }

    private static function buildIndexOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
        ];
    }

    private static function sendDeployError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $message;
    }

/**
 * Handle deploy state through `execute`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for execute.
 * @phpstan-return void
 * @psalm-return void
 * @see self::execute()
 * @example /fr/deploy/execute
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function execute($param)
    {
        Debug::parseDebug($param);

        $execute = implode(" ", $param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT name, display_name, ip, port FROM mysql_server where is_available=1;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {


            echo "Connected to ".$ob->display_name." (".$ob->ip.":".$ob->port.")\n";
            $remote = Sgbd::sql($ob->name);

            $remote->sql_query("set sql_log_bin=0");
            $res2 = $remote->sql_query($execute);

            if ($res2) {
                if ($remote->sql_num_rows($res2) > 0) {
                    while ($arr = $remote->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                        print_r($arr);
                    }
                } else {
                    echo "SUCCESS\n";
                }
            } else {
                Debug::debug("We exited because we found one error", "[ERROR]");
                exit;
            }

            $remote->sql_close();
        }
    }
}
