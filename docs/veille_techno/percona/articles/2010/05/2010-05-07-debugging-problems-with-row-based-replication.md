---
title: Debugging MySQL Problems with Row-Based Replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/debugging-problems-with-row-based-replication/
  post_id: 2311
source_author:
  name: Justin Swanhart
  slug: justin-swanhart
  url: https://www.percona.com/blog/author/justin-swanhart/
  website: http://www.percona.com/about-us/our-team/justin-swanhart/
published_at: '2010-05-07T01:27:48'
published_at_gmt: '2010-05-07T01:27:48'
modified_at: '2026-04-28T21:12:09'
modified_at_gmt: '2026-04-28T21:12:09'
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
- High Availability
- Recovery
- Replication
- row based logging
- Tips
tag_slugs:
- high-availability
- recovery
- replication
- row-based-logging
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Debugging MySQL Problems with Row-Based Replication

Source: [Percona Blog](https://www.percona.com/blog/debugging-problems-with-row-based-replication/)

Auteur source: [Justin Swanhart](https://www.percona.com/blog/author/justin-swanhart/)

Publication: 2010-05-07T01:27:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL 5.1 introduces row-based binary logging. In fact, the default binary logging format in GA versions of MySQL 5.1 is ‘MIXED’ STATEMENT*; The binlog_format variable can still be changed per sessions which means it is possible that some of your binary log entries will be written in a row-based fashion instead of the actual statement … Continued

## Auteur source

Justin is a former Principal Support Engineer on the support team. In the past, he was a trainer at Percona and a consultant. Justin also created and maintains Shard-Query, a middleware tool for sharding and parallel query execution and Flexviews, a tool for materialized views for MySQL. Prior to working at Percona Justin consulted for Proven Scaling, was a backend engineer at Yahoo! and a database administrator at Smule and Gazillion games.
