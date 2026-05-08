---
title: Looking deeper into InnoDB’s problem with many row versions
source:
  name: Percona Blog
  url: https://www.percona.com/blog/looking-deeper-innodbs-problem-many-row-versions/
  post_id: 8997
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2015-01-19T15:06:09'
published_at_gmt: '2015-01-19T15:06:09'
modified_at: '2026-05-04T21:00:24'
modified_at_gmt: '2026-05-04T21:00:24'
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
- Percona Software
category_slugs:
- benchmarks
- mysql
- percona-software
tags:
- benchmark
- InnoDB
- isolation mode
- MySQL Performance
- oprofile
- Percona Server for MySQL
- Peter Zaitsev
- Primary
- REPEATABLE READ
- sys schema
- sysbench
- Ubuntu
tag_slugs:
- benchmark
- innodb
- isolation-mode
- mysql-performance
- oprofile
- percona-server
- peter-zaitsev
- primary
- repeatable-read
- sys-schema
- sysbench
- ubuntu
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/img_54bb107dd74c8.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Looking deeper into InnoDB’s problem with many row versions

Source: [Percona Blog](https://www.percona.com/blog/looking-deeper-innodbs-problem-many-row-versions/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2015-01-19T15:06:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A few days ago I wrote about MySQL performance implications of InnoDB isolation modes and I touched briefly upon the bizarre performance regression I found with InnoDB handling a large amount of versions for a single row. Today I wanted to look a bit deeper into the problem, which I also filed as a bug. … Continued

## Images et graphiques reperes

- featured / image: [Looking deeper into InnoDB’s problem with many row versions](https://www.percona.com/wp-content/uploads/2026/03/img_54bb107dd74c8.png)
- content / image: [img_54bb10b7e2a7a.png](https://www.percona.com/wp-content/uploads/2026/03/img_54bb10b7e2a7a.png)
- content / image: [img_54bb10d04f9d2.png](https://www.percona.com/wp-content/uploads/2026/03/img_54bb10d04f9d2.png)
- content / image: [img_54bb116e4d870.png](https://www.percona.com/wp-content/uploads/2026/03/img_54bb116e4d870.png)
- content / image: [img_54bb125d2953c.png](https://www.percona.com/wp-content/uploads/2026/03/img_54bb125d2953c.png)
- content / image: [img_54bb12ed05958.png](https://www.percona.com/wp-content/uploads/2026/03/img_54bb12ed05958.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
