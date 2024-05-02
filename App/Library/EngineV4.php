<?php
/*
Used for paramter of Engine v4
With common function
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;


class EngineV4
{
    const PATH_MD5 = TMP."md5/";
    const PATH_LOCK = TMP."lock/";

	const PATH_PID = self::PATH_LOCK;
    const PATH_PIVOT_FILE = TMP."tmp_file/";
	//const PATH_PIVOT_FILE = "/dev/shm/";


    const EXT_MD5 = 'md5';
    const EXT_LOCK = 'lock';
    const EXT_PID = 'pid';
    
    const FILE_MD5 = self::PATH_MD5.'{TS_FILE}'.self::SEPERATOR.'{ID_MYSQL_SERVER}.'.self::EXT_MD5;
    const FILE_LOCK = self::PATH_LOCK.'{TS_FILE}'.self::SEPERATOR.'{ID_MYSQL_SERVER}.'.self::EXT_LOCK;

    const FILE_PID = self::PATH_PID.'{TS_FILE}'.self::SEPERATOR.'{ID_MYSQL_SERVER}.'.self::EXT_PID;
    
    const SEPERATOR = "::";



	const FILE_MYSQL_VARIABLE = "mysql_global_variable";

	const FILE_MYSQL_DATABASE = "mysql_schemata";

	const FILE_TO_LISTEN = self::FILE_MYSQL_VARIABLE .",".self::FILE_MYSQL_DATABASE;


    static public function getFileLock($ts_file, $id_mysql_server, $const=self::FILE_LOCK)
    {
        return str_replace(array('{TS_FILE}','{ID_MYSQL_SERVER}'), array($ts_file, $id_mysql_server), $const);
    }

    static public function getFileMd5($ts_file, $id_mysql_server)
    {
        return self::getFileLock($ts_file, $id_mysql_server, self::FILE_MD5);
    }

    static public function getFilePid($ts_file, $id_mysql_server)
    {
        return self::getFileLock($ts_file, $id_mysql_server, self::FILE_PID);
    }

	static function getId($file_name)
	{
		$part2 = explode(self::SEPERATOR, $file_name)[1];
		return explode(".", $part2)[1];
	}


	static function cleanMd5(array $md5 )
	{
		$db = Sgbd::sql(DB_DEFAULT);
		
		$sql = "SELECT * from ts_file";
		$res = $db->sql_query($sql);

		$ts_file = array();
		while($ob = $db->sql_fetch_object($res))
		{
			$ts_file[$ob->id]= $ob->file_name;
		}

		//self::log()->notice('var : '.print_r($md5));

		sleep(1);
		foreach($md5 as $id_ts_file => $tab) {
			if (! empty($ts_file[$id_ts_file]))
			{
				$ts_file_name = $ts_file[$id_ts_file];
				self::log()->notice('ts_file_name : '.$ts_file_name);
				$regex = self::PATH_MD5.$ts_file_name.self::SEPERATOR."*".self::EXT_MD5;
				self::log()->notice('regex : '.$regex);
				foreach(glob($regex) as $file_md5)
				{
					self::log()->notice('to delete file : '.$file_md5);
					if (file_exists($file_md5)) {
						self::log()->notice('We deleted file : '.$file_md5);
						unlink($file_md5);
					}
				}
			}
		}
	}

	static public function log()
    {
        $monolog       = new Logger("EngineV4");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        return $monolog;
    }

}

/*
to rewrite in MW


-- Nombre de secondes entre deux TIMESTAMP
DROP FUNCTION IF EXISTS seconds_between;
DELIMITER $$
CREATE FUNCTION seconds_between (A TIMESTAMP, B TIMESTAMP) RETURNS INT
BEGIN
	DECLARE RETURN_VALUE INT;
	SET RETURN_VALUE = DATEDIFF(A, B) * 24 * 60 * 60 + (TIME_TO_SEC(A) - TIME_TO_SEC(B));
	RETURN IF(RETURN_VALUE > 0, RETURN_VALUE, RETURN_VALUE * -1);
END
$$

DELIMITER ;
--SELECT seconds_between('2008-11-06 02:10:13', '2008-11-07 02:11:15');
--86462


--Nombre de minutes entre deux TIMESTAMP (dépendante de seconds_between)

DROP FUNCTION IF EXISTS minutes_between;
DELIMITER $$
CREATE FUNCTION minutes_between (A TIMESTAMP, B TIMESTAMP) RETURNS INT
BEGIN
	DECLARE RETURN_VALUE INT;
	SET RETURN_VALUE = DATEDIFF(A, B) * 24 * 60 * 60 + (TIME_TO_SEC(A) - TIME_TO_SEC(B));
	RETURN (IF(RETURN_VALUE > 0, RETURN_VALUE, RETURN_VALUE * -1)) DIV 60;
END
$$

DELIMITER ;

--SELECT minutes_between('2008-11-06 02:10:13', '2008-11-07 02:11:15');
--1441



DROP FUNCTION IF EXISTS hours_between;
DELIMITER $$
CREATE FUNCTION hours_between (A TIMESTAMP, B TIMESTAMP) RETURNS INT
BEGIN
	RETURN minutes_between(A, B) DIV 60;
END
$$

DELIMITER ;


--Nombre de mois entre deux DATE
DROP FUNCTION IF EXISTS months_between;
DELIMITER $$
CREATE FUNCTION months_between (A DATE, B DATE) RETURNS INT
BEGIN
	DECLARE FIRST_DATE DATE;
	DECLARE LAST_DATE DATE;
	DECLARE DIFF INT;
	SELECT IF(A > B, A, B) INTO LAST_DATE;
	SELECT IF(A < B, A, B) INTO FIRST_DATE;
	SET DIFF = PERIOD_DIFF(year(LAST_DATE)*100 + month(LAST_DATE), year(FIRST_DATE)*100 + month(FIRST_DATE));
	SET DIFF = IF(DAY(FIRST_DATE) <= DAY(LAST_DATE), DIFF, DIFF - 1); 
	RETURN DIFF;
END
$$

DELIMITER ;





--Nombre d'années entre deux DATE (dépendante de months_between)
DROP FUNCTION IF EXISTS years_between;
DELIMITER $$
CREATE FUNCTION years_between (A DATE, B DATE) RETURNS INT
BEGIN
	RETURN months_between(A, B) DIV 12;
END
$$

DELIMITER ;



--Nombre d'années entre deux DATE (indépendante)
DROP FUNCTION IF EXISTS years_between;
DELIMITER $$
CREATE FUNCTION years_between (A DATE, B DATE) RETURNS INT
BEGIN
	DECLARE FIRST_DATE DATE;
	DECLARE LAST_DATE DATE;
	DECLARE DIFF INT;
	SELECT IF(A > B, A, B) INTO LAST_DATE;
	SELECT IF(A < B, A, B) INTO FIRST_DATE;
	SET DIFF = PERIOD_DIFF(year(LAST_DATE)*100 + month(LAST_DATE), year(FIRST_DATE)*100 + month(FIRST_DATE));
	SET DIFF = IF(DAY(FIRST_DATE) <= DAY(LAST_DATE), DIFF, DIFF - 1); 
	RETURN DIFF DIV 12;
END
$$

DELIMITER ;






*/