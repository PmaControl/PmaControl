#!/usr/bin/php
<?php

//ini_set("display_errors", 0);
//error_reporting(0);

function myloader($param)
{

    parse_str($param, $result);

    $result_cmp = $result;

    Debug::debug($result);


    if (empty($result['h'])) {
        echo '[ERROR] : The server is missing'."\n";
        exit(1);
    } else {
        unset($result_cmp['h']);
    }

    if (empty($result['u'])) {
        echo '[ERROR] : The user is missing'."\n";
        exit(2);
    } else {
        unset($result_cmp['u']);
    }

    if (empty($result['p'])) {

        echo '[ERROR] : The MySQL password is missing'."\n";
        exit(3);
    } else {
        unset($result_cmp['p']);
    }


    if (empty($result['d'])) {
        echo '[ERROR] : The path of directory to mydumper backup is missing'."\n";
        exit(4);
    } else {
        unset($result_cmp['d']);
    }


    //port par default 3307 in case we forgit to specify
    if (empty($result['P'])) {
        $result['P'] = 3307;
    } else {
        unset($result_cmp['P']);
    }



    if (count($result_cmp) > 0) {
        echo '[ERROR] : The fallowing parameters are unknows : '."\n";
        print_r($result_cmp);
        exit(5);
    }


    $directory_backup = $result['d'];



    if (!is_dir($directory_backup)) {
        throw new Exception('PMACTRL-914 : This directory "'.$directory_backup.'" is not valid');
    }



    $db_list  = glob($directory_backup."*-schema-create.sql");
    $db_elems = glob($directory_backup."*-schema-post.sql");


    Debug::debug($db_list, "List des DB à charger");



    $db_remote = new mysqli($result['h'], $result['u'], $result['p'], "mysql", $result['P']);

    if ($db_remote->connect_error) {
        echo Color::getColoredString("Impossible de se connecté la base de données : Connect Error (".$db_remote->connect_errno.") "
            .$db_remote->connect_error, "grey", 'red')."\n";
        exit(7);
    }


    foreach ($db_list as $file) {
        $elems = explode('-', $file);

        Debug::debug($elems);

        $split    = explode('/', $elems[0]);
        $database = end($split);

        $sql = "DROP DATABASE IF EXISTS `".$database."`;";
        $db_remote->query($sql);
        Debug::debug($sql);

        $sql = file_get_contents($file);
        $db_remote->query($sql);

        Debug::debug($sql);
    }


    $tables_schema = glob($directory_backup."*-schema.sql");

    Debug::debug($tables_schema);

    foreach ($tables_schema as $table_link) {

        $db_table = str_replace("-schema.sql", "", $table_link);
        $elems    = explode("/", $db_table);

        $tmp   = end($elems);
        $elem2 = explode(".", $tmp);

        $table_name = $elem2[1];
        $db_bame    = $elem2[0];

        echo $db_bame." - ".$table_name."\n";


        $db_remote->close();

        $db_remote = new mysqli($result['h'], $result['u'], $result['p'], "mysql", $result['P']);

        if ($db_remote->connect_error) {
            echo Color::getColoredString("Impossible de se connecté la base de données : Connect Error (".$db_remote->connect_errno.") "
                .$db_remote->connect_error, "grey", 'red')."\n";
            exit(7);
        }



        $db_remote->select_db($db_bame);


        $sql = '/*!40101 SET NAMES binary*/';
        $db_remote->query($sql);
        Debug::debug($sql);

        $sql = '/*!40014 SET FOREIGN_KEY_CHECKS=0*/';
        $db_remote->query($sql);
        Debug::debug($sql);



        //if mariaDB have to let binlog for MySQL
        $sql = 'SET sql_log_bin=0';
        $db_remote->query($sql);
        Debug::debug($sql);

        $sql = file_get_contents($table_link);


        $queries = SqlFormatter::splitQuery($sql);


        foreach ($queries as $query) {

            $db_remote->query($query);
            Debug::debug($query);
        }
    }



    $tables_data = glob($directory_backup."*.*.sql");

    foreach ($tables_data as $table_data) {

        if (substr($table_data, -11) === "-schema.sql") {
            continue;
        }

        $elems = explode("/", $table_data);

        $tmp   = end($elems);
        $elem2 = explode(".", $tmp);

        $table_name = $elem2[1];
        $db_name    = $elem2[0];

        $db_remote->select_db($db_name);

        $total = filesize($table_data);
        echo $table_data." : ".human_filesize($total, 2)."\n";

        $avancement  = 0;
        $percent_mem = 0;

        $handle = fopen($table_data, "r");
        if ($handle) {

            $query = "";

            while (($buffer = fgets($handle)) !== false) {
                $query .= $buffer;

                if (substr($buffer, -2) === ";\n") {
                    Debug::debug(substr(str_replace("\n", "", $query), 0, 80));

                    $db_remote->query($query);

                    $avancement += strlen($query);

                    unset($query);
                    $query = '';

                    $percent = round($avancement / $total * 100, 0);


                    if ($percent > $percent_mem) {

                        echo round($percent, 2)."%\t";
                        $percent_mem = $percent;
                    }
                }
            }
            if (!feof($handle)) {
                echo "Erreur: fgets() a échoué\n";
            }
            fclose($handle);

            echo "\n";
        }
    }
}

function human_filesize($bytes, $decimals = 2)
{
    $sz     = ' KMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
}

function splitFileTest($param)
{
    $file = $param[0];

    $this->split_file("/data/backup/sdp_prod/sdp.checkouts.sql");
}

function split_file($dump_file)
{
    /* Number of 'insert' statements per file */
    $max_lines_per_split = 50000;

    //$dump_file      = "dump.sql";

    $path_parts = pathinfo($dump_file);

    $split_file     = $path_parts['filename'].".%d.".$path_parts['extension'];
    //$split_file     = "dump-split-%d.sql";
    $dump_directory = $path_parts['dirname']."/";

    $line_count  = 0;
    $file_count  = 1;
    $total_lines = 0;

    $handle = @fopen($dump_file, "r");
    $buffer = "";

    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            /* Only read 'insert' statements */
            if (!preg_match("/insert/i", $line)) continue;
            $buffer .= $line;
            $line_count++;

            /* Copy buffer to the split file */
            if ($line_count >= $max_lines_per_split) {
                $file_name  = $dump_directory.sprintf($split_file, $file_count);
                $out_write  = fopen($file_name, "w+");
                fputs($out_write, $buffer);
                fclose($out_write);
                $buffer     = '';
                $line_count = 0;
                $file_count++;
            }
        }

        if ($buffer) {
            /* Write out the remaining buffer */
            $file_name = $dump_directory.sprintf($split_file, $file_count);
            $out_write = fopen($file_name, "w+");
            fputs($out_write, $buffer);
            fclose($out_write);
        }

        fclose($handle);
        echo "done.";
    }
}

class Debug
{
    static $debug       = false;
    static $count       = 0;
    static $microtime   = array();
    static $display_sql = true;

    static function parseDebug(& $param)
    {
        if (!empty($param)) {
            if (is_array($param)) {
                foreach ($param as $key => $elem) {
                    if ($elem == "--debug") {
                        self::$debug = true;
                        unset($param[$key]);
                    }
                }
            } else {
                if ($param == "--debug") {
                    self::$debug = true;
                }
            }
        }
    }

    function __construct()
    {

    }

    static function debug($string, $var = "")
    {
        if (self::$debug) {

            self::head();

            if (!empty($var)) {

                if (IS_CLI) {
                    echo Color::getColoredString($var, "grey", "blue")." ";
                } else {
                    echo $var."<br>";
                }
            }


            if (is_array($string) || is_object($string)) {


                if (IS_CLI) {

                    print_r($string);
                } else {
                    echo $var."<br>";
                    echo "<pre>";
                    print_r($string);
                    echo "</pre>";
                }
            } else {

                if (IS_CLI) {
                    echo trim($string)."\n";
                } else {
                    echo "<b>".trim(str_replace("\n", "<br>", $string))."</b><br>";
                }
            }
        }
    }

    static function head()
    {
        $calledFrom = debug_backtrace();
        $file       = pathinfo(substr(str_replace(__DIR__, '', $calledFrom[1]['file']), 1))["basename"];
        $line       = $calledFrom[1]['line'];

        $file = explode(".", $file)[0];

        echo "#".self::$count++."\t";
        echo $file.":".$line."\t";

        echo self::getDate();
    }

    static function getDate()
    {
        if (IS_CLI) {
            return Color::getColoredString("[".date('Y-m-d H:i:s')."]", "purple")." ";
        } else {
            return "[".date('Y-m-d H:i:s')."] ";
        }
    }
}
/*
 *  Example of use :

  // Test some basic printing with Colors class
  echo color::getColoredString("Testing Colors class, this is purple string on yellow background.", "purple", "yellow") . "\n";
 */

class Color
{
    /*
     * http://en.wikipedia.org/wiki/ANSI_escape_code
      Code	Effect	Note
      0	Reset / Normal	all attributes off
      1	Bold or increased intensity
      2	Faint (decreased intensity)	not widely supported
      3	Italic: on	not widely supported. Sometimes treated as inverse.
      4	Underline: Single
      5	Blink: Slow	less than 150 per minute
      6	Blink: Rapid	MS-DOS ANSI.SYS; 150 per minute or more; not widely supported
      7	Image: Negative	inverse or reverse; swap foreground and background (reverse video)
      8	Conceal	not widely supported
      9	Crossed-out	Characters legible, but marked for deletion. Not widely supported.
      10	Primary(default) font
      11–19	n-th alternate font	Select the n-th alternate font. 14 being the fourth alternate font, up to 19 being the 9th alternate font.
      20	Fraktur	hardly ever supported
      21	Bold: off or Underline: Double	bold off not widely supported, double underline hardly ever
      22	Normal color or intensity	neither bold nor faint
      23	Not italic, not Fraktur
      24	Underline: None	not singly or doubly underlined
      25	Blink: off
      26	Reserved
      27	Image: Positive
      28	Reveal	conceal off
      29	Not crossed out
      30–37	Set text color (foreground)	30 + x, where x is from the color table below
      38	Set xterm-256 text color (foreground)[dubious – discuss]	next arguments are 5;x where x is color index (0..255)
      39	Default text color (foreground)	implementation defined (according to standard)
      40–47	Set background color	40 + x, where x is from the color table below
      48	Set xterm-256 background color	next arguments are 5;x where x is color index (0..255)
      49	Default background color	implementation defined (according to standard)
      50	Reserved
      51	Framed
      52	Encircled
      53	Overlined
      54	Not framed or encircled
      55	Not overlined
      56–59	Reserved
      60	ideogram underline or right side line	hardly ever supported
      61	ideogram double underline or double line on the right side	hardly ever supported
      62	ideogram overline or left side line	hardly ever supported
      63	ideogram double overline or double line on the left side	hardly ever supported
      64	ideogram stress marking	hardly ever supported
      90–99	Set foreground text color, high intensity	aixterm (not in standard)
      100–109	Set background color, high intensity	aixterm (not in standard)
     */
// Set up shell colors
    private static $color      = array(
        'black' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'purple' => 35,
        'cyan' => 36,
        'grey' => 37
    );
    private static $background = array(
        'black' => 40,
        'red' => 41,
        'green' => 42,
        'yellow' => 43,
        'blue' => 44,
        'purple' => 45,
        'cyan' => 46,
        'grey' => 47);
    private static $style      = array(
        'normal' => 0,
        'bold' => 1, //Bold or increased intensity
        'light' => 1,
        'faint' => 2, //not widely supported
        'italic' => 3, //not widely supported. Sometimes treated as inverse.
        'underline' => 4,
        'blink' => 5, //less than 150 per minute
        'blink_fast' => 6, //MS-DOS ANSI.SYS; 150 per minute or more; not widely supported
        'inverse' => 7, //inverse or reverse; swap foreground and background
        'conceal' => 8,
    );

    /* @since Glial 1.1
     * @since Glial 2.1.2 split style and background, put background in 3rd arg and style in 4th arg.
     * @description put text in color on CLI mode (16 colors foreground & 8 colors background)
     * @param $string string text to put in color
     * @param $foreground_color string the color of foreground to know witch color available have a look on $foreground_colors
     * @param $background_colors string the color of foreground to know witch color available have a look on $background_colors
     * @return return the string with Ansi code, if one color is not found generate a trow exception
     */

    public static function getColoredString($string, $color = null, $background = null, $style = null)
    {

        ($style) ? self::testColor($color, self::$color) : '';
        ($style) ? self::testColor($background, self::$background) : '';
        ($style) ? self::testColor($style, self::$style) : '';

        $colored_string = "";

        $ansi = array();

        $ansi[] = ($style) ? self::$style[$style] : '0';
        $ansi[] = ($color) ? self::$color[$color] : '37';

        if (!empty($background)) {
            $ansi[] = ($background) ? self::$background[$background] : '40';
        }
        $str = implode(';', $ansi);

        $colored_string = "\033[".$str."m";
        $colored_string .= $string."\033[0m";

        return $colored_string;
    }
    /* @since Glial 1.1
     * @description Returns all foreground color names
     * @param void void This function has no parameters.
     * @return Returns all colors available for the foreground
     */

    public static function getColor()
    {
        return array_keys(self::$color);
    }

    /**
     *  @since Glial 1.1
     * @description Returns all foreground color names
     * @param void void This function has no parameters.
     * @return Returns all background color names
     */
    public static function getBackground()
    {
        return array_keys(self::$background);
    }

    /**
     *  @since Glial 1.1
     * @description Make a preview of all combinaison between color, background and style color
     * @param void void This function has no parameters.
     * @return Returns a string sample with all combinaison available
     */
    public static function printAll()
    {
        foreach (array_keys(self::$style) as $style) {

            foreach (array_keys(self::$color) as $color) {

                foreach (array_keys(self::$background) as $background) {
                    echo self::getColoredString(str_pad($color, 7).str_pad($background, 7).str_pad($style, 9), $color, $background, $style)." ";
                }
                echo PHP_EOL;
            }
            echo PHP_EOL;
        }
        echo PHP_EOL;
    }

    /**
     * Strips ANSI color codes from a string
     *
     * @param string $string String to strip
     *
     * @acess public
     * @return string
     */
    public static function strip($string)
    {
        return preg_replace('/\033\[[\d;]+[\d]?m/', '', $string);
    }

    private static function testColor($color, $array)
    {
        //echo $color."--";
        if (!array_key_exists($color, $array)) {
            throw new \DomainException("GLI-016 : Color code not found : ".$color);
        }
    }

    static public function setColor($color = null, $background = null, $style = null)
    {
        ($style) ? self::testColor($color, self::$color) : '';
        ($style) ? self::testColor($background, self::$background) : '';
        ($style) ? self::testColor($style, self::$style) : '';

        $colored_string = "";

        $ansi = array();

        $ansi[] = ($style) ? self::$style[$style] : '0';
        $ansi[] = ($color) ? self::$color[$color] : '37';
        $ansi[] = ($background) ? self::$background[$background] : '40';

        $str = implode(';', $ansi);

        $color = "\033[".$str."m";

        return $color;
    }
}

class SqlFormatter
{
    // Constants for token types
    const TOKEN_TYPE_WHITESPACE        = 0;
    const TOKEN_TYPE_WORD              = 1;
    const TOKEN_TYPE_QUOTE             = 2;
    const TOKEN_TYPE_BACKTICK_QUOTE    = 3;
    const TOKEN_TYPE_RESERVED          = 4;
    const TOKEN_TYPE_RESERVED_TOPLEVEL = 5;
    const TOKEN_TYPE_RESERVED_NEWLINE  = 6;
    const TOKEN_TYPE_BOUNDARY          = 7;
    const TOKEN_TYPE_COMMENT           = 8;
    const TOKEN_TYPE_BLOCK_COMMENT     = 9;
    const TOKEN_TYPE_NUMBER            = 10;
    const TOKEN_TYPE_ERROR             = 11;
    const TOKEN_TYPE_VARIABLE          = 12;
    // Constants for different components of a token
    const TOKEN_TYPE                   = 0;
    const TOKEN_VALUE                  = 1;

    // Reserved words (for syntax highlighting)
    protected static $reserved               = array(
        'ACCESSIBLE', 'ACTION', 'AGAINST', 'AGGREGATE', 'ALGORITHM', 'ALL', 'ALTER', 'ANALYSE', 'ANALYZE', 'AS', 'ASC',
        'AUTOCOMMIT', 'AUTO_INCREMENT', 'BACKUP', 'BEGIN', 'BETWEEN', 'BINLOG', 'BOTH', 'CASCADE', 'CASE', 'CHANGE', 'CHANGED', 'CHARACTER SET',
        'CHARSET', 'CHECK', 'CHECKSUM', 'COLLATE', 'COLLATION', 'COLUMN', 'COLUMNS', 'COMMENT', 'COMMIT', 'COMMITTED', 'COMPRESSED', 'CONCURRENT',
        'CONSTRAINT', 'CONTAINS', 'CONVERT', 'CREATE', 'CROSS', 'CURRENT_TIMESTAMP', 'DATABASE', 'DATABASES', 'DAY', 'DAY_HOUR', 'DAY_MINUTE',
        'DAY_SECOND', 'DEFAULT', 'DEFINER', 'DELAYED', 'DELETE', 'DESC', 'DESCRIBE', 'DETERMINISTIC', 'DISTINCT', 'DISTINCTROW', 'DIV',
        'DO', 'DUMPFILE', 'DUPLICATE', 'DYNAMIC', 'ELSE', 'ENCLOSED', 'END', 'ENGINE', 'ENGINE_TYPE', 'ENGINES', 'ESCAPE', 'ESCAPED', 'EVENTS', 'EXECUTE',
        'EXISTS', 'EXPLAIN', 'EXTENDED', 'FAST', 'FIELDS', 'FILE', 'FIRST', 'FIXED', 'FLUSH', 'FOR', 'FORCE', 'FOREIGN', 'FULL', 'FULLTEXT',
        'FUNCTION', 'GLOBAL', 'GRANT', 'GRANTS', 'GROUP_CONCAT', 'HEAP', 'HIGH_PRIORITY', 'HOSTS', 'HOUR', 'HOUR_MINUTE',
        'HOUR_SECOND', 'IDENTIFIED', 'IF', 'IFNULL', 'IGNORE', 'IN', 'INDEX', 'INDEXES', 'INFILE', 'INSERT', 'INSERT_ID', 'INSERT_METHOD', 'INTERVAL',
        'INTO', 'INVOKER', 'IS', 'ISOLATION', 'KEY', 'KEYS', 'KILL', 'LAST_INSERT_ID', 'LEADING', 'LEVEL', 'LIKE', 'LINEAR',
        'LINES', 'LOAD', 'LOCAL', 'LOCK', 'LOCKS', 'LOGS', 'LOW_PRIORITY', 'MARIA', 'MASTER', 'MASTER_CONNECT_RETRY', 'MASTER_HOST', 'MASTER_LOG_FILE',
        'MATCH', 'MAX_CONNECTIONS_PER_HOUR', 'MAX_QUERIES_PER_HOUR', 'MAX_ROWS', 'MAX_UPDATES_PER_HOUR', 'MAX_USER_CONNECTIONS',
        'MEDIUM', 'MERGE', 'MINUTE', 'MINUTE_SECOND', 'MIN_ROWS', 'MODE', 'MODIFY',
        'MONTH', 'MRG_MYISAM', 'MYISAM', 'NAMES', 'NATURAL', 'NOT', 'NOW()', 'NULL', 'OFFSET', 'ON', 'OPEN', 'OPTIMIZE', 'OPTION', 'OPTIONALLY',
        'ON UPDATE', 'ON DELETE', 'OUTFILE', 'PACK_KEYS', 'PAGE', 'PARTIAL', 'PARTITION', 'PARTITIONS', 'PASSWORD', 'PRIMARY', 'PRIVILEGES', 'PROCEDURE',
        'PROCESS', 'PROCESSLIST', 'PURGE', 'QUICK', 'RANGE', 'RAID0', 'RAID_CHUNKS', 'RAID_CHUNKSIZE', 'RAID_TYPE', 'READ', 'READ_ONLY',
        'READ_WRITE', 'REFERENCES', 'REGEXP', 'RELOAD', 'RENAME', 'REPAIR', 'REPEATABLE', 'REPLACE', 'REPLICATION', 'RESET', 'RESTORE', 'RESTRICT',
        'RETURN', 'RETURNS', 'REVOKE', 'RLIKE', 'ROLLBACK', 'ROW', 'ROWS', 'ROW_FORMAT', 'SECOND', 'SECURITY', 'SEPARATOR',
        'SERIALIZABLE', 'SESSION', 'SHARE', 'SHOW', 'SHUTDOWN', 'SLAVE', 'SONAME', 'SOUNDS', 'SQL', 'SQL_AUTO_IS_NULL', 'SQL_BIG_RESULT',
        'SQL_BIG_SELECTS', 'SQL_BIG_TABLES', 'SQL_BUFFER_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_LOG_BIN', 'SQL_LOG_OFF', 'SQL_LOG_UPDATE',
        'SQL_LOW_PRIORITY_UPDATES', 'SQL_MAX_JOIN_SIZE', 'SQL_QUOTE_SHOW_CREATE', 'SQL_SAFE_UPDATES', 'SQL_SELECT_LIMIT', 'SQL_SLAVE_SKIP_COUNTER',
        'SQL_SMALL_RESULT', 'SQL_WARNINGS', 'SQL_CACHE', 'SQL_NO_CACHE', 'START', 'STARTING', 'STATUS', 'STOP', 'STORAGE',
        'STRAIGHT_JOIN', 'STRING', 'STRIPED', 'SUPER', 'TABLE', 'TABLES', 'TEMPORARY', 'TERMINATED', 'THEN', 'TO', 'TRAILING', 'TRANSACTIONAL', 'TRUE',
        'TRUNCATE', 'TYPE', 'TYPES', 'UNCOMMITTED', 'UNIQUE', 'UNLOCK', 'UNSIGNED', 'USAGE', 'USE', 'USING', 'VARIABLES',
        'VIEW', 'WHEN', 'WITH', 'WORK', 'WRITE', 'YEAR_MONTH'
    );
    // For SQL formatting
    // These keywords will all be on their own line
    protected static $reserved_toplevel      = array(
        'SELECT', 'FROM', 'WHERE', 'SET', 'ORDER BY', 'GROUP BY', 'LIMIT', 'DROP',
        'VALUES', 'UPDATE', 'HAVING', 'ADD', 'AFTER', 'ALTER TABLE', 'DELETE FROM', 'UNION ALL', 'UNION', 'EXCEPT', 'INTERSECT'
    );
    protected static $reserved_newline       = array(
        'LEFT OUTER JOIN', 'RIGHT OUTER JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'OUTER JOIN', 'INNER JOIN', 'JOIN', 'XOR', 'OR', 'AND'
    );
    protected static $functions              = array(
        'ABS', 'ACOS', 'ADDDATE', 'ADDTIME', 'AES_DECRYPT', 'AES_ENCRYPT', 'AREA', 'ASBINARY', 'ASCII', 'ASIN', 'ASTEXT', 'ATAN', 'ATAN2',
        'AVG', 'BDMPOLYFROMTEXT', 'BDMPOLYFROMWKB', 'BDPOLYFROMTEXT', 'BDPOLYFROMWKB', 'BENCHMARK', 'BIN', 'BIT_AND', 'BIT_COUNT', 'BIT_LENGTH',
        'BIT_OR', 'BIT_XOR', 'BOUNDARY', 'BUFFER', 'CAST', 'CEIL', 'CEILING', 'CENTROID', 'CHAR', 'CHARACTER_LENGTH', 'CHARSET', 'CHAR_LENGTH',
        'COALESCE', 'COERCIBILITY', 'COLLATION', 'COMPRESS', 'CONCAT', 'CONCAT_WS', 'CONNECTION_ID', 'CONTAINS', 'CONV', 'CONVERT', 'CONVERT_TZ',
        'CONVEXHULL', 'COS', 'COT', 'COUNT', 'CRC32', 'CROSSES', 'CURDATE', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_USER',
        'CURTIME', 'DATABASE', 'DATE', 'DATEDIFF', 'DATE_ADD', 'DATE_DIFF', 'DATE_FORMAT', 'DATE_SUB', 'DAY', 'DAYNAME', 'DAYOFMONTH', 'DAYOFWEEK',
        'DAYOFYEAR', 'DECODE', 'DEFAULT', 'DEGREES', 'DES_DECRYPT', 'DES_ENCRYPT', 'DIFFERENCE', 'DIMENSION', 'DISJOINT', 'DISTANCE', 'ELT', 'ENCODE',
        'ENCRYPT', 'ENDPOINT', 'ENVELOPE', 'EQUALS', 'EXP', 'EXPORT_SET', 'EXTERIORRING', 'EXTRACT', 'EXTRACTVALUE', 'FIELD', 'FIND_IN_SET', 'FLOOR',
        'FORMAT', 'FOUND_ROWS', 'FROM_DAYS', 'FROM_UNIXTIME', 'GEOMCOLLFROMTEXT', 'GEOMCOLLFROMWKB', 'GEOMETRYCOLLECTION', 'GEOMETRYCOLLECTIONFROMTEXT',
        'GEOMETRYCOLLECTIONFROMWKB', 'GEOMETRYFROMTEXT', 'GEOMETRYFROMWKB', 'GEOMETRYN', 'GEOMETRYTYPE', 'GEOMFROMTEXT', 'GEOMFROMWKB', 'GET_FORMAT',
        'GET_LOCK', 'GLENGTH', 'GREATEST', 'GROUP_CONCAT', 'GROUP_UNIQUE_USERS', 'HEX', 'HOUR', 'IF', 'IFNULL', 'INET_ATON', 'INET_NTOA', 'INSERT', 'INSTR',
        'INTERIORRINGN', 'INTERSECTION', 'INTERSECTS', 'INTERVAL', 'ISCLOSED', 'ISEMPTY', 'ISNULL', 'ISRING', 'ISSIMPLE', 'IS_FREE_LOCK', 'IS_USED_LOCK',
        'LAST_DAY', 'LAST_INSERT_ID', 'LCASE', 'LEAST', 'LEFT', 'LENGTH', 'LINEFROMTEXT', 'LINEFROMWKB', 'LINESTRING', 'LINESTRINGFROMTEXT', 'LINESTRINGFROMWKB',
        'LN', 'LOAD_FILE', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCATE', 'LOG', 'LOG10', 'LOG2', 'LOWER', 'LPAD', 'LTRIM', 'MAKEDATE', 'MAKETIME', 'MAKE_SET',
        'MASTER_POS_WAIT', 'MAX', 'MBRCONTAINS', 'MBRDISJOINT', 'MBREQUAL', 'MBRINTERSECTS', 'MBROVERLAPS', 'MBRTOUCHES', 'MBRWITHIN', 'MD5', 'MICROSECOND',
        'MID', 'MIN', 'MINUTE', 'MLINEFROMTEXT', 'MLINEFROMWKB', 'MOD', 'MONTH', 'MONTHNAME', 'MPOINTFROMTEXT', 'MPOINTFROMWKB', 'MPOLYFROMTEXT', 'MPOLYFROMWKB',
        'MULTILINESTRING', 'MULTILINESTRINGFROMTEXT', 'MULTILINESTRINGFROMWKB', 'MULTIPOINT', 'MULTIPOINTFROMTEXT', 'MULTIPOINTFROMWKB', 'MULTIPOLYGON',
        'MULTIPOLYGONFROMTEXT', 'MULTIPOLYGONFROMWKB', 'NAME_CONST', 'NULLIF', 'NUMGEOMETRIES', 'NUMINTERIORRINGS', 'NUMPOINTS', 'OCT', 'OCTET_LENGTH',
        'OLD_PASSWORD', 'ORD', 'OVERLAPS', 'PASSWORD', 'PERIOD_ADD', 'PERIOD_DIFF', 'PI', 'POINT', 'POINTFROMTEXT', 'POINTFROMWKB', 'POINTN', 'POINTONSURFACE',
        'POLYFROMTEXT', 'POLYFROMWKB', 'POLYGON', 'POLYGONFROMTEXT', 'POLYGONFROMWKB', 'POSITION', 'POW', 'POWER', 'QUARTER', 'QUOTE', 'RADIANS', 'RAND',
        'RELATED', 'RELEASE_LOCK', 'REPEAT', 'REPLACE', 'REVERSE', 'RIGHT', 'ROUND', 'ROW_COUNT', 'RPAD', 'RTRIM', 'SCHEMA', 'SECOND', 'SEC_TO_TIME',
        'SESSION_USER', 'SHA', 'SHA1', 'SIGN', 'SIN', 'SLEEP', 'SOUNDEX', 'SPACE', 'SQRT', 'SRID', 'STARTPOINT', 'STD', 'STDDEV', 'STDDEV_POP', 'STDDEV_SAMP',
        'STRCMP', 'STR_TO_DATE', 'SUBDATE', 'SUBSTR', 'SUBSTRING', 'SUBSTRING_INDEX', 'SUBTIME', 'SUM', 'SYMDIFFERENCE', 'SYSDATE', 'SYSTEM_USER', 'TAN',
        'TIME', 'TIMEDIFF', 'TIMESTAMP', 'TIMESTAMPADD', 'TIMESTAMPDIFF', 'TIME_FORMAT', 'TIME_TO_SEC', 'TOUCHES', 'TO_DAYS', 'TRIM', 'TRUNCATE', 'UCASE',
        'UNCOMPRESS', 'UNCOMPRESSED_LENGTH', 'UNHEX', 'UNIQUE_USERS', 'UNIX_TIMESTAMP', 'UPDATEXML', 'UPPER', 'USER', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP',
        'UUID', 'VARIANCE', 'VAR_POP', 'VAR_SAMP', 'VERSION', 'WEEK', 'WEEKDAY', 'WEEKOFYEAR', 'WITHIN', 'X', 'Y', 'YEAR', 'YEARWEEK'
    );
    // Punctuation that can be used as a boundary between other tokens
    protected static $boundaries             = array(',', ';', ':', ')', '(', '.', '=', '<', '>', '+', '-', '*', '/', '!', '^', '%', '|', '&', '#');
    // For HTML syntax highlighting
    // Styles applied to different token types
    public static $quote_attributes          = 'style="color: blue;"';
    public static $backtick_quote_attributes = 'style="color: purple;"';
    public static $reserved_attributes       = 'style="font-weight:bold;"';
    public static $boundary_attributes       = '';
    public static $number_attributes         = 'style="color: green;"';
    public static $word_attributes           = 'style="color: #333;"';
    public static $error_attributes          = 'style="background-color: red;"';
    public static $comment_attributes        = 'style="color: #aaa;"';
    public static $variable_attributes       = 'style="color: orange;"';
    public static $pre_attributes            = 'style="color: black; background-color: white;"';
    // Boolean - whether or not the current environment is the CLI
    // This affects the type of syntax highlighting
    // If not defined, it will be determined automatically
    public static $cli;
    // For CLI syntax highlighting
    public static $cli_quote                 = "\x1b[34;1m";
    public static $cli_backtick_quote        = "\x1b[35;1m";
    public static $cli_reserved              = "\x1b[37m";
    public static $cli_boundary              = "";
    public static $cli_number                = "\x1b[32;1m";
    public static $cli_word                  = "";
    public static $cli_error                 = "\x1b[31;1;7m";
    public static $cli_comment               = "\x1b[30;1m";
    public static $cli_functions             = "\x1b[37m";
    public static $cli_variable              = "\x1b[36;1m";
    // The tab character to use when formatting SQL
    public static $tab                       = '  ';
    // This flag tells us if queries need to be enclosed in <pre> tags
    public static $use_pre                   = true;
    // This flag tells us if SqlFormatted has been initialized
    protected static $init;
    // Regular expressions for tokenizing
    protected static $regex_boundaries;
    protected static $regex_reserved;
    protected static $regex_reserved_newline;
    protected static $regex_reserved_toplevel;
    protected static $regex_function;
    // Cache variables
    // Only tokens shorter than this size will be cached.  Somewhere between 10 and 20 seems to work well for most cases.
    public static $max_cachekey_size         = 15;
    protected static $token_cache            = array();
    protected static $cache_hits             = 0;
    protected static $cache_misses           = 0;

    /**
     * Get stats about the token cache
     * @return Array An array containing the keys 'hits', 'misses', 'entries', and 'size' in bytes
     */
    public static function getCacheStats()
    {
        return array(
            'hits' => self::$cache_hits,
            'misses' => self::$cache_misses,
            'entries' => count(self::$token_cache),
            'size' => strlen(serialize(self::$token_cache))
        );
    }

    /**
     * Stuff that only needs to be done once.  Builds regular expressions and sorts the reserved words.
     */
    protected static function init()
    {
        if (self::$init) return;

        // Sort reserved word list from longest word to shortest, 3x faster than usort
        $reservedMap    = array_combine(self::$reserved, array_map('strlen', self::$reserved));
        arsort($reservedMap);
        self::$reserved = array_keys($reservedMap);

        // Set up regular expressions
        self::$regex_boundaries        = '('.implode('|', array_map(array(__CLASS__, 'quote_regex'), self::$boundaries)).')';
        self::$regex_reserved          = '('.implode('|', array_map(array(__CLASS__, 'quote_regex'), self::$reserved)).')';
        self::$regex_reserved_toplevel = str_replace(' ', '\\s+', '('.implode('|', array_map(array(__CLASS__, 'quote_regex'), self::$reserved_toplevel)).')');
        self::$regex_reserved_newline  = str_replace(' ', '\\s+', '('.implode('|', array_map(array(__CLASS__, 'quote_regex'), self::$reserved_newline)).')');

        self::$regex_function = '('.implode('|', array_map(array(__CLASS__, 'quote_regex'), self::$functions)).')';

        self::$init = true;
    }

    /**
     * Return the next token and token type in a SQL string.
     * Quoted strings, comments, reserved words, whitespace, and punctuation are all their own tokens.
     *
     * @param String $string   The SQL string
     * @param array  $previous The result of the previous getNextToken() call
     *
     * @return Array An associative array containing the type and value of the token.
     */
    protected static function getNextToken($string, $previous = null)
    {
        // Whitespace
        if (preg_match('/^\s+/', $string, $matches)) {
            return array(
                self::TOKEN_VALUE => $matches[0],
                self::TOKEN_TYPE => self::TOKEN_TYPE_WHITESPACE
            );
        }

        // Comment
        if ($string[0] === '#' || (isset($string[1]) && ($string[0] === '-' && $string[1] === '-') || ($string[0] === '/' && $string[1] === '*'))) {
            // Comment until end of line
            if ($string[0] === '-' || $string[0] === '#') {
                $last = strpos($string, "\n");
                $type = self::TOKEN_TYPE_COMMENT;
            } else { // Comment until closing comment tag
                $last = strpos($string, "*/", 2) + 2;
                $type = self::TOKEN_TYPE_BLOCK_COMMENT;
            }

            if ($last === false) {
                $last = strlen($string);
            }

            return array(
                self::TOKEN_VALUE => substr($string, 0, $last),
                self::TOKEN_TYPE => $type
            );
        }

        // Quoted String
        if ($string[0] === '"' || $string[0] === '\'' || $string[0] === '`') {
            $return = array(
                self::TOKEN_TYPE => ($string[0] === '`' ? self::TOKEN_TYPE_BACKTICK_QUOTE : self::TOKEN_TYPE_QUOTE),
                self::TOKEN_VALUE => self::getQuotedString($string)
            );

            return $return;
        }

        // User-defined Variable
        if ($string[0] === '@' && isset($string[1])) {
            $ret = array(
                self::TOKEN_VALUE => null,
                self::TOKEN_TYPE => self::TOKEN_TYPE_VARIABLE
            );

            // If the variable name is quoted
            if ($string[1] === '"' || $string[1] === '\'' || $string[1] === '`') {
                $ret[self::TOKEN_VALUE] = '@'.self::getQuotedString(substr($string, 1));
            }
            // Non-quoted variable name
            else {
                preg_match('/^(@[a-zA-Z0-9\._\$]+)/', $string, $matches);
                if ($matches) {
                    $ret[self::TOKEN_VALUE] = $matches[1];
                }
            }

            if ($ret[self::TOKEN_VALUE] !== null) return $ret;
        }

        // Number (decimal, binary, or hex)
        if (preg_match('/^([0-9]+(\.[0-9]+)?|0x[0-9a-fA-F]+|0b[01]+)($|\s|"\'`|'.self::$regex_boundaries.')/', $string, $matches)) {
            return array(
                self::TOKEN_VALUE => $matches[1],
                self::TOKEN_TYPE => self::TOKEN_TYPE_NUMBER
            );
        }

        // Boundary Character (punctuation and symbols)
        if (preg_match('/^('.self::$regex_boundaries.')/', $string, $matches)) {
            return array(
                self::TOKEN_VALUE => $matches[1],
                self::TOKEN_TYPE => self::TOKEN_TYPE_BOUNDARY
            );
        }

        // A reserved word cannot be preceded by a '.'
        // this makes it so in "mytable.from", "from" is not considered a reserved word
        if (!$previous || !isset($previous[self::TOKEN_VALUE]) || $previous[self::TOKEN_VALUE] !== '.') {
            $upper = strtoupper($string);
            // Top Level Reserved Word
            if (preg_match('/^('.self::$regex_reserved_toplevel.')($|\s|'.self::$regex_boundaries.')/', $upper, $matches)) {
                return array(
                    self::TOKEN_TYPE => self::TOKEN_TYPE_RESERVED_TOPLEVEL,
                    self::TOKEN_VALUE => substr($string, 0, strlen($matches[1]))
                );
            }
            // Newline Reserved Word
            if (preg_match('/^('.self::$regex_reserved_newline.')($|\s|'.self::$regex_boundaries.')/', $upper, $matches)) {
                return array(
                    self::TOKEN_TYPE => self::TOKEN_TYPE_RESERVED_NEWLINE,
                    self::TOKEN_VALUE => substr($string, 0, strlen($matches[1]))
                );
            }
            // Other Reserved Word
            if (preg_match('/^('.self::$regex_reserved.')($|\s|'.self::$regex_boundaries.')/', $upper, $matches)) {
                return array(
                    self::TOKEN_TYPE => self::TOKEN_TYPE_RESERVED,
                    self::TOKEN_VALUE => substr($string, 0, strlen($matches[1]))
                );
            }
        }

        // A function must be suceeded by '('
        // this makes it so "count(" is considered a function, but "count" alone is not
        $upper = strtoupper($string);
        // function
        if (preg_match('/^('.self::$regex_function.'[(]|\s|[)])/', $upper, $matches)) {
            return array(
                self::TOKEN_TYPE => self::TOKEN_TYPE_RESERVED,
                self::TOKEN_VALUE => substr($string, 0, strlen($matches[1]) - 1)
            );
        }

        // Non reserved word
        preg_match('/^(.*?)($|\s|["\'`]|'.self::$regex_boundaries.')/', $string, $matches);

        return array(
            self::TOKEN_VALUE => $matches[1],
            self::TOKEN_TYPE => self::TOKEN_TYPE_WORD
        );
    }

    protected static function getQuotedString($string)
    {
        $ret = null;

        // This checks for the following patterns:
        // 1. backtick quoted string using `` to escape
        // 2. double quoted string using "" or \" to escape
        // 3. single quoted string using '' or \' to escape
        if (preg_match('/^(((`[^`]*($|`))+)|(("[^"\\\\]*(?:\\\\.[^"\\\\]*)*("|$))+)|((\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*(\'|$))+))/s', $string, $matches)) {
            $ret = $matches[1];
        }

        return $ret;
    }

    /**
     * Takes a SQL string and breaks it into tokens.
     * Each token is an associative array with type and value.
     *
     * @param String $string The SQL string
     *
     * @return Array An array of tokens.
     */
    protected static function tokenize($string)
    {
        self::init();

        $tokens = array();

        // Used for debugging if there is an error while tokenizing the string
        $original_length = strlen($string);

        // Used to make sure the string keeps shrinking on each iteration
        $old_string_len = strlen($string) + 1;

        $token = null;

        $current_length = strlen($string);

        // Keep processing the string until it is empty
        while ($current_length) {
            // If the string stopped shrinking, there was a problem
            if ($old_string_len <= $current_length) {
                $tokens[] = array(
                    self::TOKEN_VALUE => $string,
                    self::TOKEN_TYPE => self::TOKEN_TYPE_ERROR
                );

                return $tokens;
            }
            $old_string_len = $current_length;

            // Determine if we can use caching
            if ($current_length >= self::$max_cachekey_size) {
                $cacheKey = substr($string, 0, self::$max_cachekey_size);
            } else {
                $cacheKey = false;
            }

            // See if the token is already cached
            if ($cacheKey && isset(self::$token_cache[$cacheKey])) {
                // Retrieve from cache
                $token        = self::$token_cache[$cacheKey];
                $token_length = strlen($token[self::TOKEN_VALUE]);
                self::$cache_hits++;
            } else {
                // Get the next token and the token type
                $token        = self::getNextToken($string, $token);
                $token_length = strlen($token[self::TOKEN_VALUE]);
                self::$cache_misses++;

                // If the token is shorter than the max length, store it in cache
                if ($cacheKey && $token_length < self::$max_cachekey_size) {
                    self::$token_cache[$cacheKey] = $token;
                }
            }

            $tokens[] = $token;

            // Advance the string
            $string = substr($string, $token_length);

            $current_length -= $token_length;
        }

        return $tokens;
    }

    /**
     * Format the whitespace in a SQL string to make it easier to read.
     *
     * @param String  $string    The SQL string
     * @param boolean $highlight If true, syntax highlighting will also be performed
     *
     * @return String The SQL string with HTML styles and formatting wrapped in a <pre> tag
     */
    public static function format($string, $highlight = true)
    {
        // This variable will be populated with formatted html
        $return = '';

        // Use an actual tab while formatting and then switch out with self::$tab at the end
        $tab = "\t";

        $indent_level            = 0;
        $newline                 = false;
        $inline_parentheses      = false;
        $increase_special_indent = false;
        $increase_block_indent   = false;
        $indent_types            = array();
        $added_newline           = false;
        $inline_count            = 0;
        $inline_indented         = false;
        $clause_limit            = false;

        // Tokenize String
        $original_tokens = self::tokenize($string);

        // Remove existing whitespace
        $tokens = array();
        foreach ($original_tokens as $i => $token) {
            if ($token[self::TOKEN_TYPE] !== self::TOKEN_TYPE_WHITESPACE) {
                $token['i'] = $i;
                $tokens[]   = $token;
            }
        }

        // Format token by token
        foreach ($tokens as $i => $token) {
            // Get highlighted token if doing syntax highlighting
            if ($highlight) {
                $highlighted = self::highlightToken($token);
            } else { // If returning raw text
                $highlighted = $token[self::TOKEN_VALUE];
            }

            // If we are increasing the special indent level now
            if ($increase_special_indent) {
                $indent_level++;
                $increase_special_indent = false;
                array_unshift($indent_types, 'special');
            }
            // If we are increasing the block indent level now
            if ($increase_block_indent) {
                $indent_level++;
                $increase_block_indent = false;
                array_unshift($indent_types, 'block');
            }

            // If we need a new line before the token
            if ($newline) {
                $return        .= "\n".str_repeat($tab, $indent_level);
                $newline       = false;
                $added_newline = true;
            } else {
                $added_newline = false;
            }

            // Display comments directly where they appear in the source
            if ($token[self::TOKEN_TYPE] === self::TOKEN_TYPE_COMMENT || $token[self::TOKEN_TYPE] === self::TOKEN_TYPE_BLOCK_COMMENT) {
                if ($token[self::TOKEN_TYPE] === self::TOKEN_TYPE_BLOCK_COMMENT) {
                    $indent      = str_repeat($tab, $indent_level);
                    $return      .= "\n".$indent;
                    $highlighted = str_replace("\n", "\n".$indent, $highlighted);
                }

                $return  .= $highlighted;
                $newline = true;
                continue;
            }

            if ($inline_parentheses) {
                // End of inline parentheses
                if ($token[self::TOKEN_VALUE] === ')') {
                    $return = rtrim($return, ' ');

                    if ($inline_indented) {
                        array_shift($indent_types);
                        $indent_level --;
                        $return .= "\n".str_repeat($tab, $indent_level);
                    }

                    $inline_parentheses = false;

                    $return .= $highlighted.' ';
                    continue;
                }

                if ($token[self::TOKEN_VALUE] === ',') {
                    if ($inline_count >= 30) {
                        $inline_count = 0;
                        $newline      = true;
                    }
                }

                $inline_count += strlen($token[self::TOKEN_VALUE]);
            }

            // Opening parentheses increase the block indent level and start a new line
            if ($token[self::TOKEN_VALUE] === '(') {
                // First check if this should be an inline parentheses block
                // Examples are "NOW()", "COUNT(*)", "int(10)", key(`somecolumn`), DECIMAL(7,2)
                // Allow up to 3 non-whitespace tokens inside inline parentheses
                $length = 0;
                for ($j = 1; $j <= 250; $j++) {
                    // Reached end of string
                    if (!isset($tokens[$i + $j])) break;

                    $next = $tokens[$i + $j];

                    // Reached closing parentheses, able to inline it
                    if ($next[self::TOKEN_VALUE] === ')') {
                        $inline_parentheses = true;
                        $inline_count       = 0;
                        $inline_indented    = false;
                        break;
                    }

                    // Reached an invalid token for inline parentheses
                    if ($next[self::TOKEN_VALUE] === ';' || $next[self::TOKEN_VALUE] === '(') {
                        break;
                    }

                    // Reached an invalid token type for inline parentheses
                    if ($next[self::TOKEN_TYPE] === self::TOKEN_TYPE_RESERVED_TOPLEVEL || $next[self::TOKEN_TYPE] === self::TOKEN_TYPE_RESERVED_NEWLINE || $next[self::TOKEN_TYPE] === self::TOKEN_TYPE_COMMENT
                        || $next[self::TOKEN_TYPE] === self::TOKEN_TYPE_BLOCK_COMMENT) {
                        break;
                    }

                    $length += strlen($next[self::TOKEN_VALUE]);
                }

                if ($inline_parentheses && $length > 30) {
                    $increase_block_indent = true;
                    $inline_indented       = true;
                    $newline               = true;
                }

                // Take out the preceding space unless there was whitespace there in the original query
                if (isset($original_tokens[$token['i'] - 1]) && $original_tokens[$token['i'] - 1][self::TOKEN_TYPE] !== self::TOKEN_TYPE_WHITESPACE) {
                    $return = rtrim($return, ' ');
                }

                if (!$inline_parentheses) {
                    $increase_block_indent = true;
                    // Add a newline after the parentheses
                    $newline               = true;
                }
            }

            // Closing parentheses decrease the block indent level
            elseif ($token[self::TOKEN_VALUE] === ')') {
                // Remove whitespace before the closing parentheses
                $return = rtrim($return, ' ');

                $indent_level--;

                // Reset indent level
                while ($j = array_shift($indent_types)) {
                    if ($j === 'special') {
                        $indent_level--;
                    } else {
                        break;
                    }
                }

                if ($indent_level < 0) {
                    // This is an error
                    $indent_level = 0;

                    if ($highlight) {
                        $return .= "\n".self::highlightError($token[self::TOKEN_VALUE]);
                        continue;
                    }
                }

                // Add a newline before the closing parentheses (if not already added)
                if (!$added_newline) {
                    $return .= "\n".str_repeat($tab, $indent_level);
                }
            }

            // Top level reserved words start a new line and increase the special indent level
            elseif ($token[self::TOKEN_TYPE] === self::TOKEN_TYPE_RESERVED_TOPLEVEL) {
                $increase_special_indent = true;

                // If the last indent type was 'special', decrease the special indent for this round
                reset($indent_types);
                if (current($indent_types) === 'special') {
                    $indent_level--;
                    array_shift($indent_types);
                }

                // Add a newline after the top level reserved word
                $newline = true;
                // Add a newline before the top level reserved word (if not already added)
                if (!$added_newline) {
                    $return .= "\n".str_repeat($tab, $indent_level);
                }
                // If we already added a newline, redo the indentation since it may be different now
                else {
                    $return = rtrim($return, $tab).str_repeat($tab, $indent_level);
                }

                // If the token may have extra whitespace
                if (strpos($token[self::TOKEN_VALUE], ' ') !== false || strpos($token[self::TOKEN_VALUE], "\n") !== false || strpos($token[self::TOKEN_VALUE], "\t") !== false) {
                    $highlighted = preg_replace('/\s+/', ' ', $highlighted);
                }
                //if SQL 'LIMIT' clause, start variable to reset newline
                if ($token[self::TOKEN_VALUE] === 'LIMIT' && !$inline_parentheses) {
                    $clause_limit = true;
                }
            }

            // Checks if we are out of the limit clause
            elseif ($clause_limit && $token[self::TOKEN_VALUE] !== "," && $token[self::TOKEN_TYPE] !== self::TOKEN_TYPE_NUMBER && $token[self::TOKEN_TYPE] !== self::TOKEN_TYPE_WHITESPACE) {
                $clause_limit = false;
            }

            // Commas start a new line (unless within inline parentheses or SQL 'LIMIT' clause)
            elseif ($token[self::TOKEN_VALUE] === ',' && !$inline_parentheses) {
                //If the previous TOKEN_VALUE is 'LIMIT', resets new line
                if ($clause_limit === true) {
                    $newline      = false;
                    $clause_limit = false;
                }
                // All other cases of commas
                else {
                    $newline = true;
                }
            }

            // Newline reserved words start a new line
            elseif ($token[self::TOKEN_TYPE] === self::TOKEN_TYPE_RESERVED_NEWLINE) {
                // Add a newline before the reserved word (if not already added)
                if (!$added_newline) {
                    $return .= "\n".str_repeat($tab, $indent_level);
                }

                // If the token may have extra whitespace
                if (strpos($token[self::TOKEN_VALUE], ' ') !== false || strpos($token[self::TOKEN_VALUE], "\n") !== false || strpos($token[self::TOKEN_VALUE], "\t") !== false) {
                    $highlighted = preg_replace('/\s+/', ' ', $highlighted);
                }
            }

            // Multiple boundary characters in a row should not have spaces between them (not including parentheses)
            elseif ($token[self::TOKEN_TYPE] === self::TOKEN_TYPE_BOUNDARY) {
                if (isset($tokens[$i - 1]) && $tokens[$i - 1][self::TOKEN_TYPE] === self::TOKEN_TYPE_BOUNDARY) {
                    if (isset($original_tokens[$token['i'] - 1]) && $original_tokens[$token['i'] - 1][self::TOKEN_TYPE] !== self::TOKEN_TYPE_WHITESPACE) {
                        $return = rtrim($return, ' ');
                    }
                }
            }

            // If the token shouldn't have a space before it
            if ($token[self::TOKEN_VALUE] === '.' || $token[self::TOKEN_VALUE] === ',' || $token[self::TOKEN_VALUE] === ';') {
                $return = rtrim($return, ' ');
            }

            $return .= $highlighted.' ';

            // If the token shouldn't have a space after it
            if ($token[self::TOKEN_VALUE] === '(' || $token[self::TOKEN_VALUE] === '.') {
                $return = rtrim($return, ' ');
            }

            // If this is the "-" of a negative number, it shouldn't have a space after it
            if ($token[self::TOKEN_VALUE] === '-' && isset($tokens[$i + 1]) && $tokens[$i + 1][self::TOKEN_TYPE] === self::TOKEN_TYPE_NUMBER && isset($tokens[$i - 1])) {
                $prev = $tokens[$i - 1][self::TOKEN_TYPE];
                if ($prev !== self::TOKEN_TYPE_QUOTE && $prev !== self::TOKEN_TYPE_BACKTICK_QUOTE && $prev !== self::TOKEN_TYPE_WORD && $prev !== self::TOKEN_TYPE_NUMBER) {
                    $return = rtrim($return, ' ');
                }
            }
        }

        // If there are unmatched parentheses
        if ($highlight && array_search('block', $indent_types) !== false) {
            $return .= "\n".self::highlightError("WARNING: unclosed parentheses or section");
        }

        // Replace tab characters with the configuration tab character
        $return = trim(str_replace("\t", self::$tab, $return));

        if ($highlight) {
            $return = self::output($return);
        }

        return $return;
    }

    /**
     * Add syntax highlighting to a SQL string
     *
     * @param String $string The SQL string
     *
     * @return String The SQL string with HTML styles applied
     */
    public static function highlight($string)
    {
        $tokens = self::tokenize($string);

        $return = '';

        foreach ($tokens as $token) {
            $return .= self::highlightToken($token);
        }

        return self::output($return);
    }

    /**
     * Split a SQL string into multiple queries.
     * Uses ";" as a query delimiter.
     *
     * @param String $string The SQL string
     *
     * @return Array An array of individual query strings without trailing semicolons
     */
    public static function splitQuery($string)
    {
        $queries       = array();
        $current_query = '';
        $empty         = true;

        $tokens = self::tokenize($string);

        foreach ($tokens as $token) {
            // If this is a query separator
            if ($token[self::TOKEN_VALUE] === ';') {
                if (!$empty) {
                    $queries[] = $current_query.';';
                }
                $current_query = '';
                $empty         = true;
                continue;
            }

            // If this is a non-empty character
            if ($token[self::TOKEN_TYPE] !== self::TOKEN_TYPE_WHITESPACE && $token[self::TOKEN_TYPE] !== self::TOKEN_TYPE_COMMENT && $token[self::TOKEN_TYPE] !== self::TOKEN_TYPE_BLOCK_COMMENT) {
                $empty = false;
            }

            $current_query .= $token[self::TOKEN_VALUE];
        }

        if (!$empty) {
            $queries[] = trim($current_query);
        }

        return $queries;
    }

    /**
     * Remove all comments from a SQL string
     *
     * @param String $string The SQL string
     *
     * @return String The SQL string without comments
     */
    public static function removeComments($string)
    {
        $result = '';

        $tokens = self::tokenize($string);

        foreach ($tokens as $token) {
            // Skip comment tokens
            if ($token[self::TOKEN_TYPE] === self::TOKEN_TYPE_COMMENT || $token[self::TOKEN_TYPE] === self::TOKEN_TYPE_BLOCK_COMMENT) {
                continue;
            }

            $result .= $token[self::TOKEN_VALUE];
        }
        $result = self::format($result, false);

        return $result;
    }

    /**
     * Compress a query by collapsing white space and removing comments
     *
     * @param String $string The SQL string
     *
     * @return String The SQL string without comments
     */
    public static function compress($string)
    {
        $result = '';

        $tokens = self::tokenize($string);

        $whitespace = true;
        foreach ($tokens as $token) {
            // Skip comment tokens
            if ($token[self::TOKEN_TYPE] === self::TOKEN_TYPE_COMMENT || $token[self::TOKEN_TYPE] === self::TOKEN_TYPE_BLOCK_COMMENT) {
                continue;
            }
            // Remove extra whitespace in reserved words (e.g "OUTER     JOIN" becomes "OUTER JOIN")
            elseif ($token[self::TOKEN_TYPE] === self::TOKEN_TYPE_RESERVED || $token[self::TOKEN_TYPE] === self::TOKEN_TYPE_RESERVED_NEWLINE || $token[self::TOKEN_TYPE] === self::TOKEN_TYPE_RESERVED_TOPLEVEL) {
                $token[self::TOKEN_VALUE] = preg_replace('/\s+/', ' ', $token[self::TOKEN_VALUE]);
            }

            if ($token[self::TOKEN_TYPE] === self::TOKEN_TYPE_WHITESPACE) {
                // If the last token was whitespace, don't add another one
                if ($whitespace) {
                    continue;
                } else {
                    $whitespace               = true;
                    // Convert all whitespace to a single space
                    $token[self::TOKEN_VALUE] = ' ';
                }
            } else {
                $whitespace = false;
            }

            $result .= $token[self::TOKEN_VALUE];
        }

        return rtrim($result);
    }

    /**
     * Highlights a token depending on its type.
     *
     * @param Array $token An associative array containing type and value.
     *
     * @return String HTML code of the highlighted token.
     */
    protected static function highlightToken($token)
    {
        $type = $token[self::TOKEN_TYPE];

        if (self::is_cli()) {
            $token = $token[self::TOKEN_VALUE];
        } else {
            $token = htmlentities($token[self::TOKEN_VALUE], ENT_COMPAT, 'UTF-8');
        }

        if ($type === self::TOKEN_TYPE_BOUNDARY) {
            return self::highlightBoundary($token);
        } elseif ($type === self::TOKEN_TYPE_WORD) {
            return self::highlightWord($token);
        } elseif ($type === self::TOKEN_TYPE_BACKTICK_QUOTE) {
            return self::highlightBacktickQuote($token);
        } elseif ($type === self::TOKEN_TYPE_QUOTE) {
            return self::highlightQuote($token);
        } elseif ($type === self::TOKEN_TYPE_RESERVED) {
            return self::highlightReservedWord($token);
        } elseif ($type === self::TOKEN_TYPE_RESERVED_TOPLEVEL) {
            return self::highlightReservedWord($token);
        } elseif ($type === self::TOKEN_TYPE_RESERVED_NEWLINE) {
            return self::highlightReservedWord($token);
        } elseif ($type === self::TOKEN_TYPE_NUMBER) {
            return self::highlightNumber($token);
        } elseif ($type === self::TOKEN_TYPE_VARIABLE) {
            return self::highlightVariable($token);
        } elseif ($type === self::TOKEN_TYPE_COMMENT || $type === self::TOKEN_TYPE_BLOCK_COMMENT) {
            return self::highlightComment($token);
        }

        return $token;
    }

    /**
     * Highlights a quoted string
     *
     * @param String $value The token's value
     *
     * @return String HTML code of the highlighted token.
     */
    protected static function highlightQuote($value)
    {
        if (self::is_cli()) {
            return self::$cli_quote.$value."\x1b[0m";
        } else {
            return '<span '.self::$quote_attributes.'>'.str_replace('\n', "<br>", $value).'</span>';
        }
    }

    /**
     * Highlights a backtick quoted string
     *
     * @param String $value The token's value
     *
     * @return String HTML code of the highlighted token.
     */
    protected static function highlightBacktickQuote($value)
    {
        if (self::is_cli()) {
            return self::$cli_backtick_quote.$value."\x1b[0m";
        } else {
            return '<span '.self::$backtick_quote_attributes.'>'.$value.'</span>';
        }
    }

    /**
     * Highlights a reserved word
     *
     * @param String $value The token's value
     *
     * @return String HTML code of the highlighted token.
     */
    protected static function highlightReservedWord($value)
    {
        if (self::is_cli()) {
            return self::$cli_reserved.$value."\x1b[0m";
        } else {
            return '<span '.self::$reserved_attributes.'>'.$value.'</span>';
        }
    }

    /**
     * Highlights a boundary token
     *
     * @param String $value The token's value
     *
     * @return String HTML code of the highlighted token.
     */
    protected static function highlightBoundary($value)
    {
        if ($value === '(' || $value === ')') return $value;

        if (self::is_cli()) {
            return self::$cli_boundary.$value."\x1b[0m";
        } else {
            return '<span '.self::$boundary_attributes.'>'.$value.'</span>';
        }
    }

    /**
     * Highlights a number
     *
     * @param String $value The token's value
     *
     * @return String HTML code of the highlighted token.
     */
    protected static function highlightNumber($value)
    {
        if (self::is_cli()) {
            return self::$cli_number.$value."\x1b[0m";
        } else {
            return '<span '.self::$number_attributes.'>'.$value.'</span>';
        }
    }

    /**
     * Highlights an error
     *
     * @param String $value The token's value
     *
     * @return String HTML code of the highlighted token.
     */
    protected static function highlightError($value)
    {
        if (self::is_cli()) {
            return self::$cli_error.$value."\x1b[0m";
        } else {
            return '<span '.self::$error_attributes.'>'.$value.'</span>';
        }
    }

    /**
     * Highlights a comment
     *
     * @param String $value The token's value
     *
     * @return String HTML code of the highlighted token.
     */
    protected static function highlightComment($value)
    {
        if (self::is_cli()) {
            return self::$cli_comment.$value."\x1b[0m";
        } else {
            //return '<span ' . self::$comment_attributes . '>GGGG' . str_replace("\n",'\n',$value) . '</span>';
        }
    }

    /**
     * Highlights a word token
     *
     * @param String $value The token's value
     *
     * @return String HTML code of the highlighted token.
     */
    protected static function highlightWord($value)
    {
        if (self::is_cli()) {
            return self::$cli_word.$value."\x1b[0m";
        } else {
            return '<span '.self::$word_attributes.'>'.$value.'</span>';
        }
    }

    /**
     * Highlights a variable token
     *
     * @param String $value The token's value
     *
     * @return String HTML code of the highlighted token.
     */
    protected static function highlightVariable($value)
    {
        if (self::is_cli()) {
            return self::$cli_variable.$value."\x1b[0m";
        } else {
            return '<span '.self::$variable_attributes.'>'.$value.'</span>';
        }
    }

    /**
     * Helper function for building regular expressions for reserved words and boundary characters
     *
     * @param String $a The string to be quoted
     *
     * @return String The quoted string
     */
    private static function quote_regex($a)
    {
        return preg_quote($a, '/');
    }

    /**
     * Helper function for building string output
     *
     * @param String $string The string to be quoted
     *
     * @return String The quoted string
     */
    private static function output($string)
    {
        if (self::is_cli()) {
            return $string."\n";
        } else {
            $string = trim($string);
            if (!self::$use_pre) {
                return $string;
            }

            return '<pre '.self::$pre_attributes.'>'.$string.'</pre>';
        }
    }

    private static function is_cli()
    {
        if (isset(self::$cli)) return self::$cli;
        else return php_sapi_name() === 'cli';
    }
}
define('IS_CLI', PHP_SAPI === 'cli');

Debug::parseDebug($_SERVER["argv"]);


unset($_SERVER["argv"][0]);




foreach ($_SERVER["argv"] as $key => $elem) {
    if ($elem == "--help") {
        echo Color::getColoredString("Usage : ./myloader h=127.0.0.1 u=root p='*****' d='/path/to/mydumper/directory' P=3306", "yellow")."\n";
        echo Color::getColoredString("NB : if not specified the default port is 3307", "yellow")."\n";
        exit;
        unset($_SERVER["argv"][$key]);
    }
}


myloader(implode('&', $_SERVER["argv"]));
