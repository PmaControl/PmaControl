<?php

namespace App\Controller;

use \App\Library\Debug;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;


// https://chatgpt.com/c/691b9112-ebcc-8329-a0cd-9cfcea9a3bee
class QueryGraphExtractor extends Controller
{
    

    /* ============================================================
       1) SELECT
       ============================================================ */

    public function extractSelectBlock(string $sql): string
    {
        preg_match('/SELECT(.*?)FROM/si', $sql, $m);
        return trim($m[1] ?? '');
    }

    public function splitSelectFields(string $selectBlock): array
    {
        return preg_split('/,(?![^()]*\))/s', $selectBlock);
    }

    public function normalizeSelectField(string $field, int $order): array
    {
        $field = trim($field);

        if (preg_match('/(.+?)\s+AS\s+([a-zA-Z0-9_]+)/i', $field, $m)) {
            $expr  = trim($m[1]);
            $alias = trim($m[2]);
        } else {
            $expr  = $field;
            $alias = $field;
        }

        $table = null;
        $fname = null;

        if (preg_match('/([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)$/', $expr, $m2)) {
            $table = $m2[1];
            $fname = $m2[2];
        }

        return [
            'order'      => $order,
            'expression' => $expr,
            'alias'      => $alias,
            'table'      => $table,
            'field'      => $fname
        ];
    }

    public function extractSelectFields(string $sql): array
    {
        $block  = $this->extractSelectBlock($sql);
        $parts  = $this->splitSelectFields($block);
        $result = [];

        foreach ($parts as $i => $p) {
            $result[] = $this->normalizeSelectField($p, $i + 1);
        }

        return $result;
    }


    /* ============================================================
       2) TABLES + ALIAS
       ============================================================ */

    public function extractTables(string $sql): array
    {
        $tables = [];

        preg_match_all(
            '/(LEFT JOIN|INNER JOIN|FROM)\s+([a-zA-Z0-9_]+)\s+(AS\s+)?([a-zA-Z0-9_]+)/i',
            $sql,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $table = $m[2];
            $alias = $m[4];

            if (!isset($tables[$alias])) {
                $tables[$alias] = [
                    'table_name' => $table,
                    'alias'      => $alias,
                    'fields'     => []
                ];
            }
        }

        return $tables;
    }


    /* ============================================================
       3) JOINS
       ============================================================ */

    public function extractJoinBlocks(string $sql): array
    {
        preg_match_all(
            '/(INNER JOIN|LEFT JOIN)\s+([a-zA-Z0-9_]+)\s+(AS\s+)?([a-zA-Z0-9_]+).*?ON(.*?)(LEFT JOIN|INNER JOIN|WHERE|GROUP BY|ORDER BY|LIMIT|$)/si',
            $sql,
            $matches,
            PREG_SET_ORDER
        );
        return $matches;
    }

    public function extractJoinConditions(string $condBlock): array
    {
        preg_match_all(
            '/([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*=\s*([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)/i',
            $condBlock,
            $conds,
            PREG_SET_ORDER
        );

        $result = [];

        foreach ($conds as $c) {
            $result[] = [
                'from_table' => $c[1],
                'from_field' => $c[2],
                'to_table'   => $c[3],
                'to_field'   => $c[4]
            ];
        }

        return $result;
    }

    public function extractJoins(string $sql): array
    {
        $result = [];
        $blocks = $this->extractJoinBlocks($sql);

        foreach ($blocks as $b) {
            $type = trim($b[1]);
            $condBlock = trim($b[5]);

            $relations = $this->extractJoinConditions($condBlock);

            foreach ($relations as $r) {
                $result[] = [
                    'type'       => ($type === 'INNER JOIN' ? 'inner' : 'left'),
                    'from_table' => $r['from_table'],
                    'from_field' => $r['from_field'],
                    'to_table'   => $r['to_table'],
                    'to_field'   => $r['to_field']
                ];
            }
        }

        return $result;
    }


    /* ============================================================
       4) WHERE (avec capture de valeur)
       ============================================================ */

    public function extractWhereBlock(string $sql): string
    {
        preg_match('/WHERE(.*?)(GROUP BY|ORDER BY|LIMIT|$)/si', $sql, $m);
        return trim($m[1] ?? '');
    }

    public function extractWhereConditions(string $sql): array
    {
        $block = $this->extractWhereBlock($sql);
        $pattern = '/([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(=|!=|<>|LIKE|NOT LIKE|IN|NOT IN|BETWEEN)\s*(.+?)(?:\s+AND|\s+OR|$)/i';

        preg_match_all($pattern, $block, $matches, PREG_SET_ORDER);

        $result = [];

        foreach ($matches as $m) {
            $operator = strtoupper($m[3]);
            $valueRaw = trim($m[4]);
            $value2   = null;
            $value    = $valueRaw;

            if ($operator === 'BETWEEN') {
                if (preg_match('/BETWEEN\s+(.+?)\s+AND\s+(.+)$/i', $valueRaw, $b)) {
                    $value  = trim($b[1]);
                    $value2 = trim($b[2]);
                }
            } else {
                $value = rtrim($valueRaw, " )");
            }

            $result[] = [
                'table'    => $m[1],
                'field'    => $m[2],
                'operator' => $operator,
                'value'    => $value,
                'value2'   => $value2
            ];
        }

        return $result;
    }

    /************* */

    public function extract(string $sql): array
    {
        return [
            'select_fields' => $this->extractSelectFields($sql),
            'tables'        => $this->extractTables($sql),
            'joins'         => $this->extractJoins($sql),
            'where_fields'  => $this->extractWhereConditions($sql),
            'subqueries'    => $this->extractSubqueries($sql)
        ];
    }

    /* ============================================================
       EXTRACTION DES SOUS-REQUETES (clusters)
       ============================================================ */

    /**
     * Détecte les sous-requêtes du type :
     *    (SELECT ....) alias
     */
    public function extractSubqueryBlocks(string $sql): array
    {
        $pattern = '/\((\s*SELECT.*?\))\s+([a-zA-Z0-9_]+)/is';

        preg_match_all($pattern, $sql, $m, PREG_SET_ORDER);

        $result = [];
        foreach ($m as $block) {
            $result[] = [
                'sql'   => trim($block[1], "() \n\r\t"),
                'alias' => trim($block[2])
            ];
        }
        return $result;
    }

    /**
     * Analyse une sous-requête complète.
     */
    public function extractSubquery(string $sql, string $alias): array
    {
        return [
            'alias'        => $alias,
            'sql'          => $sql,
            'select_fields'=> $this->extractSelectFields($sql),
            'tables'       => $this->extractTables($sql),
            'joins'        => $this->extractJoins($sql),
            'where_fields' => $this->extractWhereConditions($sql),
            'group_by'     => $this->extractGroupBy($sql)
        ];
    }

    /**
     * Extraction complète des sous-requêtes trouvées.
     */
    public function extractSubqueries(string $sql): array
    {
        $blocks = $this->extractSubqueryBlocks($sql);

        $result = [];
        foreach ($blocks as $b) {
            $result[] = $this->extractSubquery($b['sql'], $b['alias']);
        }
        return $result;
    }

    /* ============================================================
       EXTRACTION DU GROUP BY POUR LES SOUS-REQUETES
       ============================================================ */

    public function extractGroupBy(string $sql): array
    {
        preg_match('/GROUP BY(.*?)(ORDER BY|LIMIT|$)/si', $sql, $m);

        if (!isset($m[1])) {
            return [];
        }

        $fields = explode(',', trim($m[1]));

        return array_map('trim', $fields);
    }

}
