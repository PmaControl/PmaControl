---
title: Performance problem with Innodb and DROP TABLE
source:
  name: Percona Blog
  url: https://www.percona.com/blog/performance-problem-with-innodb-and-drop-table/
  post_id: 2681
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2011-02-03T21:12:11'
published_at_gmt: '2011-02-03T21:12:11'
modified_at: '2026-04-28T21:23:02'
modified_at_gmt: '2026-04-28T21:23:02'
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
tag_slugs:
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Performance problem with Innodb and DROP TABLE

Source: [Percona Blog](https://www.percona.com/blog/performance-problem-with-innodb-and-drop-table/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2011-02-03T21:12:11

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I’ve been working with an application which does a lot of CREATE and DROP table for Innodb tables and we’ve discovered DROP TABLE can take a lot of time and when it happens a lot of other threads stall in “Opening Tables” State. Also contrary to my initial suspect benchmarking create/drop table was CPU bound … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
