---
title: Causes and Workarounds for Slave Performance Too Slow with Row-Based Events
source:
  name: Percona Blog
  url: https://www.percona.com/blog/slave-performance-too-slow-with-row-based-events-causes-and-workarounds/
  post_id: 18495
source_author:
  name: Alex Poritskiy
  slug: alex-poritskiy
  url: https://www.percona.com/blog/author/alex-poritskiy/
  website: ''
published_at: '2018-05-03T19:09:31'
published_at_gmt: '2018-05-03T19:09:31'
modified_at: '2026-05-05T19:11:39'
modified_at_gmt: '2026-05-05T19:11:39'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Slave-Performance-Too-Slow-e1525374217728.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Causes and Workarounds for Slave Performance Too Slow with Row-Based Events

Source: [Percona Blog](https://www.percona.com/blog/slave-performance-too-slow-with-row-based-events-causes-and-workarounds/)

Auteur source: [Alex Poritskiy](https://www.percona.com/blog/author/alex-poritskiy/)

Publication: 2018-05-03T19:09:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I worked on one customer issue that I would describe as “slave performance too slow”. During a quick analysis, I’ve found that the replication slave SQL thread cannot keep up while processing row-based events from the master’s binary log. For example: Slave thread example MySQL mysql> SHOW SLAVE STATUSG *************************** 1. row *************************** ... Master_Log_File: binlog.0000185 Read_Master_Log_Pos: 86698585 ... Relay_Master_Log_File: binlog.0000185 Slave_IO_Running: Yes Slave_SQL_Running: Yes ... Exec_Master_Log_Pos: 380 Relay_Log_Space: 85699128 ... Master_UUID: 98974e7f-2fbc-18e9-72cd-07003817585c ... Retrieved_Gtid_Set: 98974e7f-2fbc-18e9-72cd-07003817585c:1055-1057 Executed_Gtid_Set: 7f42e2c5-3fbc-16e7-7fb8-05003715789a:1-2, 98974e7f-2fbc-18e9-72cd-07003817585c:1-1056 ... 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 mysql > SHOW SLAVE STATUSG ***...

## Structure detectee

- H3: What causes that?
- H3: What can we do to solve that?
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Causes and Workarounds for Slave Performance Too Slow with Row-Based Events](https://www.percona.com/wp-content/uploads/2026/03/Slave-Performance-Too-Slow-e1525374217728.jpg)
- content / image: [Slave Performance Too Slow](https://www.percona.com/wp-content/uploads/2026/03/Slave-Performance-Too-Slow-300x213.jpg)
