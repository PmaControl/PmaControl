---
title: How many partitions can you have ?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-many-partitions-can-you-have/
  post_id: 2158
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2009-12-05T08:21:34'
published_at_gmt: '2009-12-05T08:21:34'
modified_at: '2026-04-28T21:05:35'
modified_at_gmt: '2026-04-28T21:05:35'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- Bugs
- InnoDB
- MyISAM
tag_slugs:
- bugs
- innodb
- myisam
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How many partitions can you have ?

Source: [Percona Blog](https://www.percona.com/blog/how-many-partitions-can-you-have/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2009-12-05T08:21:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I had an interesting case recently. The customer dealing with large MySQL data warehouse had the table which was had data merged into it with INSERT ON DUPLICATE KEY UPDATE statements. The performance was extremely slow. I turned out it is caused by hundreds of daily partitions created for this table. What is the most … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
