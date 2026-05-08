---
title: MySQL caching methods and tips
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-caching-methods-and-tips/
  post_id: 2768
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2011-04-05T04:39:26'
published_at_gmt: '2011-04-05T04:39:26'
modified_at: '2026-03-23T21:54:59'
modified_at_gmt: '2026-03-23T21:54:59'
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
- Memcached
- Performance
- query cache
- Tips
- Tuning
tag_slugs:
- memcached
- performance
- query-cache
- tips
- tuning
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL caching methods and tips

Source: [Percona Blog](https://www.percona.com/blog/mysql-caching-methods-and-tips/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2011-04-05T04:39:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

“The least expensive query is the query you never run.” Data access is expensive for your application. It often requires CPU, network and disk access, all of which can take a lot of time. Using less computing resources, particularly in the cloud, results in decreased overall operational costs, so caches provide real value by avoiding … Continued

## Structure detectee

- H2: “The least expensive query is the query you never run.”
- H2: Popular cache methods
- H3: The MySQL query cache
- H3: External cache (Memcached)
- H4: Cache invalidation is a problem
- H4: Use what you need
- H4: Pick an efficient cache representation
- H4: Don’t make too many round trips
- H3: Summary tables
- H4: Using INSERT .. SELECT for summary tables
- H2: Conclusion

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
