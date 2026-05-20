---
title: xtrabackup-0.5, bugfixes, incremental backup introduction
source:
  name: Percona Blog
  url: https://www.percona.com/blog/xtrabackup-05-bugfixes-introduction-incremental-backup/
  post_id: 1872
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2009-04-08T03:23:07'
published_at_gmt: '2009-04-08T03:23:07'
modified_at: '2026-04-28T20:58:58'
modified_at_gmt: '2026-04-28T20:58:58'
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
tag_slugs:
- backups
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# xtrabackup-0.5, bugfixes, incremental backup introduction

Source: [Percona Blog](https://www.percona.com/blog/xtrabackup-05-bugfixes-introduction-incremental-backup/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2009-04-08T03:23:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I am happy to announce next build of our backup tool. This version contains several bugfixes and introduces initial implementation of incremental backup. Incremental backup works in next way. When you do regular backup, at the end of procedure you can see output: The latest check point (for incremental): '1319:813219999' >> log scanned up to (1319 813701532) Transaction log of lsn (1318 3034677302) to (1319 813701532) was copied. 090404 06:03:29 innobackupex: All tables unlocked 090404 06:03:29 innobackupex: Connection to database server closed innobackupex: Backup created in directory '/mnt/data/tmp' innobackupex: MySQL binlog position: filename 'db02-bin.001271', position 247627478 090404 06:07:58 innobackupex: innobackup completed OK! innobackupex: You must use -i (--ignore-zeros) option for extraction of the tar stream. 1 2 3 4 5 6 7 8 9 10 The latest check point ( for incremental...

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
