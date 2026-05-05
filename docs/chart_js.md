# Chart.js 4.5.1

The project ships **Chart.js 4.5.1** at `App/Webroot/js/chart-4.5.1.umd.min.js`. New charts must use the v4 API.

## v4 API differences

| v2 (legacy) | v4 (current) |
|---|---|
| `options.scales.xAxes = [{ … }]` | `options.scales.x = { … }` |
| `options.scales.yAxes = [{ … }]` | `options.scales.y = { … }` |
| `options.title` (top level) | `options.plugins.title` |
| `options.tooltips` | `options.plugins.tooltip` |
| `options.legend` | `options.plugins.legend` |
| `lineTension: 0.3` (dataset) | `tension: 0.3` (dataset) |

## Time-scale charts

Load, in this order:

1. `moment.js`
2. `chartjs-adapter-moment.min.js`
3. Chart.js 4.5.1

Then set `scales.x.type = 'time'` with a `time.unit` / `time.parser` as needed.

## Legacy

`Chart.bundle.js` (v2) still ships for older screens. **Do not** use it for new code.
