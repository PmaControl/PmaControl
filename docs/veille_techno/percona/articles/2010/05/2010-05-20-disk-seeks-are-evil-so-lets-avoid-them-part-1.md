---
title: Disk Seeks are Evil, so Let’s Avoid Them, Part 1
source:
  name: Percona Blog
  url: https://www.percona.com/blog/disk-seeks-are-evil-so-lets-avoid-them-part-1/
  post_id: 9511
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2010-05-20T19:42:21'
published_at_gmt: '2010-05-20T19:42:21'
modified_at: '2026-03-25T18:14:33'
modified_at_gmt: '2026-03-25T18:14:33'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2010/05/simple-fractal-tree-300x220.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Disk Seeks are Evil, so Let’s Avoid Them, Part 1

Source: [Percona Blog](https://www.percona.com/blog/disk-seeks-are-evil-so-lets-avoid-them-part-1/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2010-05-20T19:42:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Disk seeks are expensive. Typically, a disk can perform no more than a few hundred seeks per second. So, any database operation that induces a disk seek is going to be slow, perhaps unacceptably slow. Adding disks can sometimes help performance, but that approach is expensive, adds complexity, and anyhow minimizing the disk seeks helps … Continued

## Images et graphiques reperes

- content / image: [Simple Fractal Tree](https://www.percona.com/blog/wp-content/uploads/2010/05/simple-fractal-tree-300x220.png)
  Caption: Simple Fractal Tree
