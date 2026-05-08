---
title: How Bloom Filters Work in MyRocks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-bloom-filters-work-in-myrocks/
  post_id: 26585
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2023-02-15T13:07:50'
published_at_gmt: '2023-02-15T13:07:50'
modified_at: '2026-03-26T20:30:11'
modified_at_gmt: '2026-03-26T20:30:11'
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
- Storage Engine
category_slugs:
- insight-for-dbas
- mysql
- storage-engine
tags:
- MyRocks
- MySQL
- mysql-and-variants
tag_slugs:
- myrocks
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Bloom-Filters-Work-in-MyRocks.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Bloom Filters Work in MyRocks

Source: [Percona Blog](https://www.percona.com/blog/how-bloom-filters-work-in-myrocks/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2023-02-15T13:07:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Bloom filters are an essential component of an LSM-based database engine like MyRocks. This post will illustrate through a simple example how bloom filters work in MyRocks. Why? With MyRocks/RocksDB, data is stored in a set of large SST files. When MyRocks needs to find the value associated with a given key, it uses a … Continued

## Structure detectee

- H2: Why?
- H2: How?
- H2: An example
- H2: Tuning
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [How Bloom Filters Work in MyRocks](https://www.percona.com/wp-content/uploads/2026/03/Bloom-Filters-Work-in-MyRocks.jpg)
- content / image: [pexels-marta-dzedyshko-6341419-scaled-e1676465801536-250x300.jpg](https://www.percona.com/wp-content/uploads/2026/03/pexels-marta-dzedyshko-6341419-scaled-e1676465801536-250x300.jpg)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
