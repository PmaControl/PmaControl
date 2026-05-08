---
title: Updating/Deleting Rows From Clickhouse (Part 2)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/updating-deleting-rows-from-clickhouse-part-2/
  post_id: 17850
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2018-01-16T19:44:23'
published_at_gmt: '2018-01-16T19:44:23'
modified_at: '2026-05-05T18:55:56'
modified_at_gmt: '2026-05-05T18:55:56'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- ClickHouse
- database
- deleting
- rows
- updating
tag_slugs:
- clickhouse
- database
- deleting
- rows
- updating
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Clickhouse.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Updating/Deleting Rows From Clickhouse (Part 2)

Source: [Percona Blog](https://www.percona.com/blog/updating-deleting-rows-from-clickhouse-part-2/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2018-01-16T19:44:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, we’ll look at updating and deleting rows with ClickHouse. It’s the second of two parts. In the first part of this post, we described the high-level overview of implementing incremental refresh on a ClickHouse table as an alternative support for UPDATE/DELETE. In this part, we will show you the actual steps and … Continued

## Structure detectee

- H3: Prepare Changelog Table
- H3: Create ClickHouse Table
- H3: Run Changelog Capture
- H3: Full Table Import
- H3: Incremental Refresh

## Images et graphiques reperes

- featured / image: [Updating/Deleting Rows From Clickhouse (Part 2)](https://www.percona.com/wp-content/uploads/2026/03/Clickhouse.jpg)
- content / image: [ClickHouse](https://www.percona.com/wp-content/uploads/2026/03/Clickhouse-300x266.jpg)

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
