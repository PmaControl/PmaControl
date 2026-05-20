---
title: MySQL Memory Management, Memory Allocators and Operating System
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-memory-management-memory-allocators-and-operating-system/
  post_id: 20278
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2019-05-02T13:37:10'
published_at_gmt: '2019-05-02T13:37:10'
modified_at: '2026-03-20T22:17:38'
modified_at_gmt: '2026-03-20T22:17:38'
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
- Percona Services
- Percona Software
category_slugs:
- mysql
- percona-services
- percona-software
tags:
- jemalloc
- jemalloc memory allocator
- memory
- memory allocators
- MySQL
tag_slugs:
- jemalloc
- jemalloc-memory-allocator
- memory
- memory-allocators
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/memory-management-mysql-bug.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Memory Management, Memory Allocators and Operating System

Source: [Percona Blog](https://www.percona.com/blog/mysql-memory-management-memory-allocators-and-operating-system/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2019-05-02T13:37:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When users experience memory usage issues with any software, including MySQL®, their first response is to think that it’s a symptom of a memory leak. As this story will show, this is not always the case. This story is about a bug. All Percona Support customers are eligible for bug fixes, but their options vary. … Continued

## Structure detectee

- H2: A bug as a case study
- H3: Options to fix
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL Memory Management, Memory Allocators and Operating System](https://www.percona.com/wp-content/uploads/2026/03/memory-management-mysql-bug.jpg)
- content / image: [memory management mysql bug](https://www.percona.com/wp-content/uploads/2026/03/memory-management-mysql-bug-300x200.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
