---
title: 'trx descriptors: MySQL performance improvements in Percona Server 5.5.30-30.2'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/trx-descriptors-mysql-performance-improvements-in-percona-server-5-5-30-30-2/
  post_id: 6826
source_author:
  name: Alexey Kopytov
  slug: alexey-kopytov
  url: https://www.percona.com/blog/author/alexey-kopytov/
  website: ''
published_at: '2013-04-12T17:41:53'
published_at_gmt: '2013-04-12T17:41:53'
modified_at: '2026-03-25T16:52:54'
modified_at_gmt: '2026-03-25T16:52:54'
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
- InnoDB scalability
- MySQL Performance
- Percona Server for MySQL
- trx_list scan
tag_slugs:
- innodb-scalability
- mysql-performance
- percona-server
- trx_list-scan
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Server-1.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# trx descriptors: MySQL performance improvements in Percona Server 5.5.30-30.2

Source: [Percona Blog](https://www.percona.com/blog/trx-descriptors-mysql-performance-improvements-in-percona-server-5-5-30-30-2/)

Auteur source: [Alexey Kopytov](https://www.percona.com/blog/author/alexey-kopytov/)

Publication: 2013-04-12T17:41:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One major problem in terms of MySQL performance that still stands in the way of InnoDB scalability is the trx_list scan on consistent read view creation. It was originally reported as a part of MySQL bug #49169 and can be described as follows. Whenever a connection wants to create a consistent read, it has to … Continued

## Images et graphiques reperes

- featured / image: [trx descriptors: MySQL performance improvements in Percona Server 5.5.30-30.2](https://www.percona.com/wp-content/uploads/2026/03/Percona-Server-1.jpg)
- content / image: [point_select_qps_1024](https://www.percona.com/wp-content/uploads/2026/03/point_select_qps_10241.png)
- content / image: [point_select_trx](https://www.percona.com/wp-content/uploads/2026/03/point_select_trx1.png)

## Auteur source

Alexey Kopytov is a Principal Software Engineer at Percona. Before joining Percona in 2010 he was a member of the MySQL development team at Oracle. His focus at Percona is development of both Percona Server and Percona XtraBackup.
