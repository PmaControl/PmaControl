---
title: MySQL Partitioning for Performance Optimization
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-partitioning-can-save-you-or-kill-you/
  post_id: 2526
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-12-11T07:25:54'
published_at_gmt: '2010-12-11T07:25:54'
modified_at: '2026-04-28T21:20:54'
modified_at_gmt: '2026-04-28T21:20:54'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- InnoDB
- Performance
- Production
tag_slugs:
- innodb
- performance
- production
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Partitioning for Performance Optimization

Source: [Percona Blog](https://www.percona.com/blog/mysql-partitioning-can-save-you-or-kill-you/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-12-11T07:25:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I wanted for a while to write about using MySQL Partitioning for Performance Optimization and I just got a relevant customer case to illustrate it. First, you need to understand how partitions work internally. Partitions are on the low level are separate table. This means when you’re doing lookup by partitioned key you will look … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
