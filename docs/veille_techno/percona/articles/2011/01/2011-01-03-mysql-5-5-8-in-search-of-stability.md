---
title: MySQL 5.5.8 – in search of stability
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-5-5-8-in-search-of-stability/
  post_id: 2568
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2011-01-03T20:55:28'
published_at_gmt: '2011-01-03T20:55:28'
modified_at: '2026-04-28T21:21:42'
modified_at_gmt: '2026-04-28T21:21:42'
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
- FusionIO
- Percona Server for MySQL
tag_slugs:
- fusionio
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/throughput_1.png
image_count: 24
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 5.5.8 – in search of stability

Source: [Percona Blog](https://www.percona.com/blog/mysql-5-5-8-in-search-of-stability/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2011-01-03T20:55:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A couple of days ago, Dimitri published a blog post, Analyzing Percona’s TPCC-like Workload on MySQL 5.5, which wasÂ a response to my post, MySQL 5.5.8 and Percona Server: being adaptive. I will refer to Dimitri’s article as article [1]. As always, Dimitri has provided aÂ very detailed andÂ thoughtful article, and I strongly recommend … Continued

## Images et graphiques reperes

- featured / image: [MySQL 5.5.8 – in search of stability](https://www.percona.com/wp-content/uploads/2026/03/throughput_1.png)
- content / image: [chkpt_1.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_1.png)
- content / image: [throughput_2.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_2.png)
- content / image: [chkpt_2.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_2.png)
- content / image: [throughput_3.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_3.png)
- content / image: [chkpt_31.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_31.png)
- content / image: [throughput_4.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_4.png)
- content / image: [chkpt_41.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_41.png)
- content / image: [throughput_n13.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_n13.png)
- content / image: [chkpt_n13.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_n13.png)
- content / image: [throughput_5.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_5.png)
- content / image: [chkpt_51.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_51.png)
- content / image: [throughput_n36.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_n36.png)
- content / image: [chkpt_n361.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_n361.png)
- content / image: [throughput_n37.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_n37.png)
- content / image: [chkpt_n37.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_n37.png)
- content / image: [throughput_6.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_6.png)
- content / image: [chkpt_6.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_6.png)
- content / image: [throughput_7.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_7.png)
- content / image: [chkpt_7.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_7.png)
- content / image: [throughput_8.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_8.png)
- content / image: [chkpt_8.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_8.png)
- content / image: [throughput_9.png](https://www.percona.com/wp-content/uploads/2026/03/throughput_9.png)
- content / image: [chkpt_9.png](https://www.percona.com/wp-content/uploads/2026/03/chkpt_9.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
