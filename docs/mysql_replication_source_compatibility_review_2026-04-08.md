# MySQL 8.0 Replication Compatibility Review

Date: 2026-04-08  
Scope: Glial + PmaControl replication collection and legacy master/slave compatibility

## Goal

Make MySQL 8.0 replica metadata usable everywhere in PmaControl even when the server exposes:

- `Source_Host`
- `Source_Port`
- `Replica_IO_Running`
- `Replica_SQL_Running`

instead of the legacy fields:

- `Master_Host`
- `Master_Port`
- `Slave_IO_Running`
- `Slave_SQL_Running`

The immediate functional goal was to add `source_host` and `source_port` compatibility without breaking the older code that still expects `master_host` and `master_port`.

## Code Review Findings

### P1. Glial returned raw `SHOW REPLICA STATUS` rows without legacy aliases

Files reviewed:

- [/srv/www/glial/Glial/Sgbd/Sql/Mysql/Mysql.php](/srv/www/glial/Glial/Sgbd/Sql/Mysql/Mysql.php)
- [/srv/www/glial/Glial/Sgbd/Sql/Mysql/MasterSlave.php](/srv/www/glial/Glial/Sgbd/Sql/Mysql/MasterSlave.php)

Problem:

- Glial already switches to `SHOW REPLICA STATUS` on newer MySQL 8.0 servers.
- The returned array was then consumed by older code paths that still read keys such as `Master_Host`, `Master_Port`, `Slave_IO_Running`, `Relay_Master_Log_File`, `Exec_Master_Log_Pos`, and `Connection_name`.
- That created a compatibility gap: transport was modern, consumers were still legacy.

Impact:

- replication topology code can silently lose source endpoint information
- failover tooling can read empty host/port values
- operational CLI code can misinterpret replication state

### P1. PmaControl integration stored only legacy master/slave names

File reviewed:

- [/srv/www/pmacontrol/App/Controller/Integrate.php](/srv/www/pmacontrol/App/Controller/Integrate.php)

Problem:

- the integration layer lowercases replication rows and normalizes `connection_name`
- but before the fix it only guaranteed `master_host` and `master_port`
- `source_host` and `source_port` were not backfilled

Impact:

- newer MySQL 8.0 terminology was not preserved in the integrated slave metrics
- downstream code could not query `slave::source_host` and `slave::source_port`

### P2. Several operational paths still emit legacy replication SQL

Files reviewed:

- [/srv/www/pmacontrol/App/Controller/Load.php](/srv/www/pmacontrol/App/Controller/Load.php)
- [/srv/www/pmacontrol/App/Controller/Load2.php](/srv/www/pmacontrol/App/Controller/Load2.php)
- [/srv/www/pmacontrol/App/Controller/Mysql.php](/srv/www/pmacontrol/App/Controller/Mysql.php)
- [/srv/www/pmacontrol/App/Controller/Slave.php](/srv/www/pmacontrol/App/Controller/Slave.php)
- [/srv/www/glial/Glial/Neuron/PmaCli/PmaCliSwitch.php](/srv/www/glial/Glial/Neuron/PmaCli/PmaCliSwitch.php)
- [/srv/www/glial/Glial/Neuron/PmaCli/PmaCliFailOver.php](/srv/www/glial/Glial/Neuron/PmaCli/PmaCliFailOver.php)

Problem:

- these paths still use `SHOW SLAVE STATUS`, `STOP SLAVE`, `START SLAVE`, and `CHANGE MASTER TO`
- on current MySQL 8 they are mostly still accepted as compatibility syntax, but they are legacy terminology

Impact:

- no immediate break on most environments
- but this remains technical debt and should be migrated deliberately later

## Implemented Fix

### 1. Glial replication row normalization

Added compatibility aliases in:

- [/srv/www/glial/Glial/Sgbd/Sql/Mysql/Mysql.php](/srv/www/glial/Glial/Sgbd/Sql/Mysql/Mysql.php)
- [/srv/www/glial/Glial/Sgbd/Sql/Mysql/MasterSlave.php](/srv/www/glial/Glial/Sgbd/Sql/Mysql/MasterSlave.php)

Normalization now mirrors both directions for the key fields:

- `Source_Host` <-> `Master_Host`
- `Source_Port` <-> `Master_Port`
- `Source_Log_File` <-> `Master_Log_File`
- `Read_Source_Log_Pos` <-> `Read_Master_Log_Pos`
- `Relay_Source_Log_File` <-> `Relay_Master_Log_File`
- `Exec_Source_Log_Pos` <-> `Exec_Master_Log_Pos`
- `Replica_IO_Running` <-> `Slave_IO_Running`
- `Replica_SQL_Running` <-> `Slave_SQL_Running`
- `Replica_IO_State` <-> `Slave_IO_State`
- `Replica_SQL_Running_State` <-> `Slave_SQL_Running_State`
- `Seconds_Behind_Source` <-> `Seconds_Behind_Master`
- `Channel_Name` <-> `Connection_name`

Result:

- old callers keep working
- new callers can read source/replica terminology
- mixed environments remain compatible

### 2. Integration layer normalization

Updated:

- [/srv/www/pmacontrol/App/Controller/Integrate.php](/srv/www/pmacontrol/App/Controller/Integrate.php)

Behavior now:

- if only `source_host/source_port` exist, `master_host/master_port` are populated
- if only `master_host/master_port` exist, `source_host/source_port` are populated
- `seconds_behind_source` and `seconds_behind_master` are kept in sync

Result:

- PmaControl can expose both naming conventions in extracted replication metrics

## Tests Added

Updated/added:

- [/srv/www/pmacontrol/tests/Controller/IntegrateTest.php](/srv/www/pmacontrol/tests/Controller/IntegrateTest.php)
- [/srv/www/pmacontrol/tests/Library/MysqlReplicationCompatibilityTest.php](/srv/www/pmacontrol/tests/Library/MysqlReplicationCompatibilityTest.php)

Covered cases:

- MySQL 8.0 `SHOW REPLICA STATUS` row with `Source_*` and `Replica_*`
- backfill of legacy `Master_*` and `Slave_*`
- backfill of modern `source_*`
- `seconds_behind_source` and `seconds_behind_master`
- `Channel_Name` / `Connection_name`

Validation:

- `./vendor/bin/phpunit tests/Controller/IntegrateTest.php tests/Library/MysqlReplicationCompatibilityTest.php`
- result: `OK (6 tests, 33 assertions)`

## Practical Outcome

After this change:

- Glial can read MySQL 8.0 replica rows and still feed legacy code safely
- PmaControl stores `source_host` and `source_port` compatibility fields
- topology, extraction, and replication displays no longer depend on one naming convention only

## Residual Work

Not changed in this patch:

- command emitters still use `SHOW SLAVE STATUS`
- replication control SQL still uses `STOP SLAVE`, `START SLAVE`, `CHANGE MASTER TO`

Recommended next step:

1. introduce a dedicated compatibility wrapper for replication control commands
2. migrate write-side operations to source/replica terminology where supported
3. keep aliasing on the read path for long-term backward compatibility

## Recommendation

Keep the alias layer in Glial permanently.

Reason:

- it centralizes compatibility once
- it avoids duplicated fixes in controllers
- it protects old operational tooling while allowing PmaControl to expose modern MySQL 8.0 field names
