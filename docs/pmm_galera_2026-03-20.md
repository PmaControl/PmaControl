# PMM Galera Screen Rebuild

## Route

- `Pmm/galera/{id_mysql_server}`

## PMM reference

- Dashboard inspected: `PXC Galera Cluster Summary`
- Demo URL: `https://pmmdemo.percona.com/pmm-ui/graph/d/pxc-cluster-summary/pxc-galera-cluster-summary`

## Files

- Controller: `App/Controller/Pmm.php::galera()`
- Builder: `App/Library/PmmDashboardCatalog.php::buildGalera()`
- View: `App/view/Pmm/galera.view.php`

## Implemented sections

### Cluster summary

- Type: line charts + stat cards
- PMM source family:
  - `mysql_global_status_wsrep_cluster_size`
  - `mysql_global_status_wsrep_local_state`
- PmaControl equivalents:
  - `status::wsrep_cluster_size`
  - `status::wsrep_local_state`
  - `status::wsrep_cluster_status`
  - `status::wsrep_local_state_comment`
  - `status::wsrep_ready`
  - `status::wsrep_connected`

### Flow control, queues and replication payload

- Type: line charts + current counter table
- PMM source family:
  - `mysql_global_status_wsrep_*`
- PmaControl equivalents:
  - `status::wsrep_flow_control_paused`
  - `status::wsrep_local_recv_queue`
  - `status::wsrep_local_send_queue`
  - `status::wsrep_flow_control_recv`
  - `status::wsrep_flow_control_sent`
  - `status::wsrep_received_bytes`
  - `status::wsrep_replicated_bytes`
  - `status::wsrep_repl_data_bytes`

### SST and datadir

- Type: line charts + snapshot table
- PMM equivalent:
  - panel `56` `IST Progress`
  - PMM metrics:
    - `mysql_global_status_wsrep_ist_receive_seqno_start`
    - `mysql_global_status_wsrep_ist_receive_seqno_current`
    - `mysql_global_status_wsrep_ist_receive_seqno_end`
- PmaControl-only sources:
  - `ssh_stats::mysql_sst_in_progress`
  - `ssh_stats::mysql_sst_elapsed_sec`
  - `ssh_stats::mysql_datadir_total_size`
  - `ssh_stats::mysql_datadir_clean_size`
- PmaControl equivalents for PMM IST panel:
  - `status::wsrep_ist_receive_seqno_start`
  - `status::wsrep_ist_receive_seqno_current`
  - `status::wsrep_ist_receive_seqno_end`

### IST Progress

- Type: line chart
- PMM reference:
  - dashboard `PXC Galera Cluster Summary`
  - panel `56`
  - title `IST Progress`
- PMM Prometheus expressions:
  - `avg by (service_name) (mysql_global_status_wsrep_ist_receive_seqno_start) * on (service_name) group_left avg by (service_name) (mysql_galera_variables_info{wsrep_cluster_name="$cluster"})`
  - `avg by (service_name) (mysql_global_status_wsrep_ist_receive_seqno_current) * on (service_name) group_left avg by (service_name) (mysql_galera_variables_info{wsrep_cluster_name="$cluster"})`
  - `avg by (service_name) (mysql_global_status_wsrep_ist_receive_seqno_end) * on (service_name) group_left avg by (service_name) (mysql_galera_variables_info{wsrep_cluster_name="$cluster"})`
- PmaControl implementation:
  - chart `galera_ist_progress`
  - `status::wsrep_ist_receive_seqno_start` => `IST first`
  - `status::wsrep_ist_receive_seqno_current` => `IST current`
  - `status::wsrep_ist_receive_seqno_end` => `IST last`
- Notes:
  - PMM filters series by cluster through `mysql_galera_variables_info`.
  - In PmaControl, the screen is already scoped to one `id_mysql_server`, so no Prometheus-side join is needed.
  - If these wsrep status values are absent on a given node or Galera version, the chart remains empty.

### Cluster members

- Type: table
- Discovery:
  - `wsrep_incoming_addresses`
  - fallback `wsrep_cluster_address`
  - parsed with `Dot3::getIdMysqlServerFromGalera()`
  - mapped back to `mysql_server`

## Gaps

- PMM also exposes extra PXC and EVS internals that are not all stored in PmaControl.
- PmaControl extends the PMM rebuild with SST and datadir telemetry already used by Dot3.
- PMM panel `56` is now covered, but the screen still does not reproduce PMM's cluster-wide multi-service grouping because PmaControl currently renders one server scope at a time.

## MariaDB Galera vs PMM PXC

- PMM reads Prometheus exporter metrics named:
  - `mysql_global_status_wsrep_ist_receive_seqno_start`
  - `mysql_global_status_wsrep_ist_receive_seqno_current`
  - `mysql_global_status_wsrep_ist_receive_seqno_end`
- On MariaDB Galera, these values map to native `SHOW GLOBAL STATUS` variables:
  - `wsrep_ist_receive_seqno_start`
  - `wsrep_ist_receive_seqno_current`
  - `wsrep_ist_receive_seqno_end`
- So the PMM panel and the PmaControl rebuild target the same wsrep state family. The difference is mostly presentation:
  - PMM groups and filters by Prometheus labels such as `service_name` and `wsrep_cluster_name`
  - PmaControl scopes the screen to one `id_mysql_server` and reads the already collected raw wsrep values
- MariaDB also exposes cluster state through `SHOW WSREP_STATUS` / `information_schema.WSREP_STATUS`, but that is a different access path and is not the source PMM uses for this panel.
- Practical implication:
  - if `wsrep_ist_receive_seqno_*` is absent or not collected on a given MariaDB node, `Pmm/galera` will show an empty `IST Progress` chart even though the panel exists in the screen definition

## Sources

- MariaDB Galera status variables:
  - https://mariadb.com/docs/galera-cluster/reference/galera-cluster-status-variables
- MariaDB `WSREP_STATUS` table:
  - https://mariadb.com/kb/en/information-schema-wsrep_status-table/
- MariaDB `WSREP_INFO` plugin:
  - https://mariadb.com/docs/server/reference/plugins/mariadb-replication-cluster-plugins/wsrep_info-plugin
