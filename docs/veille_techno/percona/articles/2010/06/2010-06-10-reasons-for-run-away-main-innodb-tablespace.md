---
title: Reasons for run-away main Innodb Tablespace
source:
  name: Percona Blog
  url: https://www.percona.com/blog/reasons-for-run-away-main-innodb-tablespace/
  post_id: 2359
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-06-10T19:27:49'
published_at_gmt: '2010-06-10T19:27:49'
modified_at: '2026-04-28T21:14:19'
modified_at_gmt: '2026-04-28T21:14:19'
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
tag_slugs:
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Reasons for run-away main Innodb Tablespace

Source: [Percona Blog](https://www.percona.com/blog/reasons-for-run-away-main-innodb-tablespace/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-06-10T19:27:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

So you’re running MySQL With innodb_file_per_table option but your ibdata1 file which holds main (or system) tablespace have grown dramatically from its starting 10MB size. What could be the reason of this growth and what you can do about it ? There are few things which are always stored in main tablespace – these are … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
