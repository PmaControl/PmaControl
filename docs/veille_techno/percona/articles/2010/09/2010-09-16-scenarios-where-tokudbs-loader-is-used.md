---
title: Scenarios where TokuDB’s Loader is Used
source:
  name: Percona Blog
  url: https://www.percona.com/blog/scenarios-where-tokudbs-loader-is-used/
  post_id: 9530
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2010-09-16T17:06:21'
published_at_gmt: '2010-09-16T17:06:21'
modified_at: '2026-03-25T18:15:31'
modified_at_gmt: '2026-03-25T18:15:31'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- mysql tokudb loader
tag_slugs:
- mysql-tokudb-loader
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Scenarios where TokuDB’s Loader is Used

Source: [Percona Blog](https://www.percona.com/blog/scenarios-where-tokudbs-loader-is-used/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2010-09-16T17:06:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

TokuDB’s loader uses the available multicore computing resources of the machine to presort and insert the data. In the last couple of posts (here and here), Rich and Dave presented performance results of TokuDB’s loader. Comparing load times with TokuDB 2.1.0, Rich found a 2.1x speedup on a 2 core machine, and a 4.2x speedup … Continued
