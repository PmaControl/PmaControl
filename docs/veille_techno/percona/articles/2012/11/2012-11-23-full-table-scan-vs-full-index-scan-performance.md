---
title: Full Table Scan vs Full Index Scan Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/full-table-scan-vs-full-index-scan-performance/
  post_id: 6466
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2012-11-23T14:40:57'
published_at_gmt: '2012-11-23T14:40:57'
modified_at: '2026-05-04T21:53:28'
modified_at_gmt: '2026-05-04T21:53:28'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- indexining
- MySQL Index Scan
tag_slugs:
- indexining
- mysql-index-scan
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Full-Table-Scan-vs-Full-Index-Scan.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Full Table Scan vs Full Index Scan Performance

Source: [Percona Blog](https://www.percona.com/blog/full-table-scan-vs-full-index-scan-performance/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2012-11-23T14:40:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Earlier this week, Cédric blogged about how easy we can get confused between a covering index and a full index scan in the EXPLAIN output. While a covering index (seen with EXPLAIN as Extra: Using index) is a very interesting performance optimization, a full index scan (type: index) is according to the documentation the 2nd … Continued

## Images et graphiques reperes

- featured / image: [Full Table Scan vs Full Index Scan Performance](https://www.percona.com/wp-content/uploads/2026/03/Full-Table-Scan-vs-Full-Index-Scan.jpg)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
