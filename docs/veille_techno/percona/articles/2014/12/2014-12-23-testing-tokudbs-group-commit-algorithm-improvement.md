---
title: Testing TokuDB’s Group Commit Algorithm Improvement
source:
  name: Percona Blog
  url: https://www.percona.com/blog/testing-tokudbs-group-commit-algorithm-improvement/
  post_id: 9903
source_author:
  name: Joel.Epstein
  slug: ''
  url: ''
  website: ''
published_at: '2014-12-23T16:48:19'
published_at_gmt: '2014-12-23T16:48:19'
modified_at: '2026-03-25T18:29:55'
modified_at_gmt: '2026-03-25T18:29:55'
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
- Benchmarking
- MySQL
- Storage Engine
- TokuDB
tag_slugs:
- benchmarking
- mysql
- storage-engine
- tokudb
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2014/12/sysbench-update-benchmark.png
image_count: 1
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Testing TokuDB’s Group Commit Algorithm Improvement

Source: [Percona Blog](https://www.percona.com/blog/testing-tokudbs-group-commit-algorithm-improvement/)

Auteur source: [Joel.Epstein](https://www.percona.com/blog/testing-tokudbs-group-commit-algorithm-improvement/)

Publication: 2014-12-23T16:48:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The MySQL 5.6 Release has introduced some changes to how two phase commit works and is managed. In particular, the commit phase of transactions to the binary log is now serialized and this behavior is something we identified fairly immediately. We implement a group commit algorithm that needed to be altered so that TokuDB’s group … Continued

## Images et graphiques reperes

- content / graph_or_chart: [sysbench-update-benchmark](https://www.percona.com/blog/wp-content/uploads/2014/12/sysbench-update-benchmark.png)
