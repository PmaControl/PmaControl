<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit;

/**
 * Contract every ProxySQL audit check must satisfy (#895 / #896).
 *
 * Implementations are stateless — the runner instantiates them once per
 * invocation and discards them. They receive:
 *   - $db:  Glial DB handle on the ProxySQL admin connection
 *           (`Sgbd::sql('proxysql_<id>')`).
 *   - $ctx: optional cross-source context the controller passes
 *           through, e.g. the pmacontrol DB handle, the
 *           id_proxysql_server, last-known mysql_server metadata.
 *           Keep keys stable; document any new ones in the registry.
 *
 * Returning an empty list is the success path. Returning multiple
 * findings is fine (e.g. one per offending row).
 *
 * Throwing is allowed; the runner converts unhandled throws into a
 * synthetic info-severity finding so one bad check can't bury the
 * rest of the report.
 */
interface AuditCheck
{
    /**
     * Stable, machine-friendly identifier (kebab- or snake-cased).
     * Surfaces in JSON exports and operator tooling.
     */
    public function id(): string;

    /**
     * One of the seven category buckets the EPIC defines:
     * topology / hostgroup / user / monitor / query_rules /
     * cluster / runtime — plus the framework-internal `meta`.
     */
    public function category(): string;

    /**
     * @param object              $db  Glial DB on the ProxySQL admin connection
     * @param array<string,mixed> $ctx Cross-source context (optional)
     * @return list<Finding>
     */
    public function run(object $db, array $ctx = []): array;
}
