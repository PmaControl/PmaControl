<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$inputDir = $root . '/documentation/_generated_mcpdoc';
$outputFile = $root . '/documentation/pmacontrol_tables_documentation.md';
$dotFile = $root . '/tmp/dot/1-pmacontrol-.dot';
$dotSvgFile = preg_replace('/\.dot$/', '.svg', $dotFile);

function loadRows(string $file): array
{
    $json = json_decode((string) file_get_contents($file), true);
    if (!is_array($json)) {
        throw new RuntimeException("Invalid JSON in {$file}");
    }

    $rows = $json['result']['structuredContent']['rows'] ?? null;
    if (!is_array($rows)) {
        throw new RuntimeException("Missing structuredContent.rows in {$file}");
    }

    return $rows;
}

function inferPurpose(string $table, string $comment, array $controllerActions): string
{
    if (trim($comment) !== '') {
        return trim($comment);
    }

    $map = [
        'backup_' => 'Gestion des sauvegardes (jobs, dumps, destinations, historique).',
        'benchmark_' => 'Mesure de performance et historique d\'exécutions de benchmark.',
        'binlog_' => 'Suivi des binlogs (taille, backup, historique).',
        'cleaner_' => 'Nettoyage automatique et règles de maintenance.',
        'crontab' => 'Planification de tâches et historique d\'exécution.',
        'daemon_' => 'État/configuration des démons de collecte/exécution.',
        'docker_' => 'Inventaire Docker (serveurs, images, instances).',
        'dot3_' => 'Topologie, graphe et représentation des dépendances.',
        'galera' => 'Données liées aux clusters Galera et à leur état.',
        'group' => 'Gestion des groupes, droits et rattachements.',
        'haproxy_' => 'Inventaire/configuration des noeuds HAProxy.',
        'ldap' => 'Configuration et intégration LDAP.',
        'listener' => 'Événements/listeners techniques.',
        'llm_' => 'Fonctionnalités LLM (prompts, historique, métadonnées).',
        'mysql_' => 'Inventaire MySQL et métadonnées serveurs/bases/utilisateurs.',
        'mysqlsys_' => 'Données d\'audit/configuration MySQL système.',
        'proxysql_' => 'Inventaire et configuration ProxySQL.',
        'pmm' => 'Intégration PMM et métadonnées associées.',
        'replication' => 'Suivi de réplication et états associés.',
        'ssh_' => 'Configuration et clés d\'accès SSH.',
        'tag' => 'Système de tags et catégorisation d\'objets.',
        'translation' => 'Gestion de traduction et libellés.',
        'ts_' => 'Séries temporelles et métriques collectées.',
        'user_' => 'Gestion des utilisateurs, sessions et authentification.',
        'geolocalisation_' => 'Référentiels géographiques (pays, villes, etc.).',
        'alert' => 'Gestion des alertes et statuts.',
        'event' => 'Journalisation d\'événements.',
        'archive' => 'Archivage de données et historique.',
        'database_' => 'Métriques/objets liés aux bases MySQL.',
        'table_' => 'Métriques/objets liés aux tables MySQL.',
        'query' => 'Analyse et stockage de requêtes SQL.',
    ];

    foreach ($map as $prefix => $purpose) {
        if (str_starts_with($table, $prefix)) {
            return $purpose;
        }
    }

    if (!empty($controllerActions)) {
        return 'Table métier utilisée par ' . implode(', ', array_slice(array_values(array_unique($controllerActions)), 0, 5)) . '.';
    }

    return 'Rôle à confirmer: table présente dans le schéma mais peu référencée explicitement dans le code applicatif.';
}

function parseDotForeignKeys(string $dotPath): array
{
    if (!is_file($dotPath)) {
        return [];
    }

    $lines = (array) file($dotPath, FILE_IGNORE_NEW_LINES);
    $fks = [];

    foreach ($lines as $line) {
        if (!str_contains($line, '->')) {
            continue;
        }

        if (preg_match('/tooltip="([^"]+=>[^"]+)"/', $line, $m)) {
            $fks[] = trim($m[1]);
        }
    }

    $fks = array_values(array_unique($fks));
    sort($fks, SORT_NATURAL | SORT_FLAG_CASE);

    return $fks;
}

function parseControllerFunctions(string $path): array
{
    $lines = @file($path, FILE_IGNORE_NEW_LINES);
    if (!is_array($lines)) {
        return [];
    }

    $functions = [];
    foreach ($lines as $i => $line) {
        if (preg_match('/^\s*(public|protected|private)?\s*function\s+([A-Za-z0-9_]+)\s*\(/', $line, $m)) {
            $functions[] = [
                'line' => $i + 1,
                'visibility' => $m[1] !== '' ? $m[1] : 'public',
                'name' => $m[2],
            ];
        }
    }

    return $functions;
}

function actionForLine(array $functions, int $line): ?string
{
    $action = null;
    foreach ($functions as $fn) {
        if ($fn['line'] <= $line) {
            $action = $fn['name'];
        } else {
            break;
        }
    }

    return $action;
}

$tables = loadRows($inputDir . '/10.json');
$columns = loadRows($inputDir . '/11.json');
$fks = loadRows($inputDir . '/12.json');

$columnsByTable = [];
foreach ($columns as $col) {
    $columnsByTable[$col['table_name']][] = $col;
}

$fksByTable = [];
foreach ($fks as $fk) {
    $fksByTable[$fk['table_name']][] = $fk;
}

$controllerFunctionIndex = [];
foreach (glob($root . '/App/Controller/*.php') as $controllerPath) {
    $controllerFunctionIndex[$controllerPath] = parseControllerFunctions($controllerPath);
}

$doc = [];
$doc[] = '# Documentation Base `pmacontrol`';
$doc[] = '';
$doc[] = '- Générée le: ' . date('Y-m-d H:i:s');
$doc[] = '- Source schéma: serveur MCP (`php-mcp-mysql`) + `information_schema`';
$doc[] = '- Nombre de tables détectées: ' . count($tables);
$doc[] = '';
$doc[] = '## Méthodologie';
$doc[] = '';
$doc[] = '- Schéma réel extrait via MCP (`db_select`) depuis `information_schema.tables`, `information_schema.columns`, `information_schema.key_column_usage`.';
$doc[] = '- Corrélation code par recherche texte dans `App/Controller`, `App/view`, `App/Library`, `App/Mutual`, `App/model`.';
$doc[] = '- Écrans dérivés des occurrences dans contrôleurs (`/Controller/action`) et des vues associées quand présentes.';
$doc[] = '';

foreach ($tables as $tableMeta) {
    $table = $tableMeta['table_name'];

    $pattern = '\\b' . preg_quote($table, '/') . '\\b';
    $searchCmd = 'rg -n --no-heading -S ' . escapeshellarg($pattern)
        . ' ' . escapeshellarg($root . '/App/Controller')
        . ' ' . escapeshellarg($root . '/App/view')
        . ' ' . escapeshellarg($root . '/App/Library')
        . ' ' . escapeshellarg($root . '/App/Mutual')
        . ' ' . escapeshellarg($root . '/App/model')
        . ' 2>/dev/null';

    $raw = shell_exec($searchCmd) ?: '';
    $lines = array_values(array_filter(explode("\n", trim($raw)), static fn ($v) => $v !== ''));

    $controllerRefs = [];
    $viewRefs = [];
    $otherRefs = [];
    $screens = [];
    $controllerActions = [];

    foreach ($lines as $line) {
        if (!preg_match('/^(.+?):(\d+):(.*)$/', $line, $m)) {
            continue;
        }

        $file = $m[1];
        $lineNo = (int) $m[2];

        if (str_contains($file, '/App/Controller/')) {
            $controllerRefs[] = $file . ':' . $lineNo;
            $controllerName = basename($file, '.php');
            $functions = $controllerFunctionIndex[$file] ?? [];
            $action = actionForLine($functions, $lineNo);
            if ($action !== null) {
                $controllerActions[] = $controllerName . '::' . $action;

                $visibility = 'public';
                foreach ($functions as $fn) {
                    if ($fn['name'] === $action) {
                        $visibility = $fn['visibility'];
                        break;
                    }
                }

                if ($visibility === 'public') {
                    $route = '/' . $controllerName . '/' . $action;
                    $screens[$route] = true;

                    $viewPath = $root . '/App/view/' . $controllerName . '/' . $action . '.view.php';
                    if (is_file($viewPath)) {
                        $screens[$route . ' -> App/view/' . $controllerName . '/' . $action . '.view.php'] = true;
                    }
                }
            }
        } elseif (str_contains($file, '/App/view/')) {
            $viewRefs[] = $file . ':' . $lineNo;
        } else {
            $otherRefs[] = $file . ':' . $lineNo;
        }
    }

    $modelPath = $root . '/App/model/IdentifierPmacontrol/' . $table . '.php';
    $modelShort = is_file($modelPath) ? 'App/model/IdentifierPmacontrol/' . $table . '.php' : '(absent)';

    $purpose = inferPurpose($table, (string) ($tableMeta['table_comment'] ?? ''), $controllerActions);

    $doc[] = '## Table `' . $table . '`';
    $doc[] = '';
    $doc[] = '- Rôle: ' . $purpose;
    $doc[] = '- Modèle PHP: `' . $modelShort . '`';
    $doc[] = '- Type/engine: `' . ($tableMeta['table_type'] ?? 'n/a') . '` / `' . ($tableMeta['engine'] ?? 'n/a') . '`';
    $doc[] = '- Volumétrie (estimateur moteur): rows=`' . (string) ($tableMeta['table_rows'] ?? 'n/a') . '`, data=`' . (string) ($tableMeta['data_length'] ?? 'n/a') . '`, index=`' . (string) ($tableMeta['index_length'] ?? 'n/a') . '`';
    $doc[] = '- Collation: `' . (string) ($tableMeta['table_collation'] ?? 'n/a') . '`';
    $doc[] = '- Dates: create=`' . (string) ($tableMeta['create_time'] ?? 'n/a') . '`, update=`' . (string) ($tableMeta['update_time'] ?? 'n/a') . '`';
    $doc[] = '';

    $doc[] = '### Colonnes';
    $doc[] = '';
    $doc[] = '| # | Colonne | Type | Null | Défaut | Clé | Extra |';
    $doc[] = '|---:|---|---|---|---|---|---|';
    foreach ($columnsByTable[$table] ?? [] as $col) {
        $doc[] = '| ' . $col['ordinal_position']
            . ' | `' . $col['column_name'] . '`'
            . ' | `' . str_replace('|', '\\|', (string) $col['column_type']) . '`'
            . ' | `' . $col['is_nullable'] . '`'
            . ' | `' . str_replace('|', '\\|', (string) ($col['column_default'] ?? 'NULL')) . '`'
            . ' | `' . (string) ($col['column_key'] ?? '') . '`'
            . ' | `' . str_replace('|', '\\|', (string) ($col['extra'] ?? '')) . '` |';
    }
    $doc[] = '';

    $doc[] = '### Clés étrangères';
    $doc[] = '';
    if (!empty($fksByTable[$table])) {
        foreach ($fksByTable[$table] as $fk) {
            $doc[] = '- `' . $fk['constraint_name'] . '`: `' . $fk['column_name'] . '` -> `' . $fk['referenced_table_name'] . '.' . $fk['referenced_column_name'] . '`';
        }
    } else {
        $doc[] = '- Aucune FK explicite détectée dans `information_schema.key_column_usage`.';
    }
    $doc[] = '';

    $doc[] = '### Corrélation Code PHP';
    $doc[] = '';
    $doc[] = '- Références contrôleurs: ' . count($controllerRefs);
    foreach (array_slice($controllerRefs, 0, 20) as $ref) {
        $doc[] = '  - `' . str_replace($root . '/', '', $ref) . '`';
    }
    if (count($controllerRefs) > 20) {
        $doc[] = '  - `...` (' . (count($controllerRefs) - 20) . ' occurrences supplémentaires)';
    }

    $doc[] = '- Références vues: ' . count($viewRefs);
    foreach (array_slice($viewRefs, 0, 10) as $ref) {
        $doc[] = '  - `' . str_replace($root . '/', '', $ref) . '`';
    }
    if (count($viewRefs) > 10) {
        $doc[] = '  - `...` (' . (count($viewRefs) - 10) . ' occurrences supplémentaires)';
    }

    $doc[] = '- Références autres (lib/model): ' . count($otherRefs);
    foreach (array_slice($otherRefs, 0, 10) as $ref) {
        $doc[] = '  - `' . str_replace($root . '/', '', $ref) . '`';
    }
    if (count($otherRefs) > 10) {
        $doc[] = '  - `...` (' . (count($otherRefs) - 10) . ' occurrences supplémentaires)';
    }

    $doc[] = '- Écrans/Routes probables:';
    if (!empty($screens)) {
        foreach (array_slice(array_keys($screens), 0, 20) as $screen) {
            $doc[] = '  - `' . $screen . '`';
        }
        if (count($screens) > 20) {
            $doc[] = '  - `...` (' . (count($screens) - 20) . ' routes supplémentaires)';
        }
    } else {
        $doc[] = '  - Aucun écran direct détecté (table potentiellement technique ou utilisée indirectement).';
    }

    $doc[] = '';
}

file_put_contents($outputFile, implode("\n", $doc) . "\n");

$dotFk = parseDotForeignKeys($dotFile);
if (!empty($dotFk)) {
    $append = [];
    $append[] = '';
    $append[] = '## Annexe FK Complète (Source DOT)';
    $append[] = '';
    $append[] = '- Source DOT: `' . str_replace($root . '/', '', $dotFile) . '`';
    if (is_file((string) $dotSvgFile)) {
        $append[] = '- Schéma SVG correspondant: `' . str_replace($root . '/', '', (string) $dotSvgFile) . '`';
    }
    $append[] = '- Nombre de relations FK extraites: `' . count($dotFk) . '`';
    $append[] = '';
    $append[] = '| # | Relation FK |';
    $append[] = '|---:|---|';
    foreach ($dotFk as $i => $fk) {
        $append[] = '| ' . ($i + 1) . ' | `' . str_replace('|', '\|', $fk) . '` |';
    }
    $append[] = '';
    file_put_contents($outputFile, implode("\n", $append), FILE_APPEND);
}

echo "Generated: {$outputFile}\n";
echo "Tables documented: " . count($tables) . "\n";
