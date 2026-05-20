---
title: 'Hot Indexing Part I: New Feature'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/hot-indexing-part-i-new-feature/
  post_id: 9553
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2011-04-05T19:06:38'
published_at_gmt: '2011-04-05T19:06:38'
modified_at: '2026-05-04T22:41:35'
modified_at_gmt: '2026-05-04T22:41:35'
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
- alter table
- Hot Indexing
- InnoDB
- MySQL
- TokuDB
tag_slugs:
- alter-table
- hot-indexing
- innodb
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Hot Indexing Part I: New Feature

Source: [Percona Blog](https://www.percona.com/blog/hot-indexing-part-i-new-feature/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2011-04-05T19:06:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

From 31 minutes to 2 seconds Hot Indexing Overview TokuDB v5.0 introduces several features that are new to the MySQL world. Recently, we posted on HCAD: Hot Column addition and Deletion. In this post, we talk about Hot Indexing. What happens when you try to add a new index, as follows? mysql> create index example_idx on example_tbl (example_field); 1 mysql > create index example_idx on example_tbl ( example_field ) ; In standard … Continued

## Structure detectee

- H2: From 31 minutes to 2 seconds
- H3: Hot Indexing Overview
- H3: Learning More
