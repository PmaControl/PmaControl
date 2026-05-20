---
title: MySQL Point in Time Recovery the Right Way
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-point-in-time-recovery-right-way/
  post_id: 17511
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2017-10-23T17:08:26'
published_at_gmt: '2017-10-23T17:08:26'
modified_at: '2026-05-05T19:49:29'
modified_at_gmt: '2026-05-05T19:49:29'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- pitr
- point in time recovery
- point-in-time backups
tag_slugs:
- mysql
- pitr
- point-in-time-recovery
- point-in-time-backups
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Point-In-Time-Recovery.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Point in Time Recovery the Right Way

Source: [Percona Blog](https://www.percona.com/blog/mysql-point-in-time-recovery-right-way/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2017-10-23T17:08:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I’ll look at how to do MySQL point in time recovery (PITR) correctly. Sometimes we need to restore from a backup, and then replay the transactions that happened after the backup was taken. This is a common procedure in most disaster recovery plans, when for example you accidentally drop a table/database or … Continued

## Structure detectee

- H2: MySQL Point in Time Recovery

## Images et graphiques reperes

- featured / image: [MySQL Point in Time Recovery the Right Way](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Point-In-Time-Recovery.png)
- content / image: [MySQL Point In Time Recovery](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Point-In-Time-Recovery-300x200.png)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
