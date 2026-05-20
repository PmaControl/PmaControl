---
title: Using XtraBackup on NFS for MySQL backups
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-xtrabackup-on-nfs-for-mysql-backups/
  post_id: 2519
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-12-09T18:56:42'
published_at_gmt: '2010-12-09T18:56:42'
modified_at: '2026-03-23T21:47:53'
modified_at_gmt: '2026-03-23T21:47:53'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using XtraBackup on NFS for MySQL backups

Source: [Percona Blog](https://www.percona.com/blog/using-xtrabackup-on-nfs-for-mysql-backups/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-12-09T18:56:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

XtraBackup works great for backing MySQL up to an NFS volume, but there is a gotcha that you need to be aware of. This applies to anything you do with NFS, not just XtraBackup. The gotcha is that NFS uses client-side caching to reduce overhead of sending data across the network.

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
