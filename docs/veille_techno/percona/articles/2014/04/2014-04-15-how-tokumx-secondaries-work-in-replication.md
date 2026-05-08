---
title: How TokuMX Secondaries Work in Replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-tokumx-secondaries-work-in-replication/
  post_id: 9862
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-04-15T14:19:34'
published_at_gmt: '2014-04-15T14:19:34'
modified_at: '2026-03-25T18:28:30'
modified_at_gmt: '2026-03-25T18:28:30'
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
- tokumx
tag_slugs:
- mongodb
- mysql
- replication
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How TokuMX Secondaries Work in Replication

Source: [Percona Blog](https://www.percona.com/blog/how-tokumx-secondaries-work-in-replication/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-04-15T14:19:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As I’ve mentioned in previous posts, TokuMX replication differs quite a bit from MongoDB’s replication. The differences are large enough such that we’ve completely redone some of MongoDB’s existing algorithms. One such area is how secondaries apply oplog data from a primary. In this post, I’ll explain how. In designing how secondaries apply oplog data, … Continued
