---
title: 'Write Optimization: Myths, Comparison, Clarifications, Part 2'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/write-optimization-myths-comparison-clarifications-part-2/
  post_id: 9604
source_author:
  name: Leif.Walsh
  slug: leif-walsh
  url: https://www.percona.com/blog/author/leif-walsh/
  website: ''
published_at: '2011-10-04T15:04:28'
published_at_gmt: '2011-10-04T15:04:28'
modified_at: '2026-03-25T18:19:38'
modified_at_gmt: '2026-03-25T18:19:38'
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
- Big Data
- COLA
- Fractal Tree™ indexes
- indexes
- indexing
- InnoDB
- LSM
- MySQL
- TokuDB
- Tokutek
tag_slugs:
- big-data
- cola
- fractal-tree-indexes
- indexes
- indexing
- innodb
- lsm
- mysql
- tokudb
- tokutek
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2011/10/simple-cola1.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Write Optimization: Myths, Comparison, Clarifications, Part 2

Source: [Percona Blog](https://www.percona.com/blog/write-optimization-myths-comparison-clarifications-part-2/)

Auteur source: [Leif.Walsh](https://www.percona.com/blog/author/leif-walsh/)

Publication: 2011-10-04T15:04:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my last post, we talked about the read/write tradeoff of indexing data structures, and some ways that people augment B-trees in order to get better write performance. We also talked about the significant drawbacks of each method, and I promised to show some more fundamental approaches. We had two “workload-based” techniques: inserting in sequential … Continued

## Structure detectee

- H3: Two Great Tastes: Log-Structured Merge trees (LSMs)
- H3: Have Your Cake and Eat It Too: COLAs
- H3: Write Optimization is the Best Read Optimization

## Images et graphiques reperes

- content / image: [An LSM Tree with 4 levels](https://www.percona.com/blog/wp-content/uploads/2011/10/simple-cola1.png)
  Caption: An LSM Tree with 4 levels
- content / image: [COLAs are on the optimal read/write tradeoff curve](https://www.percona.com/blog/wp-content/uploads/2011/10/tradeoff2.png)
  Caption: COLAs are on the optimal read/write tradeoff curve
