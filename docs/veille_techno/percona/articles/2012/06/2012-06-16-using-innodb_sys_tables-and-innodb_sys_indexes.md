---
title: Using innodb_sys_tables and innodb_sys_indexes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-innodb_sys_tables-and-innodb_sys_indexes/
  post_id: 3635
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2012-06-16T05:28:57'
published_at_gmt: '2012-06-16T05:28:57'
modified_at: '2026-04-28T21:36:10'
modified_at_gmt: '2026-04-28T21:36:10'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using innodb_sys_tables and innodb_sys_indexes

Source: [Percona Blog](https://www.percona.com/blog/using-innodb_sys_tables-and-innodb_sys_indexes/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2012-06-16T05:28:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I was playing with Percona Server today and found fast_index_creation does not work quite exactly like described in the manual. The point I’m looking to make though it would be very hard to catch this problem without additional information_schema tables we added in Percona Server. I was doing simple ALTER TABLE such as: “alter table … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
