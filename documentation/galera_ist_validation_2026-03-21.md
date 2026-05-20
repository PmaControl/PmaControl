# Galera IST Validation on MariaDB 10.6

## Scope

- Cluster tested:
  - `server id 2` => `zol-galera-3` => `10.68.68.233`
  - `server id 3` => `zol-galera-2` => `10.68.68.232`
  - `server id 4` => `zol-galera-1` => `10.68.68.231`
- Goal:
  - verify whether MariaDB Galera actually performs `IST`
  - verify whether PMM panel-style metrics `wsrep_ist_receive_seqno_*` exist on this stack

## Environment observed

- MariaDB version on the cluster:
  - `10.6.23-MariaDB-deb11-log`
- Cluster name:
  - `ISTOSIA`
- Galera cache configuration on the tested node:
  - `gcache.size = 1024M`
  - `gcache.recover = yes`
  - `gcache.page_size = 128M`
- Error log:
  - `/var/log/mysql/mysqld.log`

## Test procedure

1. Installed `sysbench` on `10.68.68.231`
2. Prepared a local `sbtest` schema on `10.68.68.231`
3. Confirmed the 3 nodes were initially:
   - `wsrep_cluster_size = 3`
   - `wsrep_cluster_status = Primary`
   - `wsrep_local_state_comment = Synced`
4. Stopped `mariadb` on `10.68.68.233`
5. Ran `sysbench oltp_write_only` for `60s` on `10.68.68.231`
6. Restarted `mariadb` on `10.68.68.233`
7. Observed:
   - `wsrep_*` polling during rejoin
   - `/var/log/mysql/mysqld.log` on `10.68.68.233`

## Sysbench load generated

- Threads: `8`
- Duration: `60s`
- Workload: `oltp_write_only`
- Result:
  - `188514` transactions
  - `1131107` queries
  - `3141.53 tps`
  - `18849.55 qps`
  - `95th percentile latency = 4.18 ms`

## Rejoin state observed

After restarting `10.68.68.233`:

- first polls:
  - `wsrep_cluster_size = 2`
  - `wsrep_local_state_comment = Joining: receiving State Transfer`
  - `wsrep_cluster_status = non-Primary`
  - `wsrep_ready = OFF`
- by poll `5`:
  - `wsrep_cluster_size = 3`
  - `wsrep_local_state_comment = Synced`
  - `wsrep_cluster_status = Primary`
  - `wsrep_ready = ON`

So the node rejoined and synced in about `20-25 seconds`.

## Direct proof from MariaDB log

Relevant lines from `/var/log/mysql/mysqld.log` on `10.68.68.233`:

- `WSREP: Running: 'wsrep_sst_mariabackup --role 'joiner' ...'`
- `WSREP: ####### IST uuid: ... f: 67479599, l: 67668114`
- `WSREP: Prepared IST receiver for 67479599-67668114`
- `WSREP_SST: [INFO] 'xtrabackup_ist' received from donor: Running IST`
- `WSREP: Receiving IST: 188516 writesets, seqnos 67479599-67668114`
- `WSREP: Receiving IST... 100.0% (188516/188516 events) complete.`
- `WSREP: IST received: ...:67668114`
- `WSREP: Server 10.68.68.233 synced with group`

## Important interpretation

This MariaDB Galera stack **does perform IST**.

However, the rejoin path still logs:

- `WSREP: Running: 'wsrep_sst_mariabackup ...'`
- `WSREP: SST received`
- `WSREP: SST succeeded for position ...`

and then immediately:

- `WSREP: Receiving IST: 188516 writesets ...`

So on this stack the joiner helper still goes through the `wsrep_sst_mariabackup` wrapper, but the actual catch-up was an `IST`, not a full SST copy.

## Check for PMM-style IST status variables

Executed on all 3 nodes:

- `SHOW GLOBAL STATUS LIKE 'wsrep_ist%';`

Result:

- no rows returned on `10.68.68.231`
- no rows returned on `10.68.68.232`
- no rows returned on `10.68.68.233`

## Conclusion for PmaControl / PMM screen

- PMM panel `IST Progress` is conceptually correct for Galera
- but on this MariaDB `10.6.23` cluster, the variables:
  - `wsrep_ist_receive_seqno_start`
  - `wsrep_ist_receive_seqno_current`
  - `wsrep_ist_receive_seqno_end`
  are **not exposed**
- therefore `Pmm/galera` cannot rely on these raw MariaDB status variables on this environment

## Practical conclusion

For this MariaDB cluster:

- `IST` must be detected from logs and/or SST helper state
- not from `SHOW GLOBAL STATUS LIKE 'wsrep_ist%'`

If we want a real `IST Progress` panel in PmaControl for this environment, the safest sources are:

1. `mysqld.log`
   - `Receiving IST: ...`
   - `Receiving IST... X%`
   - `IST received: ...`
2. `sst_in_progress` helper files and wsrep SST scripts
3. optional parsing of donor/joiner state from `wsrep_sst_mariabackup` output

## Impact on current PMM rebuild

The current `Pmm/galera` chart based on:

- `wsrep_ist_receive_seqno_start`
- `wsrep_ist_receive_seqno_current`
- `wsrep_ist_receive_seqno_end`

will remain empty on this cluster until the data source is changed.
