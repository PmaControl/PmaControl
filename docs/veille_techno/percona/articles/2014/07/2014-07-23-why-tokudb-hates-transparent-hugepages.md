---
title: Why TokuDB hates Transparent HugePages
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-tokudb-hates-transparent-hugepages/
  post_id: 8394
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-07-23T10:00:45'
published_at_gmt: '2014-07-23T10:00:45'
modified_at: '2026-03-25T17:37:56'
modified_at_gmt: '2026-03-25T17:37:56'
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
- jemalloc memory allocator
- TokuDB
- Transparent HugePages
tag_slugs:
- jemalloc-memory-allocator
- tokudb
- transparent-hugepages
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why TokuDB hates Transparent HugePages

Source: [Percona Blog](https://www.percona.com/blog/why-tokudb-hates-transparent-hugepages/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-07-23T10:00:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you try to install the TokuDB storage engine on a modern Linux distribution it might fail with following error message: 2014-07-17 19:02:55 13865 [ERROR] TokuDB will not run with transparent huge pages enabled.2014-07-17 19:02:55 13865 [ERROR] Please disable them to continue.2014-07-17 19:02:55 13865 [ERROR] (echo never > /sys/kernel/mm/transparent_hugepage/enabled) You might be curious why TokuDB … Continued

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
