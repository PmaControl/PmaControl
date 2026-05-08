---
title: ProxySQL versus MaxScale for OLTP RO workloads
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proxysql-versus-maxscale-for-oltp-ro-workloads/
  post_id: 15088
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2016-05-12T17:52:47'
published_at_gmt: '2016-05-12T17:52:47'
modified_at: '2026-03-20T21:01:25'
modified_at_gmt: '2026-03-20T21:01:25'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MaxScale
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:maxscale
- search:proxysql
- tag:maxscale:1758
categories:
- MySQL
category_slugs:
- mysql
tags:
- MaxScale
- OLTP RO workloads
- ProxySQL
- ProxySQL versus MaxScale for OLTP RO workloads
tag_slugs:
- maxscale
- oltp-ro-workloads
- proxysql
- proxysql-versus-maxscale-for-oltp-ro-workloads
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-versus-MaxScale-for-OLTP-RO-workloads.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ProxySQL versus MaxScale for OLTP RO workloads

Source: [Percona Blog](https://www.percona.com/blog/proxysql-versus-maxscale-for-oltp-ro-workloads/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2016-05-12T17:52:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss ProxySQL versus MaxScale for OLTP RO workloads. Continuing my series of READ-ONLY benchmarks (you can find the other posts here and here), in this post I want to see how much overhead a proxy adds. At this In my opinion, there are only two solid proxy software options for … Continued

## Images et graphiques reperes

- featured / image: [ProxySQL versus MaxScale for OLTP RO workloads](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-versus-MaxScale-for-OLTP-RO-workloads.png)
- content / image: [proxysql-1.png](https://www.percona.com/wp-content/uploads/2026/03/proxysql-1.png)
- content / image: [proxysql-ff-1.png](https://www.percona.com/wp-content/uploads/2026/03/proxysql-ff-1.png)
- content / image: [proxysql-maxscale-1.png](https://www.percona.com/wp-content/uploads/2026/03/proxysql-maxscale-1.png)
- content / image: [proxysql-maxscale-16thr-1.png](https://www.percona.com/wp-content/uploads/2026/03/proxysql-maxscale-16thr-1.png)
- content / image: [schema-relative-2-1.png](https://www.percona.com/wp-content/uploads/2026/03/schema-relative-2-1.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
