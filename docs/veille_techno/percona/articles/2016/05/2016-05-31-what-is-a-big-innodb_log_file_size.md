---
title: What is a big innodb_log_file_size?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/what-is-a-big-innodb_log_file_size/
  post_id: 15181
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2016-05-31T15:45:22'
published_at_gmt: '2016-05-31T15:45:22'
modified_at: '2026-05-05T23:43:23'
modified_at_gmt: '2026-05-05T23:43:23'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- MySQL
category_slugs:
- mysql
tags:
- InnoDB
- innodb_log_file_size
- MySQL
tag_slugs:
- innodb
- innodb_log_file_size
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/big-innodb_log_file_size.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# What is a big innodb_log_file_size?

Source: [Percona Blog](https://www.percona.com/blog/what-is-a-big-innodb_log_file_size/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2016-05-31T15:45:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, we’ll discuss what constitutes a big innodb_log_file_size , and how it can affect performance. In the comments for our post on Percona Server 5.7 performance improvements, someone asked why we use innodb_log_file_size = 10G with an indication that it might be too big? In my previous post, the example used innodb_log_file_size = 15G . Is that too big? … Continued

## Structure detectee

- H2: Looking at innodb_log_file_size

## Images et graphiques reperes

- featured / image: [What is a big innodb_log_file_size?](https://www.percona.com/wp-content/uploads/2026/03/big-innodb_log_file_size.png)
- content / image: [checkpoint.png](https://www.percona.com/wp-content/uploads/2026/03/checkpoint.png)
- content / image: [flushing.png](https://www.percona.com/wp-content/uploads/2026/03/flushing.png)
- content / image: [bufferpool-1.png](https://www.percona.com/wp-content/uploads/2026/03/bufferpool-1.png)
- content / image: [transactions-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/transactions-scaled.png)
- content / image: [cpu_recovery](https://www.percona.com/wp-content/uploads/2026/03/cpu_recovery.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
