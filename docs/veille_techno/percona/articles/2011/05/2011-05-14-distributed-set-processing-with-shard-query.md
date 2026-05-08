---
title: Distributed Set Processing with Shard-Query
source:
  name: Percona Blog
  url: https://www.percona.com/blog/distributed-set-processing-with-shard-query/
  post_id: 2938
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2011-05-14T07:00:01'
published_at_gmt: '2011-05-14T07:00:01'
modified_at: '2026-05-04T21:33:18'
modified_at_gmt: '2026-05-04T21:33:18'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2011/05/chart_23.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Distributed Set Processing with Shard-Query

Source: [Percona Blog](https://www.percona.com/blog/distributed-set-processing-with-shard-query/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2011-05-14T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Can Shard-Query scale to 20 nodes? Peter asked this question in comments to to my previous Shard-Query benchmark. Actually he asked if it could scale to 50, but testing 20 was all I could due to to EC2 and time limits. I think the results at 20 nodes are very useful to understand the … Continued

## Structure detectee

- H2: Can Shard-Query scale to 20 nodes?
- H2: Distributed set processing (theory)
- H3: What is SQL?
- H3: What is a result set?
- H3: Materialized views techniques applied to distributed computation
- H3: Shard-Query works only on sets
- H3: Intern-node communication
- H2: Work on problems of any size.
- H3: Set processing is massively parallel
- H3: Distributed set processing is database agnostic.
- H3: Shard-Query can provide query execution plans based on the relational algebra rewrites

## Images et graphiques reperes

- featured / image: [Distributed Set Processing with Shard-Query](https://www.percona.com/blog/wp-content/uploads/2011/05/chart_23.png)

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
