---
title: On TokuMX Oplog, Tailable Cursors, and Concurrency
source:
  name: Percona Blog
  url: https://www.percona.com/blog/on-tokumx-oplog-tailable-cursors-and-concurrency/
  post_id: 9859
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-04-02T12:50:38'
published_at_gmt: '2014-04-02T12:50:38'
modified_at: '2026-03-25T18:28:22'
modified_at_gmt: '2026-03-25T18:28:22'
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
- oplog
- Replication
- tokumx
tag_slugs:
- mongodb
- oplog
- replication
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# On TokuMX Oplog, Tailable Cursors, and Concurrency

Source: [Percona Blog](https://www.percona.com/blog/on-tokumx-oplog-tailable-cursors-and-concurrency/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-04-02T12:50:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In a post last week, I described the difference in concurrency behavior between MongoDB’s oplog and TokuMX’s oplog. In short, here are the key differences: MongoDB protects access to the oplog with a database level reader/writer lock, whereas TokuMX does not. TokuMX can write data to the oplog concurrently, whereas MongoDB cannot. As a result, … Continued
