---
title: Fast Updates with TokuDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/fast-updates-with-tokudb/
  post_id: 9759
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2013-02-12T15:50:46'
published_at_gmt: '2013-02-12T15:50:46'
modified_at: '2026-05-05T16:59:24'
modified_at_gmt: '2026-05-05T16:59:24'
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
- Big Data
- Fractal Trees
- MySQL
- NewSQL
- TokuDB
- update
- Upsert
tag_slugs:
- big-data
- fractal-trees
- mysql
- newsql
- tokudb
- update
- upsert
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Fast Updates with TokuDB

Source: [Percona Blog](https://www.percona.com/blog/fast-updates-with-tokudb/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2013-02-12T15:50:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

With TokuDB v6.6 out now, I’m excited to present one of my favorite enhancements: fast updates with TokuDB. Update intensive applications can have their throughput limited by the random read capacity of the storage system. The cause of the throughput limit is the read-modify-write algorithm that MySQL uses when processing update statements. MySQL reads a … Continued
