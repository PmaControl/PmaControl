---
title: REPEATABLE-READ and READ-COMMITTED Transaction Isolation Levels
source:
  name: Percona Blog
  url: https://www.percona.com/blog/differences-between-read-committed-and-repeatable-read-transaction-isolation-levels/
  post_id: 3705
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2012-08-28T18:31:03'
published_at_gmt: '2012-08-28T18:31:03'
modified_at: '2026-05-05T17:49:23'
modified_at_gmt: '2026-05-05T17:49:23'
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
tags:
- ACID
- InnoDB
- Locking
- Transaction isolation
tag_slugs:
- acid
- innodb
- locking
- transaction-isolation
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# REPEATABLE-READ and READ-COMMITTED Transaction Isolation Levels

Source: [Percona Blog](https://www.percona.com/blog/differences-between-read-committed-and-repeatable-read-transaction-isolation-levels/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2012-08-28T18:31:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As an instructor with Percona, I’m sometimes asked about the differences between the REPEATABLE-READ and READ-COMMITTED transaction isolation levels. There are a few differences between them, and they are all related to locking.

## Structure detectee

- H2: REPEATABLE-READ
- H2: READ-COMMITTED
- H3: Non-repeatable reads (read-committed)

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
