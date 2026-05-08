---
title: When it’s faster to use SQL in MySQL NDB Cluster over memcache API
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-its-faster-to-use-sql-in-mysql-cluster-over-memcache-api/
  post_id: 7101
source_author:
  name: Peter Boros
  slug: peter-boros
  url: https://www.percona.com/blog/author/peter-boros/
  website: ''
published_at: '2013-07-03T10:00:29'
published_at_gmt: '2013-07-03T10:00:29'
modified_at: '2026-05-04T20:54:11'
modified_at_gmt: '2026-05-04T20:54:11'
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
- benchmark
- memcache
- memcache API
- NDBCluster
- ndbmemcache schema
- Peter Boros
tag_slugs:
- benchmark
- memcache
- memcache-api
- ndbcluster
- ndbmemcache-schema
- peter-boros
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ndb_memcache_benchmark.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# When it’s faster to use SQL in MySQL NDB Cluster over memcache API

Source: [Percona Blog](https://www.percona.com/blog/when-its-faster-to-use-sql-in-mysql-cluster-over-memcache-api/)

Auteur source: [Peter Boros](https://www.percona.com/blog/author/peter-boros/)

Publication: 2013-07-03T10:00:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Memcache access for MySQL Cluster (or NDBCluster) provides faster access to the data because it avoids the SQL parsing overhead for simple lookups – which is a great feature. But what happens if I try to get multiple records via memcache API (multi-GET) and via SQL (SELECT with IN())? I’ve encountered this a few times … Continued

## Images et graphiques reperes

- featured / image: [When it’s faster to use SQL in MySQL NDB Cluster over memcache API](https://www.percona.com/wp-content/uploads/2026/03/ndb_memcache_benchmark.png)

## Auteur source

Peter is a Principal Architect at Percona's European consulting team, his special interests are performance tuning and automation for large scale systems. Before Percona, he worked at Zuora, Dropbox, and Sun microsystems, also taught MySQL courses for Oracle University. He currently lives in Debrecen, Hungary with his wife and kids.
