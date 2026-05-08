---
title: 'Announcing TokuDB v5.2: Improved Multi-Client Scaling and Faster Queries'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/announcing-tokudb-v5-2-improved-multi-client-scaling-and-faster-queries/
  post_id: 9631
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2012-01-19T16:26:00'
published_at_gmt: '2012-01-19T16:26:00'
modified_at: '2026-04-28T22:40:09'
modified_at_gmt: '2026-04-28T22:40:09'
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
- Announcement
- compression
- Fractal Tree™ indexes
- hot schema changes
- MySQL
- NewSQL
- Performance
- TokuDB
- Tokutek
tag_slugs:
- announcement
- compression
- fractal-tree-indexes
- hot-schema-changes
- mysql
- newsql
- performance
- tokudb
- tokutek
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2012/01/SysBench.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Announcing TokuDB v5.2: Improved Multi-Client Scaling and Faster Queries

Source: [Percona Blog](https://www.percona.com/blog/announcing-tokudb-v5-2-improved-multi-client-scaling-and-faster-queries/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2012-01-19T16:26:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

TokuDB® v5.2, the latest version of Tokutek’s flagship storage engine for MySQL and MariaDB, is now available. This version offers performance enhancements over previous releases, especially for multi-client scale up and point queries, and extends the cases where ALTER TABLE is non-blocking, in particular adding Hot Column Rename. TokuDB v5.2 maintains all our established advantages: … Continued

## Structure detectee

- H2: Multi-client workloads
- H3: SysBench
- H3: TPCC
- H2: Other key improvements
- H2: Summary
- H3: Appendix – Configuration Details

## Images et graphiques reperes

- content / image: [SysBench.png](https://www.percona.com/blog/wp-content/uploads/2012/01/SysBench.png)
- content / image: [TPCC-5000W.png](https://www.percona.com/blog/wp-content/uploads/2012/01/TPCC-5000W.png)
