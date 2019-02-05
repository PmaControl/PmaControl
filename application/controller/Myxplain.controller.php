<?php

use \Glial\Synapse\Controller;
use Glial\Cli\Table;

class Myxplain extends Controller
{
    var $id = '<p>This is <b>the query identifier</b>, this column <B>shows the number of SELECTs</B> (This is not the identifier of a join)</p>
	<p>Therefore, in the case of a simple SELECT query, this identifier will always be equal to 1 for each row of the EXPLAIN output</p>
	<p>Other values are possible when using <a href="'.LINK.__CLASS__.'/index/id/18">subqueries</a>, '
        . '<a href="'.LINK.__CLASS__.'/index/id/16">derived tables</a> or <a href="'.LINK.__CLASS__.'/index/id/18">UNION</a></p>
	<p>NULL value will be displayed for the results of an UNION of two (or more) SELECTs of the EXPLAIN output</p>
	<p>In our example, the column <i>table</i> looks like that : &lt;union1,2&gt;, where 1 and 2 are the two SELECT of the EXPLAIN plan</p>';
    var $select_type   = "This column indicates if you run a simple or a complex query

This is an informative column related to the id column

Unlike a simple query, a complex query contains subqueries or UNIONs

This column displays SIMPLE when you execute a query involving one table or joins. This is the most common case

For complex queries, the main SELECT will always labeled PRIMARY and the other subparts can take the following values related to the query executed :

    SUBQUERY
    DEPENDENT SUBQUERY
    UNCACHEABLE SUBQUERY : The result must be re-evaluated for each row of the outer query
    DERIVED
    UNION / UNION RESULT
    DEPENDENT UNION
    UNCACHEABLE UNION : see UNCACHEABLE SUBQUERY

Note that converting subqueries or derived tables to JOINs is better for performance

Subqueries runs as part of explain execution, pay attention to this if you love your tranquility";
    var $table         = "This column simply displays the name of the table (or its alias) being accessed for the specified row

For complex queries, the output of this column can be <unionM,N> or <derivedN>

Take a special care while choosing your aliases, if too vague, they can disrupt the reading of your queries

This column is also useful to see what was the optimizer choices about the join order :

NULL value is also possible in the case of an impossible query or for queries with no table
";
    var $type          = "The join type shows how data is accessed

This is an important information about the query optimization, it must be as good as possible

Here are the different access methods ordered from the worst type to the best :

    ALL : Full table scan, reading the entire content of a table
    INDEX : Full index scan for the join, reading the entire content of a table in the index order (Covering index is possible if Using index appears in Extra column)
    RANGE : Limited index scan (ex : where id in (1,3))
    INDEX_SUBQUERY : Index lookup on non-unique index of a subquery
    UNIQUE_SUBQUERY : Index lookup on the primary key or unique key of a subquery
    INDEX_MERGE : Index scan of two merge index, use more than one index (ex : where id=10 or text1='OK')
    REF_OR_NULL : Non-unique index access with an extra lookup needed for NULL values (ex : where id is null)
    FULLTEXT : Fulltext index access
    REF : Non-unique index access aka index lookup (ex : where text1='OK')
    EQ_REF : Index lookup access that returns a single value (ex : where t1.id=t2.id)
    CONST : Primary or unique index access turns into a constant (ex : where t1.primary_key=1)
    SYSTEM : Applies for system tables with one row or in-memory table
    NULL : No access to the table or index (or impossible WHERE clause)

Every types except ALL means that an index is used

It is important to note that access via an index does not necessarily means a faster access to data (also be aware that too much indexes may kill performances)

However a full table scan is generally not recommended (except for particular cases)

Take a look at the key, row and extra columns for details about how data are processed";
    var $possible_keys = "Which indexes could be used to perform the query, these are the potential indexes

Note that some of the indexes listed there could be useless because optimizer ignores the tables order displayed in the EXPLAIN output

The possible_keys and key columns are essential in the understanding of how the optimizer works

You should find one of the indexes listed there in the key column

If this is not the case, the trouble begins... hey, it's your job!

If NULL value is displayed, it is possible that an index is missing (or it means that you are looking to an UNION or a DERIVED TABLE row)

Remember to take a look at the where clause of the query to understand the optimizer choice";
    var $key           = "This is the key that the optimizer decided to use to access to the data and minimize the query cost

This is one of the most important column related to the query optimization

possible_keys, key, key_len and ref columns are the most essentials to understand how your data are accessed

However, the key which appears here is not necessary listed in the possible_keys

But the most important is to have an index name in this column

Use the show index command to show the indexes declared for the tables listed in the explain plan

It's possible to force the optimizer to use or ignore an index with hints";
    var $key_len       = "This is the length of the index that the optimizer decided to use

This information allows you to know how many parts of a multiple-part index are used

In the examples below the table t3 has the following structure, the PRIMARY KEY has two parts (id and ts) :

In this first example, only the first part of the primary key is used, you can read a key length at 4 bytes

These 4 bytes corresponds to the size of an integer type (4 bytes)

In this second example, the two parts of the primary key are used, you can read a key length at 8 bytes

These 8 bytes corresponds to the sum of the size of an integer type and the size of a timestamp type (4 bytes + 4 bytes)

Note that if the column is nullable, the key length is incremented by 1. UTF8 character set can also affect this size";
    var $ref           = "Which columns or constants were compared with index in the key column

This column allows you to see what is compared

In this example, the id values of the table t3 are compared with the values of the idx1 index";
    var $row           = "This is the estimated amount of rows that the optimizer have to examine to retrieve the result of the query

This is not the number of rows of the result set

Optimizer relies on statistics and the selectivity of the indexes to calculate this estimate

Note that LIMIT doesn't affect this view

To calculate the estimated amount of rows needed for the entire query, you have to multiply all the rows values

Focus should be made on the difference between this estimate and the actual number of rows returned by the query

If this difference is significant, it's time to analyze your where clause dude

type and key columns may help to diagnose a potential problem

Note that run an analyze table command before the explain command may help the optimizer to estimate a more accurate number of rows";
    var $filtered      = "This column is specific with EXPLAIN EXTENDED command

It corresponds to an estimated percentage of rows filtered by the table condition

In other words, it is the number of rows which will be joined with previous tables

These information may be a very valuable addition to EXPLAIN for performance troubleshooting

See documentation for more details about special markers that can appear in EXTENDED output";
    var $extra         = "The Extra column provides additinal informations about how MySQL resolves the query

It can be a concatenation of multiple informations

These informations may be good news, bad news or simply informative

The most common values for this colomn are the following :

    Using where (neutral): Filter rows after the storage engine retrieve them. May be wrong if join type is ALL or INDEX
    Using index (good) : MySQL uses a covering index. The result set is retrieved from the index pages, no table read
    Using temporary (bad) : A temporary table is needed to achieve the query. Can be very bad if the temporary table is created on disk
    Using filesort (bad) : MySQL uses an extra sort rather than using an index. With GROUP BY, sorting can be avoided with ORDER BY NULL
    Using join buffer (bad) : Index needed
    Impossible WHERE (bad) : Use valid values for your constants
    Using index for group-by (good) : MySQL uses an index to process all the columns in the GROUP BY

See documentation for more details about other possible values";

    public function index($param)
    {
        $colonne = $param[0] ?? "id";
        $query   = $param[1] ?? 1;

        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql1 = "SELECT * FROM myxplain WHERE id = ".$query;


        $res1 = $db->sql_query($sql1);

        while ($ob1 = $db->sql_fetch_array($res1, MYSQLI_ASSOC)) {
            $data['query'] = $ob1;
        }

        $data['query']['explain'] = json_decode($data['query']['explain'], true);


        $table = new Table(2);


        $keys = array_keys(end($data['query']['explain']));


        //$keys[0] = '<a href="">'.$keys[0].'</a>';

        $table->addHeader($keys);

        $data['rows'] = 0;
        foreach ($data['query']['explain'] as $line) {
            $vals = array_values($line);

            $table->addLine($vals);
            $data['rows'] ++;
        }

        $data['table'] = $table->display();

        $lines = explode("\n", $data['table']);


        foreach ($keys as $key) {
            $search   = '/(\s|\|)('.$key.')(\s|\|)/';
            $lines[1] = preg_replace($search, '$1<a href="'.LINK.__CLASS__.'/'.__FUNCTION__.'/$2">$2</a>$3', $lines[1]);
        }


        $data['table'] = implode("<br>", $lines);

        //$data['table'] = str_replace("\n", "<br>", $data['table']);
        $data['table'] = str_replace("  ", "&nbsp;&nbsp;", $data['table']);


        $data['explication'] = $this->{$colonne};

        $this->set('data', $data);
    }

    public function import()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);


        foreach (glob("/data/www/MYXPLAIN/data/explain*.json") as $filename) {


            $json = file_get_contents($filename);

            $tab = json_decode($json, true);

            $data = $tab['mysqlcommand'];

            debug($data);


            $file = pathinfo($filename);

            $sql = "INSERT INTO myxplain (`name`,`command`,`explain`,`duration`,`date`)
                VALUES (
                '".$file['filename']."',
                '".$data['command']."',
                '".json_encode($data['results'])."',
                '".$data['duration']."',
                '".date('Y-m-d H:i:s')."' )";


            $db->sql_query($sql);
        }
    }
}