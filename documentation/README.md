# documentation/ — deep references

Historical analyses, per-class references and feature-level docs. Load only what the current task needs.

For day-to-day conventions (style, routing, AJAX contract, build/test…), use `docs/` instead — see [../docs/README.md](../docs/README.md).

## Subdirectory indexes

- [controller/README.md](controller/README.md) — one page per controller (122 entries, from `App/Controller/`)
- [Controller~Library/README.md](Controller~Library/README.md) — per-class method summary for `App/Controller` + `App/Library`
- [domains/README.md](domains/README.md) — in-depth domain notes (backup, cleaner, aspirateur, schema, dot3)

## Feature references

- [alert.md](alert.md) — alert timeline export
- [ameliorations_completes.md](ameliorations_completes.md) — aggregate improvement list (2026-03-12)
- [api_complete.md](api_complete.md) — full API documentation
- [api-rest.md](api-rest.md) — REST API scope & endpoints
- [audit_securite.md](audit_securite.md) — security audit
- [Dot3.md](Dot3.md) — Graphviz graph generator, functional + technical
- [Galera.md](Galera.md) — Galera cluster rules used by Dot3
- [Listener.md](Listener.md) — post-ingestion Listener pipeline
- [repman_interface_reconstruction.md](repman_interface_reconstruction.md) — Replication Manager UI rebuild
- [reverse_engineering_complete.md](reverse_engineering_complete.md) — project reverse-engineering overview
- [slave_show_page.md](slave_show_page.md) — Slave / Show replication detail page (2026-04-10)
- [vip-servers.md](vip-servers.md) — VIP server type
- [schema-model-structure.md](schema-model-structure.md) — SQL schema export tree migration
- [pmacontrol_documentation_master.md](pmacontrol_documentation_master.md) — master doc assembly (2026-03-12)

## Data model

- [pmacontrol_tables_documentation.md](pmacontrol_tables_documentation.md) — `pmacontrol` schema, table by table
- [global_variable_non_default_id1.md](global_variable_non_default_id1.md) — non-default global variable investigation

## Root-cause analyses (dated)

- [crash_investigation_2026-01-25.md](crash_investigation_2026-01-25.md)
- [dot3_svg_icon_root_cause_2026-03-21.md](dot3_svg_icon_root_cause_2026-03-21.md)
- [galera_ist_validation_2026-03-21.md](galera_ist_validation_2026-03-21.md)
- [information_schema_tables_timeout_review_2026-04-03.md](information_schema_tables_timeout_review_2026-04-03.md)
- [mariadb_oom_stop_analysis_2026-04-02.md](mariadb_oom_stop_analysis_2026-04-02.md)
- [mdev-39044_findings.md](mdev-39044_findings.md)
- [mysql_available_readonly_review_analysis_2026-03-19.md](mysql_available_readonly_review_analysis_2026-03-19.md)
- [mysql_available_refresh_analysis_2026-03-19.md](mysql_available_refresh_analysis_2026-03-19.md)
- [mysql_replication_source_compatibility_review_2026-04-08.md](mysql_replication_source_compatibility_review_2026-04-08.md)
- [mysqlrouter_vip_state_mismatch_analysis_2026-03-19.md](mysqlrouter_vip_state_mismatch_analysis_2026-03-19.md)
- [sharedmemory_locking_analysis_2026-03-19.md](sharedmemory_locking_analysis_2026-03-19.md)
- [sql_error_log_collection_analysis_2026-03-19.md](sql_error_log_collection_analysis_2026-03-19.md)
- [ts_aggregation_control_study_2026-03-21.md](ts_aggregation_control_study_2026-03-21.md)

## PMM dashboards (2026-03-20)

- [pmm_overview_2026-03-20.md](pmm_overview_2026-03-20.md)
- [pmm_system_2026-03-20.md](pmm_system_2026-03-20.md)
- [pmm_mysql_dashboard_inventory_analysis_2026-03-19.md](pmm_mysql_dashboard_inventory_analysis_2026-03-19.md)
- [pmm_dashboard_rebuild_2026-03-20.md](pmm_dashboard_rebuild_2026-03-20.md)
- [pmm_performance_schema_2026-03-20.md](pmm_performance_schema_2026-03-20.md)
- [pmm_binlog_2026-03-20.md](pmm_binlog_2026-03-20.md)
- [pmm_innodb_2026-03-20.md](pmm_innodb_2026-03-20.md)
- [pmm_galera_2026-03-20.md](pmm_galera_2026-03-20.md)
- [pmm_proxysql_2026-03-20.md](pmm_proxysql_2026-03-20.md)
- [pmm_aria_2026-03-20.md](pmm_aria_2026-03-20.md)
- [pmm_rocksdb_2026-03-20.md](pmm_rocksdb_2026-03-20.md)

## Performance studies (`performance/`)

- [performance/integrate-aspirateur-mariadb-io-analysis-2026-04-15.md](performance/integrate-aspirateur-mariadb-io-analysis-2026-04-15.md) — MariaDB IO cost of Integrate / Aspirateur
- `performance/cpu-study-aspirateur-integrate-2026-04-15.{html,pdf}` — matching CPU study (binary formats)

## Install / deployment reports

- [install_pmacontrol_10.68.68.78_2026-04-02.md](install_pmacontrol_10.68.68.78_2026-04-02.md)

## Framework / historical debug notes

- [glial-ajax.md](glial-ajax.md) — original AJAX/JSON debug note (2026-03-19). Current rules live in [../docs/glial_framework.md](../docs/glial_framework.md).

## Binary and generated assets

- `*.pdf`, `*.html` — pre-rendered copies of the `.md` files above
- `engine.dot`, `o2s_proxysql_mirroring_topology.{dot,svg}` — Graphviz sources / rendered topology
- `images/` — screenshots used by feature references
- `_generated_mcpdoc/*.json` — auto-generated MCP doc snapshots (not hand-edited)
- `mysql.cri.py`, `mysql.ori.py`, `galera sst`, `multi_instance`, `wsrep-notify`, `size_database`, `netbeans.config.zip` — reference scripts / configs
