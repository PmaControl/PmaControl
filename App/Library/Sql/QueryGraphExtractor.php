<?php

declare(strict_types=1);

namespace App\Library\Sql;

use PhpMyAdmin\SqlParser\Components\Condition;
use PhpMyAdmin\SqlParser\Components\Expression;
use PhpMyAdmin\SqlParser\Components\JoinKeyword;
use PhpMyAdmin\SqlParser\Lexer;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Token;
use Throwable;

final class QueryGraphExtractor
{
    /**
     * @return array{
     *     statement_type:string,
     *     select_fields:array<int,array<string,mixed>>,
     *     tables:array<int,array<string,mixed>>,
     *     aliases:array<string,array<string,mixed>>,
     *     joins:array<int,array<string,mixed>>,
     *     where_fields:array<int,array<string,mixed>>,
     *     errors:array<int,string>
     * }
     */
    public static function extract(string $sql): array
    {
        $parser = new Parser($sql);
        $statement = $parser->statements[0] ?? null;
        $errors = [];

        foreach ($parser->errors as $error) {
            $errors[] = method_exists($error, 'getMessage') ? $error->getMessage() : (string)$error;
        }

        if ($statement === null) {
            return self::emptyGraph('UNKNOWN', ['No SQL statement could be parsed.']);
        }

        $statementType = self::statementType($statement);
        $tables = self::extractTablesFromStatement($statement, $sql);
        $aliases = self::indexTablesByAlias($tables);

        return [
            'statement_type' => $statementType,
            'select_fields' => self::extractSelectFieldsFromStatement($statement),
            'tables' => $tables,
            'aliases' => $aliases,
            'joins' => self::extractJoinsFromStatement($statement),
            'where_fields' => self::extractWhereFieldsFromStatement($statement),
            'errors' => $errors,
        ];
    }

    /**
     * @return array<int,array{database:?string,table:string,alias:?string,offset:int,line:int}>
     */
    public static function extractTablesWithOffsets(string $sql): array
    {
        return array_map(
            static function (array $table): array {
                return [
                    'database' => $table['database'],
                    'table' => $table['table'],
                    'alias' => $table['alias'],
                    'offset' => $table['offset'],
                    'line' => $table['line'],
                ];
            },
            self::extract($sql)['tables']
        );
    }

    /**
     * @return array<int,array{database:?string,table:string,alias:?string}>
     */
    public static function extractTablesAndAliases(string $sql): array
    {
        return array_map(
            static function (array $table): array {
                return [
                    'database' => $table['database'],
                    'table' => $table['table'],
                    'alias' => $table['alias'],
                ];
            },
            self::extract($sql)['tables']
        );
    }

    /**
     * @return array<int,array{database:?string,table:string}>
     */
    public static function extractTablesFromSql(string $sql): array
    {
        return array_map(
            static function (array $table): array {
                return [
                    'database' => $table['database'],
                    'table' => $table['table'],
                ];
            },
            self::extract($sql)['tables']
        );
    }

    private static function emptyGraph(string $statementType, array $errors): array
    {
        return [
            'statement_type' => $statementType,
            'select_fields' => [],
            'tables' => [],
            'aliases' => [],
            'joins' => [],
            'where_fields' => [],
            'errors' => $errors,
        ];
    }

    private static function statementType(object $statement): string
    {
        $class = get_class($statement);
        if (preg_match('/\\\\([A-Za-z]+)Statement$/', $class, $matches) === 1) {
            return strtoupper($matches[1]);
        }

        return 'UNKNOWN';
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private static function extractTablesFromStatement(object $statement, string $sql): array
    {
        $positions = self::extractTablePositions($sql);
        $tables = [];

        foreach (self::tableExpressions($statement) as $entry) {
            $expression = $entry['expression'];
            $table = self::normalizeTableExpression($expression, (string)$entry['source'], (string)$entry['join_type']);
            if ($table === null) {
                continue;
            }

            $position = self::consumeBestPosition($positions, $table['database'], $table['table'], $table['source']);
            $table['offset'] = $position['offset'] ?? 0;
            $table['line'] = $position['line'] ?? 1;
            $tables[] = $table;
        }

        return $tables;
    }

    /**
     * @return array<int,array{expression:Expression,source:string,join_type:string}>
     */
    private static function tableExpressions(object $statement): array
    {
        $expressions = [];

        foreach (['from' => 'FROM', 'tables' => 'TABLE', 'table' => 'TABLE'] as $property => $source) {
            if (!isset($statement->{$property})) {
                continue;
            }

            $value = $statement->{$property};
            if ($value instanceof Expression) {
                $expressions[] = ['expression' => $value, 'source' => $source, 'join_type' => ''];
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $expression) {
                    if ($expression instanceof Expression) {
                        $expressions[] = ['expression' => $expression, 'source' => $source, 'join_type' => ''];
                    }
                }
            }
        }

        if (isset($statement->into) && isset($statement->into->dest) && $statement->into->dest instanceof Expression) {
            $expressions[] = ['expression' => $statement->into->dest, 'source' => 'INTO', 'join_type' => ''];
        }

        foreach (self::joinKeywords($statement) as $join) {
            if ($join->expr instanceof Expression) {
                $expressions[] = [
                    'expression' => $join->expr,
                    'source' => 'JOIN',
                    'join_type' => (string)$join->type,
                ];
            }
        }

        return $expressions;
    }

    private static function normalizeTableExpression(Expression $expression, string $source, string $joinType): ?array
    {
        $table = self::cleanIdentifier((string)($expression->table ?: $expression->expr));
        if ($table === '' || str_starts_with($table, '(')) {
            return null;
        }

        $alias = self::cleanIdentifier((string)($expression->alias ?? ''));
        if ($alias === '') {
            $alias = null;
        }

        return [
            'database' => self::nullableIdentifier($expression->database),
            'table' => $table,
            'alias' => $alias,
            'source' => $source,
            'join_type' => $joinType !== '' ? $joinType : null,
        ];
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private static function indexTablesByAlias(array $tables): array
    {
        $aliases = [];
        foreach ($tables as $table) {
            $key = (string)($table['alias'] ?: $table['table']);
            $aliases[$key] = $table;
        }

        return $aliases;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private static function extractSelectFieldsFromStatement(object $statement): array
    {
        if (!isset($statement->expr) || !is_array($statement->expr)) {
            return [];
        }

        $fields = [];
        foreach ($statement->expr as $index => $expression) {
            if (!$expression instanceof Expression) {
                continue;
            }

            $fields[] = [
                'order' => $index + 1,
                'expression' => (string)$expression->expr,
                'alias' => self::nullableIdentifier($expression->alias),
                'table' => self::nullableIdentifier($expression->table),
                'field' => self::nullableIdentifier($expression->column),
            ];
        }

        return $fields;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private static function extractJoinsFromStatement(object $statement): array
    {
        $joins = [];
        foreach (self::joinKeywords($statement) as $join) {
            foreach ((array)$join->on as $condition) {
                if (!$condition instanceof Condition || count($condition->identifiers) < 4) {
                    continue;
                }

                $joins[] = [
                    'type' => strtolower((string)$join->type),
                    'from_table' => $condition->identifiers[0],
                    'from_field' => $condition->identifiers[1],
                    'to_table' => $condition->identifiers[2],
                    'to_field' => $condition->identifiers[3],
                    'condition' => $condition->expr,
                ];
            }
        }

        return $joins;
    }

    /**
     * @return array<int,JoinKeyword>
     */
    private static function joinKeywords(object $statement): array
    {
        if (!isset($statement->join) || !is_array($statement->join)) {
            return [];
        }

        return array_values(array_filter(
            $statement->join,
            static fn($join): bool => $join instanceof JoinKeyword
        ));
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private static function extractWhereFieldsFromStatement(object $statement): array
    {
        if (!isset($statement->where) || !is_array($statement->where)) {
            return [];
        }

        $fields = [];
        foreach ($statement->where as $condition) {
            if (!$condition instanceof Condition || $condition->isOperator || count($condition->identifiers) < 2) {
                continue;
            }

            $fields[] = [
                'table' => $condition->identifiers[0],
                'field' => $condition->identifiers[1],
                'expression' => $condition->expr,
            ];
        }

        return $fields;
    }

    /**
     * @return array<int,array{database:?string,table:string,source:string,offset:int,line:int,used:bool}>
     */
    private static function extractTablePositions(string $sql): array
    {
        try {
            $lexer = new Lexer($sql);
        } catch (Throwable) {
            return [];
        }

        $positions = [];
        $tokens = $lexer->list->tokens;
        $expectTable = false;
        $source = '';
        $count = count($tokens);

        for ($index = 0; $index < $count; $index++) {
            $token = $tokens[$index];
            if (self::isIgnoredToken($token)) {
                continue;
            }

            $keyword = strtoupper((string)$token->keyword);
            $value = strtoupper((string)$token->value);
            if ($keyword === 'FROM' || $keyword === 'INTO' || $keyword === 'UPDATE' || str_ends_with($keyword, 'JOIN')) {
                $expectTable = true;
                $source = str_ends_with($keyword, 'JOIN') ? 'JOIN' : $keyword;
                continue;
            }

            if ($keyword === 'DELETE') {
                $source = 'DELETE';
                continue;
            }

            if (!$expectTable) {
                if ((string)$token->value === ',' && in_array($source, ['FROM', 'TABLE'], true)) {
                    $expectTable = true;
                }
                continue;
            }

            if ($value === '(') {
                $expectTable = false;
                continue;
            }

            if (!self::isIdentifierToken($token)) {
                continue;
            }

            $start = (int)$token->position;
            $first = self::cleanIdentifier((string)$token->value);
            $database = null;
            $table = $first;
            $next = self::nextNonIgnoredToken($tokens, $index + 1);

            if ($next !== null && (string)$next['token']->value === '.') {
                $tableToken = self::nextNonIgnoredToken($tokens, $next['index'] + 1);
                if ($tableToken !== null && self::isIdentifierToken($tableToken['token'])) {
                    $database = $first;
                    $table = self::cleanIdentifier((string)$tableToken['token']->value);
                }
            }

            if ($table !== '') {
                $positions[] = [
                    'database' => $database,
                    'table' => $table,
                    'source' => $source,
                    'offset' => $start,
                    'line' => substr_count(substr($sql, 0, $start), "\n") + 1,
                    'used' => false,
                ];
            }

            $expectTable = false;
        }

        return $positions;
    }

    /**
     * @param array<int,array{database:?string,table:string,source:string,offset:int,line:int,used:bool}> $positions
     *
     * @return array{offset:int,line:int}|null
     */
    private static function consumeBestPosition(array &$positions, ?string $database, string $table, string $source): ?array
    {
        foreach ($positions as $index => $position) {
            if ($position['used']) {
                continue;
            }

            if ($position['table'] !== $table) {
                continue;
            }

            if ($database !== null && $position['database'] !== $database) {
                continue;
            }

            if ($source !== 'TABLE' && $position['source'] !== $source) {
                continue;
            }

            $positions[$index]['used'] = true;
            return ['offset' => $position['offset'], 'line' => $position['line']];
        }

        return null;
    }

    private static function nextNonIgnoredToken(array $tokens, int $start): ?array
    {
        $count = count($tokens);
        for ($index = $start; $index < $count; $index++) {
            if (!self::isIgnoredToken($tokens[$index])) {
                return ['index' => $index, 'token' => $tokens[$index]];
            }
        }

        return null;
    }

    private static function isIgnoredToken(Token $token): bool
    {
        return in_array($token->type, [Token::TYPE_WHITESPACE, Token::TYPE_COMMENT], true);
    }

    private static function isIdentifierToken(Token $token): bool
    {
        return in_array($token->type, [Token::TYPE_SYMBOL, Token::TYPE_KEYWORD, Token::TYPE_NONE], true);
    }

    private static function nullableIdentifier($value): ?string
    {
        $value = self::cleanIdentifier((string)($value ?? ''));

        return $value === '' ? null : $value;
    }

    private static function cleanIdentifier(string $identifier): string
    {
        return trim($identifier, "` \t\n\r\0\x0B");
    }
}
