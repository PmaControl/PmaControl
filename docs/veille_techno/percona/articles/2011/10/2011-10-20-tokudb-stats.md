---
title: TokuDB Stats
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tokudb-stats/
  post_id: 9609
source_author:
  name: Tim.Callaghan
  slug: tim-callaghan
  url: https://www.percona.com/blog/author/tim-callaghan/
  website: ''
published_at: '2011-10-20T17:27:48'
published_at_gmt: '2011-10-20T17:27:48'
modified_at: '2026-04-28T22:39:54'
modified_at_gmt: '2026-04-28T22:39:54'
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
- InnoDB
- MySQL
- Storage Engine
- TokuDB
- Tokutek
tag_slugs:
- innodb
- mysql
- storage-engine
- tokudb
- tokutek
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuDB Stats

Source: [Percona Blog](https://www.percona.com/blog/tokudb-stats/)

Auteur source: [Tim.Callaghan](https://www.percona.com/blog/author/tim-callaghan/)

Publication: 2011-10-20T17:27:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I’ve been benchmarking and testing TokuDB for a few months now. One goal of benchmarking is to understand what is limiting the performance of a particular configuration. I frequently use “show engine [innodb/tokudb] status;” from within the MySQL command line client as part of my research. As I run most of my benchmarks on InnoDB … Continued
