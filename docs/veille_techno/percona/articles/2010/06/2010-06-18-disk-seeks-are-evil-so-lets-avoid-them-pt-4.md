---
title: Disk seeks are evil, so let’s avoid them, pt. 4
source:
  name: Percona Blog
  url: https://www.percona.com/blog/disk-seeks-are-evil-so-lets-avoid-them-pt-4/
  post_id: 9516
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2010-06-18T21:12:49'
published_at_gmt: '2010-06-18T21:12:49'
modified_at: '2026-04-28T22:31:39'
modified_at_gmt: '2026-04-28T22:31:39'
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
- B-Tree
- disk seek
- Fractal Trees
- MySQL
- TokuDB
tag_slugs:
- b-tree
- disk-seek
- fractal-trees
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Disk seeks are evil, so let’s avoid them, pt. 4

Source: [Percona Blog](https://www.percona.com/blog/disk-seeks-are-evil-so-lets-avoid-them-pt-4/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2010-06-18T21:12:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Continuing in the theme from previous posts, I’d like to examine another case where we can eliminate all disk seeks from a MySQL operation and therefore get two orders-of-magnitude speedup. The general outline of these posts is: B-trees do insertion disk seeks. While they’re at it, they piggyback some other work on the disk … Continued
