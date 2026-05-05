<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 *
 * 15% moins bien lors du chargement par rapport à une sauvegarde générer avec mysqldump
 * le temps de load peut être optimisé
 */

namespace App\Controller;

use Glial\Synapse\Controller;
use App\Library\Debug;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for enum workflows.
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
class Enum extends Controller
{

/**
 * Render enum state through `index`.
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
 * @example /fr/enum/index
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

        $db = Sgbd::sql("hb01_maria_cart01"); //prod
        //$db = Sgbd::sql("preprod_maria_cart01_preprod_rdc");
        $db->sql_select_db("cart");

        $tables                           = array();
        $tables['cart']['cart_status'][1] = "new";
        $tables['cart']['cart_status'][2] = "payment_processing";
        $tables['cart']['cart_status'][3] = "processed";
        $tables['cart']['cart_status'][4] = "processing";
        $tables['cart']['cart_status'][5] = "refused";
        $tables['cart']['cart_status'][6] = "closed";
        $tables['cart']['cart_status'][7] = "expired";
        $tables['cart']['cart_status'][8] = "cancelled";


        $tables['delivery_method']['delivery_method_type'][1] = "home";
        $tables['delivery_method']['delivery_method_type'][2] = "home_delivery";
        $tables['delivery_method']['delivery_method_type'][3] = "home_relays";
        $tables['delivery_method']['delivery_method_type'][4] = "relays";

        foreach ($tables as $table => $fields) {
            foreach ($fields as $field => $rows) {
                foreach ($rows as $id => $libelle) {
                    $sql = "UPDATE `".$table."` SET `".$field."_id`=".$id." WHERE `".$field."`='".$libelle."' AND `".$field."_id` IS NULL LIMIT 1000;";
                    Debug::sql($sql);
                    do {
                        $db->sql_query($sql);
                        $affected = $db->sql_affected_rows();

                        Debug::debug($affected, "Nombre de lignes affectées");
                    } while ($affected != "0" && $affected != "-1");
                }
            }
        }
    }
}
