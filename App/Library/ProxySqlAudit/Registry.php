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
            \App\Library\ProxySqlAudit\Checks\Hostgroup\OrphanHostgroup::class,               // #905
            \App\Library\ProxySqlAudit\Checks\Hostgroup\AllServersShunnedTooLong::class,      // #906
            \App\Library\ProxySqlAudit\Checks\Hostgroup\ReaderHostgroupReadOnlyOff::class,    // #907
            \App\Library\ProxySqlAudit\Checks\Hostgroup\WriterCountExceedsMaxWriters::class,  // #908
            \App\Library\ProxySqlAudit\Checks\User\FrontendBackendTwinMissing::class,             // #909
            \App\Library\ProxySqlAudit\Checks\User\DefaultHostgroupUndefined::class,              // #910
            \App\Library\ProxySqlAudit\Checks\User\SuspiciousDefaultSchema::class,                // #911
            \App\Library\ProxySqlAudit\Checks\User\MonitorUserMissingOnBackends::class,           // #912
            \App\Library\ProxySqlAudit\Checks\User\MonitorUserMissingReplicationGrants::class,    // #913
            \App\Library\ProxySqlAudit\Checks\User\MonitorUserHostMismatch::class,                // #914
            \App\Library\ProxySqlAudit\Checks\User\WeakDefaultPassword::class,                    // #915
            \App\Library\ProxySqlAudit\Checks\Monitor\MonitorUsernameUnset::class,                // #916
            \App\Library\ProxySqlAudit\Checks\Monitor\IntervalOutliers::class,                    // #917
            \App\Library\ProxySqlAudit\Checks\Monitor\PtHeartbeatMisconfig::class,                // #918
            \App\Library\ProxySqlAudit\Checks\Monitor\ServerVersionDrift::class,                  // #919
            \App\Library\ProxySqlAudit\Checks\QueryRules\DestinationHostgroupUndefined::class,    // #920
            \App\Library\ProxySqlAudit\Checks\QueryRules\FlagInOutCycle::class,                   // #921
            \App\Library\ProxySqlAudit\Checks\Cluster\ProxySqlServersDuplicate::class,            // #922
            \App\Library\ProxySqlAudit\Checks\Cluster\AsymmetricPeering::class,                   // #923
            \App\Library\ProxySqlAudit\Checks\Cluster\ClusterDiffsBeforeSyncZero::class,          // #924
            \App\Library\ProxySqlAudit\Checks\Runtime\ShunnedTooLong::class,                      // #925
            \App\Library\ProxySqlAudit\Checks\Runtime\ZombieSessions::class,                      // #926
            \App\Library\ProxySqlAudit\Checks\Runtime\ConnectFailuresGrowing::class,              // #927
            \App\Library\ProxySqlAudit\Checks\Runtime\ErrlogStorm::class,                         // #928
        ];
    }
}
