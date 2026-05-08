---
title: 'MySQL performance: Impact of memory allocators (Part 2)'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-performance-impact-of-memory-allocators-part-2/
  post_id: 6674
source_author:
  name: Alexey Stroganov
  slug: alexey-stroganov
  url: https://www.percona.com/blog/author/alexey-stroganov/
  website: http://www.percona.com
published_at: '2013-03-08T15:46:58'
published_at_gmt: '2013-03-08T15:46:58'
modified_at: '2026-04-28T21:51:00'
modified_at_gmt: '2026-04-28T21:51:00'
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
- Alexey Stroganov
- glibc malloc
- memory allocators
- MySQL
tag_slugs:
- alexey-stroganov
- glibc-malloc
- memory-allocators
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/malloc_test2_oltp_ro1.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL performance: Impact of memory allocators (Part 2)

Source: [Percona Blog](https://www.percona.com/blog/mysql-performance-impact-of-memory-allocators-part-2/)

Auteur source: [Alexey Stroganov](https://www.percona.com/blog/author/alexey-stroganov/)

Publication: 2013-03-08T15:46:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Last time I wrote about memory allocators and how they can affect MySQL performance in general. This time I would like to explore this topic from a bit different angle: What impact does the number of processor cores have on different memory allocators and what difference we will see in MySQL performance in this scenario? … Continued

## Images et graphiques reperes

- featured / image: [MySQL performance: Impact of memory allocators (Part 2)](https://www.percona.com/wp-content/uploads/2026/03/malloc_test2_oltp_ro1.png)
- content / image: [malloc_test2_point_select](https://www.percona.com/wp-content/uploads/2026/03/malloc_test2_point_select.png)

## Auteur source

Alexey Stroganov is a Performance Engineer at Percona, where he works on improvements and features that makes Percona Server even more flexible, faster and scalable. Before joining Percona he worked on the performance testings/analysis of MySQL server and it components at MySQL AB/Sun/Oracle for more than ten years. During this time he was focused on performance evaluations, benchmarks, analysis, profiling, various optimizations and tunings.
