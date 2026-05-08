---
title: Replay the Execution of MySQL With RR (Record and Replay)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/replay-the-execution-of-mysql-with-rr-record-and-replay/
  post_id: 24194
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2021-04-12T19:00:26'
published_at_gmt: '2021-04-12T19:00:26'
modified_at: '2026-04-27T22:26:15'
modified_at_gmt: '2026-04-27T22:26:15'
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
- tag:percona-xtrabackup:330
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
tags:
- insight for developers
- MySQL
- mysql-and-variants
- Percona XtraBackup
tag_slugs:
- insight-for-developers
- mysql
- mysql-and-variants
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySql-Record-and-Replay.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Replay the Execution of MySQL With RR (Record and Replay)

Source: [Percona Blog](https://www.percona.com/blog/replay-the-execution-of-mysql-with-rr-record-and-replay/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2021-04-12T19:00:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Chasing bugs can be a tedious task, and multi-threaded software doesn’t make it any easier. Threads will be scheduled at different times, instructions will not have deterministic results, and in order for one to reproduce a particular issue, it might require the exact same threads, doing the exact same work, at the exact same time. … Continued

## Structure detectee

- H2: A Backup Problem
- H2: Replaying the Execution
- H2: Replaying the Execution Backward
- H2: Root Cause
- H3: Summary

## Images et graphiques reperes

- featured / image: [Replay the Execution of MySQL With RR (Record and Replay)](https://www.percona.com/wp-content/uploads/2026/03/MySql-Record-and-Replay.png)
- content / image: [MySql Record and Replay](https://www.percona.com/wp-content/uploads/2026/03/MySql-Record-and-Replay-300x160.png)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
