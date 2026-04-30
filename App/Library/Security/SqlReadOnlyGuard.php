<?php

declare(strict_types=1);

namespace App\Library\Security;

final class SqlReadOnlyGuard
{
    private const ALLOWED_FIRST_TOKENS = [
        'DESC',
        'DESCRIBE',
        'EXPLAIN',
        'SELECT',
        'SHOW',
        'WITH',
    ];

    private const BLOCKED_KEYWORDS = [
        'ALTER',
        'ANALYZE',
        'BENCHMARK',
        'CALL',
        'CREATE',
        'DELETE',
        'DROP',
        'EXEC',
        'EXECUTE',
        'FLUSH',
        'GRANT',
        'GET_LOCK',
        'HANDLER',
        'INSERT',
        'INSTALL',
        'IS_FREE_LOCK',
        'IS_USED_LOCK',
        'KILL',
        'LOAD',
        'LOCK',
        'MASTER_POS_WAIT',
        'OPTIMIZE',
        'PROCEDURE',
        'RENAME',
        'REPAIR',
        'REPLACE',
        'RESET',
        'RELEASE_LOCK',
        'REVOKE',
        'SET',
        'SLEEP',
        'TRUNCATE',
        'UNINSTALL',
        'UNLOCK',
        'UPDATE',
        'USE',
        'WAIT_FOR_EXECUTED_GTID_SET',
        'WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS',
    ];

    private const BLOCKED_PHRASES = [
        ['FOR', 'UPDATE'],
        ['INTO', 'DUMPFILE'],
        ['INTO', 'OUTFILE'],
        ['LOCK', 'IN', 'SHARE', 'MODE'],
    ];

    public static function isReadOnly(string $sql): bool
    {
        $tokens = self::scan($sql);
        if ($tokens === null) {
            return false;
        }

        $meaningful = array_values(array_filter(
            $tokens,
            static fn (array $token): bool => $token['type'] === 'word' || $token['type'] === 'semicolon'
        ));

        if ($meaningful === [] || !self::hasValidSemicolons($meaningful)) {
            return false;
        }

        $words = array_values(array_map(
            static fn (array $token): string => $token['value'],
            array_filter($meaningful, static fn (array $token): bool => $token['type'] === 'word')
        ));

        if ($words === [] || !in_array($words[0], self::ALLOWED_FIRST_TOKENS, true)) {
            return false;
        }

        if (self::containsBlockedKeyword($words) || self::containsBlockedPhrase($words)) {
            return false;
        }

        if ($words[0] === 'WITH' && !in_array('SELECT', $words, true)) {
            return false;
        }

        return true;
    }

    /**
     * @return array<int,array{type:string,value:string}>|null
     */
    private static function scan(string $sql): ?array
    {
        $tokens = [];
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($char === '\'' || $char === '"' || $char === '`') {
                $i = self::skipQuotedString($sql, $i, $char);
                if ($i === null) {
                    return null;
                }
                continue;
            }

            if (($char === '-' && $next === '-') || $char === '#' || ($char === '/' && $next === '*')) {
                return null;
            }

            if ($char === ';') {
                $tokens[] = ['type' => 'semicolon', 'value' => ';'];
                continue;
            }

            if (self::isWordStart($char)) {
                $start = $i;
                while ($i + 1 < $length && self::isWordPart($sql[$i + 1])) {
                    $i++;
                }

                $tokens[] = [
                    'type' => 'word',
                    'value' => strtoupper(substr($sql, $start, $i - $start + 1)),
                ];
            }
        }

        return $tokens;
    }

    private static function skipQuotedString(string $sql, int $offset, string $quote): ?int
    {
        $length = strlen($sql);

        for ($i = $offset + 1; $i < $length; $i++) {
            if ($quote !== '`' && $sql[$i] === '\\') {
                $i++;
                continue;
            }

            if ($sql[$i] === $quote) {
                if ($i + 1 < $length && $sql[$i + 1] === $quote) {
                    $i++;
                    continue;
                }

                return $i;
            }
        }

        return null;
    }

    /**
     * @param array<int,array{type:string,value:string}> $tokens
     */
    private static function hasValidSemicolons(array $tokens): bool
    {
        $semicolonIndexes = [];
        foreach ($tokens as $index => $token) {
            if ($token['type'] === 'semicolon') {
                $semicolonIndexes[] = $index;
            }
        }

        if ($semicolonIndexes === []) {
            return true;
        }

        return count($semicolonIndexes) === 1 && $semicolonIndexes[0] === count($tokens) - 1;
    }

    /**
     * @param array<int,string> $words
     */
    private static function containsBlockedKeyword(array $words): bool
    {
        foreach ($words as $word) {
            if (in_array($word, self::BLOCKED_KEYWORDS, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int,string> $words
     */
    private static function containsBlockedPhrase(array $words): bool
    {
        $wordCount = count($words);
        foreach (self::BLOCKED_PHRASES as $phrase) {
            $phraseLength = count($phrase);
            if ($phraseLength > $wordCount) {
                continue;
            }

            for ($i = 0; $i <= $wordCount - $phraseLength; $i++) {
                if (array_slice($words, $i, $phraseLength) === $phrase) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function isWordStart(string $char): bool
    {
        $ord = ord($char);

        return ($ord >= 65 && $ord <= 90) || ($ord >= 97 && $ord <= 122) || $char === '_';
    }

    private static function isWordPart(string $char): bool
    {
        $ord = ord($char);

        return self::isWordStart($char) || ($ord >= 48 && $ord <= 57) || $char === '$';
    }
}
