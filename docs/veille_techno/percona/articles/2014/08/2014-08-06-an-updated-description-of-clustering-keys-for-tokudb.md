---
title: An Updated Description of Clustering Keys for TokuDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/an-updated-description-of-clustering-keys-for-tokudb/
  post_id: 9887
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2014-08-06T18:12:48'
published_at_gmt: '2014-08-06T18:12:48'
modified_at: '2026-03-25T18:29:23'
modified_at_gmt: '2026-03-25T18:29:23'
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
- clustering indexes
- MariaDB
- MySQL
- Percona
- Storage Engine
- TokuDB
tag_slugs:
- clustering-indexes
- mariadb
- mysql
- cap-percona
- storage-engine
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# An Updated Description of Clustering Keys for TokuDB

Source: [Percona Blog](https://www.percona.com/blog/an-updated-description-of-clustering-keys-for-tokudb/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2014-08-06T18:12:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Covering indexes can result in orders of magnitude performance improvements for queries. Bradley’s presentation on covering indexes describes what a covering index is, how it can effect performance, and why it works. However, the definition of a covering index can get cumbersome since MySQL limits the number of columns in a key to 16 (32 on … Continued
