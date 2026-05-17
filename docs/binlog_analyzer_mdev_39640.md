# `BinlogAnalyzer` and MDEV-39640

[MDEV-39640](https://jira.mariadb.org/browse/MDEV-39640) is an upstream
MariaDB bug in `mariadb-binlog --stop-datetime`. When this flag is
applied to a closed binlog file produced by a secondary with
`log_slave_updates = ON`, the binary emits only the 3 file-header
events (`Start_log_event`, `Gtid_list`, `Binlog_checkpoint`) and
silently skips every transactional event — regardless of the value of
the stop-datetime. The trailing `Binlog_checkpoint` of such files is
dated at the rotation time, not at the time of the transactional
data inside the file, and the scanner trips its stop heuristic against
that event.

Reproduced on bundled `mariadb-binlog` 10.11.16 (BuildID
`dfa3536b343f…`) and 11.8.7 (BuildID `acd49e16280d…`). Distinct from
the already-fixed [MDEV-35528](https://jira.mariadb.org/browse/MDEV-35528)
which covers the multi-file scan case.

## Workaround in PmaControl

Until MDEV-39640 ships in every supported `mariadb-binlog` version,
`App/Library/BinlogAnalyzer.php` does not pass `--stop-datetime` to
the binary. Instead it filters by header timestamp in PHP via
`BinlogAnalyzer::mysqlbinlogLineMatchesWindow($line, $startTs, $endTs)`
during the parse loop in `parseGtidEventsMariaDB()`.

`--start-datetime` is still passed — it works correctly and saves
piping a lot of bytes for old events. The end of the window is
enforced at parse time.

A second fix shipped in the same commit: the MariaDB transaction
counter now increments on each `GTID` event, not on each `Xid`. A
BLACKHOLE binlog relay (cf. [binlog_relay_blackhole.md](binlog_relay_blackhole.md))
never emits `Xid` because the relay's tables are `ENGINE=BLACKHOLE` —
yet still emits one `GTID` per replicated transaction. Counting GTID
gives the right number for both InnoDB masters and BLACKHOLE relays.

## When MDEV-39640 is fixed upstream

Revisit `BinlogAnalyzer::buildBinlogCmd()` and re-introduce
`--stop-datetime` once the binaries shipped in
`bin/mysqlbinlog/x86_64/` all contain the fix:

```php
// in BinlogAnalyzer::buildBinlogCmd()
$cmd .= " --start-datetime=" . escapeshellarg($analysis['time_start']);
$cmd .= " --stop-datetime="  . escapeshellarg($analysis['time_end']);   // ← re-add
```

The PHP-side helper `mysqlbinlogLineMatchesWindow()` and its PHPUnit
tests can stay as a defensive double-check (cheap), or be removed if
the test coverage of the upstream fix is good enough.

## Identifying the code points

```
$ git grep -nE 'MDEV-39640' -- App tests docs
App/Library/BinlogAnalyzer.php:…  buildBinlogCmd() comment
App/Library/BinlogAnalyzer.php:…  mysqlbinlogLineMatchesWindow() docblock
App/Library/BinlogAnalyzer.php:…  parseGtidEventsMariaDB() comment
tests/Library/BinlogAnalyzerWindowFilterTest.php:…
docs/binlog_analyzer_mdev_39640.md   (this file)
```
