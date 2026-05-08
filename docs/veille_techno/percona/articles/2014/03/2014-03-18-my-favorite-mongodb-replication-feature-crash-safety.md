---
title: 'My Favorite MongoDB Replication Feature: Crash Safety'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/my-favorite-mongodb-replication-feature-crash-safety/
  post_id: 3198
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-03-18T12:48:45'
published_at_gmt: '2014-03-18T12:48:45'
modified_at: '2026-03-23T22:09:16'
modified_at_gmt: '2026-03-23T22:09:16'
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
- MongoDB
- MySQL
- Replication
- TokuDB
- tokumx
tag_slugs:
- mongodb
- mysql
- replication
- tokudb
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# My Favorite MongoDB Replication Feature: Crash Safety

Source: [Percona Blog](https://www.percona.com/blog/my-favorite-mongodb-replication-feature-crash-safety/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-03-18T12:48:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

At an extremely high level, replication in MongoDB and MySQL are similar. Both databases have exactly one machine, the primary (or master), that accepts writes from clients. With a single transaction (or atomic operation, in MongoDB’s case), the tables and oplog (or binary log in MySQL) are modified to reflect the change. The log captures … Continued
