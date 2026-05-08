---
title: Using pt-online-schema-change for Purging Rows and Reclaiming Disk Space in a Single Operation
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-pt-online-schema-change-for-purging-rows-and-reclaiming-disk-space-in-a-single-operation/
  post_id: 28802
source_author:
  name: marcos.albe
  slug: marcos-albe
  url: https://www.percona.com/blog/author/marcos-albe/
  website: http://www.percona.com
published_at: '2024-07-15T15:23:27'
published_at_gmt: '2024-07-15T15:23:27'
modified_at: '2026-03-26T20:26:09'
modified_at_gmt: '2026-03-26T20:26:09'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- pt-archiver
- pt-online-schema-change
tag_slugs:
- mysql
- mysql-and-variants
- pt-archiver
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pt-online-schema-change-for-Purging-Rows-and-Reclaiming-Disk-Space-in-a-Single-Operation.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using pt-online-schema-change for Purging Rows and Reclaiming Disk Space in a Single Operation

Source: [Percona Blog](https://www.percona.com/blog/using-pt-online-schema-change-for-purging-rows-and-reclaiming-disk-space-in-a-single-operation/)

Auteur source: [marcos.albe](https://www.percona.com/blog/author/marcos-albe/)

Publication: 2024-07-15T15:23:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

You probably missed the news, but… PT-1751: Adds –where param to pt-online-schema-change This brings the possibility to perform what I would call an “inverted purge” because you are not actually purging rows from your multi-terabyte table, but rather, you copy the small percentage of rows you want to keep to a new table and then … Continued

## Images et graphiques reperes

- featured / image: [Using pt-online-schema-change for Purging Rows and Reclaiming Disk Space in a Single Operation](https://www.percona.com/wp-content/uploads/2026/03/pt-online-schema-change-for-Purging-Rows-and-Reclaiming-Disk-Space-in-a-Single-Operation.jpg)
