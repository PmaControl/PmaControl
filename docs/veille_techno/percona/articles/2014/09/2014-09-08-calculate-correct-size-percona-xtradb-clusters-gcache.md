---
title: How to calculate the correct size of Percona XtraDB Cluster’s gcache
source:
  name: Percona Blog
  url: https://www.percona.com/blog/calculate-correct-size-percona-xtradb-clusters-gcache/
  post_id: 8518
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2014-09-08T14:12:41'
published_at_gmt: '2014-09-08T14:12:41'
modified_at: '2026-05-04T22:26:13'
modified_at_gmt: '2026-05-04T22:26:13'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
- Percona Services
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-services
- percona-software
tags:
- galera.cache
- Gcache
- Percona XtraDB Cluster
tag_slugs:
- galera-cache
- gcache
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Working-With-Large-PostgreSQL-Databases.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to calculate the correct size of Percona XtraDB Cluster’s gcache

Source: [Percona Blog](https://www.percona.com/blog/calculate-correct-size-percona-xtradb-clusters-gcache/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2014-09-08T14:12:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When a write query is sent to Percona XtraDB Cluster all the nodes store the writeset on a file called gcache. By default the name of that file is galera.cache and it is stored in the MySQL datadir. This is a very important file, and as usual with the most important variables in MySQL, the … Continued

## Images et graphiques reperes

- featured / image: [How to calculate the correct size of Percona XtraDB Cluster’s gcache](https://www.percona.com/wp-content/uploads/2026/03/Working-With-Large-PostgreSQL-Databases.png)
- content / image: [How to calculate the correct size of Percona XtraDB Cluster's gcache](https://www.percona.com/wp-content/uploads/2026/03/XtraDB-Cluster1.jpg)

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
