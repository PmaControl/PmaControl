---
title: Check for MySQL slave lag with Percona Toolkit plugin for Tungsten Replicator
source:
  name: Percona Blog
  url: https://www.percona.com/blog/check-for-mysql-slave-lag-with-percona-toolkit-plugin-for-tungsten-replicator/
  post_id: 8366
source_author:
  name: Kenny Gryp
  slug: gryp
  url: https://www.percona.com/blog/author/gryp/
  website: ''
published_at: '2014-07-09T13:28:07'
published_at_gmt: '2014-07-09T13:28:07'
modified_at: '2026-05-04T22:23:32'
modified_at_gmt: '2026-05-04T22:23:32'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
- tag:percona-toolkit:378
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- '''--plugin'
- Continuent
- Kenny Gryp
- Percona Toolkit
- pt-table-checksum
- slave lag
- Tungsten Replicator
tag_slugs:
- plugin
- continuent
- kenny-gryp
- percona-toolkit
- pt-table-checksum
- slave-lag
- tungsten-replicator
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Tungsten_favorite_sticker.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Check for MySQL slave lag with Percona Toolkit plugin for Tungsten Replicator

Source: [Percona Blog](https://www.percona.com/blog/check-for-mysql-slave-lag-with-percona-toolkit-plugin-for-tungsten-replicator/)

Auteur source: [Kenny Gryp](https://www.percona.com/blog/author/gryp/)

Publication: 2014-07-09T13:28:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A while back, I made some changes to the plugin interface for pt-online-schema-change which allows custom replication checks to be written. As I was adding this functionality, I also added the –plugin option to pt-table-checksum. This was released in Percona Toolkit 2.2.8. With these additions, I spent some time writing a plugin that allows Percona … Continued

## Structure detectee

- H2: Requirements
- H2: Preparation
- H2: Configuration
- H2: Running A Checksum
- H2: Making Schema Changes
- H2: Binlog Format & pt-online-schema-change
- H3: Be Warned
- H2: Summary

## Images et graphiques reperes

- featured / image: [Check for MySQL slave lag with Percona Toolkit plugin for Tungsten Replicator](https://www.percona.com/wp-content/uploads/2026/03/Tungsten_favorite_sticker.png)

## Auteur source

Kenny is currently MySQL Practice Manager at Percona.
