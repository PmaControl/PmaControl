---
title: 'MySQL’s INNODB_METRICS table: How much is the overhead?'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysqls-innodb_metrics-table-how-much-is-the-overhead/
  post_id: 8770
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-11-18T13:00:12'
published_at_gmt: '2014-11-18T13:00:12'
modified_at: '2026-05-04T22:29:47'
modified_at_gmt: '2026-05-04T22:29:47'
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
- InnoDB
- INNODB_METRICS
- MySQL Performance
- Peter Zaitsev
- Primary
tag_slugs:
- innodb
- innodb_metrics
- mysql-performance
- peter-zaitsev
- primary
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/img_54692c0556d11.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL’s INNODB_METRICS table: How much is the overhead?

Source: [Percona Blog](https://www.percona.com/blog/mysqls-innodb_metrics-table-how-much-is-the-overhead/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-11-18T13:00:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Starting with MySQL 5.6 there is an INNODB_METRICS table available in INFORMATION_SCHEMA which contains some additional information than provided in the SHOW GLOBAL STATUS output – yet might be more lightweight than PERFORMANCE_SCHEMA. Too bad INNODB_METRICS was designed during the Oracle-Sun split under MySQL leadership and so it covers only InnoDB counters. I think this … Continued

## Images et graphiques reperes

- featured / image: [MySQL’s INNODB_METRICS table: How much is the overhead?](https://www.percona.com/wp-content/uploads/2026/03/img_54692c0556d11.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
