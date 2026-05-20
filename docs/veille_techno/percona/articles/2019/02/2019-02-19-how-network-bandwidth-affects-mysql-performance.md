---
title: How Network Bandwidth Affects MySQL Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-network-bandwidth-affects-mysql-performance/
  post_id: 20029
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2019-02-19T11:52:35'
published_at_gmt: '2019-02-19T11:52:35'
modified_at: '2026-05-05T20:43:10'
modified_at_gmt: '2026-05-05T20:43:10'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:pmm
categories:
- Benchmarks
- MySQL
category_slugs:
- benchmarks
- mysql
tags:
- network
- network performance
tag_slugs:
- network
- network-performance
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/10gb-network-and-10gb-with-SSL.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Network Bandwidth Affects MySQL Performance

Source: [Percona Blog](https://www.percona.com/blog/how-network-bandwidth-affects-mysql-performance/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2019-02-19T11:52:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Network is a major part of a database infrastructure. However, often performance benchmarks are done on a local machine, where a client and a server are collocated – I am guilty myself. This is done to simplify the setup and to exclude one more variable (the networking part), but with this we also miss looking … Continued

## Structure detectee

- H2: Setup
- H3: Benchmark N1. Network bandwidth
- H3: Benchmark N2. Protocol compression
- H3: Benchmark N3. Network encryption
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [How Network Bandwidth Affects MySQL Performance](https://www.percona.com/wp-content/uploads/2026/03/10gb-network-and-10gb-with-SSL.png)
- content / image: [network test topology](https://www.percona.com/wp-content/uploads/2026/03/network-test-topology.png)
- content / image: [1gb vs 10gb network](https://www.percona.com/wp-content/uploads/2026/03/1gb-vs-10gb-network.png)
- content / image: [network traffic in PMM](https://www.percona.com/wp-content/uploads/2026/03/network-traffic-in-PMM.png)
- content / image: [1gb network with compression protocol](https://www.percona.com/wp-content/uploads/2026/03/1gb-network-with-compression-protocol.png)
- content / image: [10g network with compression protocol](https://www.percona.com/wp-content/uploads/2026/03/10g-network-with-compression-protocol.png)
- content / image: [1gb network and 1gb with SSL](https://www.percona.com/wp-content/uploads/2026/03/1gb-network-and-1gb-with-SSL.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
