<?php

declare(strict_types=1);

if (!defined('IS_CLI')) {
    define('IS_CLI', true);
}

if (!defined('TMP')) {
    define('TMP', sys_get_temp_dir().DIRECTORY_SEPARATOR);
}

use Glial\Sgbd\Sql\Sql;
use PHPUnit\Framework\TestCase;

final class GlialSqlCallerTraceTest extends TestCase
{
    public function testDirectSqlQueryStoresExternalCallerFile(): void
    {
        $db = new TraceableSqlConnection();

        $db->sql_query('SELECT 1');

        $this->assertSame(__FILE__, $db->query[0]['file']);
        $this->assertGreaterThan(0, $db->query[0]['line']);
        $this->assertStringNotContainsString('/Sgbd/Sql/Sql.php', str_replace('\\', '/', $db->query[0]['file']));
    }

    public function testSilentSqlQueryStoresExternalCallerFile(): void
    {
        $db = new TraceableSqlConnection();

        $this->runSilentQuery($db);

        $this->assertSame(__FILE__, $db->query[0]['file']);
        $this->assertGreaterThan(0, $db->query[0]['line']);
        $this->assertStringNotContainsString('/Sgbd/Sql/Sql.php', str_replace('\\', '/', $db->query[0]['file']));
    }

    private function runSilentQuery(TraceableSqlConnection $db): void
    {
        $db->sql_query_silent('SELECT 1');
    }
}

final class TraceableSqlConnection extends Sql
{
    public function __construct($name = 'test', $elem = [])
    {
        unset($name, $elem);
    }

    public function _query($sql)
    {
        unset($sql);

        return true;
    }

    protected function sql_connect($var1, $var2, $var3, $db, $port, $ssl, $timeout)
    {
        unset($var1, $var2, $var3, $db, $port, $ssl, $timeout);

        return true;
    }

    protected function sql_select_db($var1)
    {
        unset($var1);

        return true;
    }

    protected function sql_close()
    {
        return true;
    }

    protected function sql_real_escape_string($var1)
    {
        return addslashes((string) $var1);
    }

    protected function sql_affected_rows()
    {
        return 0;
    }

    protected function sql_num_rows($var1)
    {
        unset($var1);

        return 0;
    }

    protected function sql_num_fields($res)
    {
        unset($res);

        return 0;
    }

    protected function sql_field_name($result, $i)
    {
        unset($result, $i);

        return '';
    }

    protected function sql_free_result($result)
    {
        unset($result);

        return true;
    }

    protected function _insert_id()
    {
        return 0;
    }

    protected function _error()
    {
        return '';
    }

    protected function getListTable()
    {
        return ['table' => []];
    }

    protected function getIndexUnique($table_name)
    {
        unset($table_name);

        return [];
    }

    protected function sql_thread_id()
    {
        return 0;
    }
}
