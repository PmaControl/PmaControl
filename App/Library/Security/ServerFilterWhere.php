<?php

declare(strict_types=1);

namespace App\Library\Security;

use InvalidArgumentException;

final class ServerFilterWhere
{
    private const DENY_ALL = ' AND 0=1';

    /**
     * Cap for the user-controlled $idMysqlServer branch — anti-abuse is irrelevant
     * for internal callers (Util::filterServerList → Extraction2 → Dot3 daemon)
     * which legitimately pass the full monitored-server list (issue #732).
     */
    private const INTERNAL_LIST_MAX_IDS = PHP_INT_MAX;

    public static function build(array &$get, array $session, $idMysqlServer = [], string $alias = 'a'): string
    {
        $alias = self::normalizeAlias($alias);
        $where = '';

        $environment = self::readSelection($get, $session, 'environment');
        if ($environment['present']) {
            $ids = PositiveIntegerSelection::normalizeList($environment['value']);
            if ($ids === null) {
                return self::DENY_ALL;
            }
            $where .= self::andIn($alias, 'id_environment', $ids);
        }

        $client = self::readSelection($get, $session, 'client');
        if ($client['present']) {
            $ids = PositiveIntegerSelection::normalizeList($client['value']);
            if ($ids === null) {
                return self::DENY_ALL;
            }
            $where .= self::andIn($alias, 'id_client', $ids);
        }

        if (self::hasServerSelection($idMysqlServer)) {
            $ids = PositiveIntegerSelection::normalizeList($idMysqlServer, self::INTERNAL_LIST_MAX_IDS);
            if ($ids === null) {
                return self::DENY_ALL;
            }
            $where .= self::andIn($alias, 'id', $ids) . ' ';
        }

        return $where;
    }

    private static function readSelection(array &$get, array $session, string $group): array
    {
        if (array_key_exists($group, $get)) {
            if (!is_array($get[$group]) || !array_key_exists('libelle', $get[$group])) {
                return ['present' => true, 'value' => null];
            }

            if (!self::isEmptySelection($get[$group]['libelle'])) {
                return ['present' => true, 'value' => $get[$group]['libelle']];
            }
        }

        if (array_key_exists($group, $session)) {
            if (!is_array($session[$group]) || !array_key_exists('libelle', $session[$group])) {
                return ['present' => true, 'value' => null];
            }

            if (!self::isEmptySelection($session[$group]['libelle'])) {
                $get[$group]['libelle'] = $session[$group]['libelle'];

                return ['present' => true, 'value' => $session[$group]['libelle']];
            }
        }

        return ['present' => false, 'value' => null];
    }

    private static function isEmptySelection($value): bool
    {
        return $value === null || (is_scalar($value) && trim((string) $value) === '');
    }

    private static function hasServerSelection($value): bool
    {
        if (is_array($value)) {
            return $value !== [];
        }

        if (is_scalar($value)) {
            $text = trim((string) $value);

            return $text !== '' && $text !== '0';
        }

        return $value !== null;
    }

    private static function andIn(string $alias, string $column, array $ids): string
    {
        return ' AND `' . $alias . '`.' . $column . ' IN (' . PositiveIntegerSelection::toCsv($ids) . ')';
    }

    private static function normalizeAlias(string $alias): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $alias)) {
            throw new InvalidArgumentException('SQL alias must be a simple identifier.');
        }

        return $alias;
    }
}
