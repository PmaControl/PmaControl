---
title: Scaling TokuDB Performance with Binlog Group Commit
source:
  name: Percona Blog
  url: https://www.percona.com/blog/scaling-tokudb-performance-binlog-group-commit/
  post_id: 9901
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2014-12-17T19:52:26'
published_at_gmt: '2014-12-17T19:52:26'
modified_at: '2026-05-05T22:23:18'
modified_at_gmt: '2026-05-05T22:23:18'
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
- Benchmarking
- MySQL
- Percona
- Performance
- TokuDB
tag_slugs:
- benchmarking
- mysql
- cap-percona
- performance
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Scaling TokuDB Performance with Binlog Group Commit

Source: [Percona Blog](https://www.percona.com/blog/scaling-tokudb-performance-binlog-group-commit/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2014-12-17T19:52:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

TokuDB offers high throughput for write intensive applications, and the throughput scales with the number of concurrent clients. However, when the binary log is turned on, TokuDB 7.5.2 throughput suffers. The throughput scaling problem is caused by a poor interaction between the binary log group commit algorithm in MySQL 5.6 and the way TokuDB commits … Continued
