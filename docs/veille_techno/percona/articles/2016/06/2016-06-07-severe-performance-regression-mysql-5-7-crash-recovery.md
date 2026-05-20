---
title: Severe performance regression in MySQL 5.7 crash recovery
source:
  name: Percona Blog
  url: https://www.percona.com/blog/severe-performance-regression-mysql-5-7-crash-recovery/
  post_id: 15302
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2016-06-07T13:01:20'
published_at_gmt: '2016-06-07T13:01:20'
modified_at: '2016-06-07T13:01:20'
modified_at_gmt: '2016-06-07T13:01:20'
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
- crash recovery
- MySQL 5.7
tag_slugs:
- crash-recovery
- mysql-5-7
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-5.7-Crash-Recovery.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Severe performance regression in MySQL 5.7 crash recovery

Source: [Percona Blog](https://www.percona.com/blog/severe-performance-regression-mysql-5-7-crash-recovery/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2016-06-07T13:01:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, we’ll discuss some insight I’ve gained regarding severe performance regression in MySQL 5.7 crash recovery. Working on different InnoDB log file sizes in my previous post: https://www.percona.com/blog/2016/05/31/what-is-a-big-innodb_log_file_size/ I tried to understand how we can make InnoDB crash recovery faster, but found a rather surprising 5.7 crash recovery regression. Basically, crash recovery in MySQL … Continued

## Images et graphiques reperes

- featured / image: [Severe performance regression in MySQL 5.7 crash recovery](https://www.percona.com/wp-content/uploads/2026/03/MySQL-5.7-Crash-Recovery.jpg)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
