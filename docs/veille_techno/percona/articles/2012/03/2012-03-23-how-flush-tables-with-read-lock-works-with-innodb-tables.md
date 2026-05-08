---
title: How MySQL FLUSH TABLES WITH READ LOCK works w/ Innodb Tables
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-flush-tables-with-read-lock-works-with-innodb-tables/
  post_id: 3432
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2012-03-23T15:40:24'
published_at_gmt: '2012-03-23T15:40:24'
modified_at: '2026-05-04T21:43:02'
modified_at_gmt: '2026-05-04T21:43:02'
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

# How MySQL FLUSH TABLES WITH READ LOCK works w/ Innodb Tables

Source: [Percona Blog](https://www.percona.com/blog/how-flush-tables-with-read-lock-works-with-innodb-tables/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2012-03-23T15:40:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Many backup tools including Percona Xtrabackup, MyLVMBackup, and others use FLUSH TABLES WITH READ LOCK to temporary make MySQL read-only. In many cases, the period for which server has to be made read-only is very short, just a few seconds, yet the impact of FLUSH TABLES WITH READ LOCK can be quite large because of … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
