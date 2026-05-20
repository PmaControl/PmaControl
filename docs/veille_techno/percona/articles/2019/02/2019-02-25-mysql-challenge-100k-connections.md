---
title: 'MySQL Challenge: 100k Connections'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-challenge-100k-connections/
  post_id: 20051
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2019-02-25T15:00:28'
published_at_gmt: '2019-02-25T15:00:28'
modified_at: '2026-05-05T19:27:45'
modified_at_gmt: '2026-05-05T19:27:45'
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
- MySQL
category_slugs:
- benchmarks
- mysql
tags:
- connection pool
- connection pooling
- Connections
- Thread pool
tag_slugs:
- connection-pool
- connection-pooling
- connections
- thread-pool
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/thread-pools-MySQL-100k-connections.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Challenge: 100k Connections

Source: [Percona Blog](https://www.percona.com/blog/mysql-challenge-100k-connections/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2019-02-25T15:00:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, I want to explore a way to establish 100,000 connections to MySQL. Not just idle connections, but executing queries. 100,000 connections. Is that really needed for MySQL, you may ask? Although it may seem excessive, I have seen a lot of different setups in customer deployments. Some deploy an application connection pool, … Continued

## Structure detectee

- H2: Setup
- H3: Initial server setup
- H2: Step 1. 10,000 connections
- H2: Step 2. 25,000 connections
- H2: Step 3. 50,000 connections
- H4: Sysbench 1.0.x limitation
- H2: Step 3. 75,000 connections
- H2: Step 4. 100,000 connections
- H2: Conclusions
- H2: Appendix: full my.cnf

## Images et graphiques reperes

- featured / image: [MySQL Challenge: 100k Connections](https://www.percona.com/wp-content/uploads/2026/03/thread-pools-MySQL-100k-connections.jpg)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
