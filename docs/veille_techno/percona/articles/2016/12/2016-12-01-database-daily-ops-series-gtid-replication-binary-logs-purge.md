---
title: GTID Replication and Binary Logs Purge
source:
  name: Percona Blog
  url: https://www.percona.com/blog/database-daily-ops-series-gtid-replication-binary-logs-purge/
  post_id: 15952
source_author:
  name: Wagner Bianchi
  slug: wagner-bianchi
  url: https://www.percona.com/blog/author/wagner-bianchi/
  website: ''
published_at: '2016-12-01T17:43:34'
published_at_gmt: '2016-12-01T17:43:34'
modified_at: '2026-05-05T19:42:24'
modified_at_gmt: '2026-05-05T19:42:24'
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
- Percona Services
category_slugs:
- insight-for-dbas
- mysql
- percona-services
tags:
- GTID-replication
- MySQL
- operations
tag_slugs:
- gtid-replication
- mysql
- operations
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-replication-e1480466617340.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# GTID Replication and Binary Logs Purge

Source: [Percona Blog](https://www.percona.com/blog/database-daily-ops-series-gtid-replication-binary-logs-purge/)

Auteur source: [Wagner Bianchi](https://www.percona.com/blog/author/wagner-bianchi/)

Publication: 2016-12-01T17:43:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog continues the ongoing series on daily operations and GTID replication and binary logs purge. In this blog, I’m going to investigate why the error below has been appearing in a special environment I’ve been working with on the last few days: MySQL Last_IO_Errno: 1236 Last_IO_Error: Got fatal error 1236 from master when reading data from binary log: 'The slave is connecting using CHANGE MASTER TO MASTER_AUTO_POSITION = 1, but the master has purged binary logs containing GTIDs that the slave requires.' 1 2 3 4 Last_IO_Errno: 1236 Last_IO_Error: Got fatal error 1236 from master when reading data from binary log : 'The slave is connecting using CHANGE MASTER TO MASTER_AUTO_POSITION = 1, but the master has purged binary logs containing GTIDs that the slave requires.' The error provides the right message and explains what is going … Continued

## Images et graphiques reperes

- featured / image: [GTID Replication and Binary Logs Purge](https://www.percona.com/wp-content/uploads/2026/03/MySQL-replication-e1480466617340.jpg)

## Auteur source

Bianchi, as he likes to be called, is a MySQL DBA for more than 10 years, has been working with many of the biggest companies in the world with focus centered in Data-Infrastructure, Performance, Scale-Out and HA. Before working at Percona, Bianchi worked at Splunk, Oracle and IBM.
