<?php

declare(strict_types=1);

namespace App\Library;

/**
 * Small GTID set algebra helper for replication observability.
 *
 * Supports MySQL UUID GTIDs (`uuid:1-5:8`) and the MariaDB forms seen in
 * PmaControl data (`domain-server-seq`, `domain-server-start-end` and the
 * compatibility form `domain-server-seq:1-3`). Mixed-family sets are marked
 * incompatible and are never subtracted from each other.
 */
final class GtidSet
{
    /** @var array<string,list<array{0:int,1:int}>> */
    private array $intervals = [];
    private string $family;
    /** @var array<string,string> */
    private array $styles = [];

    private function __construct(string $family = 'empty')
    {
        $this->family = $family;
    }

    public static function parse(string $input): self
    {
        $input = trim($input);
        $set = new self($input === '' ? 'empty' : 'unknown');
        if ($input === '') {
            return $set;
        }

        $family = null;
        foreach (preg_split('/\s*,\s*/', $input) ?: [] as $token) {
            $token = trim($token);
            if ($token === '') {
                continue;
            }

            $parsed = self::parseToken($token);
            if ($parsed === null) {
                $set->family = 'invalid';
                return $set;
            }

            [$tokenFamily, $key, $ranges, $style] = $parsed;
            if ($family !== null && $family !== $tokenFamily) {
                $set->family = 'mixed';
                return $set;
            }
            $family = $tokenFamily;
            $set->styles[$key] = $style;
            foreach ($ranges as $range) {
                $set->addRange($key, $range[0], $range[1]);
            }
        }

        $set->family = $family ?? 'empty';
        $set->normalize();

        return $set;
    }

    /**
     * @return null|array{0:string,1:string,2:list<array{0:int,1:int}>,3:string}
     */
    private static function parseToken(string $token): ?array
    {
        if (preg_match('/^([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}):(.*)$/i', $token, $m)) {
            $ranges = self::parseIntervalList($m[2], ':');
            return $ranges === [] ? null : ['mysql', strtolower($m[1]), $ranges, 'mysql'];
        }

        if (preg_match('/^(\d+-\d+-\d+):(.*)$/', $token, $m)) {
            $ranges = self::parseIntervalList($m[2], ':');
            return $ranges === [] ? null : ['mariadb', $m[1], $ranges, 'mariadb-colon'];
        }

        if (preg_match('/^(\d+)-(\d+)-(\d+)(?:-(\d+))?$/', $token, $m)) {
            $start = (int) $m[3];
            $end = isset($m[4]) && $m[4] !== '' ? (int) $m[4] : $start;
            if ($start < 1 || $end < $start) {
                return null;
            }
            return ['mariadb', $m[1] . '-' . $m[2], [[$start, $end]], 'mariadb'];
        }

        return null;
    }

    /**
     * @return list<array{0:int,1:int}>
     */
    private static function parseIntervalList(string $value, string $separator): array
    {
        $ranges = [];
        foreach (preg_split('/' . preg_quote($separator, '/') . '/', trim($value)) ?: [] as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if (preg_match('/^(\d+)-(\d+)$/', $part, $m)) {
                $start = (int) $m[1];
                $end = (int) $m[2];
            } elseif (preg_match('/^\d+$/', $part)) {
                $start = $end = (int) $part;
            } else {
                return [];
            }
            if ($start < 1 || $end < $start) {
                return [];
            }
            $ranges[] = [$start, $end];
        }

        return $ranges;
    }

    public function family(): string
    {
        return $this->family;
    }

    public function isCompatibleWith(self $other): bool
    {
        if ($this->family === 'empty' || $other->family === 'empty') {
            return true;
        }

        return in_array($this->family, ['mysql', 'mariadb'], true)
            && $this->family === $other->family;
    }

    public function subtract(self $other): self
    {
        if (!$this->isCompatibleWith($other)) {
            return new self('incompatible');
        }

        $result = new self($this->family);
        $result->styles = $this->styles;
        foreach ($this->intervals as $key => $ranges) {
            $remaining = $ranges;
            foreach ($other->intervals[$key] ?? [] as $remove) {
                $remaining = self::subtractRangeList($remaining, $remove);
            }
            foreach ($remaining as $range) {
                $result->addRange($key, $range[0], $range[1]);
            }
        }
        $result->normalize();

        return $result;
    }

    public function intersect(self $other): self
    {
        if (!$this->isCompatibleWith($other)) {
            return new self('incompatible');
        }

        $result = new self($this->family === 'empty' ? $other->family : $this->family);
        $result->styles = $this->styles + $other->styles;
        foreach ($this->intervals as $key => $ranges) {
            foreach ($ranges as $left) {
                foreach ($other->intervals[$key] ?? [] as $right) {
                    $start = max($left[0], $right[0]);
                    $end = min($left[1], $right[1]);
                    if ($start <= $end) {
                        $result->addRange($key, $start, $end);
                    }
                }
            }
        }
        $result->normalize();

        return $result;
    }

    public function count(): int
    {
        $count = 0;
        foreach ($this->intervals as $ranges) {
            foreach ($ranges as $range) {
                $count += $range[1] - $range[0] + 1;
            }
        }

        return $count;
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function toString(): string
    {
        if ($this->family === 'incompatible') {
            return '(incompatible GTID families)';
        }
        if ($this->intervals === []) {
            return '';
        }

        ksort($this->intervals, SORT_NATURAL);
        $tokens = [];
        foreach ($this->intervals as $key => $ranges) {
            $style = $this->styles[$key] ?? $this->family;
            if ($this->family === 'mysql' || $style === 'mariadb-colon') {
                $tokens[] = $key . ':' . implode(':', array_map([self::class, 'formatRange'], $ranges));
                continue;
            }

            foreach ($ranges as $range) {
                $tokens[] = $key . '-' . self::formatRange($range);
            }
        }

        return implode(',', $tokens);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function addRange(string $key, int $start, int $end): void
    {
        $this->intervals[$key][] = [$start, $end];
    }

    private function normalize(): void
    {
        foreach ($this->intervals as $key => $ranges) {
            usort($ranges, static fn(array $a, array $b): int => $a[0] <=> $b[0]);
            $merged = [];
            foreach ($ranges as $range) {
                if ($merged === [] || $range[0] > $merged[array_key_last($merged)][1] + 1) {
                    $merged[] = $range;
                    continue;
                }
                $idx = array_key_last($merged);
                $merged[$idx][1] = max($merged[$idx][1], $range[1]);
            }
            $this->intervals[$key] = $merged;
        }
        if ($this->count() === 0 && in_array($this->family, ['mysql', 'mariadb', 'unknown'], true)) {
            $this->family = 'empty';
        }
    }

    /**
     * @param list<array{0:int,1:int}> $ranges
     * @param array{0:int,1:int} $remove
     * @return list<array{0:int,1:int}>
     */
    private static function subtractRangeList(array $ranges, array $remove): array
    {
        $out = [];
        foreach ($ranges as $range) {
            if ($remove[1] < $range[0] || $remove[0] > $range[1]) {
                $out[] = $range;
                continue;
            }
            if ($remove[0] > $range[0]) {
                $out[] = [$range[0], $remove[0] - 1];
            }
            if ($remove[1] < $range[1]) {
                $out[] = [$remove[1] + 1, $range[1]];
            }
        }

        return $out;
    }

    /**
     * @param array{0:int,1:int} $range
     */
    private static function formatRange(array $range): string
    {
        return $range[0] === $range[1]
            ? (string) $range[0]
            : $range[0] . '-' . $range[1];
    }
}
