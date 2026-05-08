---
title: MySQL performance implications of InnoDB isolation modes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-performance-implications-of-innodb-isolation-modes/
  post_id: 8965
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2015-01-14T11:00:08'
published_at_gmt: '2015-01-14T11:00:08'
modified_at: '2026-04-28T22:16:16'
modified_at_gmt: '2026-04-28T22:16:16'
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
- InnoDB isolation modes
- Multiversion concurrency control
- MVCC
- mydumper
- MySQL
- Peter Zaitsev
- Primary
- READ COMMITTED
- READ UNCOMMITTED
- REPEATABLE READ
- SERIALIZABLE
tag_slugs:
- innodb
- innodb-isolation-modes
- multiversion-concurrency-control
- mvcc
- mydumper
- mysql
- peter-zaitsev
- primary
- read-committed
- read-uncommitted
- repeatable-read
- serializable
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/img_54b574382faf4.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL performance implications of InnoDB isolation modes

Source: [Percona Blog](https://www.percona.com/blog/mysql-performance-implications-of-innodb-isolation-modes/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2015-01-14T11:00:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Over the past few months I’ve written a couple of posts about dangerous debt of InnoDB Transactional History and about the fact MVCC can be the cause of severe MySQL performance issues. In this post I will cover a related topic – InnoDB Transaction Isolation Modes, their relationship with MVCC (multi-version concurrency control) and how … Continued

## Images et graphiques reperes

- featured / image: [MySQL performance implications of InnoDB isolation modes](https://www.percona.com/wp-content/uploads/2026/03/img_54b574382faf4.png)
- content / image: [img_54b574e317ef3.png](https://www.percona.com/wp-content/uploads/2026/03/img_54b574e317ef3.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
