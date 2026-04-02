# MariaDB Stops Repeatedly on `ist-pmacontrol`

## Executive Summary

MariaDB does **not** stop because of corruption, Galera recovery failure, or an SQL crash loop.

It stops because the Linux kernel kills `mariadbd` for **out-of-memory** conditions.

The evidence is explicit in both `systemd` and the kernel log:

- `mariadb.service: Failed with result 'oom-kill'`
- `Out of memory: Killed process ... (mariadbd)`
- `Memory cgroup out of memory: Killed process ... (mariadbd)`

The service also has an explicit systemd memory cap:

- `MemoryMax=16G`

The machine has about:

- `MemTotal: 19.5 GiB`
- `SwapTotal: ~1.0 GiB`

MariaDB reaches roughly:

- `anon-rss: ~16.6 GiB`

and is then killed by the kernel or by the service cgroup memory limit.

## Primary Evidence

### systemd

`systemctl status mariadb` showed:

- `Active: failed (Result: oom-kill)`
- `Main process exited, code=killed, status=9/KILL`
- `A process of this unit has been killed by the OOM killer`

Two recent stop events were visible:

- `2026-03-28 00:10:55`
- `2026-03-31 22:21:22`

### kernel log

`journalctl -k -b` showed:

- `Out of memory: Killed process 1177 (mariadbd) total-vm:22267612kB, anon-rss:16649820kB`
- `Memory cgroup out of memory: Killed process 1146610 (mariadbd) total-vm:21608520kB, anon-rss:16659476kB`

and also:

- `oom-kill:constraint=CONSTRAINT_MEMCG`
- `oom_memcg=/system.slice/mariadb.service`

So the last failure is not just a global host OOM; it is also aligned with the service cgroup limit.

### MariaDB service config

`systemctl cat mariadb` showed:

- `MemoryMax=16G`
- `LimitNOFILE=32768`

The `LimitNOFILE` warning is present at startup but is not the reason for the crash.

## What MariaDB Was Doing Before the Kill

The MariaDB error log at `/srv/mysql/log/error.log` shows repeated memory pressure events:

- `Memory pressure event shrunk innodb_buffer_pool_size=1536m from 2048m`
- then `1280m`
- then `1152m`
- then `1088m`
- then `1056m`
- then `1040m`
- then `1032m`
- then `1024m`

After that:

- `Memory pressure event disregarded; innodb_buffer_pool_size=1024m, innodb_buffer_pool_size_auto_min=1024m`

So MariaDB was already under pressure long before the final kill and had already auto-shrunk InnoDB as much as allowed.

## Current Memory-Related Configuration

### systemd

- `MemoryMax=16G`

### MariaDB core config

From `/etc/mysql/mariadb.cnf`:

- `max_connections = 100`
- `sort_buffer_size = 32M`
- `tmp_table_size = 256M`
- `max_heap_table_size = 256M`
- `key_buffer_size = 128M`
- `read_buffer_size = 2M`
- `read_rnd_buffer_size = 1M`
- `innodb_buffer_pool_size = 2G`
- `innodb_buffer_pool_size_auto_min = 1G`
- `innodb_buffer_pool_size_max = 3G`

### Releem override

From `/etc/mysql/releem.conf.d/z_aiops_mysql.cnf`:

- `tmp_table_size = 805306368` (`768M`)
- `max_heap_table_size = 805306368` (`768M`)

This is materially higher than the base config and significantly increases worst-case per-session memory.

### RocksDB

Both files exist:

- `/etc/mysql/mariadb.conf.d/rocksdb.cnf`
- `/etc/mysql/conf.d/rocksdb.cnf`

and both set:

- `rocksdb_block_cache_size = 4G`
- `rocksdb_max_background_jobs = 8`

The duplicated config is not the direct crash trigger by itself, but it is a sign that RocksDB memory deserves explicit review.

## Likely Root Cause

The repeated stops are caused by the combination of:

1. A hard service memory cap:
   - `MemoryMax=16G`
2. MariaDB dynamic memory growth under load
3. Aggressive per-session memory settings:
   - especially `tmp_table_size = 768M`
   - and `max_heap_table_size = 768M`
4. A large always-on RocksDB cache:
   - `rocksdb_block_cache_size = 4G`
5. Too many concurrent connections or connection storms

The final kill happens after InnoDB has already shrunk itself down to `1G`, so InnoDB is not the main remaining culprit at kill time.

The remaining likely contributors are:

- session memory
- temporary tables
- sort buffers
- RocksDB block cache
- active connection churn

## Aggravating Signal: Connection Storm / Proxy Pressure

Just before the OOM window, the MariaDB log shows many aborted unauthenticated connections from:

- `10.68.68.103`

This IP resolves to:

- `proxysql3-test`
- `mysql_server.id = 163`
- `10.68.68.103:6033`

The error log contains:

- many `Aborted connection ... user: 'unauthenticated' host: '10.68.68.103'`
- `Too many connections`

This does not prove ProxySQL is the sole cause of the OOM, but it is a strong aggravating factor:

- more connection churn
- more thread/session memory
- more pressure on temp/sort/session allocations

## What It Is Not

It is **not**:

- a startup failure
- a broken Galera recovery
- a corrupted datadir
- a simple open-files problem

This was confirmed because MariaDB restarted cleanly on `2026-04-02 13:39:42` and came back `active (running)` immediately.

## Recommended Fix

### Immediate actions

1. Reduce session memory aggressively

Recommended first pass:

- `tmp_table_size = 128M`
- `max_heap_table_size = 128M`
- `sort_buffer_size = 4M` or `8M`
- keep `read_buffer_size` and `read_rnd_buffer_size` conservative

The current `768M` Releem values are too high for a server constrained at `16G`.

2. Reduce RocksDB cache

Recommended first pass:

- `rocksdb_block_cache_size = 2G`

Current:

- `4G`

On a 19 GiB host with a 16 GiB service cap, 4 GiB is expensive when combined with active SQL memory.

3. Review or raise `MemoryMax`

Options:

- preferred: keep `MemoryMax=16G` and reduce MariaDB memory usage
- alternative: raise `MemoryMax` modestly, for example `18G`, only if the host has enough headroom for Apache + OS + page cache

Raising the cap without reducing dynamic memory just postpones the kill.

4. Investigate `10.68.68.103` connection behavior

Check on `proxysql3-test`:

- health check frequency
- connect storms
- auth failures
- backend retry loops
- whether a monitor user is misconfigured

### Medium-term actions

1. Remove duplicate RocksDB config file ownership ambiguity

Keep a single authoritative RocksDB config source.

2. Add alerting before kill

Alert on:

- repeated `Memory pressure event shrunk innodb_buffer_pool_size`
- `Too many connections`
- rising MariaDB RSS near `14-15G`

3. Add connection profile review

Measure:

- active thread count
- temp table usage
- sort usage
- connection churn from ProxySQL / app hosts

## Safe First Configuration Proposal

Suggested first remediation set:

```ini
# /etc/mysql/releem.conf.d/z_aiops_mysql.cnf
tmp_table_size = 134217728
max_heap_table_size = 134217728
```

```ini
# /etc/mysql/mariadb.cnf
sort_buffer_size = 8M
```

```ini
# authoritative rocksdb config
rocksdb_block_cache_size = 2G
```

Optionally later:

```ini
# /etc/systemd/system/mariadb.service.d/override.conf
[Service]
MemoryMax=18G
```

But only after reducing session memory first.

## Final Conclusion

MariaDB stops because it exceeds available memory and the service cgroup limit.

The strongest evidence is:

- explicit `oom-kill`
- MariaDB RSS around `16.6 GiB`
- `MemoryMax=16G`
- repeated InnoDB memory pressure events
- high per-session memory settings
- likely connection churn from `proxysql3-test`

The most pragmatic fix is:

- reduce per-session memory now
- reduce RocksDB cache
- review ProxySQL connection behavior
- only then consider increasing `MemoryMax`
