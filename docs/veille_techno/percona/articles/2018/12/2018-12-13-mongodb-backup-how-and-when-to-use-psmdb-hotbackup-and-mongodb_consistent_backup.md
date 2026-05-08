---
title: 'MongoDB Backup: How and When To Use PSMDB hotbackup and mongodb_consistent_backup'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-backup-how-and-when-to-use-psmdb-hotbackup-and-mongodb_consistent_backup/
  post_id: 19731
source_author:
  name: Vinodh Krishnaswamy
  slug: vinodh-krishnaswamy
  url: https://www.percona.com/blog/author/vinodh-krishnaswamy/
  website: ''
published_at: '2018-12-13T11:43:28'
published_at_gmt: '2018-12-13T11:43:28'
modified_at: '2026-03-26T20:17:22'
modified_at_gmt: '2026-03-26T20:17:22'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- XtraBackup
matched_filters:
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MongoDB
- Percona Software
category_slugs:
- insight-for-dbas
- mongodb
- percona-software
tags:
- hotbackup
- MongoDB
- mongodb_consistent_backup
- PSMDB
tag_slugs:
- hotbackup
- mongodb
- mongodb_consistent_backup
- psmdb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mongodb-backup.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB Backup: How and When To Use PSMDB hotbackup and mongodb_consistent_backup

Source: [Percona Blog](https://www.percona.com/blog/mongodb-backup-how-and-when-to-use-psmdb-hotbackup-and-mongodb_consistent_backup/)

Auteur source: [Vinodh Krishnaswamy](https://www.percona.com/blog/author/vinodh-krishnaswamy/)

Publication: 2018-12-13T11:43:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We have many backup methods to backup a MongoDB database using native mongodump or external tools. However, in this article, we’ll take a look at the backup tools offered by Percona, keeping in mind the restoration scenarios for MongoDB replicaSet and Sharded Cluster environments. We’ll explore how and when to use the tool mongodb-consistent-backup from Percona … Continued

## Structure detectee

- H2: Backup is done – What about Restore?
- H2: Hot backup for both replicaset and Sharded cluster
- H3: For sharding cluster:
- H3: For replicaSet:
- H2: Hot but Cold backup
- H2: Conclusion
- H2: REFERENCES :

## Images et graphiques reperes

- featured / image: [MongoDB Backup: How and When To Use PSMDB hotbackup and mongodb_consistent_backup](https://www.percona.com/wp-content/uploads/2026/03/mongodb-backup.jpg)
- content / image: [mongodb backup](https://www.percona.com/wp-content/uploads/2026/03/mongodb-backup-300x200.jpg)

## Auteur source

Vinodh Krishnaswamy is a member of Support Team! Prior to joining Percona, he worked as a MySQL and MongoDB DBA in companies such as iGate, Datavail, and Sify Ltd. He is a trainer and has provided training programs on MySQL and MongoDB. He enjoys writing Shell script and loves driving, reading books, and playing table tennis.
