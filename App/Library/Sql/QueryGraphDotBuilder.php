<?php

declare(strict_types=1);

namespace App\Library\Sql;

final class QueryGraphDotBuilder
{
    public static function build(array $graph): string
    {
        $lines = [
            'digraph query_graph {',
            '  graph [rankdir=LR, bgcolor="transparent", pad="0.2", nodesep="0.45", ranksep="0.7"];',
            '  node [shape=plain, fontname="Arial"];',
            '  edge [fontname="Arial", fontsize=10, color="#64748b", arrowsize=0.8];',
        ];

        $aliases = [];
        foreach ($graph['tables'] ?? [] as $table) {
            $nodeId = self::nodeId((string)($table['alias'] ?: $table['table']));
            $aliases[(string)($table['alias'] ?: $table['table'])] = $nodeId;
            $lines[] = self::tableNode($nodeId, $table);
        }

        foreach ($graph['joins'] ?? [] as $join) {
            $from = (string)($join['from_table'] ?? '');
            $to = (string)($join['to_table'] ?? '');
            if ($from === '' || $to === '' || !isset($aliases[$from], $aliases[$to])) {
                continue;
            }

            $label = trim((string)($join['from_field'] ?? '') . ' = ' . (string)($join['to_field'] ?? ''));
            $type = strtoupper((string)($join['type'] ?? 'JOIN'));
            $lines[] = sprintf(
                '  %s -> %s [label="%s", tooltip="%s"];',
                $aliases[$from],
                $aliases[$to],
                self::escape($label),
                self::escape($type)
            );
        }

        foreach ($graph['where_fields'] ?? [] as $where) {
            $table = (string)($where['table'] ?? '');
            if ($table === '' || !isset($aliases[$table])) {
                continue;
            }

            $filterId = self::nodeId('filter_' . $table . '_' . count($lines));
            $lines[] = sprintf(
                '  %s [label=<%s>];',
                $filterId,
                self::htmlTable('Filter', [(string)($where['expression'] ?? '')], '#fef3c7', '#92400e')
            );
            $lines[] = sprintf('  %s -> %s [style=dashed, color="#d97706"];', $filterId, $aliases[$table]);
        }

        if (($graph['tables'] ?? []) === []) {
            $lines[] = '  empty [label=<<TABLE BORDER="0" CELLBORDER="1" CELLSPACING="0" CELLPADDING="8"><TR><TD>No table detected</TD></TR></TABLE>>];';
        }

        $lines[] = '}';

        return implode("\n", $lines) . "\n";
    }

    private static function tableNode(string $nodeId, array $table): string
    {
        $title = (string)($table['database'] ?? '');
        $title = $title === '' ? (string)$table['table'] : $title . '.' . (string)$table['table'];

        $rows = [];
        if (!empty($table['alias'])) {
            $rows[] = 'alias: ' . (string)$table['alias'];
        }

        if (!empty($table['source'])) {
            $rows[] = strtolower((string)$table['source']);
        }

        return sprintf(
            '  %s [label=<%s>];',
            $nodeId,
            self::htmlTable($title, $rows, '#e0f2fe', '#075985')
        );
    }

    private static function htmlTable(string $title, array $rows, string $headerColor, string $fontColor): string
    {
        $html = '<TABLE BORDER="0" CELLBORDER="1" CELLSPACING="0" CELLPADDING="6">';
        $html .= '<TR><TD BGCOLOR="' . $headerColor . '"><FONT COLOR="' . $fontColor . '"><B>' . self::escape($title) . '</B></FONT></TD></TR>';

        foreach ($rows as $row) {
            if ($row === '') {
                continue;
            }

            $html .= '<TR><TD ALIGN="LEFT">' . self::escape((string)$row) . '</TD></TR>';
        }

        return $html . '</TABLE>';
    }

    private static function nodeId(string $value): string
    {
        return 'qg_' . substr(sha1($value), 0, 16);
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
