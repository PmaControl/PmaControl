---
title: Best kept MySQLDump Secret
source:
  name: Percona Blog
  url: https://www.percona.com/blog/best-kept-mysqldump-secret/
  post_id: 3430
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2012-03-23T14:59:02'
published_at_gmt: '2012-03-23T14:59:02'
modified_at: '2026-04-28T21:34:21'
modified_at_gmt: '2026-04-28T21:34:21'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Best kept MySQLDump Secret

Source: [Percona Blog](https://www.percona.com/blog/best-kept-mysqldump-secret/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2012-03-23T14:59:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Many people use mysqldump –single-transaction to get consistent backup for their Innodb tables without making database read only. In most cases it works, but did you know there are some cases when you can get table entirely missing from the backup if you use this technique ? The problem comes from the fact how MySQL’s … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
