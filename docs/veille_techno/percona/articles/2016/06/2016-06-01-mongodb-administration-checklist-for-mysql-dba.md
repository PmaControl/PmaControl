---
title: MongoDB Administration Checklist for MySQL DBAs
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-administration-checklist-for-mysql-dba/
  post_id: 9384
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2016-06-01T17:50:20'
published_at_gmt: '2016-06-01T17:50:20'
modified_at: '2026-03-26T20:22:36'
modified_at_gmt: '2026-03-26T20:22:36'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-toolkit
- search:percona-xtrabackup
- search:xtrabackup
categories:
- MongoDB
- MySQL
category_slugs:
- mongodb
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_a_wide_tree_leaf_made_out_of_computer_parts_green__d76a90ff-423c-4b6e-a83a-d2a2e5ddf4db.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB Administration Checklist for MySQL DBAs

Source: [Percona Blog](https://www.percona.com/blog/mongodb-administration-checklist-for-mysql-dba/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2016-06-01T17:50:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I discuss a MongoDB administration checklist designed to help MySQL DBAs. If you are MySQL DBA, starting MongoDB administration is not always an easy transition. Although most of the concepts and even implementation are similar, the commands are different. The following table outlines the typical MySQL concepts and DBA tasks (on the … Continued

## Structure detectee

- H3: Architecture: Basic Concepts
- H4: Replication:
- H4: Sharding:
- H3: Day-to-day operations
- H4: MySQL: SELECT
- H4: MongoDB: FIND
- H4: MySQL: Schema
- H4: MongoDB: Flexible Schema
- H4: MySQL: Config file
- H4: MongoDB:
- H4: MySQL: databases
- H4: MongoDB: Databases
- H4: MySQL: Storage Engines
- H4: MongoDB: Storage Engines
- H4: MySQL: Processlist
- H4: MongoDB: CurrentOp()
- H4: MySQL: Grants
- H4: MongoDB: createUser
- H4: MySQL: Index
- H4: MongoDB: Index
- H4: MySQL: Add Index
- H4: MongoDB: Create Index
- H4: MySQL: Explain
- H4: MongoDB: Explain
- H4: MySQL: Alter Table
- H4: MongoDB: Flexible schema
- H4: MySQL: Slow Query Log
- H4: MongoDB: Profiling
- H4: MySQL: Percona Toolkit
- H4: MongoDB: Mtools
- H4: MySQL 5.7: GIS
- H4: MongoDB 3.2: GIS
- H4: MySQL: Backup
- H4: MongoDB: Backup

## Images et graphiques reperes

- featured / image: [MongoDB Administration Checklist for MySQL DBAs](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_a_wide_tree_leaf_made_out_of_computer_parts_green__d76a90ff-423c-4b6e-a83a-d2a2e5ddf4db.png)
- content / image: [MySQL_MongoDB_replication](https://www.percona.com/wp-content/uploads/2026/03/MySQL_MongoDB_replication-1.png)
- content / image: [MySQL_MongoDB_sharding](https://www.percona.com/wp-content/uploads/2026/03/MySQL_MongoDB_sharding.png)
- content / image: [mlogvis_example](https://www.percona.com/wp-content/uploads/2026/03/mlogvis_example-300x118.png)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
