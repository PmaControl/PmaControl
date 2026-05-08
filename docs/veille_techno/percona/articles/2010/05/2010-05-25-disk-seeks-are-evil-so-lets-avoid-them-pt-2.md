---
title: Disk seeks are evil, so let’s avoid them, pt. 2
source:
  name: Percona Blog
  url: https://www.percona.com/blog/disk-seeks-are-evil-so-lets-avoid-them-pt-2/
  post_id: 9513
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2010-05-25T20:23:26'
published_at_gmt: '2010-05-25T20:23:26'
modified_at: '2026-04-28T22:25:55'
modified_at_gmt: '2026-04-28T22:25:55'
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
- Fractal Trees
- MySQL
- TokuDB
tag_slugs:
- fractal-trees
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Disk seeks are evil, so let’s avoid them, pt. 2

Source: [Percona Blog](https://www.percona.com/blog/disk-seeks-are-evil-so-lets-avoid-them-pt-2/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2010-05-25T20:23:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In part 1, I discussed why having many disk seeks are bad (they slow down performance), and how fractal tree data structures minimize disk seeks on ad-hoc insertions, whereas B-trees practically guarantee that disk seeks are performed on ad-hoc insertions. As a result, fractal tree data structures can insert data up to two orders of … Continued
