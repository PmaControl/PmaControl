---
title: A workaround for the performance problems of TEMPTABLE views
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-workaround-for-the-performance-problems-of-temptable-views/
  post_id: 2324
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2010-05-19T18:17:32'
published_at_gmt: '2010-05-19T18:17:32'
modified_at: '2026-04-28T21:12:43'
modified_at_gmt: '2026-04-28T21:12:43'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags:
- Optimizer
- Performance
- Tips
- views mysql merge temptable workaround
tag_slugs:
- optimizer
- performance
- tips
- views-mysql-merge-temptable-workaround
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A workaround for the performance problems of TEMPTABLE views

Source: [Percona Blog](https://www.percona.com/blog/a-workaround-for-the-performance-problems-of-temptable-views/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2010-05-19T18:17:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL supports two different algorithms for views: the MERGE algorithm and the TEMPTABLE algorithm. These two algorithms differ greatly. A view which uses the MERGE algorithm can merge filter conditions into the view query itself. This has significant performance advantages over TEMPTABLE views. A view which uses the TEMPTABLE algorithm will have to compute the … Continued

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
