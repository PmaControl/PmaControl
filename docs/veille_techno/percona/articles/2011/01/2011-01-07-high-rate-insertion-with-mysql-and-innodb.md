---
title: High Rate insertion with MySQL and Innodb
source:
  name: Percona Blog
  url: https://www.percona.com/blog/high-rate-insertion-with-mysql-and-innodb/
  post_id: 2631
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2011-01-07T20:58:18'
published_at_gmt: '2011-01-07T20:58:18'
modified_at: '2026-03-23T21:49:38'
modified_at_gmt: '2026-03-23T21:49:38'
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
tag_slugs:
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# High Rate insertion with MySQL and Innodb

Source: [Percona Blog](https://www.percona.com/blog/high-rate-insertion-with-mysql-and-innodb/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2011-01-07T20:58:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I again work with the system which needs high insertion rate for data which generally fits in memory. Last time I worked with similar system it used MyISAM and the system was built using multiple tables. Using multiple key caches was the good solution at that time and we could get over 200K of inserts/sec. … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
