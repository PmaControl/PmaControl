---
title: Thread_Statistics and High Memory Usage
source:
  name: Percona Blog
  url: https://www.percona.com/blog/thread_statistics-and-high-memory-usage/
  post_id: 17081
source_author:
  name: Alex Poritskiy
  slug: alex-poritskiy
  url: https://www.percona.com/blog/author/alex-poritskiy/
  website: ''
published_at: '2017-07-11T20:15:15'
published_at_gmt: '2017-07-11T20:15:15'
modified_at: '2026-05-05T18:43:50'
modified_at_gmt: '2026-05-05T18:43:50'
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
- Open Source
- Percona Software
category_slugs:
- mysql
- open-source
- percona-software
tags:
- errors
- memory
- MySQL
- Percona Server for MySQL
- thread_statistics
tag_slugs:
- errors
- memory
- mysql
- percona-server
- thread_statistics
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/thread_statistics.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Thread_Statistics and High Memory Usage

Source: [Percona Blog](https://www.percona.com/blog/thread_statistics-and-high-memory-usage/)

Auteur source: [Alex Poritskiy](https://www.percona.com/blog/author/alex-poritskiy/)

Publication: 2017-07-11T20:15:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how using thread_statistics can cause high memory usage. I was recently working on a high memory usage issue for one of our clients, and made some interesting discoveries: high memory usage with no bounds. It was really tricky to diagnose. Below, I am going to show you how … Continued

## Structure detectee

- H4: Part 1: Issue Background
- H4: Part 2: Team Is on Rescue
- H4: Part 3: Cause Verification – Did It Really Eat Our Memory?
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Thread_Statistics and High Memory Usage](https://www.percona.com/wp-content/uploads/2026/03/thread_statistics.jpg)
