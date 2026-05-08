---
title: MySQL InnoDB Sorted Index Builds
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-innodb-sorted-index-builds/
  post_id: 20281
source_author:
  name: Satya Bodapati
  slug: satya-bodapati
  url: https://www.percona.com/blog/author/satya-bodapati/
  website: ''
published_at: '2019-05-08T12:21:46'
published_at_gmt: '2019-05-08T12:21:46'
modified_at: '2026-05-05T16:21:03'
modified_at_gmt: '2026-05-05T16:21:03'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
- Percona Software
category_slugs:
- insight-for-developers
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/bulk_load_77.png
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL InnoDB Sorted Index Builds

Source: [Percona Blog](https://www.percona.com/blog/mysql-innodb-sorted-index-builds/)

Auteur source: [Satya Bodapati](https://www.percona.com/blog/author/satya-bodapati/)

Publication: 2019-05-08T12:21:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It’s not essential to understand how MySQL® and Percona Server for MySQL build indexes. However, if you have an understanding of the processing, it could help when you want to reserve an appropriate amount of space for data inserts. From MySQL 5.7, developers changed the way they built secondary indexes for InnoDB, applying a bottom-up … Continued

## Structure detectee

- H2: Index building process
- H2: Use cases
- H2: Algorithm
- H2: Walk through of building an index, bottom-up
- H3: Initial insert phase
- H3: Index building as pages become filled
- H2: Index fill factor
- H2: Advantages of Sorted Index Build
- H2: Disadvantages:

## Images et graphiques reperes

- featured / image: [MySQL InnoDB Sorted Index Builds](https://www.percona.com/wp-content/uploads/2026/03/bulk_load_77.png)
- content / image: [1-6.png](https://www.percona.com/wp-content/uploads/2026/03/1-6.png)
- content / image: [2-5.png](https://www.percona.com/wp-content/uploads/2026/03/2-5.png)
- content / image: [bulk_load_44.png](https://www.percona.com/wp-content/uploads/2026/03/bulk_load_44.png)
- content / image: [bulk_load_55_66.png](https://www.percona.com/wp-content/uploads/2026/03/bulk_load_55_66.png)
- content / image: [bulk_load_77-1.png](https://www.percona.com/wp-content/uploads/2026/03/bulk_load_77-1.png)
- content / image: [bulk_load_88_99-1.png](https://www.percona.com/wp-content/uploads/2026/03/bulk_load_88_99-1.png)
- content / image: [Bulk_Load-Page-5.png](https://www.percona.com/wp-content/uploads/2026/03/Bulk_Load-Page-5.png)

## Auteur source

Satya works with Percona Server Engineering team. He is responsible for Percona Server for MySQL, XtraBackup features, bug fixes, etc. He has an overall experience of 18 years in MySQL. He joined Percona in 2018. Before joining Percona, Satya worked with the InnoDB Development team at Oracle for 6 years.
