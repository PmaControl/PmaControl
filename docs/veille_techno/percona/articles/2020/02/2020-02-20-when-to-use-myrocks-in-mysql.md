---
title: 'A Hidden Gem in MySQL: MyRocks'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-to-use-myrocks-in-mysql/
  post_id: 21597
source_author:
  name: Alkin Tezuysal
  slug: alkin-tezuysal
  url: https://www.percona.com/blog/author/alkin-tezuysal/
  website: https://askdbablog.wordpress.com/
published_at: '2020-02-20T16:47:58'
published_at_gmt: '2020-02-20T16:47:58'
modified_at: '2026-05-05T20:58:10'
modified_at_gmt: '2026-05-05T20:58:10'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
- PMM
- Percona Toolkit
- XtraBackup
matched_filters:
- category:mariadb:1281
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MariaDB
- MySQL
- Storage Engine
category_slugs:
- insight-for-dbas
- mariadb
- mysql
- storage-engine
tags:
- insight for DBAs
- MariaDB
- MyRocks
- MySQL
- Storage Engine
tag_slugs:
- insight-for-dbas
- mariadb
- myrocks
- mysql
- storage-engine
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/using-MyRocks-in-MySQL.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Hidden Gem in MySQL: MyRocks

Source: [Percona Blog](https://www.percona.com/blog/when-to-use-myrocks-in-mysql/)

Auteur source: [Alkin Tezuysal](https://www.percona.com/blog/author/alkin-tezuysal/)

Publication: 2020-02-20T16:47:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will share some experiences with the hidden gem in MySQL called MyRocks, a storage engine for MySQL’s famous pluggable storage engine system. MyRocks is based on RocksDB which is a fork of LevelDB. In short, it’s another key-value store based on LSM-tree, thus granting it some distinctive features compared with … Continued

## Structure detectee

- H2: Background and History
- H2: Working Dynamics of MyRocks
- H3: Writes
- H3: Reads
- H2: Tools and Utilities
- H2: Load Test and Comparison Versus InnoDB
- H3: Conclusion
- H3: References

## Images et graphiques reperes

- featured / image: [A Hidden Gem in MySQL: MyRocks](https://www.percona.com/wp-content/uploads/2026/03/using-MyRocks-in-MySQL.png)
- content / image: [using MyRocks in MySQL](https://www.percona.com/wp-content/uploads/2026/03/using-MyRocks-in-MySQL-300x168.png)
- content / image: [MyRocks in MySQL](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Write-drawing-1.png)
- content / image: [LSM Leveled Compaction](https://www.percona.com/wp-content/uploads/2026/03/Leveled-Compaction.png)
- content / image: [MyRocks in MySQL](https://www.percona.com/wp-content/uploads/2026/03/MyRocks-Read-Drawing-1.png)

## Auteur source

Alkin has extensive experience in enterprise relational databases working in various sectors for large corporations. With more than 20 years of industry experience, he has acquired skills for managing large projects from the ground up to production. For the past 10 years, he's been focusing on e-commerce, SaaS and MySQL technologies. He managed and architected database topologies for high volume sites at eBay Intl. He has several years of experience in 24X7 support and operational tasks as well as improving database systems for major companies. He has led MySQL global operations team on Tier 1/2/3 support for MySQL customers. In 2016 he has joined Percona's expert technical management team.
