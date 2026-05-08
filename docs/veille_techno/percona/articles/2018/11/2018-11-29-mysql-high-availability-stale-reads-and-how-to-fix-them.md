---
title: 'MySQL High Availability: Stale Reads and How to Fix Them'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-high-availability-stale-reads-and-how-to-fix-them/
  post_id: 19663
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2018-11-29T14:51:48'
published_at_gmt: '2018-11-29T14:51:48'
modified_at: '2026-03-20T22:07:28'
modified_at_gmt: '2026-03-20T22:07:28'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- application design
- latency
- stale read
tag_slugs:
- application-design
- latency
- stale-read
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/solutions-for-MySQL-Stale-Reads.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL High Availability: Stale Reads and How to Fix Them

Source: [Percona Blog](https://www.percona.com/blog/mysql-high-availability-stale-reads-and-how-to-fix-them/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2018-11-29T14:51:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Continuing on the series of blog posts about MySQL High Availability, today we will talk about stale reads and how to overcome this issue. The Problem Stale reads is a read operation that fetches an incorrect value from a source that has not synchronized an update operation to the value (source Wiktionary). A practical scenario … Continued

## Structure detectee

- H2: The Problem
- H2: How NOT to fix stale reads
- H3: SELECT SLEEP(X)
- H3: Semisync replication
- H2: How to PROPERLY fix stale reads
- H3: 1) MASTER_POS_WAIT
- H3: 2) WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS
- H3: 3) Querying slave_relay_log_info
- H3: 4) wsrep-sync-wait
- H3: 5) ProxySQL 2.0 GTID consistent reads
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [MySQL High Availability: Stale Reads and How to Fix Them](https://www.percona.com/wp-content/uploads/2026/03/solutions-for-MySQL-Stale-Reads.jpg)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
