---
title: How well does your table fits in innodb buffer pool ?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-well-does-your-table-fits-in-innodb-buffer-pool/
  post_id: 2525
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-12-10T01:59:48'
published_at_gmt: '2010-12-10T01:59:48'
modified_at: '2026-04-28T21:20:38'
modified_at_gmt: '2026-04-28T21:20:38'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- InnoDB
- Percona
- Performance
- Production
- XtraDB
tag_slugs:
- innodb
- cap-percona
- performance
- production
- xtradb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How well does your table fits in innodb buffer pool ?

Source: [Percona Blog](https://www.percona.com/blog/how-well-does-your-table-fits-in-innodb-buffer-pool/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-12-10T01:59:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Understanding how well your tables and indexes fit to buffer pool are often very helpful to understand why some queries are IO bound and others not – it may be because the tables and indexes they are accessing are not in cache, for example being washed away by other queries. MySQL Server does not provide … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
