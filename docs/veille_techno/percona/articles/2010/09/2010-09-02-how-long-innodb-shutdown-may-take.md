---
title: How long Innodb Shutdown may take
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-long-innodb-shutdown-may-take/
  post_id: 2435
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2010-09-02T21:40:44'
published_at_gmt: '2010-09-02T21:40:44'
modified_at: '2026-03-23T21:44:41'
modified_at_gmt: '2026-03-23T21:44:41'
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

# How long Innodb Shutdown may take

Source: [Percona Blog](https://www.percona.com/blog/how-long-innodb-shutdown-may-take/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2010-09-02T21:40:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

How long it may take MySQL with Innodb tables to shut down ? It can be quite a while. In default configuration innodb_fast_shutdown=ON the main job Innodb has to do to complete shutdown is flushing dirty buffers. The number of dirty buffers in the buffer pool varies depending on innodb_max_dirty_pages_pct as well as workload and … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
