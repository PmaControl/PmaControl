---
title: 'Grafana Dashboards: A PoC Implementing the PostgreSQL Extension pg_stat_monitor'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/grafana-dashboards-a-poc-implementing-the-postgresql-extension-pg_stat_monitor/
  post_id: 27817
source_author:
  name: Robert Bernier
  slug: robert-bernier
  url: https://www.percona.com/blog/author/robert-bernier/
  website: ''
published_at: '2023-12-26T13:23:22'
published_at_gmt: '2023-12-26T13:23:22'
modified_at: '2026-03-26T20:07:40'
modified_at_gmt: '2026-03-26T20:07:40'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- Percona Software
- PostgreSQL
category_slugs:
- insight-for-dbas
- percona-software
- postgresql
tags:
- PostgreSQL
- Robert PP
tag_slugs:
- postgresql
- robert-planetpostgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_a_grey_high_tech_texture_made_out_of_computer_part_6c6d0530-f0bf-495c-b368-150aeab6b465.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Grafana Dashboards: A PoC Implementing the PostgreSQL Extension pg_stat_monitor

Source: [Percona Blog](https://www.percona.com/blog/grafana-dashboards-a-poc-implementing-the-postgresql-extension-pg_stat_monitor/)

Auteur source: [Robert Bernier](https://www.percona.com/blog/author/robert-bernier/)

Publication: 2023-12-26T13:23:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This PoC demonstrates how to install and configure pg_stat_monitor in order to extract useful and actionable metrics from a PostgreSQL database and display them on a Grafana dashboard. About the environment Grafana: version 10.0.0 Grafana database backend: Prometheus version 2.15.2+d PostgreSQL version 13 pgbench version 13 In order to investigate the potential opportunities for implementing … Continued

## Structure detectee

- H2: About the environment
- H2: Configuring Grafana
- H2: pg_stat_monitor
- H3: About
- H3: Features
- H3: Installation (example: CENTOS8, pg14)
- H3: Create extension
- H2: About pgbench
- H3: Querying the data
- H3: Table: pg_stat_monitor_archive
- H3: Table: pg_stat_monitor_qry
- H3: Table: pg_stat_monitor_shared_blk_io
- H3: Table: pg_stat_monitor_blk_io
- H3: Table: pg_stat_monitor_uniq_id
- H2: Benchmarking
- H2: Dashboard example 1: Querying saved data
- H3: Top panel (Query execution time vs. DML)
- H3: Bottom panel (Query execution time vs. shared blocks)
- H2: Analysis
- H2: Dashboard example 2: Monitoring in real time
- H3: Top panel (Execution time vs. DML)
- H3: Bottom panel (Time vs. IO)
- H3: Analysis
- H1: Conclusion

## Images et graphiques reperes

- featured / image: [Grafana Dashboards: A PoC Implementing the PostgreSQL Extension pg_stat_monitor](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_a_grey_high_tech_texture_made_out_of_computer_part_6c6d0530-f0bf-495c-b368-150aeab6b465.png)
- content / image: [Grafana Pgbench](https://www.percona.com/wp-content/uploads/2026/03/image1-34.png)
- content / image: [image2-28.png](https://www.percona.com/wp-content/uploads/2026/03/image2-28.png)
- content / image: [image4-22.png](https://www.percona.com/wp-content/uploads/2026/03/image4-22.png)

## Auteur source

Robert's first working computer was the very user-friendly IBM 360 with an awesome 4MB RAM. After a round of much needed therapy overcoming the trauma of programming with punch cards he discovered the IBM-XT and the miracle of DOS 2.0. Years later, Robert became enamored with Linux and the opensource world and after meeting one of the members of CORE his primary focus had become all things PostgreSQL. Robert has since then worked in mom and pop companies, fortune 50 corporations and a number of very cool environments including the famed Los Alamos National Laboratory in New Mexico, birthplace of the atomic age. Although reluctant to leave the enjoyable experience of California's Silicon Valley commuter life, he returned to the Pacific Northwest and once again experienced real weather. These days, he serves as the PostgreSQL Consultant here at Percona.
