# Pmm Overview

## Route / Code

- Route: `/Pmm/index/{id_mysql_server}`
- Controller action: `App/Controller/Pmm.php::index()`
- View: `App/view/Pmm/index.view.php`
- Shared view shell: `App/view/Pmm/dashboard.view.php`
- Shared menu: `App/view/Pmm/menu.view.php`
- Frontend: `App/Webroot/js/Pmm/dashboard.js`

## PMM Source

- PMM source repo: `percona/grafana-dashboards`
- This screen is a PmaControl inventory screen, not a 1:1 PMM dashboard.

## Purpose

This screen centralizes the rebuilt PMM-style navigation and documents:

- which PMM dashboards were inspected
- which `Pmm/*` screens were implemented
- which domains are still partial or missing in PmaControl

## Implemented Blocks

- summary cards
  - selected server
  - product banner
  - current MySQL availability
  - current CPU
  - current memory used
- screen inventory table
  - screen
  - route
  - PMM dashboard source
  - coverage comment

## PmaControl Sources

- `version`
- `version_comment`
- `mysql_available`
- `cpu_usage`
- `memory_used`

All current values come from `Extraction2::display(...)`.

## Missing / Gap Notes

- no official PMM MySQL Router dashboard exists in the inspected PMM repository
- PMM exporter-side derived metrics are only approximated where PmaControl stores cumulative counters
