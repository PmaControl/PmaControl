---
title: Improving InnoDB recovery time
source:
  name: Percona Blog
  url: https://www.percona.com/blog/improving-innodb-recovery-time/
  post_id: 1943
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2009-07-08T02:42:22'
published_at_gmt: '2009-07-08T02:42:22'
modified_at: '2026-04-28T21:00:50'
modified_at_gmt: '2026-04-28T21:00:50'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- XtraBackup
matched_filters:
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Percona Software
category_slugs:
- percona-software
tags:
- Backups
- XtraDB
tag_slugs:
- backups
- xtradb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Improving InnoDB recovery time

Source: [Percona Blog](https://www.percona.com/blog/improving-innodb-recovery-time/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2009-07-08T02:42:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Speed of InnoDB recovery is known and quite annoying problem. It was discussed many times, see: http://bugs.mysql.com/bug.php?id=29847 http://dammit.lt/2008/10/26/innodb-crash-recovery/ This is problem when your InnoDB crashes, it may takes long time to start. Also it affects restoring from backup (both LVM and xtrabackup / innobackup) In this is simple test, I do crash mysql during in-memory … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
