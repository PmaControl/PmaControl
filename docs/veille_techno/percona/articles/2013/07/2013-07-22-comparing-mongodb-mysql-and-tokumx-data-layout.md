---
title: Comparing MongoDB, MySQL, and TokuMX Data Layout
source:
  name: Percona Blog
  url: https://www.percona.com/blog/comparing-mongodb-mysql-and-tokumx-data-layout/
  post_id: 9800
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2013-07-22T17:01:14'
published_at_gmt: '2013-07-22T17:01:14'
modified_at: '2026-05-04T22:42:55'
modified_at_gmt: '2026-05-04T22:42:55'
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
- InnoDB
- MongoDB
- MyISAM
- MySQL
- Storage Engine
- TokuDB
- tokumx
tag_slugs:
- innodb
- mongodb
- myisam
- mysql
- storage-engine
- tokudb
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Comparing MongoDB, MySQL, and TokuMX Data Layout

Source: [Percona Blog](https://www.percona.com/blog/comparing-mongodb-mysql-and-tokumx-data-layout/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2013-07-22T17:01:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A lot is said about the differences in the data between MySQL and MongoDB. Things such as “MongoDB is document based”, “MySQL is relational”, “InnoDB has a clustering key”, etc.. Some may wonder how TokuDB, our MySQL storage engine, and TokuMX, our MongoDB product, fit in with these data layouts. I could not find anything … Continued

## Structure detectee

- H2: What do the collections and tables look like?
- H2: What is a row identifier?
- H2: How do secondary indexes work (note, not talking primary keys yet)?
- H2: How is data laid out?
- H2: Summary
