---
title: Extending Index for Innodb tables can hurt performance in a surprising way
source:
  name: Percona Blog
  url: https://www.percona.com/blog/extending-index-for-innodb-tables-can-hurt-performance-in-a-surprising-way/
  post_id: 2331
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-05-22T20:39:14'
published_at_gmt: '2010-05-22T20:39:14'
modified_at: '2026-04-28T21:13:30'
modified_at_gmt: '2026-04-28T21:13:30'
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
- Optimizer
tag_slugs:
- innodb
- optimizer
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Extending Index for Innodb tables can hurt performance in a surprising way

Source: [Percona Blog](https://www.percona.com/blog/extending-index-for-innodb-tables-can-hurt-performance-in-a-surprising-way/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-05-22T20:39:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One schema optimization we often do is extending index when there are queries which can use more key part. Typically this is safe operation, unless index length increases dramatically queries which can use index can also use prefix of the new index are they ? It turns there are special cases when this is not … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
