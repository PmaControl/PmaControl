---
title: How to Fully Disable Query Cache in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/is-your-query-cache-really-disabled/
  post_id: 15672
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2016-11-11T18:46:16'
published_at_gmt: '2016-11-11T18:46:16'
modified_at: '2026-05-05T18:18:32'
modified_at_gmt: '2026-05-05T18:18:32'
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
- MySQL
- query cache
tag_slugs:
- mysql
- query-cache
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Query-Cache.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Fully Disable Query Cache in MySQL

Source: [Percona Blog](https://www.percona.com/blog/is-your-query-cache-really-disabled/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2016-11-11T18:46:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post was motivated by an internal discussion about how to fully disable query cache in MySQL. According to the manual, we should be able to disable “Query Cache” on the fly by changing query_cache_type to 0, but as we will show this is not fully true. This blog will show you how to … Continued

## Structure detectee

- H2: Some Query Cache context
- H2: Disabling Query Cache
- H2: Digging more code

## Images et graphiques reperes

- featured / image: [How to Fully Disable Query Cache in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Query-Cache.png)
- content / image: [Disable the Query Cache](https://www.percona.com/wp-content/uploads/2026/03/qc_mutex.png)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
