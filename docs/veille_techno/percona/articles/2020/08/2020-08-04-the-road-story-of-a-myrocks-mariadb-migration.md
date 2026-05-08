---
title: The Road Story of a MyRocks/MariaDB Migration
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-road-story-of-a-myrocks-mariadb-migration/
  post_id: 22827
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2020-08-04T14:07:49'
published_at_gmt: '2020-08-04T14:07:49'
modified_at: '2026-05-05T16:31:23'
modified_at_gmt: '2026-05-05T16:31:23'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
categories:
- MariaDB
- MySQL
- Storage Engine
category_slugs:
- mariadb
- mysql
- storage-engine
tags:
- MariaDB
- MySQL
- mysql-and-variants
- Storage Engine
tag_slugs:
- mariadb
- mysql
- mysql-and-variants
- storage-engine
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/myrocks-migration.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The Road Story of a MyRocks/MariaDB Migration

Source: [Percona Blog](https://www.percona.com/blog/the-road-story-of-a-myrocks-mariadb-migration/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2020-08-04T14:07:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post has been written in collaboration with Nicolas Modalvsky of e-planning. Nicolas and I recently worked together on a tuning engagement involving MyRocks on MariaDB. While it is easy to find online articles and posts about InnoDB performance, finding information about MyRocks tuning is more difficult. Both storage engines are well documented but what … Continued

## Structure detectee

- H2: Context
- H2: First Attempt with MyRocks
- H2: The First Round of Tuning
- H2: Improved Compression
- H2: High Memory Usage
- H2: Increased Read Load Over Time
- H2: Lazy Deletions with TTL
- H2: MyRocks Vs InnoDB
- H2: Startup and Shutdown Times
- H2: Migration in Production
- H2: Two Months in Production
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [The Road Story of a MyRocks/MariaDB Migration](https://www.percona.com/wp-content/uploads/2026/03/myrocks-migration.png)
- content / image: [myrocks migration](https://www.percona.com/wp-content/uploads/2026/03/myrocks-migration-300x157.png)
- content / image: [Disk write bandwidth comparison, MyRocks and InnoDB](https://www.percona.com/wp-content/uploads/2026/03/Disk-write-bandwidth.png)
  Caption: Disk write bandwidth comparison, MyRocks and InnoDB
- content / image: [Disk read bandwidth comparison, MyRocks and InnoDB](https://www.percona.com/wp-content/uploads/2026/03/Disk-read-bandwidth.png)
  Caption: Disk read bandwidth comparison, MyRocks and InnoDB
- content / image: [Total CPU usage comparison, MyRocks and InnoDB](https://www.percona.com/wp-content/uploads/2026/03/Total-CPU-usage.png)
  Caption: Total CPU usage comparison, MyRocks and InnoDB

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
