---
title: Concurrency Improvements in TokuDB v6.6 (Part 2)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/concurrency-improvements-in-tokudb-v6-6-part-2/
  post_id: 9758
source_author:
  name: Leif.Walsh
  slug: leif-walsh
  url: https://www.percona.com/blog/author/leif-walsh/
  website: ''
published_at: '2013-02-05T16:11:05'
published_at_gmt: '2013-02-05T16:11:05'
modified_at: '2026-03-25T18:25:16'
modified_at_gmt: '2026-03-25T18:25:16'
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
- Fractal Tree™ indexes
- MySQL
- NewSQL
- Storage Engine
- TokuDB
tag_slugs:
- fractal-tree-indexes
- mysql
- newsql
- storage-engine
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Concurrency Improvements in TokuDB v6.6 (Part 2)

Source: [Percona Blog](https://www.percona.com/blog/concurrency-improvements-in-tokudb-v6-6-part-2/)

Auteur source: [Leif.Walsh](https://www.percona.com/blog/author/leif-walsh/)

Publication: 2013-02-05T16:11:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In Part 1, we showed performance results of some of the work that’s gone in to TokuDB v6.6. In this post, we’ll take a closer look at how this happened, on the engineering side, and how to think about the performance characteristics in the new version. Background It’s easiest to think about our concurrency changes … Continued

## Structure detectee

- H2: Background
- H2: Implementation Details
- H3: Read Concurrency
- H3: Write Concurrency
