---
title: Is there room for more MySQL IO Optimization?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/is-there-a-room-for-more-mysql-io-optimization/
  post_id: 6512
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2013-01-03T23:22:51'
published_at_gmt: '2013-01-03T23:22:51'
modified_at: '2026-05-04T21:55:15'
modified_at_gmt: '2026-05-04T21:55:15'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- innodb_flush_method=O_DIRECT
- MySQL IO Optimization
- Percona Server for MySQL
tag_slugs:
- innodb_flush_methodo_direct
- mysql-io-optimization
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/chart_1-1.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Is there room for more MySQL IO Optimization?

Source: [Percona Blog](https://www.percona.com/blog/is-there-a-room-for-more-mysql-io-optimization/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2013-01-03T23:22:51

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I prefer to run MySQL with innodb_flush_method=O_DIRECT in most cases – it makes sure there is no overhead of double buffering and I can save the limited amount of file system cache I would normally have on database server for those things which need to be cached — system files, binary log, FRM files, MySQL … Continued

## Images et graphiques reperes

- featured / image: [Is there room for more MySQL IO Optimization?](https://www.percona.com/wp-content/uploads/2026/03/chart_1-1.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
