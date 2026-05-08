<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit;

/**
 * Drives every registered AuditCheck against a ProxySQL admin
 * connection (#895 / #896).
 *
 * Contract:
 *   - Calls each check's `run()` exactly once.
 *   - Catches every Throwable so one buggy check can't bury the rest
 *     of the report; the failure becomes a `meta` info-severity
 *     finding the operator can see and report.
 *   - Returns a flat list of Findings in registry order.
 *
 * The runner does NOT cache or persist — that's the home-page
 * surfacing job (Lot 9 / #929) so this stays a pure function of its
 * inputs and trivially testable.
 */
final class Runner
{
    /**
     * @param object              $db    Glial DB on the ProxySQL admin connection
     * @param array<string,mixed> $ctx   Cross-source context for every check
     * @param list<class-string<AuditCheck>>|null $checks  Optional override (tests / dry-run)
     * @return list<Finding>
     */
    public static function runAll(object $db, array $ctx = [], ?array $checks = null): array
    {
        $checks = $checks ?? Registry::checks();
        $findings = [];

        foreach ($checks as $checkClass) {
            try {
                $check = new $checkClass();
                if (!$check instanceof AuditCheck) {
                    $findings[] = self::syntheticFailure(
                        $checkClass,
                        'meta',
                        'Audit check class does not implement AuditCheck',
                        'class: ' . $checkClass
                    );
                    continue;
                }

                $rows = $check->run($db, $ctx);
                foreach ($rows as $row) {
                    if ($row instanceof Finding) {
                        $findings[] = $row;
                    }
                }
            } catch (\Throwable $e) {
                $findings[] = self::syntheticFailure(
                    $checkClass,
                    'meta',
                    'Audit check threw: ' . substr($e->getMessage(), 0, 200),
                    sprintf('%s thrown at %s:%d', get_class($e), $e->getFile(), $e->getLine())
                );
            }
        }

        return $findings;
    }

    private static function syntheticFailure(
        string $checkClass,
        string $category,
        string $title,
        string $evidence
    ): Finding {
        return new Finding(
            Finding::SEVERITY_INFO,
            $category,
            'meta:' . $checkClass,
            $title,
            $evidence,
            'No operator action — open an issue against the audit framework.'
        );
    }
}
