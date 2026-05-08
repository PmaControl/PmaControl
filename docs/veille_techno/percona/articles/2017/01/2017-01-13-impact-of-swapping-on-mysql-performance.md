---
title: The Impact of Swapping on MySQL Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/impact-of-swapping-on-mysql-performance/
  post_id: 16183
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2017-01-13T17:27:11'
published_at_gmt: '2017-01-13T17:27:11'
modified_at: '2026-05-05T18:28:26'
modified_at_gmt: '2026-05-05T18:28:26'
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
- tag:pmm:2167
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Benchmarks
- InnoDB
- MySQL
- Performance
- PMM
- swapping
tag_slugs:
- benchmarks
- innodb
- mysql
- performance
- pmm
- swapping
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Swapping-on-MySQL-Pareto-1-1.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The Impact of Swapping on MySQL Performance

Source: [Percona Blog](https://www.percona.com/blog/impact-of-swapping-on-mysql-performance/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2017-01-13T17:27:11

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I’ll look at the impact of swapping on MySQL performance. It’s common sense that when you’re running MySQL (or really any other DBMS) you don’t want to see any I/O in your swap space. Scaling the cache size (using innodb_buffer_pool_size in MySQL’s case) is standard practice to make sure there is enough … Continued

## Images et graphiques reperes

- featured / image: [The Impact of Swapping on MySQL Performance](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Swapping-on-MySQL-Pareto-1-1.png)
- content / image: [Impact of Swapping on MySQL PMM 1](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Swapping-on-MySQL-PMM-1-1-1024x312.png)
- content / image: [Impact of Swapping on MySQL PMM 2](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Swapping-on-MySQL-PMM-2-1-1024x318.png)
- content / image: [Impact of Swapping on MySQL PMM 3](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Swapping-on-MySQL-PMM-3-1-1024x296.png)
- content / image: [Impact of Swapping on MySQL PMM 4](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Swapping-on-MySQL-PMM-4-1-1024x315.png)
- content / image: [Impact of Swapping on MySQL Pareto 1](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Swapping-on-MySQL-Pareto-1.png)
- content / image: [Impact of Swapping on MySQL Pareto 2](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Swapping-on-MySQL-Pareto-2.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
