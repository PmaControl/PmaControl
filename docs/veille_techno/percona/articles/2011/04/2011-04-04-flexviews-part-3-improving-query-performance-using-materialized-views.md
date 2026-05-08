---
title: Flexviews – part 3 – improving query performance using materialized views
source:
  name: Percona Blog
  url: https://www.percona.com/blog/flexviews-part-3-improving-query-performance-using-materialized-views/
  post_id: 2771
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2011-04-04T20:36:31'
published_at_gmt: '2011-04-04T20:36:31'
modified_at: '2026-05-04T21:30:39'
modified_at_gmt: '2026-05-04T21:30:39'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Flexviews – part 3 – improving query performance using materialized views

Source: [Percona Blog](https://www.percona.com/blog/flexviews-part-3-improving-query-performance-using-materialized-views/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2011-04-04T20:36:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Combating “data drift” In my first post in this series, I described materialized views (MVs). An MV is essentially a cached result set at one point in time. The contents of the MV will become incorrect (out of sync) when the underlying data changes. This loss of synchronization is sometimes called drift. This is conceptually … Continued

## Structure detectee

- H2: Combating “data drift”
- H2: Selecting a refresh method
- H3: The cost of the query
- H3: SQL features used in the query
- H2: Refresh methods
- H3: The incremental refresh method
- H3: The Flexviews SQL_API
- H3: Enable the view to use it
- H3: Data dictionary
- H3: Using the dictionary
- H2: The complete refresh method
- H2: Refresh method performance comparison
- H3: Refreshing the MVs
- H3: The flexviews.refresh() stored procedure
- H3: And then confirm they contain the same results
- H2: Conclusion

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
