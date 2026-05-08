---
title: How much memory Innodb Dictionary can take ?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-much-memory-innodb-dictionary-can-take/
  post_id: 2310
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-05-06T18:20:40'
published_at_gmt: '2010-05-06T18:20:40'
modified_at: '2026-04-28T21:11:54'
modified_at_gmt: '2026-04-28T21:11:54'
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

# How much memory Innodb Dictionary can take ?

Source: [Percona Blog](https://www.percona.com/blog/how-much-memory-innodb-dictionary-can-take/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-05-06T18:20:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The amount of memory Innodb will require for its data dictionary depends on amount of tables you have as well as number of fields and indexes. Innodb allocates this memory once table is accessed and keeps until server is shut down. In XtraDB we have an option to restrict that limit. So how much memory … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
