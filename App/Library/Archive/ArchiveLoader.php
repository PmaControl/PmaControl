<?php

declare(strict_types=1);

namespace App\Library\Archive;

use App\Controller\Archives;
use App\Library\Debug;
use Glial\I18n\I18n;
use Glial\Sgbd\Sgbd;

final class ArchiveLoader
{
    public static function dispatch(Archives $controller, array $payload): void
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $idMysqlServer = (int) $payload['id_mysql_server'];
        $database = (string) $payload['database'];
        $idCleanerMain = (int) $payload['id_cleaner_main'];

        $archiveLoad = array();
        $archiveLoad['archive_load']['id_cleaner_main'] = $idCleanerMain;
        $archiveLoad['archive_load']['id_mysql_server'] = $idMysqlServer;
        $archiveLoad['archive_load']['database']        = $database;
        $archiveLoad['archive_load']['date_start']      = date('Y-m-d H:i:s');
        $archiveLoad['archive_load']['progression']     = 0;
        $archiveLoad['archive_load']['duration']        = 0;
        $archiveLoad['archive_load']['pid']             = 0;
        $archiveLoad['archive_load']['status']          = "NOT_STARTED";
        $archiveLoad['archive_load']['id_user_main']    = 1;

        $idArchiveLoad = $db->sql_save($archiveLoad);

        if ($idArchiveLoad) {
            if (IS_CLI) {
                $controller->load(array($idArchiveLoad));
                $pid = getmypid();
            } else {
                $cmd = self::buildLoadCommand($idArchiveLoad, $idCleanerMain, $database);

                Debug::debug($cmd);

                $pid = shell_exec($cmd);
            }

            $db = Sgbd::sql(DB_DEFAULT);
            $archiveLoad = array();
            $archiveLoad['archive_load']['pid'] = (int) $pid;
            $archiveLoad['archive_load']['id']  = $idArchiveLoad;

            Debug::debug($archiveLoad);

            $db->sql_save($archiveLoad);

            $msg   = I18n::getTranslation(__("The loading on database is currently in progress ..."));
            $title = I18n::getTranslation(__("Loading"));
            set_flash("success", $title, $msg);
            return;
        }

        debug($db->sql_error());
        debug($archiveLoad);

        $msg   = I18n::getTranslation(__("Impossible to save : ")."'".print_r($db->sql_error())."'");
        $title = I18n::getTranslation(__("Loading"));
        set_flash("error", $title, $msg);
    }

    private static function buildLoadCommand(int $idArchiveLoad, int $idCleanerMain, string $database): string
    {
        $command = array(
            PHP_BINARY,
            GLIAL_INDEX,
            'Archives',
            'load',
            (string) $idArchiveLoad,
        );

        return implode(' ', array_map('escapeshellarg', $command))
            .' >> '
            .escapeshellarg(TMP.'archive_'.$idCleanerMain.'_'.$database.'.sql')
            .' & echo $!';
    }
}
