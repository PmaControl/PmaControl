---
title: A TokuDB Stall Caused by Conflicting Transactions When Opening a Table
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-tokudb-stall-caused-by-conflicting-transactions-when-opening-a-table/
  post_id: 3029
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2013-10-03T18:02:58'
published_at_gmt: '2013-10-03T18:02:58'
modified_at: '2026-03-23T22:04:11'
modified_at_gmt: '2026-03-23T22:04:11'
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
- Fractal Tree
- MariaDB
- MySQL
- TokuDB
- tokumx
tag_slugs:
- fractal-tree
- mariadb
- mysql
- tokudb
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A TokuDB Stall Caused by Conflicting Transactions When Opening a Table

Source: [Percona Blog](https://www.percona.com/blog/a-tokudb-stall-caused-by-conflicting-transactions-when-opening-a-table/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2013-10-03T18:02:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of our customers reported that ‘create table select from’ statements stall for a period of time equal to the TokuDB lock timeout. This indicated a lock conflict between multiple transactions. In addition, other MySQL clients that were opening unrelated tables were also stalled. This indicated that some shared mutex is held too long. We … Continued
