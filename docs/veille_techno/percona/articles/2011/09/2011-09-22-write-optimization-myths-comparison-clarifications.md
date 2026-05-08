---
title: 'Write Optimization: Myths, Comparison, Clarifications'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/write-optimization-myths-comparison-clarifications/
  post_id: 9596
source_author:
  name: Leif.Walsh
  slug: leif-walsh
  url: https://www.percona.com/blog/author/leif-walsh/
  website: ''
published_at: '2011-09-22T15:43:29'
published_at_gmt: '2011-09-22T15:43:29'
modified_at: '2026-03-25T18:19:19'
modified_at_gmt: '2026-03-25T18:19:19'
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
- indexes
- indexing
- InnoDB
- LSM
- MySQL
- TokuDB
- Tokutek
tag_slugs:
- fractal-tree-indexes
- indexes
- indexing
- innodb
- lsm
- mysql
- tokudb
- tokutek
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2011/09/tradeoff1.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Write Optimization: Myths, Comparison, Clarifications

Source: [Percona Blog](https://www.percona.com/blog/write-optimization-myths-comparison-clarifications/)

Auteur source: [Leif.Walsh](https://www.percona.com/blog/author/leif-walsh/)

Publication: 2011-09-22T15:43:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Some indexing structures are write optimized in that they are better than B-trees at ingesting data. Other indexing structures are read optimized in that they are better than B-trees at query time. Even within B-trees, there is a tradeoff between write performance and read performance. For example, non-clustering B-trees (such as MyISAM) are typically faster … Continued

## Structure detectee

- H3: Extreme solutions
- H3: B-trees

## Images et graphiques reperes

- content / image: [B-trees are not on the optimal insert/query tradeoff curve](https://www.percona.com/blog/wp-content/uploads/2011/09/tradeoff1.png)
  Caption: B-trees are not on the optimal insert/query tradeoff curve
