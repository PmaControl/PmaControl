<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit\Checks\Monitor;

use App\Library\ProxySqlAudit\AuditCheck;
use App\Library\ProxySqlAudit\Finding;

/**
 * #917 — flags `mysql-monitor_*` interval / timeout settings that
 * fall outside the recommended [100 ms, 30000 ms] window.
 *
 * Below 100 ms: aggressive enough to noticeably load the backends
 * with monitor traffic.
 * Above 30000 ms: failover detection lags past the wall-clock
 * tolerance most clients have for connection failure.
 *
 * Returns one warning per offending variable so the operator gets
 * a clean per-variable fix command.
 */
final class IntervalOutliers implements AuditCheck
{
    private const MIN_MS = 100;
    private const MAX_MS = 30000;

    public function id(): string
    {
        return 'monitor.interval-outliers';
    }

    public function category(): string
    {
        return 'monitor';
    }

    public function run(object $db, array $ctx = []): array
    {
        if (!method_exists($db, 'sql_query_silent')) {
            return [];
        }

        $sql = "SELECT variable_name, variable_value "
             . "FROM runtime_global_variables "
             . "WHERE variable_name LIKE 'mysql-monitor\\_%' "
             .   "AND (variable_name LIKE '%\\_interval%' "
             .     "OR variable_name LIKE '%\\_timeout%')";

        $res = $db->sql_query_silent($sql);
        if ($res === false) {
            return [];
        }

        $findings = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $name = (string) ($row['variable_name'] ?? '');
            $rawValue = (string) ($row['variable_value'] ?? '');
            if ($name === '' || $rawValue === '' || !ctype_digit($rawValue)) {
                continue;
            }
            $value = (int) $rawValue;

            if ($value >= self::MIN_MS && $value <= self::MAX_MS) {
                continue;
            }

            $findings[] = new Finding(
                Finding::SEVERITY_WARNING,
                $this->category(),
                $this->id(),
                sprintf(
                    '%s = %d ms is out of the recommended range [%d, %d]',
                    $name,
                    $value,
                    self::MIN_MS,
                    self::MAX_MS
                ),
                $sql . "\n  → " . $name . '=' . $value,
                "Set " . $name . " to a value inside [" . self::MIN_MS . ", " . self::MAX_MS . "] ms:\n"
                . "  SET " . $name . "=<safe_value>;\n"
                . "  LOAD MYSQL VARIABLES TO RUNTIME;\n"
                . "  SAVE MYSQL VARIABLES TO DISK;",
                null
            );
        }

        return $findings;
    }
}
