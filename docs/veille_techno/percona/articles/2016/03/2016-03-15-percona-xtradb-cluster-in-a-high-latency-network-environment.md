---
title: Percona XtraDB Cluster in a high latency network environment
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-in-a-high-latency-network-environment/
  post_id: 14768
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2016-03-15T02:01:38'
published_at_gmt: '2016-03-15T02:01:38'
modified_at: '2026-05-05T18:01:55'
modified_at_gmt: '2026-05-05T18:01:55'
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
tags:
- high latency network environment
tag_slugs:
- high-latency-network-environment
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/high-latency-network-enviroment.jpg
image_count: 6
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster in a high latency network environment

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-in-a-high-latency-network-environment/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2016-03-15T02:01:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we’ll discuss how Percona XtraDB Cluster handled a high latency network environment. Recently I was working in an environment where Percona XtraDB Cluster was running over a 10GB network, but one of the nodes was located in a distant location and the ping time was higher than what you would typically expect. … Continued

## Images et graphiques reperes

- featured / graph_or_chart: [Percona XtraDB Cluster in a high latency network environment](https://www.percona.com/wp-content/uploads/2026/03/high-latency-network-enviroment.jpg)
- content / graph_or_chart: [high latency network enviroment](https://www.percona.com/wp-content/uploads/2026/03/high-latency-network-enviroment-300x225.jpg)
- content / image: [initial-thrp](https://www.percona.com/wp-content/uploads/2026/03/initial-thrp-1024x683.png)
- content / image: [initial-resp](https://www.percona.com/wp-content/uploads/2026/03/initial-resp-1024x683.png)
- content / image: [thrp-optim](https://www.percona.com/wp-content/uploads/2026/03/thrp-optim-1024x683.png)
- content / image: [resp-optimized](https://www.percona.com/wp-content/uploads/2026/03/resp-optimized-1024x683.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
