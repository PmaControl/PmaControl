---
title: MySQL Schema Agility on SSDs
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-schema-agility-on-ssds/
  post_id: 9719
source_author:
  name: Tim.Callaghan
  slug: tim-callaghan
  url: https://www.percona.com/blog/author/tim-callaghan/
  website: ''
published_at: '2012-09-29T13:49:38'
published_at_gmt: '2012-09-29T13:49:38'
modified_at: '2026-03-25T18:24:13'
modified_at_gmt: '2026-03-25T18:24:13'
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
- hot schema changes
- InnoDB
- MySQL
- TokuDB
tag_slugs:
- benchmarking
- hot-schema-changes
- innodb
- mysql
- tokudb
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2012/09/tmc.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Schema Agility on SSDs

Source: [Percona Blog](https://www.percona.com/blog/mysql-schema-agility-on-ssds/)

Auteur source: [Tim.Callaghan](https://www.percona.com/blog/author/tim-callaghan/)

Publication: 2012-09-29T13:49:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

TokuDB v6.5 adds the ability to expand certain column types without downtime. Users can now enlarge char, varchar, varbinary, and integer columns with no interruption to insert/update/delete statements on the altered table. Prior to this feature, enlarging one of these column types required a full table rebuild. InnoDB blocks all insert/update/delete operations to a table … Continued

## Images et graphiques reperes

- content / image: [tmc.png](https://www.percona.com/blog/wp-content/uploads/2012/09/tmc.png)
