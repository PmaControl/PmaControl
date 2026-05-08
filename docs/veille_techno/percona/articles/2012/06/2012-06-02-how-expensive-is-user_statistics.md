---
title: How expensive is USER_STATISTICS?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-expensive-is-user_statistics/
  post_id: 3620
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2012-06-02T16:36:11'
published_at_gmt: '2012-06-02T16:36:11'
modified_at: '2026-03-23T22:21:51'
modified_at_gmt: '2026-03-23T22:21:51'
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
- Percona Software
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How expensive is USER_STATISTICS?

Source: [Percona Blog](https://www.percona.com/blog/how-expensive-is-user_statistics/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2012-06-02T16:36:11

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of our customers asked me whether it’s safe to enable the so-called USER_STATISTICS features of Percona Server in a heavy-use production server with many tens of thousands of tables. If you’re not familiar with this feature, it creates some new INFORMATION_SCHEMA tables that add counters for activity on users, hosts, tables, indexes, and more. … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
