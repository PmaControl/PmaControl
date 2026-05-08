---
title: Impact of the sort buffer size in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/impact-of-the-sort-buffer-size-in-mysql/
  post_id: 2463
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2010-10-25T16:08:19'
published_at_gmt: '2010-10-25T16:08:19'
modified_at: '2026-04-28T21:16:59'
modified_at_gmt: '2026-04-28T21:16:59'
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
- Performance
- Tuning
tag_slugs:
- performance
- tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/sort_buffer_2.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Impact of the sort buffer size in MySQL

Source: [Percona Blog](https://www.percona.com/blog/impact-of-the-sort-buffer-size-in-mysql/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2010-10-25T16:08:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The parameter sort_buffer_size is one the MySQL parameters that is far from obvious to adjust. It is a per session buffer that is allocated every time it is needed. The problem with the sort buffer comes from the way Linux allocates memory. Monty Taylor (here) have described the underlying issue in detail, but basically above … Continued

## Images et graphiques reperes

- featured / image: [Impact of the sort buffer size in MySQL](https://www.percona.com/wp-content/uploads/2026/03/sort_buffer_2.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
