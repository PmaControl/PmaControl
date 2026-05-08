---
title: Using MySQL OPTIMIZE tables for InnoDB? Stop!
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-optimize-tables-innodb-stop/
  post_id: 2520
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-12-09T19:06:16'
published_at_gmt: '2010-12-09T19:06:16'
modified_at: '2026-05-05T17:37:49'
modified_at_gmt: '2026-05-05T17:37:49'
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
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- InnoDB
- MySQL optimize table
- Production
- XtraDB
tag_slugs:
- innodb
- mysql-optimize-table
- production
- xtradb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-OPTIMIZE-tables.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using MySQL OPTIMIZE tables for InnoDB? Stop!

Source: [Percona Blog](https://www.percona.com/blog/mysql-optimize-tables-innodb-stop/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-12-09T19:06:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Innodb/XtraDB tables do benefit from being reorganized often. You can get data physically laid out in primary key order as well as get a better feel for the primary key and index pages, and so use less space, it’s just that MySQL OPTIMIZE TABLE might not be the best way to do it. Why you … Continued

## Structure detectee

- H2: Why you shouldn’t necessarily optimize tables with MySQL OPTIMIZE
- H3: More resources on performance:
- H4: Posts
- H4: Webinars
- H4: Presentations
- H4: Free eBooks
- H4: Tools

## Images et graphiques reperes

- featured / image: [Using MySQL OPTIMIZE tables for InnoDB? Stop!](https://www.percona.com/wp-content/uploads/2026/03/MySQL-OPTIMIZE-tables.jpg)
- content / image: [MySQL OPTIMIZE tables](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_304642883-300x200.jpg)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
