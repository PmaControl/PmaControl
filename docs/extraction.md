# App\Library\Extraction — time-series helper

```php
App\Library\Extraction::extract(
    $vars,       // metric names / variable keys
    $servers,    // list of id_mysql_server
    $dateRange,  // start/end datetimes
    $range,      // bucket / interval
    $graph       // bool — emit client-ready graph literals
);
```

## Modes

- `$graph = false` — returns raw rows (one per bucket).
- `$graph = true` combined with:
  ```php
  Extraction::setOption('groupbyday', true);
  ```
  — each result row gains a `graph` field: a comma-separated series of JS literals in the form `{x:new Date(...),y:...}`, plus `day`, `min`, `max`, `avg`, `std`.

The `graph` field is meant to be injected as-is inside a Chart.js dataset (`data: [{{ $row['graph'] }}]`), hence the raw JS literal output rather than JSON.

## Related

- Data lives in `ts_*` tables — see [time_series_tables.md](time_series_tables.md) for the existence-check rule.
- For charting: see [chart_js.md](chart_js.md).
