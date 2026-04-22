# Worker Architecture

PmaControl uses a multi-process architecture based on System V message queues to collect metrics from MySQL, ProxySQL, MaxScale, SSH, and MySQL Router servers.

## Overview

```
┌─────────────────────────────────────────────────────────┐
│                    daemon_main                          │
│  Each row = one Agent::launch() loop (infinite while)   │
│  Interval = daemon_main.refresh_time seconds            │
└──────────┬──────────────────────────────────────────────┘
           │ every refresh_time seconds, calls:
           │ Worker::addToQueue($id_worker_queue)
           │   or
           │ Dot3::run(), Integrate::integrateAll(), etc.
           ▼
┌─────────────────────────────────────────────────────────┐
│                   worker_queue                          │
│  Each row = one worker pool (mysql, ssh, proxysql, ...) │
│  nb_worker = number of concurrent PHP worker processes  │
│  queue_number = System V IPC key for msg_get_queue()    │
└──────────┬──────────────────────────────────────────────┘
           │ msg_send() one message per server to monitor
           ▼
┌─────────────────────────────────────────────────────────┐
│              System V Message Queue                     │
│  IPC key = worker_queue.queue_number                    │
│  Messages: { id, name, refresh }                        │
└──────────┬──────────────────────────────────────────────┘
           │ msg_receive() — blocks until a message arrives
           ▼
┌─────────────────────────────────────────────────────────┐
│                Worker::run($id_worker_queue)             │
│  N independent PHP processes (N = nb_worker)            │
│  Each process loops: receive msg → call worker_method   │
│  → record execution in worker_execution → wait next msg │
└─────────────────────────────────────────────────────────┘
```

## Tables

### `daemon_main`

The master loop table. Each row is an independent daemon started by `Agent::launch($id)`.

| Column | Description |
|--------|-------------|
| `id` | Primary key |
| `name` | Human label (e.g. "Aspirateur MySQL") |
| `pid` | OS PID of the running Agent process (0 = stopped) |
| `refresh_time` | Seconds between two runs of `class::method` |
| `max_delay` | Max seconds to wait for the queue to drain before re-filling |
| `class` | PHP controller class to call (e.g. `Worker`, `Dot3`, `Integrate`) |
| `method` | PHP method to call (e.g. `addToQueue`, `run`, `integrateAll`) |
| `params` | Arguments passed to the method (e.g. `1` = worker_queue.id) |

For worker-based daemons, `class=Worker` and `method=addToQueue`, with `params` being the `worker_queue.id`.

### `worker_queue`

Defines each worker pool.

| Column | Description |
|--------|-------------|
| `id` | Primary key, referenced by daemon_main.params |
| `id_daemon_main` | FK to daemon_main |
| `name` | Pool name (e.g. `worker_mysql`, `worker_ssh`) |
| `nb_worker` | **Number of concurrent worker processes.** Can be changed at runtime without restart — `Worker::checkAll()` will adjust within one cycle. |
| `timeout` | Unused in current code |
| `queue_number` | System V IPC key — must be unique per pool |
| `worker_class` | PHP class called by each worker (e.g. `Aspirateur`) |
| `worker_method` | PHP method called by each worker (e.g. `tryMysqlConnection`) |
| `max_execution_time` | Max seconds a worker can spend on one server before it's considered stuck |
| `table` | Name of the source table for `adaptNumberWorker()` auto-scaling |
| `query` | SQL query listing servers to monitor — results are sent as messages |

### `worker_run`

Tracks currently running worker processes.

| Column | Description |
|--------|-------------|
| `id_worker_queue` | FK to worker_queue |
| `pid` | OS PID of the worker process |
| `is_working` | 1 = alive, 0 = killed/finished |
| `date_killed` | When the worker was stopped |
| `is_safe_kill` | 1 = intentionally killed by removeWorker |

### `worker_execution`

Per-server execution log.

| Column | Description |
|--------|-------------|
| `id_worker_run` | FK to worker_run |
| `id_mysql_server` | Server that was processed |
| `date_started` | When the worker started processing this server |
| `date_end` | When it finished |
| `execution_time` | Duration in milliseconds |

## Process lifecycle

### 1. `Daemon::startAll()`

Iterates all rows in `daemon_main` and calls `Agent::start($id)` for each. This spawns one background PHP process per daemon.

### 2. `Agent::launch($id_daemon)`

An infinite `while(true)` loop that:
1. Reads `daemon_main` to get `class`, `method`, `params`, `refresh_time`
2. Spawns a background PHP process: `php index.php {class} {method} {params}`
3. Sleeps for `refresh_time` seconds
4. Repeats

For worker pools, this calls `Worker::addToQueue($id_worker_queue)`.

### 3. `Worker::addToQueue($id_worker_queue)`

1. Reads the `query` from `worker_queue` to get the list of servers to monitor
2. Checks if the queue is already full (more messages than servers) — if yes, waits up to `max_delay` seconds for it to drain
3. Skips servers that are currently being processed (lock file exists and PID is alive)
4. Sends one message per remaining server to the System V queue via `msg_send()`
5. Spaces messages with a calculated delay to smooth CPU load: `delay = 1000000 * refresh_time / 2 / nb_servers`

### 4. `Worker::run($id_worker_queue)`

Each worker process is a blocking loop:
1. `msg_receive()` — blocks until a message arrives from the queue
2. Creates a lock file (`tmp/lock/{name}::{id}.lock`) with PID and timestamp
3. Records start in `worker_execution`
4. Calls `FactoryController::addNode($worker_class, $worker_method, [$name, $id, $refresh])`
   - For MySQL: `Aspirateur::tryMysqlConnection($name, $id, $refresh)`
5. Records end time and execution duration
6. Removes lock file
7. Returns to step 1

### 5. `Worker::checkAll()` — the auto-scaler

Called periodically by daemon id=23 ("Check all worker", every 7 seconds).

For each `worker_queue`:
1. Counts running workers where `worker_run.is_working = 1` and PID is still alive
2. Compares to `worker_queue.nb_worker`
3. If too few: calls `addWorker()` to spawn new PHP processes
4. If too many: calls `removeWorker()` to kill excess workers

**This is why changing `nb_worker` in the database takes effect without restart.** Within 7 seconds, `checkAll()` will detect the mismatch and add/remove workers accordingly.

## Changing the number of workers

Simply update the `nb_worker` column:

```sql
-- Increase MySQL workers to 30
UPDATE worker_queue SET nb_worker = 30 WHERE name = 'worker_mysql';

-- Reduce ProxySQL workers to 5
UPDATE worker_queue SET nb_worker = 5 WHERE name = 'worker_proxysql';
```

**No daemon restart needed.** The `Worker::checkAll()` daemon (id=23, interval 7s) will detect the difference and spawn/kill workers within one cycle.

You can also change it from the UI: **Daemon > Worker** page has inline-editable fields.

## Auto-scaling: `adaptNumberWorker()`

`Worker::adaptNumberWorker()` can automatically adjust `nb_worker` based on server availability:

```
nb_worker = floor(available_servers / 5) + unavailable_servers
```

Logic: available servers are fast (1 worker handles ~5), unavailable servers are slow (each needs its own worker because of connection timeouts).

## Message queue internals

- Uses PHP's `msg_get_queue()` / `msg_send()` / `msg_receive()` (System V IPC)
- Each pool has a unique `queue_number` (IPC key)
- Messages are `stdClass` objects: `{ name, id, refresh }`
- `msg_receive()` blocks — workers consume zero CPU while idle
- Queue stats visible via `msg_stat_queue()`: `msg_qnum` = pending messages

To inspect queues from the shell:

```bash
ipcs -q   # list all System V message queues
```

## Lock file mechanism

Two types of lock files in `tmp/lock/`:

1. **`{name}::{id_mysql_server}.lock`** — indicates a server is currently being processed. Contains JSON: `{"pid": 12345, "id": 42, "microtime": 1713200000.123}`. Used by `addToQueue()` to skip servers already in progress.

2. **`{name}::{pid}.pid`** — maps a worker PID to the server it's currently processing. Contains either the `id_mysql_server` or "Waiting..." when idle. Used by the UI to show which server each worker is handling.

## Hot-reload of DB config

Workers hot-reload `configuration/db.config.ini.php` via `keepConfigFile()`. On each message received, the worker checks `filemtime()` of the config file. If it changed, `Sgbd::setConfig()` is called with the new config — no worker restart needed.

## Current pools

| id | name | nb_worker | class::method | interval | description |
|----|------|-----------|---------------|----------|-------------|
| 1 | worker_mysql | 30 | Aspirateur::tryMysqlConnection | 5s | MySQL/MariaDB metric collection |
| 2 | worker_ssh | 26 | Aspirateur::trySshConnection | 5s | SSH hardware/stats collection |
| 3 | worker_proxysql | 10 | Aspirateur::tryProxySqlConnection | 5s | ProxySQL admin collection |
| 4 | worker_maxscale | 20 | Aspirateur::tryMaxScaleConnection | 5s | MaxScale REST API collection |
| 5 | worker_mysqlrouter | 10 | Aspirateur::tryMysqlRouterConnection | 5s | MySQL Router metadata collection |
| 6 | worker_mysql_log | 1 | Aspirateur::tryMysqlLogCollection | 15s | MySQL error log collection |
| 7 | worker_aggregate_metric | 1 | AggregateMetric::aggregateRecentByServer | 30s | Time-series rollup aggregation |

## Troubleshooting

### Workers not picking up code changes

Workers are long-running PHP processes. A code change (e.g. editing `Aspirateur.php`) is **not** picked up until the worker process restarts. Use:

```bash
php App/Webroot/index.php daemon stopAll
php App/Webroot/index.php daemon startAll
```

Note: this kills all workers and daemons. The `checkAll` daemon will respawn workers within 7 seconds of `startAll`.

### Queue filling up

If `msg_qnum` keeps growing, workers are too slow. Either:
- Increase `nb_worker` (see above)
- Investigate slow workers via `worker_execution` (high `execution_time`)
- Check for stuck workers: `ls tmp/lock/worker_mysql::*.lock`

### Orphaned workers

If a worker crashes, its lock file stays. `checkAll()` detects dead PIDs and cleans up lock files. `addToQueue()` also checks if the PID in a lock file is still alive before skipping a server.
