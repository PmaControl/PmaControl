---
title: 'Percona Server 5.7: multi-threaded LRU flushing'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-server-5-7-multi-threaded-lru-flushing/
  post_id: 14823
source_author:
  name: Laurynas Biveinis
  slug: laurynas-biveinis
  url: https://www.percona.com/blog/author/laurynas-biveinis/
  website: ''
published_at: '2016-05-05T13:34:19'
published_at_gmt: '2016-05-05T13:34:19'
modified_at: '2026-03-20T20:57:28'
modified_at_gmt: '2026-03-20T20:57:28'
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
- InnoDB
- multi-threaded LRU flushing
- MySQL
tag_slugs:
- innodb
- multi-threaded-lru-flushing
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/multi-threaded-LRU-flushing.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Server 5.7: multi-threaded LRU flushing

Source: [Percona Blog](https://www.percona.com/blog/percona-server-5-7-multi-threaded-lru-flushing/)

Auteur source: [Laurynas Biveinis](https://www.percona.com/blog/author/laurynas-biveinis/)

Publication: 2016-05-05T13:34:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss how to use multi-threaded LRU flushing to prevent bottlenecks in MySQL. In the previous post, we saw that InnoDB 5.7 performs a lot of single-page LRU flushes, which in turn are serialized by the shared doublewrite buffer. Based on our 5.6 experience we have decided to attack the single-page … Continued

## Images et graphiques reperes

- featured / image: [Percona Server 5.7: multi-threaded LRU flushing](https://www.percona.com/wp-content/uploads/2026/03/multi-threaded-LRU-flushing.png)
- content / image: [multi-threaded LRU flushing](https://www.percona.com/wp-content/uploads/2026/03/512.io_.conc0_.svg)
- content / image: [multi-threaded LRU flushing](https://www.percona.com/wp-content/uploads/2026/03/MySQL-MT-flushing-cropped.png)
- content / image: [multi-threaded LRU flushing](https://www.percona.com/wp-content/uploads/2026/03/Untitled-drawing-12.png)

## Auteur source

Laurynas is a software engineer and Percona Server lead whose primary interest is InnoDB performance. In the past he worked in industry, interned in Google as a compiler software engineer, as well as academia where he researched physical database indexes, including large-scale spatial models of the brain.
