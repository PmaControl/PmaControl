---
title: 'pg_stat_monitor: A New Way Of Looking At PostgreSQL Metrics'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pg_stat_monitor-a-new-way-of-looking-at-postgresql-metrics/
  post_id: 23553
source_author:
  name: Robert Bernier
  slug: robert-bernier
  url: https://www.percona.com/blog/author/robert-bernier/
  website: ''
published_at: '2021-01-19T14:53:00'
published_at_gmt: '2021-01-19T14:53:00'
modified_at: '2026-03-26T20:09:30'
modified_at_gmt: '2026-03-26T20:09:30'
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
- insight for DBAs
- insight for developers
- Percona Software
- PostgreSQL
tag_slugs:
- insight-for-dbas
- insight-for-developers
- percona-software
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-pg_stat_monitor.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# pg_stat_monitor: A New Way Of Looking At PostgreSQL Metrics

Source: [Percona Blog](https://www.percona.com/blog/pg_stat_monitor-a-new-way-of-looking-at-postgresql-metrics/)

Auteur source: [Robert Bernier](https://www.percona.com/blog/author/robert-bernier/)

Publication: 2021-01-19T14:53:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Enter pg_stat_monitor: this extension, created here at Percona, has been developed as an advanced replacement of pg_stat_statement, providing new capabilities in addition to the standard fare. As you may recall, PostgreSQL’s pg_stat_statements extension provides a means of tracking execution statistics of all SQL statements executed by the server. But sometimes just having the basics is … Continued

## Structure detectee

- H2: Download/Compile/Install
- H3: Method 1: The Percona Distribution For PostgreSQL
- H3: Method 2: Compile And Install (Community PostgreSQL Repository)
- H3: Method 3: Roll Your Own Packages
- H3: Method 4: Using PGXN
- H2: Create Extension “pg_stat_monitor”
- H2: Using pg_stat_monitor
- H2: Updating pg_stat_monitor_settings
- H2: Error Monitoring

## Images et graphiques reperes

- featured / image: [pg_stat_monitor: A New Way Of Looking At PostgreSQL Metrics](https://www.percona.com/wp-content/uploads/2026/03/Percona-pg_stat_monitor.png)

## Auteur source

Robert's first working computer was the very user-friendly IBM 360 with an awesome 4MB RAM. After a round of much needed therapy overcoming the trauma of programming with punch cards he discovered the IBM-XT and the miracle of DOS 2.0. Years later, Robert became enamored with Linux and the opensource world and after meeting one of the members of CORE his primary focus had become all things PostgreSQL. Robert has since then worked in mom and pop companies, fortune 50 corporations and a number of very cool environments including the famed Los Alamos National Laboratory in New Mexico, birthplace of the atomic age. Although reluctant to leave the enjoyable experience of California's Silicon Valley commuter life, he returned to the Pacific Northwest and once again experienced real weather. These days, he serves as the PostgreSQL Consultant here at Percona.
