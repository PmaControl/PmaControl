<?php

/**
 * Backward-compatible shim for jdorn/sql-formatter.
 *
 * Wraps doctrine/sql-formatter (namespaced, instance-based API) behind
 * the legacy global \SqlFormatter static API used throughout the codebase.
 */

use Doctrine\SqlFormatter\SqlFormatter as DoctrineSqlFormatter;
use Doctrine\SqlFormatter\HtmlHighlighter;
use Doctrine\SqlFormatter\NullHighlighter;

class SqlFormatter
{
    /** @var bool When true, format()/highlight() return plain text (no HTML). */
    public static bool $cli = false;

    private static ?DoctrineSqlFormatter $htmlInstance = null;
    private static ?DoctrineSqlFormatter $plainInstance = null;

    private static function getInstance(): DoctrineSqlFormatter
    {
        if (self::$cli) {
            if (self::$plainInstance === null) {
                self::$plainInstance = new DoctrineSqlFormatter(new NullHighlighter());
            }
            return self::$plainInstance;
        }

        if (self::$htmlInstance === null) {
            self::$htmlInstance = new DoctrineSqlFormatter(new HtmlHighlighter());
        }
        return self::$htmlInstance;
    }

    public static function format(?string $string, bool $highlight = true): string
    {
        if ($string === null || $string === '') {
            return '';
        }

        try {
            if (!$highlight || self::$cli) {
                return (new DoctrineSqlFormatter(new NullHighlighter()))->format($string);
            }
            return self::getInstance()->format($string);
        } catch (\Throwable $e) {
            return $highlight && !self::$cli ? '<pre>' . htmlspecialchars($string) . '</pre>' : $string;
        }
    }

    public static function highlight(?string $string): string
    {
        if ($string === null || $string === '') {
            return '';
        }

        try {
            return self::getInstance()->highlight($string);
        } catch (\Throwable $e) {
            return self::$cli ? $string : '<pre>' . htmlspecialchars($string) . '</pre>';
        }
    }

    public static function compress(?string $string): string
    {
        if ($string === null || $string === '') {
            return '';
        }

        return self::getInstance()->compress($string);
    }

    /**
     * Split a SQL string into individual queries on semicolons.
     * doctrine/sql-formatter dropped this method, so we reimplement it.
     */
    public static function splitQuery(?string $string): array
    {
        if ($string === null || $string === '') {
            return [];
        }

        $queries = [];
        $current = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBacktick = false;
        $inLineComment = false;
        $inBlockComment = false;
        $len = strlen($string);

        for ($i = 0; $i < $len; $i++) {
            $char = $string[$i];
            $next = ($i + 1 < $len) ? $string[$i + 1] : '';

            // Line comment
            if (!$inSingleQuote && !$inDoubleQuote && !$inBacktick && !$inBlockComment) {
                if (($char === '-' && $next === '-') || $char === '#') {
                    $inLineComment = true;
                }
            }
            if ($inLineComment) {
                $current .= $char;
                if ($char === "\n") {
                    $inLineComment = false;
                }
                continue;
            }

            // Block comment
            if (!$inSingleQuote && !$inDoubleQuote && !$inBacktick && !$inBlockComment && $char === '/' && $next === '*') {
                $inBlockComment = true;
                $current .= $char;
                continue;
            }
            if ($inBlockComment) {
                $current .= $char;
                if ($char === '*' && $next === '/') {
                    $current .= $next;
                    $i++;
                    $inBlockComment = false;
                }
                continue;
            }

            // Quoted strings
            if ($char === "'" && !$inDoubleQuote && !$inBacktick) {
                $inSingleQuote = !$inSingleQuote;
            } elseif ($char === '"' && !$inSingleQuote && !$inBacktick) {
                $inDoubleQuote = !$inDoubleQuote;
            } elseif ($char === '`' && !$inSingleQuote && !$inDoubleQuote) {
                $inBacktick = !$inBacktick;
            }

            // Semicolon outside quotes = query separator
            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $queries[] = $trimmed . ';';
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $trimmed = trim($current);
        if ($trimmed !== '') {
            $queries[] = $trimmed;
        }

        return $queries;
    }
}
