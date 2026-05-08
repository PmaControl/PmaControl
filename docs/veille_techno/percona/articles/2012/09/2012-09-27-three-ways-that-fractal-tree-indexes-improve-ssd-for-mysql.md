---
title: Three Ways that Fractal Tree Indexes Improve SSD for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/three-ways-that-fractal-tree-indexes-improve-ssd-for-mysql/
  post_id: 9717
source_author:
  name: kuszmaul
  slug: kuszmaul
  url: https://www.percona.com/blog/author/kuszmaul/
  website: ''
published_at: '2012-09-27T15:06:14'
published_at_gmt: '2012-09-27T15:06:14'
modified_at: '2026-03-25T18:24:11'
modified_at_gmt: '2026-03-25T18:24:11'
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
- Flash Drives
- iiBench
- InnoDB
- MySQL
- NewSQL
- TokuDB
tag_slugs:
- flash-drives
- iibench
- innodb
- mysql
- newsql
- tokudb
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2012/09/flash-iibench.png
image_count: 4
graph_or_chart_count: 4
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Three Ways that Fractal Tree Indexes Improve SSD for MySQL

Source: [Percona Blog](https://www.percona.com/blog/three-ways-that-fractal-tree-indexes-improve-ssd-for-mysql/)

Auteur source: [kuszmaul](https://www.percona.com/blog/author/kuszmaul/)

Publication: 2012-09-27T15:06:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Since Fractal Tree indexes turn random writes into sequential writes, it’s easy to see why they offer a big advantage for maintaining indexes on rotating disks. It turns out that that Fractal Tree indexing also offers signficant advantages on SSD. Here are three ways that Fractal Trees improve your life if you use SSDs. Advantage … Continued

## Structure detectee

- H3: Advantage 1: Index maintenence performance.
- H3: Advantage 2: Compression.
- H3: Advantage 3: Reduced wear.

## Images et graphiques reperes

- content / graph_or_chart: [iibench graph](https://www.percona.com/blog/wp-content/uploads/2012/09/flash-iibench.png)
- content / graph_or_chart: [compression ratio graph](https://www.percona.com/blog/wp-content/uploads/2012/09/flash-compression.png)
- content / graph_or_chart: [compression speed graph](https://www.percona.com/blog/wp-content/uploads/2012/09/flash-loader.png)
- content / graph_or_chart: [flash wear life graph](https://www.percona.com/blog/wp-content/uploads/2012/09/flash-wearlife.png)
