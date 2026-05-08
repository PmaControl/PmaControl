---
title: A Dive Into MySQL Multi-Threaded Replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-dive-into-mysql-multi-threaded-replication/
  post_id: 25484
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2022-03-24T11:51:23'
published_at_gmt: '2022-03-24T11:51:23'
modified_at: '2026-05-04T21:14:36'
modified_at_gmt: '2026-05-04T21:14:36'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Multi-Threaded-Replication.png
image_count: 7
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Dive Into MySQL Multi-Threaded Replication

Source: [Percona Blog](https://www.percona.com/blog/a-dive-into-mysql-multi-threaded-replication/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2022-03-24T11:51:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

For a very long part of its history, MySQL replication has been limited in terms of performance. Because there was no way of knowing if transactions or updates were independent, the updates had to be executed on a replica following the exact same sequence of operations as on the primary server. The only way to … Continued

## Structure detectee

- H2: Context
- H3: Per-Database Replication
- H3: Group Commit
- H3: Logical_clock Replication
- H2: Test Configuration
- H2: How Good is MTR?
- H2: The Cost of Durability
- H3: Sync Delay
- H3: Sync No Delay Count
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [A Dive Into MySQL Multi-Threaded Replication](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Multi-Threaded-Replication.png)
- content / image: [MySQL Multi-Threaded Replication](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Multi-Threaded-Replication-300x160.png)
- content / graph_or_chart: [sysbench indexed update benchmark](https://www.percona.com/wp-content/uploads/2026/03/parallel-replication-acid-1t-with-annotations.png)
- content / image: [replica_parallel_workers](https://www.percona.com/wp-content/uploads/2026/03/parallel-replication-acid-16t-with-annotations.png)
- content / image: [Multi-Threaded Replication](https://www.percona.com/wp-content/uploads/2026/03/Parallel_noacid_cleanflush_syncdelay0-with-annotations.png)
- content / image: [Sync Delay](https://www.percona.com/wp-content/uploads/2026/03/Parallel_noacid_cleanflush_syncdelay5-with-annotations.png)
- content / image: [Parallel_noacid_cleanflush_synccount-with-annotations.png](https://www.percona.com/wp-content/uploads/2026/03/Parallel_noacid_cleanflush_synccount-with-annotations.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
