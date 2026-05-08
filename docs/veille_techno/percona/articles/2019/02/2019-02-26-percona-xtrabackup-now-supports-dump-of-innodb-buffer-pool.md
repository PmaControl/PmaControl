---
title: Percona XtraBackup Now Supports Dump of InnoDB Buffer Pool
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtrabackup-now-supports-dump-of-innodb-buffer-pool/
  post_id: 19789
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2019-02-26T10:38:24'
published_at_gmt: '2019-02-26T10:38:24'
modified_at: '2026-04-27T21:09:29'
modified_at_gmt: '2026-04-27T21:09:29'
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
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- backup
- backup tools
- MySQL Backup tools
tag_slugs:
- backup
- backup-tools
- mysql-backup-tools
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/percona-xtra-backup-buffer-pool-restore.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraBackup Now Supports Dump of InnoDB Buffer Pool

Source: [Percona Blog](https://www.percona.com/blog/percona-xtrabackup-now-supports-dump-of-innodb-buffer-pool/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2019-02-26T10:38:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

InnoDB keeps hot data in memory on its buffer named InnoDB Buffer Pool. For a long time, when a MySQL instance needed to bounce, this hot cached data was lost and the instance required a warm-up period to perform as well as it did before the service restart. That is not the case anymore. Newer … Continued

## Structure detectee

- H2: How it works
- H2: Percona XtraDB Cluster
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Percona XtraBackup Now Supports Dump of InnoDB Buffer Pool](https://www.percona.com/wp-content/uploads/2026/03/percona-xtra-backup-buffer-pool-restore.jpg)
- content / image: [percona-xtra-backup buffer pool restore](https://www.percona.com/wp-content/uploads/2026/03/percona-xtra-backup-buffer-pool-restore-300x200.jpg)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
