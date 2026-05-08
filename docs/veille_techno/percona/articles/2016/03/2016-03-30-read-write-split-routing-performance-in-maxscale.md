---
title: Read-write split routing in MaxScale
source:
  name: Percona Blog
  url: https://www.percona.com/blog/read-write-split-routing-performance-in-maxscale/
  post_id: 14689
source_author:
  name: Frederic Descamps
  slug: lefred
  url: https://www.percona.com/blog/author/lefred/
  website: http://www.lefred.be
published_at: '2016-03-30T00:42:50'
published_at_gmt: '2016-03-30T00:42:50'
modified_at: '2026-05-05T19:32:17'
modified_at_gmt: '2026-05-05T19:32:17'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MaxScale
- MySQL
matched_filters:
- category:mysql:83
- search:maxscale
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- read-write split routing performance in MaxScale
tag_slugs:
- read-write-split-routing-performance-in-maxscale
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Read-write-split-routing-performance-in-MaxScale.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Read-write split routing in MaxScale

Source: [Percona Blog](https://www.percona.com/blog/read-write-split-routing-performance-in-maxscale/)

Auteur source: [Frederic Descamps](https://www.percona.com/blog/author/lefred/)

Publication: 2016-03-30T00:42:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss read-write split routing in MaxScale. The two previous posts have shown how to setup high availability (HA) with Maxscale using asynchronous replication and how we monitor replication. Now let’s focus on the routing module performing read-write splits. This is our current configuration: [Splitter Service] type=service router=readwritesplit servers=percona1, percona2 max_slave_replication_lag=30 user=maxscale passwd=264D375EC77998F13F4D0EC739AABAD4 1 2 3 4 5 6 7 [ Splitter Service ] type = service router = readwritesplit servers = percona1 , percona2 max_slave_replication_lag = 30 user = maxscale passwd = 264D375EC77998F13F4D0EC739AABAD4 This router module is designed to … Continued

## Images et graphiques reperes

- featured / image: [Read-write split routing in MaxScale](https://www.percona.com/wp-content/uploads/2026/03/Read-write-split-routing-performance-in-MaxScale.jpg)
- content / image: [Read-write split routing in MaxScale](https://www.percona.com/wp-content/uploads/2026/03/Read-write-split-routing-performance-in-MaxScale-150x150.jpg)

## Auteur source

Frédéric joined Percona in June 2011, he is an experienced Open Source consultant with expertise in infrastructure projects as well in development tracks and database administration. Frédéric is a believer of devops culture.
