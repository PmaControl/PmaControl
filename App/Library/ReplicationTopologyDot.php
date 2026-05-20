<?php

declare(strict_types=1);

namespace App\Library;

final class ReplicationTopologyDot
{
    /**
     * @param list<array{source_id:int,source_name:string,replica_id:int,replica_name:string,channel?:string,lag?:int,healthy?:bool,ssl?:bool,filters?:bool}> $edges
     */
    public static function build(array $edges): string
    {
        $nodes = [];
        $lines = [
            'digraph replication {',
            '  graph [rankdir=LR, bgcolor="transparent"];',
            '  node [shape=box, style="rounded,filled", fillcolor="#f8fafc", color="#cbd5e1", fontname="Arial"];',
            '  edge [fontname="Arial", color="#16a34a"];',
        ];

        foreach ($edges as $edge) {
            $sourceId = 's' . (int)$edge['source_id'];
            $replicaId = 's' . (int)$edge['replica_id'];
            $nodes[$sourceId] = (string)$edge['source_name'];
            $nodes[$replicaId] = (string)$edge['replica_name'];

            $lag = (int)($edge['lag'] ?? 0);
            $color = !($edge['healthy'] ?? true) ? '#dc2626' : ($lag > 30 ? '#d97706' : '#16a34a');
            $style = !empty($edge['ssl']) ? 'solid' : 'dashed';
            if (!empty($edge['filters'])) {
                $style = 'dashed';
                $color = '#dc2626';
            }
            $label = trim((string)($edge['channel'] ?? 'default') . ' lag=' . $lag . 's');
            $lines[] = sprintf(
                '  %s -> %s [label="%s", color="%s", style="%s"];',
                $sourceId,
                $replicaId,
                self::dotEscape($label),
                $color,
                $style
            );
        }

        foreach ($nodes as $id => $label) {
            $lines[] = sprintf('  %s [label="%s"];', $id, self::dotEscape($label));
        }
        $lines[] = '}';

        return implode("\n", $lines);
    }

    private static function dotEscape(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }
}
