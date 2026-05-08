<?php

declare(strict_types=1);

namespace App\Library\ProxySqlAudit;

/**
 * Flat list of audit-check class names. Adding a new check = adding
 * one line here. Keeping it dumb-simple so the failure mode is "check
 * didn't run" rather than "magic loader picked the wrong class".
 *
 * Order matters only for output; the runner iterates in registry
 * order so the first findings the operator sees are the ones we
 * judged most actionable.
 */
final class Registry
{
    /**
     * @return list<class-string<AuditCheck>>
     */
    public static function checks(): array
    {
        // Lots 1-7 plug their classes in here as their sub-issues land.
        return [
            \App\Library\ProxySqlAudit\Checks\Topology\GaleraAsGroupReplication::class,       // #899
            \App\Library\ProxySqlAudit\Checks\Topology\GroupReplicationAsGalera::class,       // #900
            \App\Library\ProxySqlAudit\Checks\Topology\AsyncReplicationAsClusterTable::class, // #901
            \App\Library\ProxySqlAudit\Checks\Topology\GaleraHostgroupIdCollision::class,     // #902
            \App\Library\ProxySqlAudit\Checks\Topology\BackendInMultipleClusterTypes::class,  // #903
            \App\Library\ProxySqlAudit\Checks\Hostgroup\EmptyDefaultHostgroup::class,         // #904
        ];
    }
}
