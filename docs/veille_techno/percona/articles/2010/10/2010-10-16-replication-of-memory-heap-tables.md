---
title: Replication of MEMORY (HEAP) Tables
source:
  name: Percona Blog
  url: https://www.percona.com/blog/replication-of-memory-heap-tables/
  post_id: 2472
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-10-16T05:36:51'
published_at_gmt: '2010-10-16T05:36:51'
modified_at: '2026-03-23T21:46:03'
modified_at_gmt: '2026-03-23T21:46:03'
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
- Production
- Replication
tag_slugs:
- production
- replication
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Replication of MEMORY (HEAP) Tables

Source: [Percona Blog](https://www.percona.com/blog/replication-of-memory-heap-tables/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-10-16T05:36:51

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Some Applications need to store some transient data which is frequently regenerated and MEMORY table look like a very good match for this sort of tasks. Unfortunately this will bite when you will be looking to add Replication to your environment as MEMORY tables do not play well with replication.

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
