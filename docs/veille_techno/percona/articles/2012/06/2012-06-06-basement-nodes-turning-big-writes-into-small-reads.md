---
title: 'Basement Nodes: Turning Big Writes into Small Reads'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/basement-nodes-turning-big-writes-into-small-reads/
  post_id: 9681
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2012-06-06T15:13:04'
published_at_gmt: '2012-06-06T15:13:04'
modified_at: '2026-03-25T18:22:24'
modified_at_gmt: '2026-03-25T18:22:24'
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
- Fractal Tree® Indexing
- MySQL
- NewSQL
- Storage Engine
- TokuDB
- Tokutek
tag_slugs:
- big-data
- fractal-tree-indexing
- mysql
- newsql
- storage-engine
- tokudb
- tokutek
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Basement Nodes: Turning Big Writes into Small Reads

Source: [Percona Blog](https://www.percona.com/blog/basement-nodes-turning-big-writes-into-small-reads/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2012-06-06T15:13:04

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Executive Summary Fast indexing requires the leaves of a Fractal Tree® Index to be big. But some queries require the leaves to be small in order to get any reasonable performance. Basements nodes are our way to achieve these conflicting goals, and here I’ll explain how. Big Leaves On many occasions, we at Tokutek have … Continued

## Structure detectee

- H2: Executive Summary
- H2: Big Leaves
- H2: What about reads?
- H2: Picking a basement-node size
